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

if (!$config['enable_pseudowires']) { return; } // Pseudowires disabled

// WARNING. Discovered all Pseudowires, but polled only 'active'
$sql = "SELECT * FROM `pseudowires` LEFT JOIN `pseudowires-state` USING (`pseudowire_id`) WHERE `device_id` = ? AND `pwRowStatus` = 'active';";
//$sql = "SELECT * FROM `pseudowires` WHERE `device_id` = ? AND `pwRowStatus` = 'active';";
foreach (dbFetchRows($sql, array($device['device_id'])) as $entry)
{
  $index = $entry['pwIndex'];

  $pseudowires_db[$entry['mib']][$index] = $entry;
}

if (count($pseudowires_db) == 0) { return; } // Pseudowires not exist, exit

$table_rows = array();

print_cli_data_field("MIBs", 2);

foreach (array_keys($pseudowires_db) as $mib)
{
  // NOTE. Multiple pseudoware MIBs on single device theoretically impossible, but keep this common logic
  echo(" $mib ");
  $mib_lower = strtolower($mib);
  $oids = $config['mibs'][$mib]['pseudowire']['oids'];

  // Cache SNMP data
  $cache_pseudowires[$mib_lower] = array();
  foreach ($oids as $oid_type => $oid_entry)
  {
    $cache_pseudowires[$mib_lower] = snmpwalk_cache_multi_oid($device, $oid_entry['oid'], $cache_pseudowires[$mib_lower], $mib, NULL, OBS_SNMP_ALL_NUMERIC);
    if ($oid_type == 'Uptime' && $GLOBALS['snmp_status'] === FALSE)
    {
      break;
    }
  }
  $pseudowire_polled_time = time(); // Store polled time for current MIB

  if (OBS_DEBUG > 1 && count($cache_pseudowires[$mib_lower]))
  {
    print_vars($cache_pseudowires[$mib_lower]);
  }

  foreach ($pseudowires_db[$mib] as $index => $pw)
  {
    $rrd_filename = "pseudowire-" . $mib_lower . '-' . $index . ".rrd";
    $rrd_uptime   = "pseudowire-" . $mib_lower . '-uptime-' . $index . ".rrd";
    $rrd_ds       = '';

    if (isset($cache_pseudowires[$mib_lower][$index]))
    {
      $pw_poll   = &$cache_pseudowires[$mib_lower][$index];

      // Uptime graph
      $pw_uptime = timeticks_to_sec($pw_poll[$oids['Uptime']['oid']]); // Convert uptime to sec
      rrdtool_create($device, $rrd_uptime, "DS:Uptime:GAUGE:600:0:U ");
      rrdtool_update($device, $rrd_uptime, "N:".$pw_uptime);
      $graphs['pseudowire_uptime'] = TRUE;

      // Bits & Packets graphs
      $pw_values = array();
      foreach (array('InOctets', 'OutOctets', 'InPkts', 'OutPkts') as $oid_type)
      {
        if (!isset($oids[$oid_type])) { break; }

        $rrd_ds .= 'DS:' . $oid_type . ':DERIVE:600:0:' . $config['max_port_speed'] . ' ';
        $pw_values[] = $pw_poll[$oids[$oid_type]['oid']];
      }
      if (count($pw_values))
      {
        rrdtool_create($device, $rrd_filename, $rrd_ds);
        rrdtool_update($device, $rrd_filename, $pw_values);
        $graphs['pseudowire_bits'] = TRUE;
        $graphs['pseudowire_pkts'] = TRUE;
      }

      // Event
      $pw_operstatus = $pw_poll[$oids['OperStatus']['oid']];
      $pw_poll['event'] = $config['mibs'][$mib]['pseudowire']['states'][$pw_operstatus]['event'];

      // Last changed
      if (empty($pw['last_change']))
      {
        // If last change never set, use current time
        $pw_poll['last_change'] = $pseudowire_polled_time - $pw_uptime;
      }
      else if ($pw['pwOperStatus'] != $pw_operstatus)
      {
        // Pseudowire changed, log and set last_change
        $pw_poll['last_change'] = $pseudowire_polled_time; // - $pw_uptime;
        if ($pw['pwOperStatus']) // Log only if old status not empty
        {
          log_event('Pseudowire changed: [#'.$pw['pwID'].'] ' . $pw['pwOperStatus'] . ' -> ' . $pw_operstatus, $device, 'pseudowire', $pw['pseudowire_id'], 'warning');
        }
      }
      else if ($pw['pwUptime'] > $pw_uptime)
      {
        // Pseudowire flapped, log and set last_change
        $pw_poll['last_change'] = $pseudowire_polled_time; // - $pw_uptime;
        if ($pw['pwOperStatus']) // Log only if old status not empty
        {
          log_event('Pseudowire flapped: [#'.$pw['pwID'].'] time ' . formatUptime($pw_uptime) . ' ago', $device, 'pseudowire', $pw['pseudowire_id']);
        }
      } else {
        // If status not changed, leave old last_change
        $pw_poll['last_change'] = $pw['last_change'];
      }

      // Metrics
      $metrics = array();
      $metrics['pwOperStatus']    = $pw_poll[$oids['OperStatus']['oid']];
      $metrics['pwRemoteStatus']  = $pw_poll[$oids['RemoteStatus']['oid']];
      $metrics['pwLocalStatus']   = $pw_poll[$oids['LocalStatus']['oid']];
      $metrics['event']           = $pw_poll['event'];
      $metrics['pwUptime']        = $pw_uptime;
      $metrics['last_change']     = $pw_poll['last_change'];

      // Check entity
      check_entity('pseudowire', $pw, $metrics);

      // Update SQL State
      if (is_numeric($pw['pwUptime']))
      {
        dbUpdate($metrics, 'pseudowires-state', '`pseudowire_id` = ?', array($pw['pseudowire_id']));
      } else {
        $metrics['pseudowire_id'] = $pw['pseudowire_id'];
        dbInsert($metrics, 'pseudowires-state');
      }

      // Add table row
      $table_row = array();
      $table_row[] = $pw['pwID'];
      $table_row[] = $pw['mib'];
      $table_row[] = $pw['pwType'];
      $table_row[] = $pw['pwPsnType'];
      $table_row[] = $pw['peer_addr'];
      $table_row[] = $metrics['pwOperStatus'];
      $table_row[] = formatUptime($pw_uptime);
      $table_rows[] = $table_row;
      unset($table_row);

    }
  }
}

echo(PHP_EOL);

$headers = array('%WpwID%n', '%WMIB%n', '%WType%n', '%WPsnType%n', '%WPeer%n', '%WOperStatus%n', '%WUptime%n');
print_cli_table($table_rows, $headers);

unset($pseudowires_db, $cache_pseudowires, $pseudowire_polled_time, $pw, $pw_poll, $pw_values, $pw_uptime, $oids, $metrics, $table_rows);

// EOF
