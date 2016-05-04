<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$table_rows = array();

// WARNING. Discovered all SLAs, but polled only 'active'
$sql = "SELECT * FROM `slas` LEFT JOIN `slas-state` USING (`sla_id`) WHERE `device_id` = ? AND `deleted` = 0 AND `sla_status` = 'active';";
foreach (dbFetchRows($sql, array($device['device_id'])) as $entry)
{
  if (!isset($entry['sla_mib'])) { $entry['sla_mib'] = 'CISCO-RTTMON-MIB'; } // CLEANME, remove in r7500, but not before CE 0.16.1

  $index = $entry['sla_index'];
  $mib_lower = strtolower($entry['sla_mib']);
  if ($mib_lower != 'cisco-rttmon-mib')
  {
    // Use 'owner.index' as index, because all except Cisco use this!
    $index = $entry['sla_owner'] . '.' . $index;
  }

  $sla_db[$mib_lower][$index] = $entry;
}

print_cli_data_field("MIBs", 2);

foreach (array_keys($sla_db) as $mib_lower)
{
  $file = $config['install_dir']."/includes/polling/slas/".$mib_lower.".inc.php";

  if (is_file($file))
  {
    $cache_sla = array();

    include($file);
  } else {
    continue;
  }
  if (OBS_DEBUG > 1) { print_vars($cache_sla); }

  $sla_polled_time = time(); // Store polled time for current MIB

  foreach ($sla_db[$mib_lower] as $sla)
  {

    $rrd_index    = $mib_lower . '-' . $sla['sla_index'];
    if ($sla['sla_owner'])
    {
      // Add owner name to rrd file if not empty
      $rrd_index  .= '-' . $sla['sla_owner'];
    }
    $rrd_filename = "sla-" . $rrd_index . ".rrd";
    $rrd_ds       = "DS:rtt:GAUGE:600:0:300000";

    $index = $sla['sla_index'];
    if ($mib_lower != 'cisco-rttmon-mib')
    {
      // Use 'owner.index' as index, because all except Cisco use this!
      $index = $sla['sla_owner'] . '.' . $index;
    }

    if (isset($cache_sla[$mib_lower][$index]))
    {
      $sla_state = $cache_sla[$mib_lower][$index];

      //echo($sla_state['rtt_value'] . 'ms at ' . format_unixtime($sla_state['rtt_unixtime']) . ', Sense code - "' . $sla_state['rtt_sense'] . '"');

      $rrd_value  = $sla_state['rtt_value'];

      // Check limits
      $rtt_loss_percent = 100 * $sla_state['rtt_loss'] / ($sla_state['rtt_success'] + $sla_state['rtt_loss']);
      $limit_msg = ''; // FIXME, Later use 'rtt_reason' state entry
      if ($sla_state['rtt_event'] == 'ok' || $sla_state['rtt_event'] == 'warning')
      {
        if (is_numeric($sla_state['rtt_value']) && is_numeric($sla['sla_limit_high']))
        {
          if ($sla_state['rtt_value'] >= $sla['sla_limit_high'])
          {
            $limit_msg = ', Timeout exceeded';
            $sla_state['rtt_event'] = 'alert';
          }
          else if ($sla_state['rtt_value'] >= $sla['sla_limit_high_warn'])
          {
            $limit_msg = ', Threshold exceeded';
            $sla_state['rtt_event'] = 'warning';
          }
        }
        if ($sla_state['rtt_event'] == 'ok' && $rtt_loss_percent >= 50)
        {
          $limit_msg = ', Probes loss >= 50%';
          $sla_state['rtt_event'] = 'warning'; // Set to warning, because alert only on full SLA down
        }
      }

      // Last change time
      if (empty($sla['rtt_last_change']))
      {
        // If last change never set, use current time
        $sla['rtt_last_change'] = $sla_polled_time;
      }
      if (($sla['rtt_sense'] != $sla_state['rtt_sense']) ||
          ($sla['rtt_event'] != $sla_state['rtt_event']))
      {
        // SLA sense changed, log and set rtt_last_change
        $sla_state['rtt_last_change'] = $sla_polled_time;
        if ($sla['rtt_sense']) // Log only if old sense not empty
        {
          log_event('SLA changed: [#'.$index.', '.$sla['sla_tag'].'] ' . $sla['rtt_sense'] . ' -> ' . $sla_state['rtt_sense'] . ' (value: '.$sla_state['rtt_value'].'ms, event: '.$sla_state['rtt_event'].$limit_msg.')', $device, 'sla', $sla['sla_id'], 'warning');
        }
      } else {
        // If sense not changed, leave old last_change
        $sla_state['rtt_last_change'] = $sla['rtt_last_change'];
      }

      // Compatability with old code
      if (empty($sla['sla_graph']))
      {
        if (stripos($sla['rtt_type'], 'jitter') !== FALSE)
        {
          $sla['sla_graph'] = "jitter";
        } else {
          $sla['sla_graph'] = "echo";
        }
      }

      switch ($sla['sla_graph'])
      {
        case 'jitter':
          $rrd_filename = 'sla_jitter-' . $rrd_index . '.rrd';
          $rrd_ds      .= ' DS:rtt_minimum:GAUGE:600:0:300000 DS:rtt_maximum:GAUGE:600:0:300000 DS:rtt_success:GAUGE:600:0:300000 DS:rtt_loss:GAUGE:600:0:300000';
          if (is_numeric($sla_state['rtt_success']))
          {
            $rrd_value .= ':'.$sla_state['rtt_minimum'].':'.$sla_state['rtt_maximum'].':'.$sla_state['rtt_success'].':'.$sla_state['rtt_loss'];
          } else {
            $rrd_value .= ':U:U:U:U';
          }
          //var_dump($rrd_ds);
          //$graphs['sla-'.$sla['rtt_type']] = TRUE;

          // CLEANME, remove in r7500, but not before CE 0.16.1
          $old_rrd  = $config['rrd_dir'] . '/'.$device['hostname'].'/sla_jitter-'.$sla['sla_index'].'.rrd';
          $new_rrd  = $config['rrd_dir'] . '/'.$device['hostname'].'/sla_jitter-'.$rrd_index.'.rrd';
          if (is_file($old_rrd) && !is_file($new_rrd)) { rename($old_rrd, $new_rrd); print_warning('Moved RRD'); }
          break;
        case 'echo':
        default:
          //$graphs['sla'] = TRUE;

          // CLEANME, remove in r7500, but not before CE 0.16.1
          $old_rrd  = $config['rrd_dir'] . '/'.$device['hostname'].'/sla-'.$sla['sla_index'].'.rrd';
          $new_rrd  = $config['rrd_dir'] . '/'.$device['hostname'].'/sla-'.$rrd_index.'.rrd';
          if (is_file($old_rrd) && !is_file($new_rrd)) { rename($old_rrd, $new_rrd); print_warning('Moved RRD'); }
      }

      // Update SQL State
      if (is_numeric($sla['rtt_unixtime']))
      {
        dbUpdate($sla_state, 'slas-state', '`sla_id` = ?', array($sla['sla_id']));
      } else {
        $sla_state['sla_id'] = $sla['sla_id'];
        dbInsert($sla_state, 'slas-state');
      }

      // Check alerts
      $metrics = array();
      $metrics['rtt_value']        = $sla_state['rtt_value'];
      $metrics['rtt_sense']        = $sla_state['rtt_sense'];
      $metrics['rtt_sense_uptime'] = $sla_polled_time - $sla_state['rtt_last_change'];
      $metrics['rtt_event']        = $sla_state['rtt_event'];
      $metrics['rtt_minimum']      = $sla_state['rtt_minimum'];
      $metrics['rtt_maximum']      = $sla_state['rtt_maximum'];
      $metrics['rtt_success']      = $sla_state['rtt_success'];
      $metrics['rtt_loss']         = $sla_state['rtt_loss'];
      $metrics['rtt_loss_percent'] = $rtt_loss_percent;

      check_entity('sla', $sla, $metrics);

      //echo("SLA " . $sla['sla_index'] . ": " . $sla['rtt_type'] . " " . $sla['sla_owner'] . " " . $sla['sla_tag']. "... ");
      //echo($sla_state['rtt_value'] . 'ms at ' . format_unixtime($sla_state['rtt_unixtime']) . ', Sense code - "' . $sla_state['rtt_sense'] . '"');

      $table_row = array();
      $table_row[] = "SLA ".$sla['sla_index'];
      $table_row[] = $sla['sla_mib'];
      $table_row[] = $sla['rtt_type'];
      $table_row[] = $sla['sla_owner'];
      $table_row[] = $sla['sla_tag'];
      $table_row[] = $sla_state['rtt_sense'];
      $table_row[] = $sla_state['rtt_value']."ms";
      $table_rows[] = $table_row;
      unset($table_row);

    } else {
      echo("NaN");
      $rrd_value = 'U';
    }

    rrdtool_create($device, $rrd_filename, $rrd_ds);
    rrdtool_update($device, $rrd_filename, "N:".$rrd_value);

    unset($rrd_ds, $rrd_value, $rrd_filename);

  }
}

echo(PHP_EOL);

$headers = array('%WLabel%n', '%WMIB%n', '%WType%n', '%WOwner%n', '%WTag%n', '%WSense%n', '%WResponse%n');
print_cli_table($table_rows, $headers);

// EOF
