<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

/// FIXME. Make this module generic, not only cisco-like

if (is_device_mib($device, 'CISCO-RTTMON-MIB'))
{
  echo("SLAs : ");

  $sla_oids = array(
    'jitter'     => array('rttMonLatestJitterOperRTTMin', 'rttMonLatestJitterOperRTTMax', 'rttMonLatestJitterOperNumOfRTT', 'rttMonLatestJitterOperPacketLossSD', 'rttMonLatestJitterOperPacketLossDS'),
    'icmpjitter' => array('rttMonLatestIcmpJitterRTTMin', 'rttMonLatestIcmpJitterRTTMax', 'rttMonLatestIcmpJitterNumRTT', 'rttMonLatestIcmpJitterPktLoss'),
  );

  // WARNING. Discovered all SLAs, but polled only 'active'
  $sla_db = dbFetchRows("SELECT * FROM `slas` LEFT JOIN `slas-state` USING (`sla_id`) WHERE `device_id` = ? AND `deleted` = 0 AND `sla_status` = 'active';", array($device['device_id']));
  
  if (count($sla_db))
  {
    $sla_poll = snmpwalk_cache_multi_oid($device, "rttMonLatestRttOperEntry", array(), "CISCO-RTTMON-MIB", mib_dirs('cisco'));
    foreach (dbFetchColumn("SELECT DISTINCT `rtt_type` FROM `slas` WHERE `device_id` = ? AND `rtt_type` != ? AND `deleted` = 0 AND `sla_status` = 'active';", array($device['device_id'], 'echo')) as $rtt_type)
    {
      switch ($rtt_type)
      {
        case 'jitter': // Additional data for Jitter
          $sla_poll = snmpwalk_cache_multi_oid($device, "rttMonLatestJitterOperEntry", $sla_poll, "CISCO-RTTMON-MIB", mib_dirs('cisco'));
          break;
        case 'icmpjitter': // Additional data for ICMP jitter
          $sla_poll = snmpwalk_cache_multi_oid($device, "rttMonLatestIcmpJitterOperEntry", $sla_poll, "CISCO-RTTMON-ICMP-MIB", mib_dirs('cisco'));
          break;
      }
    }     

    $uptime = timeticks_to_sec($poll_device['sysUpTime']);
    $uptime_offset = time() - intval($uptime) / 100; /// WARNING. System timezone BOMB
  
    // Convert timestamps
    foreach ($sla_poll as &$sla)
    {
      $sla['UnixTime'] = intval(timeticks_to_sec($sla['rttMonLatestRttOperTime']) / 100 + $uptime_offset);
      $sla['TimeStr']  = format_unixtime($sla['UnixTime']);
    }
    unset($sla);
  }
  
  foreach ($sla_db as $sla)
  {
    echo("SLA " . $sla['sla_index'] . ": " . $sla['rtt_type'] . " " . $sla['sla_owner'] . " " . $sla['sla_tag']. "... ");
  
    $rrd_filename = "sla-" . $sla['sla_index'] . ".rrd";
    $rrd_ds       = "DS:rtt:GAUGE:600:0:300000";
  
    if (isset($sla_poll[$sla['sla_index']]))
    {
      $entry = $sla_poll[$sla['sla_index']];
      //if ($sla['sla_index'] == '99') { $entry['rttMonLatestRttOperSense'] = 'ok'; } // DEBUG
      echo($entry['rttMonLatestRttOperCompletionTime'] . 'ms at ' . $entry['TimeStr'] . ', Sense code - "'.$entry['rttMonLatestRttOperSense'].'"');
      $sla_state = array('rtt_value'    => $entry['rttMonLatestRttOperCompletionTime'],
                         'rtt_sense'    => $entry['rttMonLatestRttOperSense'],
                         'rtt_unixtime' => $entry['UnixTime']);
      $rrd_value  = $sla_state['rtt_value'];
      
      if ($sla['rtt_sense'] && $sla['rtt_sense'] != $sla_state['rtt_sense'])
      {
        // SLA sense changed, log
        if ($sla['rtt_sense'] == 'ok' || $sla_state['rtt_sense'] == 'ok') // Log only ok/not ok events
        {
          log_event('SLA changed: [#'.$sla['sla_index'].', '.$sla['sla_tag'].'] ' . $sla['rtt_sense'] . ' -> ' . $sla_state['rtt_sense'], $device, 'sla', $sla['sla_id'], 'warning');
        }
      }
      switch ($sla['rtt_type'])
      {
        case 'jitter':
          $rrd_filename = 'sla_jitter-' . $sla['sla_index'] . '.rrd';
          $rrd_ds      .= ' DS:rtt_minimum:GAUGE:600:0:300000 DS:rtt_maximum:GAUGE:600:0:300000 DS:rtt_success:GAUGE:600:0:300000 DS:rtt_loss:GAUGE:600:0:300000';
          if (is_numeric($entry['rttMonLatestJitterOperNumOfRTT']))
          {
            $sla_state['rtt_minimum'] = $entry['rttMonLatestJitterOperRTTMin'];
            $sla_state['rtt_maximum'] = $entry['rttMonLatestJitterOperRTTMax'];
            $sla_state['rtt_success'] = $entry['rttMonLatestJitterOperNumOfRTT'];
            $sla_state['rtt_loss']    = $entry['rttMonLatestJitterOperPacketLossSD'] + $entry['rttMonLatestJitterOperPacketLossDS'];
            $rrd_value .= ':'.$sla_state['rtt_minimum'].':'.$sla_state['rtt_maximum'].':'.$sla_state['rtt_success'].':'.$sla_state['rtt_loss'];
          } else {
            $rrd_value .= ':U:U:U:U';
          }
          //var_dump($rrd_ds);
          //$graphs['sla-'.$sla['rtt_type']] = TRUE;
          break;
        case 'icmpjitter':
          $rrd_filename = 'sla_jitter-' . $sla['sla_index'] . '.rrd';
          $rrd_ds      .= ' DS:rtt_minimum:GAUGE:600:0:300000 DS:rtt_maximum:GAUGE:600:0:300000 DS:rtt_success:GAUGE:600:0:300000 DS:rtt_loss:GAUGE:600:0:300000';
          if (is_numeric($entry['rttMonLatestIcmpJitterNumRTT']))
          {
            $sla_state['rtt_minimum'] = $entry['rttMonLatestIcmpJitterRTTMin'];
            $sla_state['rtt_maximum'] = $entry['rttMonLatestIcmpJitterRTTMax'];
            $sla_state['rtt_success'] = $entry['rttMonLatestIcmpJitterNumRTT'];
            $sla_state['rtt_loss']    = $entry['rttMonLatestIcmpJitterPktLoss'];
            $rrd_value .= ':'.$sla_state['rtt_minimum'].':'.$sla_state['rtt_maximum'].':'.$sla_state['rtt_success'].':'.$sla_state['rtt_loss'];
          } else {
            $rrd_value .= ':U:U:U:U';
          }
          //var_dump($rrd_ds);
          //$graphs['sla-'.$sla['rtt_type']] = TRUE;
          break;
        default:
          //$graphs['sla'] = TRUE;
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
      $metrics = array();;
      $metrics['rtt_value']   = $sla_state['rtt_value'];
      $metrics['rtt_sense']   = $sla_state['rtt_sense'];
      $metrics['rtt_minimum'] = $sla_state['rtt_minimum'];
      $metrics['rtt_maximum'] = $sla_state['rtt_maximum'];
      $metrics['rtt_success'] = $sla_state['rtt_success'];
      $metrics['rtt_loss']    = $sla_state['rtt_loss'];
      $metrics['rtt_loss_percent'] = 100 * $sla_state['rtt_loss'] / ($sla_state['rtt_success'] + $sla_state['rtt_loss']);

      check_entity('sla', $sla, $metrics);

    } else {
      echo("NaN");
      $rrd_value = 'U';
    }

    rrdtool_create($device, $rrd_filename, $rrd_ds);
    rrdtool_update($device, $rrd_filename, "N:".$rrd_value);

    unset($rrd_ds, $rrd_value, $rrd_filename);

    echo(PHP_EOL);
  }
}

// EOF
