<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// Parse output of ipmitool sensor
function parse_ipmitool_sensor($device, $results, $source = 'ipmi')
{
  global $valid, $config;

  $index = 0;

  foreach (explode("\n",$results) as $row)
  {
    $index++;

    # BB +1.1V IOH     | 1.089      | Volts      | ok    | na        | 1.027     | 1.054     | 1.146     | 1.177     | na
    list($desc,$current,$unit,$state,$low_nonrecoverable,$limit_low,$limit_low_warn,$limit_high_warn,$limit_high,$high_nonrecoverable) = explode('|',$row);

    if (trim($current) != "na" && trim($state) != "nr" && $config['ipmi_unit'][trim($unit)])
    {
      $limits = array('limit_high'      => trim($limit_high),
                      'limit_low'       => trim($limit_low),
                      'limit_high_warn' => trim($limit_high_warn),
                      'limit_low_warn'  => trim($limit_low_warn));
      discover_sensor($valid['sensor'], $config['ipmi_unit'][trim($unit)], $device, '', $index, $source, trim($desc), 1, trim($current), $limits, $source);

      $ipmi_sensors[$config['ipmi_unit'][trim($unit)]][$source][$index] = array('description' => $desc, 'current' => $current, 'index' => $index, 'unit' => $unit);
    }
  }

  return $ipmi_sensors;
}

// Poll a sensor
function poll_sensor($device, $class, $unit, &$oid_cache)
{
  global $config, $agent_sensors, $ipmi_sensors;

  $sql  = "SELECT *, `sensors`.`sensor_id` AS `sensor_id`";
  $sql .= " FROM  `sensors`";
  $sql .= " LEFT JOIN  `sensors-state` ON  `sensors`.sensor_id =  `sensors-state`.sensor_id";
  $sql .= " WHERE `sensor_class` = ? AND `device_id` = ?";

  foreach (dbFetchRows($sql, array($class, $device['device_id'])) as $sensor)
  {
    echo("Checking (" . $sensor['poller_type'] . ") $class " . $sensor['sensor_descr'] . " ");

    $sensor_new = $sensor; // Cache non-humanized sensor array
    humanize_sensor($sensor);

    if ($sensor['poller_type'] == "snmp")
    {
      # if ($class == "temperature" && $device['os'] == "papouch")
      // Why all temperature?
      if ($class == "temperature" && !$sensor['sensor_state'])
      {
        for ($i = 0;$i < 5;$i++) // Try 5 times to get a valid temp reading
        {
          // Take value from $oid_cache if we have it, else snmp_get it
          if (is_numeric($oid_cache[$sensor['sensor_oid']]))
          {
            print_debug("value taken from oid_cache");
            $sensor_value = $oid_cache[$sensor['sensor_oid']];
          } else {
            $sensor_value = preg_replace("/[^0-9\-\.]/", "", snmp_get($device, $sensor['sensor_oid'], "-OUqnv", "SNMPv2-MIB", mib_dirs()));
          }

          if (is_numeric($sensor_value) && $sensor_value != 9999) { break; } // Papouch TME sometimes sends 999.9 when it is right in the middle of an update;
          sleep(1); // Give the TME some time to reset
        }
        // Also reduce value by 32 if sensor in Fahrenheit unit
        if (float_cmp($sensor['sensor_multiplier'], 5/9) === 0) { $sensor_value -= 32; }
        // If we received 999.9 degrees still, reset to Unknown.
        if ($sensor_value == 9999) { $sensor_value = "U"; }
      }
      else if ($class == "runtime" && !$sensor['sensor_state'])
      {
        if (isset($oid_cache[$sensor['sensor_oid']]))
        {
          print_debug("value taken from oid_cache");
          $sensor_value = timeticks_to_sec($oid_cache[$sensor['sensor_oid']]);
        } else {
          $sensor_value = trim(str_replace("\"", "", snmp_get($device, $sensor['sensor_oid'], "-OUqnv", "SNMPv2-MIB", mib_dirs())));
          $sensor_value = timeticks_to_sec($sensor_value);
        }
      } else {
        // Take value from $oid_cache if we have it, else snmp_get it
        if (is_numeric($oid_cache[$sensor['sensor_oid']]))
        {
          print_debug("value taken from oid_cache");
          $sensor_value = $oid_cache[$sensor['sensor_oid']];
        } else {
          $sensor_value = trim(str_replace("\"", "", snmp_get($device, $sensor['sensor_oid'], "-OUqnv", "SNMPv2-MIB", mib_dirs())));
        }
      }
    }
    else if ($sensor['poller_type'] == "agent")
    {
      if (isset($agent_sensors))
      {
        $sensor_value = $agent_sensors[$class][$sensor['sensor_type']][$sensor['sensor_index']]['current'];
        // FIXME pass unit?
      } else {
        print_warning("No agent sensor data available.");
        continue;
      }
    }
    else if ($sensor['poller_type'] == "ipmi")
    {
      if (isset($ipmi_sensors))
      {
        $sensor_value = $ipmi_sensors[$class][$sensor['sensor_type']][$sensor['sensor_index']]['current'];
        $unit = $ipmi_sensors[$class][$sensor['sensor_type']][$sensor['sensor_index']]['unit'];
      } else {
        print_warning("No IPMI sensor data available.");
        continue;
      }
    } else {
      print_warning("Unknown sensor poller type.");
      continue;
    }

    if (!$sensor['sensor_state'])
    {
      if ($sensor_value == -32768) { echo("Invalid (-32768) "); $sensor_value = 0; }
      if (isset($sensor['sensor_divisor']) && $sensor['sensor_divisor'] > 1)
      {
        /// This is fix for r5351
        if ($sensor['sensor_multiplier'] >= 1)
        {
          $sensor_value = $sensor_value / $sensor['sensor_divisor'];
        }
      }
      if (isset($sensor['sensor_multiplier']) && $sensor['sensor_multiplier'] != 0)    { $sensor_value = $sensor_value * $sensor['sensor_multiplier']; }
    }

    $rrd_file = get_sensor_rrd($device, $sensor);

    rrdtool_create($device, $rrd_file, "DS:sensor:GAUGE:600:-20000:U");

    echo("$sensor_value $unit ");

    // Write new value and humanize (for alert checks)
    $sensor_new['sensor_value'] = $sensor_value;
    humanize_sensor($sensor_new);

    // FIXME I left the eventlog code for now, as soon as alerts send an entry to the eventlog this can go.
    if ($sensor['state_event'] != 'ignore')
    {
      if (!$sensor['sensor_state'])
      {
        if ($sensor['sensor_limit_low'] != "" && $sensor['sensor_value'] >= $sensor['sensor_limit_low'] && $sensor_value < $sensor['sensor_limit_low'])
        {
          $msg  = ucfirst($class) . " Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'] . " is under threshold: " . $sensor_value . "$unit (< " . $sensor['sensor_limit_low'] . "$unit)";
          log_event(ucfirst($class) . ' ' . $sensor['sensor_descr'] . " under threshold: " . $sensor_value . " $unit (< " . $sensor['sensor_limit_low'] . " $unit)", $device, $class, $sensor['sensor_id']);
        }
        else if ($sensor['sensor_limit'] != "" && $sensor['sensor_value'] <= $sensor['sensor_limit'] && $sensor_value > $sensor['sensor_limit'])
        {
          $msg  = ucfirst($class) . " Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'] . " is over threshold: " . $sensor_value . "$unit (> " . $sensor['sensor_limit'] . "$unit)";
          log_event(ucfirst($class) . ' ' . $sensor['sensor_descr'] . " above threshold: " . $sensor_value . " $unit (> " . $sensor['sensor_limit'] . " $unit)", $device, $class, $sensor['sensor_id']);
        }
      }
      else if ($sensor_new['state_event'] != $sensor['state_event'] && $sensor['state_event'] != '')
      {
        $sensor_state_name  = $sensor_new['state_name'];
        $sensor_state_event = $sensor_new['state_event'];
        switch ($sensor_state_event)
        {
          case 'alert':
            $msg  = ucfirst($class) . " Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'] . " is under ALERT state: " . $sensor_state_name . " (previous state: " . $sensor['state_name'] . ")";
            log_event($msg, $device, $class, $sensor['sensor_id']);
            break;
          case 'warning':
            $msg  = ucfirst($class) . " Warning: " . $device['hostname'] . " " . $sensor['sensor_descr'] . " in WARNING state: " . $sensor_state_name . " (previous state: " . $sensor['state_name'] . ")";
            log_event($msg, $device, $class, $sensor['sensor_id']);
            break;
          case 'up':
            $msg  = ucfirst($class) . " Up: " . $device['hostname'] . " " . $sensor['sensor_descr'] . " in NORMAL state: " . $sensor_state_name . " (previous state: " . $sensor['state_name'] . ")";
            log_event($msg, $device, $class, $sensor['sensor_id']);
            break;
        }
      }
    } else {
      print_message("[%ySensor Ignored%n]", 'color');
    }
    echo("\n");

    // Send statistics array via AMQP/JSON if AMQP is enabled globally and for the ports module
    if ($config['amqp']['enable'] == TRUE && $config['amqp']['modules']['sensors'])
    {
      $json_data = array('value' => $sensor_value);
      messagebus_send(array('attribs' => array('t' => time(), 'device' => $device['hostname'], 'device_id' => $device['device_id'],
                                               'e_type' => 'sensor', 'e_class' => $sensor['sensor_class'], 'e_type' => $sensor['sensor_type'], 'e_index' => $sensor['sensor_index']), 'data' => $json_data));
    }

    // Update StatsD/Carbon
    if ($config['statsd']['enable'] == TRUE)
    {
      StatsD::gauge(str_replace(".", "_", $device['hostname']).'.'.'sensor'.'.'.$sensor['sensor_class'].'.'.$sensor['sensor_type'].'.'.$sensor['sensor_index'], $sensor_value);
    }

    // Update RRD
    rrdtool_update($device, $rrd_file,"N:$sensor_value");

    // Check alerts
    $metrics = array();

    if (!$sensor['sensor_state'])
    {
      $metrics['sensor_value'] = $sensor_new['sensor_value'];
    }

    $metrics['sensor_event'] = $sensor_new['state_event'];

    check_entity('sensor', $sensor, $metrics);

    // Update SQL State
    if (is_numeric($sensor['sensor_polled']))
    {
      dbUpdate(array('sensor_value' => $sensor_value, 'sensor_polled' => time()), 'sensors-state', '`sensor_id` = ?', array($sensor['sensor_id']));
    } else {
      dbInsert(array('sensor_id' => $sensor['sensor_id'], 'sensor_value' => $sensor_value, 'sensor_polled' => time()), 'sensors-state');
    }
  }
}

function poll_device($device, $options)
{
  global $config, $debug, $device, $polled_devices, $db_stats, $memcache, $exec_status, $alert_rules, $alert_table;

  $alert_metrics = array();

  $oid_cache = array();

  $old_device_state = unserialize($device['device_state']);

  $attribs = get_dev_attribs($device['device_id']);

  $alert_rules = cache_alert_rules();
  $alert_table = cache_device_alert_table($device['device_id']);

  if ($debug && (count($alert_rules) || count($alert_table))) // Fuck you, dirty outputs.
  {
    print_vars($alert_rules);
    print_vars($alert_table);
  }

  $status = 0; unset($array);
  $device_start = utime();  // Start counting device poll time

  echo($device['hostname'] . " ".$device['device_id']." ".$device['os']." ");
  if ($config['os'][$device['os']]['group'])
  {
    $device['os_group'] = $config['os'][$device['os']]['group'];
    echo("(".$device['os_group'].")");
  }
  echo("\n");

  unset($poll_update); unset($poll_update_query); unset($poll_separator);
  $poll_update_array = array();

  $host_rrd_dir = $config['rrd_dir'] . "/" . $device['hostname'];
  if (!is_dir($host_rrd_dir)) { mkdir($host_rrd_dir); echo("Created directory : $host_rrd_dir\n"); }

  $device['pingable'] = isPingable($device['hostname']);
  if ($device['pingable'])
  {
    $device['snmpable'] = isSNMPable($device);
    if ($device['snmpable'])
    {
      $status = "1";
      $status_type = '';
    } else {
      echo("SNMP Unreachable");
      $status = "0";
      $status_type = 'snmp';
    }
  } else {
    echo("Unpingable");
    $status = "0";
    $status_type = 'ping';
  }

  if ($device['status'] != $status)
  {
    $poll_update .= $poll_separator . "`status` = '$status'";
    $poll_separator = ", ";

    dbUpdate(array('status' => $status), 'devices', 'device_id=?', array($device['device_id']));
    dbInsert(array('importance' => '0', 'device_id' => $device['device_id'], 'message' => "Device is " .($status == '1' ? 'up' : 'down')), 'alerts');

    log_event('Device status changed to ' . ($status == '1' ? 'Up' : 'Down') . ($status_type != '' ? ' (' . $status_type . ')' : ''), $device, 'system');
  }

  $rrd_filename = "status.rrd";

  rrdtool_create($device, $rrd_filename, "DS:status:GAUGE:600:0:1 ");

  if ($status == "1" || $status == "0")
  {
    rrdtool_update($device, $rrd_filename, "N:".$status);
  } else {
    rrdtool_update($device, $rrd_filename, "N:U");
  }

  // Ping response RRD database.
  $ping_rrd = 'ping.rrd';
  rrdtool_create($device, $ping_rrd, "DS:ping:GAUGE:600:0:65535 " );

  if ($device['pingable'])
  {
    rrdtool_update($device, $ping_rrd,"N:".$device['pingable']);
  } else {
    rrdtool_update($device, $ping_rrd,"N:U");
  }

  // SNMP response RRD database.
  $ping_snmp_rrd = 'ping_snmp.rrd';
  rrdtool_create($device, $ping_snmp_rrd, "DS:ping_snmp:GAUGE:600:0:65535 " );

  if ($device['snmpable'])
  {
    rrdtool_update($device, $ping_snmp_rrd,"N:".$device['snmpable']);
  } else {
    rrdtool_update($device, $ping_snmp_rrd,"N:U");
  }

  $alert_metrics['device_status'] = $status;
  $alert_metrics['device_status_type'] = $status_type;
  $alert_metrics['device_ping'] = $device['pingable'];
  $alert_metrics['device_snmp'] = $device['snmpable'];

  if ($status == "1")
  {
    // Arrays for store and check enabled/disabled graphs
    $graphs    = array();
    $graphs_db = array();
    foreach (dbFetchRows("SELECT * FROM `device_graphs` WHERE `device_id` = ?", array($device['device_id'])) as $entry)
    {
      $graphs_db[$entry['graph']] = (isset($entry['enabled']) ? (bool)$entry['enabled'] : TRUE);
    }

    // Enable Ping graphs
    $graphs['ping'] = TRUE;

    // Enable SNMP graphs
    $graphs['ping_snmp'] = TRUE;

    // Run these base modules always and before all other modules!
    $poll_modules = array('system', 'os');

    if ($options['m'])
    {
      foreach (explode(',', $options['m']) as $module)
      {
        $module = trim($module);
        if (in_array($module, $poll_modules)) { continue; } // Skip already added modules
        if ($module == 'unix-agent')
        {
          array_unshift($poll_modules, $module);            // Add 'unix-agent' before all
          continue;
        }
        if (is_file($config['install_dir'] . "/includes/polling/$module.inc.php"))
        {
          $poll_modules[] = $module;
        }
      }
    } else {
      foreach ($config['poller_modules'] as $module => $module_status)
      {
        if (in_array($module, $poll_modules)) { continue; } // Skip already added modules
        if ($attribs['poll_'.$module] || ($module_status && !isset($attribs['poll_'.$module])))
        {
          if (poller_module_excluded($device, $module))
          {
            print_warning("Module [ $module ] excluded for device.");
            continue;
          }
          if ($module == 'unix-agent')
          {
            array_unshift($poll_modules, $module);          // Add 'unix-agent' before all
            continue;
          }
          if (is_file($config['install_dir'] . "/includes/polling/$module.inc.php"))
          {
            $poll_modules[] = $module;
          }
        }
        elseif (isset($attribs['poll_'.$module]) && !$attribs['poll_'.$module])
        {
          print_warning("Module [ $module ] disabled on device.");
        } else {
          print_warning("Module [ $module ] disabled globally.");
        }
      }

    }

    foreach ($poll_modules as $module)
    {
      print_debug(PHP_EOL."including: includes/polling/$module.inc.php");

      $m_start = utime();

      include($config['install_dir'] . "/includes/polling/$module.inc.php");

      $m_end   = utime();
      $m_run   = round($m_end - $m_start, 4);
      $device_state['poller_mod_perf'][$module] = number_format($m_run,4);
      print_message("Module [ $module ] time: $m_run"."s");
    }

    // Fields to notify about in event log - FIXME should move to definitions?
    $update_fields = array('version', 'features', 'hardware', 'serial', 'kernel', 'distro', 'distro_ver', 'arch', 'asset_tag');

    // Log changed variables
    foreach ($update_fields as $field)
    {
      if (isset($$field) && $$field != $device[$field])
      {
        $update_array[$field] = $$field;
        log_event(ucfirst($field)." -> ".$update_array[$field], $device, 'system');
      }
    }

    // Check and update graphs DB
    $graphs_stat = array();

    if (!isset($options['m']))
    {
      // Hardcoded poller performance
      $graphs['poller_perf'] = TRUE;

      // Delete not exists graphs from DB (only if poller run without modules option)
      foreach ($graphs_db as $graph => $value)
      {
        if (!isset($graphs[$graph]))
        {
          dbDelete('device_graphs', "`device_id` = ? AND `graph` = ?", array($device['device_id'], $graph));
          unset($graphs_db[$graph]);
          $graphs_stat['deleted'][] = $graph;
        }
      }
    }

    // Add or update graphs in DB
    foreach ($graphs as $graph => $value)
    {
      if (!isset($graphs_db[$graph]))
      {
        dbInsert(array('device_id' => $device['device_id'], 'graph' => $graph, 'enabled' => $value), 'device_graphs');
        $graphs_stat['added'][] = $graph;
      }
      else if ($value != $graphs_db[$graph])
      {
        dbUpdate(array('enabled' => $value), 'device_graphs', '`device_id` = ? AND `graph` = ?', array($device['device_id'], $graph));
        $graphs_stat['updated'][] = $graph;
      } else {
        $graphs_stat['checked'][] = $graph;
      }
    }

    // Print graphs stats
    foreach ($graphs_stat as $key => $stat)
    {
      if (count($stat)) { echo(' Graphs ['.$key.']: '.implode(', ', $stat).PHP_EOL); }
    }

    $device_end = utime(); $device_run = $device_end - $device_start; $device_time = round($device_run, 4);

    $update_array['last_polled'] = array('NOW()');
    $update_array['last_polled_timetaken'] = $device_time;

    $update_array['device_state'] = serialize($device_state);

    #echo("$device_end - $device_start; $device_time $device_run");
    print_message(PHP_EOL."Polled in $device_time seconds");

    // Only store performance data if we're not doing a single-module poll
    if (!$options['m'])
    {
      dbInsert(array('device_id' => $device['device_id'], 'operation' => 'poll', 'start' => $device_start, 'duration' => $device_run), 'devices_perftimes');

      $poller_rrd = "perf-poller.rrd";
      rrdtool_create($device, $poller_rrd, "DS:val:GAUGE:600:0:38400 ");
      rrdtool_update($device, $poller_rrd, "N:".$device_time);
    }

    if ($debug) { echo("Updating " . $device['hostname'] . " - ");print_vars($update_array);echo(" \n"); }

    $updated = dbUpdate($update_array, 'devices', '`device_id` = ?', array($device['device_id']));
    if ($updated) { echo("UPDATED!\n"); }

    $alert_metrics['device_uptime'] = $device['uptime'];
    $alert_metrics['device_duration_poll'] = $device['last_polled_timetaken'];

    unset($cache_storage); // Clear cache of hrStorage ** MAYBE FIXME? ** (ok, later)
    unset($cache); // Clear cache (unify all things here?)
  }

  check_entity('device', $device, $alert_metrics);

  unset($alert_metrics);
}

///FIXME. It's not a very nice solution, but will approach as temporal.
// Function return FALSE, if poller module allowed for device os (otherwise TRUE).
function poller_module_excluded($device, $module)
{
  ///FIXME. rename module: 'wmi' -> 'windows-wmi'
  if ($module == 'wmi'  && $device['os'] != 'windows') { return TRUE; }

  if ($module == 'ipmi' && !($device['os_group'] == 'unix' || $device['os'] == 'drac' || $device['os'] == 'windows' || $device['os'] == 'generic')) { return TRUE; }
  if ($module == 'unix-agent' && !($device['os_group'] == 'unix' || $device['os'] == 'generic')) { return TRUE; }

  $os_test = explode('-', $module, 2);
  if (count($os_test) === 1) { return FALSE; } // Check modules only with a dash.
  list($os_test) = $os_test;

  ///FIXME. rename module: 'cipsec-tunnels' -> 'cisco-ipsec-tunnels'
  if (($os_test == 'cisco' || $os_test == 'cipsec') && $device['os_group'] != 'cisco') { return TRUE; }
  //$os_groups = array('cisco', 'unix');
  //foreach ($os_groups as $os_group)
  //{
  //  if ($os_test == $os_group && $device['os_group'] != $os_group) { return TRUE; }
  //}

  $oses = array('junose', 'arista_eos', 'netscaler', 'arubaos');
  foreach ($oses as $os)
  {
    if (strpos($os, $os_test) !== FALSE && $device['os'] != $os) { return TRUE; }
  }

  return FALSE;
}

/**
 * Poll a table or oids from SNMP and build an RRD based on an array of arguments.
 *
 * Current limitations:
 *  - single MIB and RRD file for all graphs
 *  - single table per MIB
 *  - if set definition 'call_function', than poll used specific function for snmp walk/get,
 *    else by default used snmpwalk_cache_oid()
 *  - allowed oids only with simple numeric index (oid.0, oid.33), NOT allowed (oid.1.2.23)
 *  - only numeric data
 *
 * Example of (full) args array:
 *  array(
 *   'file'          => 'someTable.rrd',              // [MANDATORY] RRD filename, but if not set used MIB_table.rrd as filename
 *   'call_function' => 'snmpwalk_cache_oid'          // [OPTIONAL] Which function to use for snmp poll, bu default snmpwalk_cache_oid()
 *   'mib'           => 'SOMETHING-MIB',              // [OPTIONAL] MIB or list of MIBs separated by a colon
 *   'mib_dir'       => 'something',                  // [OPTIONAL] OS MIB directory or array of directories
 *   'graphs'        => array('one','two'),           // [OPTIONAL] List of graph_types that this table provides
 *   'table'         => 'someTable',                  // [RECOMENDED] Table name for OIDs
 *   'numeric'       => '.1.3.6.1.4.1.555.4.1.1.48',  // [OPTIONAL] Numeric table OID
 *   'ds_rename'     => array('http' => ''),          // [OPTIONAL] Array for renaming OIDs to DSes
 *   'oids'          => array(                        // List of OIDs you can use as key: full OID name
 *     'someOid' => array(                                 // OID name (You can use OID name, like 'cpvIKECurrSAs')
 *       'descr'     => 'Current IKE SAs',                 // [OPTIONAL] Description of the OID contents
 *       'numeric'   => '.1.3.6.1.4.1.555.4.1.1.48.45',    // [OPTIONAL] Numeric OID
 *       'index'     => '0',                               // [OPTIONAL] OID index, if not set equals '0'
 *       'ds_name'   => 'IKECurrSAs',                      // [OPTIONAL] DS name, if not set used OID name truncated to 18 chars
 *       'ds_type'   => 'GAUGE',                           // [OPTIONAL] DS type, if not set equals 'COUNTER'
 *       'ds_min'    => '0',                               // [OPTIONAL] Min value for DS, if not set equals 'U'
 *       'ds_max'    => '30000'                            // [OPTIONAL] Max value for DS, if not set equals '100000000000'
 *    )
 *  )
 *
 */

function collect_table($device, $oids_def, &$graphs)
{
  $rrd      = array();
  $mib      = NULL;
  $mib_dirs = NULL;
  $use_walk = isset($oids_def['table']) && $oids_def['table']; // Use snmpwalk by default
  $call_function = strtolower($oids_def['call_function']);
  switch ($call_function)
  {
    case 'snmp_get_multi':
      $use_walk = FALSE;
      break;
    case 'snmpwalk_cache_oid':
    default:
      $call_function = 'snmpwalk_cache_oid';
      if (!$use_walk)
      {
        // Break because we should use snmpwalk, but walking table not set
        return FALSE;
      }
  }
  if (isset($oids_def['numeric'])) { $oids_def['numeric'] = '.'.trim($oids_def['numeric'], '. '); } // Remove trailing dot
  if (isset($oids_def['mib']))     { $mib      = $oids_def['mib']; }
  if (isset($oids_def['mib_dir'])) { $mib_dirs = mib_dirs($oids_def['mib_dir']); }
  if (isset($oids_def['file']))
  {
    $rrd_file = $oids_def['file'];
  }
  else if ($mib && isset($oids_def['table']))
  {
    // Try to use MIB & tableName as rrd_file
    $rrd_file = strtolower(safename($mib.'_'.$oids_def['table'])).'.rrd';
  } else {
    print_debug("  WARNING, not have rrd filename.");
    return FALSE; // Not have RRD filename
  }

  // Get MIBS/Tables/OIDs permissions
  if ($use_walk)
  {
    // if use table walk, than check only this table permission (not oids)
    if (dbFetchCell("SELECT COUNT(*) FROM `devices_mibs` WHERE `device_id` = ? AND `mib` = ? AND `table_name` = ?
                    AND (`oid` = '' OR `oid` IS NULL) AND `disabled` = '1'", array($device['device_id'], $mib, $oids_def['table'])))
    {
      print_debug("  WARNING, table '".$oids_def['table']."' for '$mib' disabled and skipped.");
      return FALSE; // table disabled, exit
    }
    $oids_ok = TRUE;
  } else {
    // if use multi_get, than get all disabled oids
    $oids_disabled = dbFetchColumn("SELECT `oid` FROM `devices_mibs` WHERE `device_id` = ? AND `mib` = ?
                                   AND (`oid` != '' AND `oid` IS NOT NULL) AND `disabled` = '1'", array($device['device_id'], $mib));
    $oids_ok = empty($oids_disabled); // if empty disabled, than set to TRUE
  }

  $search  = array();
  $replace = array();
  if (is_array($oids_def['ds_rename']))
  {
    foreach ($oids_def['ds_rename'] as $s => $r)
    {
      $search[]  = $s;
      $replace[] = $r;
    }
  }

  $oids       = array();
  $oids_index = array();
  foreach ($oids_def['oids'] as $oid => $entry)
  {
    //if (!isset($entry['descr']))   { $entry['descr'] = ''; }   // Descr not used in any case
    if (is_numeric($entry['numeric']) && isset($oids_def['numeric']))
    {
      $entry['numeric'] = $oids_def['numeric'] . '.' . $entry['numeric']; // Numeric oid, for future using
    }
    if (!isset($entry['index']))   { $entry['index'] = '0'; }
    if (!isset($entry['ds_type'])) { $entry['ds_type'] = 'COUNTER'; }
    if (!isset($entry['ds_min']))  { $entry['ds_min']  = 'U'; }
    if (!isset($entry['ds_max']))  { $entry['ds_max']  = '100000000000'; }
    if (!isset($entry['ds_name']))
    {
      // Convert OID name to DS name
      $ds_name = $oid;
      if (is_array($oids_def['ds_rename'])) { $ds_name = str_replace($search, $replace, $ds_name); }
    } else {
      $ds_name = $entry['ds_name'];
    }
    $ds_len = ($mib != 'NS-ROOT-MIB' ? 19 : 18); // Hardcode max len for NS-ROOT-MIB to 18 chars
    if (strlen($ds_name) > $ds_len) { $ds_name = truncate($ds_name, $ds_len, ''); }

    $oids[]       = $oid.'.'.$entry['index'];
    $oids_index[] = array('index' => $entry['index'], 'oid' => $oid);

    if (!$use_walk)
    {
      // Check permissions for snmp_get_multi _ONLY_
      // if at least one oid missing in $oids_disabled than TRUE
      $oids_ok = $oids_ok || !in_array($oid, $oids_disabled);
    }

    $rrd['rrd_create'][] = ' DS:'.$ds_name.':'.$entry['ds_type'].':600:'.$entry['ds_min'].':'.$entry['ds_max'];
    if ($GLOBALS['debug']) { $rrd['ds_list'][] = $ds_name; } // Make DS lists for compare with RRD file in debug
  }

  if (!$use_walk && !$oids_ok)
  {
    print_debug("  WARNING, oids '".implode("', '", array_keys($oids_def['oids']))."' for '$mib' disabled and skipped.");
    return FALSE;  // All oids disabled, exit
  }

  switch ($call_function)
  {
    case 'snmpwalk_cache_oid':
      $data = snmpwalk_cache_oid($device, $oids_def['table'], array(), $mib, $mib_dirs);
      break;
    case 'snmp_get_multi':
      $data = snmp_get_multi($device, $oids, "-OQUs", $mib, $mib_dirs);
      break;
  }
  if (isset($GLOBALS['exec_status']['exitcode']) && $GLOBALS['exec_status']['exitcode'] !== 0)
  {
    // Break because latest snmp walk/get return not good exitstatus (wrong mib/timeout/error/etc)
    print_debug("  WARNING, latest snmp walk/get return not good exitstatus for '$mib', RRD update skipped.");
    return FALSE;
  }

  foreach ($oids_index as $entry)
  {
    $index = $entry['index'];
    $oid   = $entry['oid'];
    if (is_numeric($data[$index][$oid]))
    {
      $rrd['ok']           = TRUE; // We have any data for current rrd_file
      $rrd['rrd_update'][] = $data[$index][$oid];
    } else {
      $rrd['rrd_update'][] = 'U';
    }
  }

  // Ok, all previous checks done, update RRD, table/oids permissions, $graphs
  if (isset($rrd['ok']) && $rrd['ok'])
  {
    // Create/update RRD file
    $rrd_create = implode('', $rrd['rrd_create']);
    $rrd_update = 'N:'.implode(':', $rrd['rrd_update']);
    rrdtool_create($device, $rrd_file, $rrd_create);
    rrdtool_update($device, $rrd_file, $rrd_update);

    foreach ($oids_def['graphs'] as $graph)
    {
      $graphs[$graph] = TRUE; // Set all graphs to TRUE
    }

    // Compare DSes form RRD file with DSes from array
    if ($GLOBALS['debug'])
    {
      $graph_template  = "\$config['graph_types']['device']['GRAPH_CHANGE_ME'] = array(\n";
      $graph_template .= "  'file'      => '$rrd_file',\n";
      $graph_template .= "  'ds'        => array(\n";
      $rrd_file_info = rrdtool_file_info(get_rrd_path($device, $rrd_file));
      foreach ($rrd_file_info['DS'] as $ds => $nothing)
      {
        $ds_list[] = $ds;
        $graph_template .= "    '$ds' => array('label' => 'CHANGE_ME'),\n";
      }
      $graph_template .= "  )\n);";
      $in_args = array_diff($rrd['ds_list'], $ds_list);
      if ($in_args)
      {
        print_message("%rWARNING%n, in file '%W".$rrd_file_info['filename']."%n' different DS lists. NOT have: ".implode(', ', $in_args));
      }
      $in_file = array_diff($ds_list, $rrd['ds_list']);
      if ($in_file)
      {
        print_message("%rWARNING%n, in file '%W".$rrd_file_info['filename']."%n' different DS lists. Excess: ".implode(', ', $in_file));
      }

      // Print example for graph template using rrd_file and ds list
      print_message($graph_template);
    }
  }
  else if ($use_walk)
  {
    // Table NOT exist on device!
    // Disable polling table (only if table not enabled manually in DB)
    if (!dbFetchCell("SELECT COUNT(*) FROM `devices_mibs` WHERE `device_id` = ? AND `mib` = ?
                     AND `table_name` = ? AND (`oid` = '' OR `oid` IS NULL)", array($device['device_id'], $mib, $oids_def['table'])))
    {
      dbInsert(array('device_id' => $device['device_id'], 'mib' => $mib,
                     'table_name' => $oids_def['table'], 'disabled' => '1'), 'devices_mibs');
    }
    print_debug("  WARNING, table '".$oids_def['table']."' for '$mib' disabled.");
  } else {
    // OIDs NOT exist on device!
    // Disable polling oids (only if table not enabled manually in DB)
    foreach (array_keys($oids_def['oids']) as $oid)
    {
      if (!dbFetchCell("SELECT COUNT(*) FROM `devices_mibs` WHERE `device_id` = ? AND `mib` = ?
                       AND `oid` = ?", array($device['device_id'], $mib, $oid)))
      {
        dbInsert(array('device_id' => $device['device_id'], 'mib' => $mib,
                       'oid' => $oid, 'disabled' => '1'), 'devices_mibs');
      }
    }
    print_debug("  WARNING, oids '".implode("', '", array_keys($oids_def['oids']))."' for '$mib' disabled.");
  }

  // Return obtained snmp data
  return $data;
}

// Poll a table from SNMP and build an RRD based on an array of arguments.

function collect_table_old($args, $device, &$graphs)
{

  $data = snmpwalk_cache_oid($device, $args['table'], array(), $args['mib']);

  echo("Collecting: ".$args['table']." ");

  $rrd_update = "N";

  $search  = array();
  $replace = array();
  if (is_array($args['ds_rename']))
  {
    foreach ($args['ds_rename'] AS $s => $r)
    {
      $search[]  = $s;
      $replace[] = $r;
    }
  }

  foreach ($args['ds_list'] as $ds_name => $ds_data)
  {

    if (!isset($ds_data['type'])) { $ds_data['type'] = 'COUNTER'; }
    if (!isset($ds_data['min']))  { $ds_data['min']  = 'U'; }
    if (!isset($ds_data['max']))  { $ds_data['max']  = '100000000000'; }

    if (is_array($args['ds_rename'])) { $ds = str_replace($search, $replace, $ds_name); } else { $ds = $ds_name; }
    if (strlen($ds) > 18) { $ds = truncate($ds, 18, ''); }

    $rrd_create .= ' DS:'.$ds.':'.$ds_data['type'].':600:'.$ds_data['min'].':'.$ds_data['max'];

    if (is_numeric($data[0][$ds_name]))
    {
      $rrd_update .= ":".$data[0][$ds_name];
    } else {
      $rrd_update .= ":U";
    }

  }

  rrdtool_create($device, $args['file'], $rrd_create);
  rrdtool_update($device, $args['file'], $rrd_update);

  // We should only create a graph when the OID was present -- FIXME :)
  foreach ($args['graphs'] as $g) { $graphs[$g] = TRUE; }
}

// EOF
