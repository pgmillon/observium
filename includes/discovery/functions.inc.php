<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @subpackage functions
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

/// FIXME. Deprecated.
function discover_new_device_ip($host, $source = 'xdp', $protocol = NULL, $device = NULL, $port = NULL)
{
  print_error("Function discover_new_device_ip() is deprecated, use discover_new_device().");
  discover_new_device($host, $source, $protocol, $device, $port);
}

function discover_new_device($hostname, $source = 'xdp', $protocol = NULL, $device = NULL, $port = 161)
{
  global $config;

  $source = strtolower($source);
  if ($config['autodiscovery'][$source])
  {
    if (!$protocol) { $protocol = strtoupper($source); }
    print_message("Discovering new host $hostname through $protocol");

    // By first detect hostname is IP or domain name (IPv4/6 == 4/6, hostname == FALSE)
    $ip_version = get_ip_version($hostname);
    if ($ip_version)
    {
      // Hostname is IPv4/IPv6
      $use_ip = TRUE;
    } else {
      $use_ip = FALSE;
      if (!empty($config['mydomain']) && isDomainResolves($hostname . '.' . $config['mydomain']))
      {
        $hostname .= '.' . $config['mydomain'];
      }
      $ip = gethostbyname6($hostname);
      if ($ip)
      {
        $ip_version = get_ip_version($ip);
        print_debug("Host $hostname resolved as $ip");
      } else {
        // No DNS records
        print_debug("Host $hostname not resolved, autodiscovery fails.");
        return FALSE;
      }
    }

    if (match_network($ip, $config['autodiscovery']['ip_nets']))
    {
      print_debug("Host $hostname ($ip) founded inside configured nets, try to adding:");

      if (isPingable($ip))
      {
        // Check if device duplicated by IP
        $ip = ($ip_version == 4 ? $hostname : Net_IPv6::uncompress($hostname, TRUE));
        $db = dbFetchRow('SELECT D.`hostname` FROM ipv'.$ip_version.'_addresses AS A
                         LEFT JOIN `ports`   AS P ON A.`port_id`   = P.`port_id`
                         LEFT JOIN `devices` AS D ON D.`device_id` = P.`device_id`
                         WHERE D.`disabled` = 0 AND A.`ipv'.$ip_version.'_address` = ?', array($ip));
        if ($db)
        {
          print_debug('Already have device '.$db['hostname']." with $ip");
          return FALSE;
        }

        // Detect snmp transport
        $transport = ($ip_version == 4 ? 'udp' : 'udp6');

        $new_device = detect_device_snmpauth($ip, $port, $transport);
        if ($new_device)
        {
          if ($use_ip)
          {
            // Detect FQDN hostname
            // by sysName
            $snmphost = snmp_get($new_device, "sysName.0", "-Oqv", "SNMPv2-MIB", mib_dirs());
            if ($snmphost)
            {
              $snmp_ip = gethostbyname6($snmphost);
            }

            if ($snmp_ip == $ip)
            {
              $hostname = $snmphost;
            } else {
              // by PTR
              $ptr = gethostbyaddr6($ip);
              if ($ptr)
              {
                $ptr_ip = gethostbyname6($ptr);
              }

              if ($ptr && $ptr_ip == $ip)
              {
                $hostname = $ptr;
              } else {
                print_debug("Device IP $ip not have FQDN name");
                return FALSE;
              }
            }
            print_debug("Device IP $ip founded FQDN name: $hostname");
          }

          $new_device['hostname'] = $hostname;
          if (!check_device_duplicated($new_device))
          {
            $v3 = array();
            if ($new_device['snmpver'] === 'v3')
            {
              $v3['authlevel']  = $new_device['authlevel'];
              $v3['authname']   = $new_device['authname'];
              $v3['authpass']   = $new_device['authpass'];
              $v3['authalgo']   = $new_device['authalgo'];
              $v3['cryptopass'] = $new_device['cryptopass'];
              $v3['cryptoalgo'] = $new_device['cryptoalgo'];
            }
            $remote_device_id = createHost($new_device['hostname'], $new_device['community'], $new_device['snmpver'], $new_device['port'], $new_device['transport'], $v3);

            if ($remote_device_id)
            {
              $remote_device = device_by_id_cache($remote_device_id, 1);

              if ($port)
              {
                humanize_port($port);
                log_event("Device autodiscovered through $protocol on " . $device['hostname'] . " (port " . $port['label'] . ")", $remote_device_id, 'port', $port['port_id']);
              } else {
                log_event("Device autodiscovered through $protocol on " . $device['hostname'], $remote_device_id, $protocol);
              }

              //array_push($GLOBALS['devices'], $remote_device); // createHost() already puth this
              return $remote_device_id;
            }
          }
        }
      }
    } else {
      print_debug("IP $ip ($hostname) not permitted inside \$config['autodiscovery']['ip_nets'] in config.php");
    }
    print_debug('Autodiscovery for host ' . $hostname . ' fails.');
  } else {
    print_debug('Autodiscovery for protocol ' . $protocol . ' disabled.');
  }
  return FALSE;
}

function discover_device($device, $options = NULL)
{
  global $config, $valid, $exec_status, $discovered_devices;

  $valid = array(); // Reset $valid array

  $attribs = get_dev_attribs($device['device_id']);

  $device_start = utime();  // Start counting device poll time

  echo($device['hostname'] . " ".$device['device_id']." ".$device['os']." ");

  $detect_os = TRUE; // Set TRUE or FALSE for module 'os' (exclude double os detection)
  if ($device['os'] == 'generic' || (isset($options['h']) && $options['h'] == 'new')) // verify if OS has changed
  {
    $detect_os = FALSE;
    $old_os = $device['os'];
    $device['os'] = get_device_os($device);
    if ($device['os'] != $old_os)
    {
      print_warning("Device OS changed: $old_os -> ".$device['os']."!");
      log_event('OS changed: '.$old_os.' -> '.$device['os'], $device, 'system');
      dbUpdate(array('os' => $device['os']), 'devices', '`device_id` = ?', array($device['device_id']));
    }
  }

  if ($config['os'][$device['os']]['group'])
  {
    $device['os_group'] = $config['os'][$device['os']]['group'];
    echo(" (".$device['os_group'].")");
  }

  echo("\n");

  // If we've specified a module, use that, else walk the modules array
  if ($options['m'])
  {
    foreach (explode(",", $options['m']) as $module)
    {
      if (is_file("includes/discovery/$module.inc.php"))
      {
        $m_start = utime();

        include("includes/discovery/$module.inc.php");

        $m_end   = utime();
        $m_run   = round($m_end - $m_start, 4);
        print_message("Module [ $module ] time: $m_run"."s");
      }
    }
  } else {
    foreach ($config['discovery_modules'] as $module => $module_status)
    {
      if (in_array($device['os_group'], $config['os']['discovery_blacklist']))
      {
        // Module is blacklisted for this OS.
        print_debug("Module [ $module ] is in the blacklist for ".$device['os_group']);
      } elseif(in_array($device['os'], $config['os']['discovery_blacklist']))
      {
        // Module is blacklisted for this OS.
        print_debug("Module [ $module ] is in the blacklist for ".$device['os']);
      } else {
        if ($attribs['discover_'.$module] || ( $module_status && !isset($attribs['discover_'.$module])))
        {
          $m_start = utime();

          include("includes/discovery/$module.inc.php");

          $m_end   = utime();
          $m_run   = round($m_end - $m_start, 4);
          print_message("Module [ $module ] time: $m_run"."s");
        } elseif (isset($attribs['discover_'.$module]) && $attribs['discover_'.$module] == "0")
        {
          print_debug("Module [ $module ] disabled on host.");
        } else {
          print_debug("Module [ $module ] disabled globally.");
        }
      }
    }
  }

  // Set type to a predefined type for the OS if it's not already set

  if ($device['type'] == "unknown" || $device['type'] == "")
  {
    if ($config['os'][$device['os']]['type'])
    {
      $device['type'] = $config['os'][$device['os']]['type'];
    }
  }

  $device_end = utime(); $device_run = $device_end - $device_start; $device_time = substr($device_run, 0, 5);

  dbUpdate(array('last_discovered' => array('NOW()'), 'type' => $device['type'], 'last_discovered_timetaken' => $device_time), 'devices', '`device_id` = ?', array($device['device_id']));

  // put performance into devices_perftimes

  dbInsert(array('device_id' => $device['device_id'], 'operation' => 'discover', 'start' => $device_start, 'duration' => $device_run), 'devices_perftimes');

  print_message("Discovered in $device_time seconds");

  // not worth putting discovery data into rrd. it's not done every 5 mins :)

  echo(PHP_EOL);
  $discovered_devices++;
}

// Discover sensors
function discover_sensor(&$valid, $class, $device, $oid, $index, $type, $sensor_descr, $scale = 1, $current = NULL, $options = array(), $poller_type = 'snmp')
{
  global $config;

  // Init main
  $param_main = array('oid' => 'sensor_oid', 'sensor_descr' => 'sensor_descr', 'scale' => 'sensor_multiplier');

  // Init numeric values
  if (!is_numeric($scale) || $scale == 0) { $scale = 1; }
  $param_limits = array('limit_high' => 'sensor_limit', 'limit_high_warn' => 'sensor_limit_warn',
                         'limit_low_warn' => 'sensor_limit_low_warn', 'limit_low' => 'sensor_limit_low');
  foreach ($param_limits as $key => $column)
  {
    $$key = (is_numeric($options[$key]) ? $options[$key] : NULL);
  }

  // Init optional
  $param_opt = array('entPhysicalIndex', 'entPhysicalClass', 'entPhysicalIndex_measured', 'measured_class', 'measured_entity');
  foreach ($param_opt as $key)
  {
    $$key = ($options[$key] ? $options[$key] : NULL);
  }

  print_debug("Discover sensor: $class, ".$device['hostname'].", $oid, $index, $type, $sensor_descr, $scale, $limit_low, $limit_low_warn, $limit_high_warn, $limit_high, $current, $poller_type, $entPhysicalIndex, $entPhysicalClass");

  // Check sensor ignore filters
  foreach ($config['ignore_sensor'] as $bi)        { if (strcasecmp($bi, $sensor_descr) == 0)   { print_debug("Skipped by equals: $bi, $sensor_descr "); return FALSE; } }
  foreach ($config['ignore_sensor_string'] as $bi) { if (stripos($sensor_descr, $bi) !== FALSE) { print_debug("Skipped by strpos: $bi, $sensor_descr "); return FALSE; } }
  foreach ($config['ignore_sensor_regexp'] as $bi) { if (preg_match($bi, $sensor_descr) > 0)    { print_debug("Skipped by regexp: $bi, $sensor_descr "); return FALSE; } }

  if (!is_null($limit_low_warn) && !is_null($limit_high_warn) && ($limit_low_warn > $limit_high_warn))
  {
    // Fix high/low thresholds (i.e. on negative numbers)
    list($limit_high_warn, $limit_low_warn) = array($limit_low_warn, $limit_high_warn);
  }

  if (dbFetchCell('SELECT COUNT(`sensor_id`) FROM `sensors` WHERE `poller_type`= ? AND `sensor_class` = ? AND `device_id` = ? AND `sensor_type` = ? AND `sensor_index` = ?', array($poller_type, $class, $device['device_id'], $type, $index)) == '0')
  {
    if (!isset($config['sensor_states'][$type]))
    {
      if (!$limit_high) { $limit_high = sensor_limit_high($class, $current); }
      if (!$limit_low)  { $limit_low  = sensor_limit_low($class, $current); }

      if (!is_null($limit_low) && !is_null($limit_high) && ($limit_low > $limit_high))
      {
        // Fix high/low thresholds (i.e. on negative numbers)
        list($limit_high, $limit_low) = array($limit_low, $limit_high);
      }
    } else {
      // For state sensors limits always is NULL
      $limit_high = NULL; $limit_low = NULL;
      $limit_high_warn = NULL; $limit_low_warn = NULL;
    }

    $sensor_insert = array('poller_type' => $poller_type, 'sensor_class' => $class, 'device_id' => $device['device_id'],
                           'sensor_index' => $index, 'sensor_type' => $type);
    foreach ($param_main as $key => $column)
    {
      $sensor_insert[$column] = $$key;
    }
    foreach ($param_limits as $key => $column)
    {
      // Convert strings/numbers to (float) or to array('NULL')
      $$key = ($$key === NULL ? array('NULL') : (float)$$key);
      $sensor_insert[$column] = $$key;
    }
    foreach ($param_opt as $key)
    {
      if (is_null($$key)) { $$key = array('NULL'); }
      $sensor_insert[$key] = $$key;
    }

    $sensor_id = dbInsert($sensor_insert, 'sensors');

    $state_insert = array('sensor_id' => $sensor_id, 'sensor_value' => $current, 'sensor_polled' => 'NOW()');
    dbInsert($state_insert, 'sensors-state');

    print_debug("( $sensor_id inserted )");
    echo("+");
    log_event("Sensor added: $class $type $index $sensor_descr", $device, 'sensor', $sensor_id);
  } else {
    $sensor_entry = dbFetchRow("SELECT * FROM `sensors` WHERE `sensor_class` = ? AND `device_id` = ? AND `sensor_type` = ? AND `sensor_index` = ?", array($class, $device['device_id'], $type, $index));

    // Limits
    if (!$sensor_entry['sensor_custom_limit'])
    {
      if (!isset($config['sensor_states'][$type]))
      {
        if (!is_numeric($limit_high) && !is_numeric($limit_low))
        {
          if (!$sensor_entry['sensor_limit'])
          {
            // Calculate a reasonable limit
            $limit_high = sensor_limit_high($class, $current);
          } else {
            // Use existing limit. (this is wrong! --mike)
            $limit_high = $sensor_entry['sensor_limit'];
          }

          if (!$sensor_entry['sensor_limit_low'])
          {
            // Calculate a reasonable limit
            $limit_low = sensor_limit_low($class, $current);
          } else {
            // Use existing limit. (this is wrong! --mike)
            $limit_low = $sensor_entry['sensor_limit_low'];
          }
        }

        // Fix high/low thresholds (i.e. on negative numbers)
        if (!is_null($limit_low) && !is_null($limit_high) && ($limit_low > $limit_high))
        {
          list($limit_high, $limit_low) = array($limit_low, $limit_high);
        }
      } else {
        // For state sensors limits always is NULL
        $limit_high = NULL; $limit_low = NULL;
        $limit_high_warn = NULL; $limit_low_warn = NULL;
      }

      // Update limits
      $update = array();
      $msg_debug = 'Current sensor value: "'.$current.'", scale: "'.$scale.'"'.PHP_EOL;
      foreach ($param_limits as $v => $k)
      {
        $msg_debug .= '  '.$v.': "'.$sensor_entry[$k].'" -> "'.$$v.'"'.PHP_EOL;
        //convert strings/numbers to identical type (float) or to array('NULL') for correct comparison
        $$v = ($$v === NULL ? array('NULL') : (float)$$v);
        $sensor_entry[$k] = ($sensor_entry[$k] === NULL ? array('NULL') : (float)$sensor_entry[$k]);
        if (float_cmp($$v, $sensor_entry[$k]) !== 0)
        {
          $update[$k] = $$v;
        }
      }
      if (count($update))
      {
        echo("L");
        print_debug($msg_debug);
        $msg = 'Sensor updated (limits): '.$class.' '.$type.' '.$index.' '.$sensor_descr.' ';
        foreach (array('L'=>'sensor_limit_low', 'Lw'=>'sensor_limit_low_warn',
                       'Hw'=>'sensor_limit_warn', 'H'=>'sensor_limit') as $v => $k)
        {
          if (isset($update[$k]))
          {
            $msg .= (is_array($update[$k]) ? "[$v: ".$update[$k][0]."]" : "[$v: ".$update[$k]."]");
          }
        }
        log_event($msg, $device, 'sensor', $sensor_entry['sensor_id']);
        $updated = dbUpdate($update, 'sensors', '`sensor_id` = ?', array($sensor_entry['sensor_id']));
      }
    }

    $update = array();
    foreach ($param_main as $key => $column)
    {
      if (float_cmp($$key, $sensor_entry[$column]) !== 0)
      {
        $update[$column] = $$key;
      }
    }
    foreach ($param_opt as $key)
    {
      if ($$key != $sensor_entry[$key])
      {
        $update[$key] = $$key;
      }
    }
    if (count($update))
    {
      $updated = dbUpdate($update, 'sensors', '`sensor_id` = ?', array($sensor_entry['sensor_id']));
      echo("U");
      log_event("Sensor updated: $class $type $index $sensor_descr", $device, 'sensor', $sensor_entry['sensor_id']);
    } else {
      echo(".");
    }
  }
  $valid[$class][$type][$index] = 1;
}

function sensor_limit_low($class, $current)
{
  $limit = NULL;

  switch($class)
  {
    case 'temperature':
      #$limit = $current * 0.7;
      $limit = 0; // FIXME only OK when temp>0
      break;
    case 'voltage':
      if ($current < 0)
      {
        $limit = $current * (1 + (sgn($current) * 0.15));
      }
      else
      {
        $limit = $current * (1 - (sgn($current) * 0.15));
      }
      break;
    case 'humidity':
      $limit = 20;
      break;
    case 'frequency':
      $limit = $current * 0.95;
      break;
    case 'current':
      $limit = NULL;
      break;
    case 'fanspeed':
      $limit = $current * 0.80;
      break;
    case 'power':
      $limit = NULL;
      break;
  }
  return $limit;
}

function sensor_limit_high($class, $current)
{
  $limit = NULL;

  switch($class)
  {
    case 'temperature':
      if ($current < 0)
      {
        // Negative temperatures are usually used for "Thermal margins",
        // indicating how far from the critical point we are.
        $limit = 0;
      } else {
        $limit = $current * 1.60;
      }
      break;
    case 'voltage':
      if ($current < 0)
      {
        $limit = $current * (1 - (sgn($current) * 0.15));
      }
      else
      {
        $limit = $current * (1 + (sgn($current) * 0.15));
      }
      break;
    case 'humidity':
      $limit = 70;
      break;
    case 'frequency':
      $limit = $current * 1.05;
      break;
    case 'current':
      $limit = $current * 1.50;
      break;
    case 'fanspeed':
      $limit = $current * 1.80;
      break;
    case 'power':
      $limit = $current * 1.50;
      break;
  }
  return $limit;
}

function check_valid_sensors($device, $class, $valid, $poller_type = 'snmp')
{
  global $debug;

  $entries = dbFetchRows("SELECT * FROM `sensors` AS S, `devices` AS D WHERE S.`sensor_class` = ? AND S.`device_id` = D.`device_id` AND D.`device_id` = ? AND S.`poller_type` = ?", array($class, $device['device_id'], $poller_type));

  if (count($entries))
  {
    foreach ($entries as $entry)
    {
      $index = $entry['sensor_index'];
      $type = $entry['sensor_type'];
      print_debug($index . " -> " . $type);
      if (!$valid[$class][$type][$index])
      {
        echo("-");
        dbDelete('sensors', "`sensor_id` =  ?", array($entry['sensor_id']));
        log_event("Sensor deleted: ".$entry['sensor_class']." ".$entry['sensor_type']." ". $entry['sensor_index']." ".$entry['sensor_descr'], $device, 'sensor', $entry['sensor_id']);
      }
      unset($oid); unset($type);
    }
  }
}

function discover_juniAtmVp(&$valid, $port_id, $vp_id, $vp_descr)
{
  global $config, $debug;

  if (dbFetchCell("SELECT COUNT(*) FROM `juniAtmVp` WHERE `port_id` = ? AND `vp_id` = ?", array($port_id, $vp_id)) == "0")
  {
     $inserted = dbInsert(array('port_id' => $port_id,'vp_id' => $vp_id,'vp_descr' => $vp_descr), 'juniAtmVp');
     if ($debug) { echo("( $inserted inserted )\n"); }
     #FIXME vv no $device in front of 'juniAtmVp' - will not log correctly!
     log_event("Juniper ATM VP Added: port $port_id vp $vp_id descr $vp_descr", 'juniAtmVp', $inserted);
  }
  else
  {
    echo(".");
  }
  $valid[$port_id][$vp_id] = 1;
}

function discover_link(&$valid, $local_port_id, $protocol, $remote_port_id, $remote_hostname, $remote_port, $remote_platform, $remote_version, $remote_address = NULL)
{
  global $config, $debug;

  $params   = array('protocol', 'remote_port_id', 'remote_hostname', 'remote_port', 'remote_platform', 'remote_version', 'remote_address');
  $links_db = dbFetchRow("SELECT * FROM `links` WHERE `local_port_id` = ? AND `remote_hostname` = ? AND `protocol` = ? AND `remote_port` = ?", array($local_port_id, $remote_hostname, $protocol, $remote_port));
  if (!isset($links_db['id']))
  {
    $update = array('local_port_id' => $local_port_id);
    foreach ($params as $param) { $update[$param] = $$param; if ($$param == NULL) { $update[$param] = array('NULL'); } }
    $id = dbInsert($update, 'links');

    echo("+");
  } else {
    $update = array();
    foreach ($params as $param)
    {
      if ($$param != $links_db[$param]) { $update[$param] = $$param; }
    }
    if (count($update))
    {
      dbUpdate($update, 'links', '`id` = ?', array($links_db['id']));
      echo('U');
    } else {
      echo('.');
    }
  }
  $valid[$local_port_id][$remote_hostname][$remote_port] = 1;
}

function discover_storage(&$valid, $device, $storage_index, $storage_type, $storage_mib, $storage_descr, $storage_units, $storage_size, $storage_used, $storage_hc = 0)
{
  global $config;

  print_debug($device['device_id']." -> $storage_index, $storage_type, $storage_mib, $storage_descr, $storage_units, $storage_size, $storage_used, $storage_hc");

  // Check storage description and size
  if (!($storage_descr && $storage_size > 0)) { return FALSE; }

  // Check storage ignore filters
  foreach ($config['ignore_mount'] as $bi)        { if (strcasecmp($bi, $storage_descr) == 0)   { print_debug("Skipped by equals: $bi, $storage_descr "); return FALSE; } }
  foreach ($config['ignore_mount_string'] as $bi) { if (stripos($storage_descr, $bi) !== FALSE) { print_debug("Skipped by strpos: $bi, $storage_descr "); return FALSE; } }
  foreach ($config['ignore_mount_regexp'] as $bi) { if (preg_match($bi, $storage_descr) > 0)    { print_debug("Skipped by regexp: $bi, $storage_descr "); return FALSE; } }

  $params       = array('storage_index', 'storage_mib', 'storage_type', 'storage_descr', 'storage_hc');
  $params_state = array('storage_units', 'storage_size', 'storage_used', 'storage_free', 'storage_perc');
  $device_id    = $device['device_id'];
  $storage_free = $storage_size - $storage_used;
  $storage_perc = round($storage_used / $storage_size * 100, 2);
  $storage_mib  = strtolower($storage_mib);

  $storage_db = dbFetchRow("SELECT * FROM `storage` WHERE `device_id` = ? AND `storage_index` = ? AND `storage_mib` = ?", array($device_id, $storage_index, $storage_mib));
  if (!isset($storage_db['storage_id']))
  {
    $update = array('device_id' => $device_id);
    foreach ($params as $param) { $update[$param] = ($$param === NULL ? array('NULL') : $$param); }
    $id = dbInsert($update, 'storage');

    $update_state = array('storage_id' => $id);
    foreach ($params_state as $param) { $update_state[$param] = $$param; }
    dbInsert($update_state, 'storage-state');

    echo('+');
    log_event("Storage added: index $storage_index, mib $storage_mib, descr $storage_descr", $device, 'storage', $id);
  } else {
    $update = array();
    foreach ($params as $param)
    {
      if ($$param != $storage_db[$param] ) { $update[$param] = ($$param === NULL ? array('NULL') : $$param); }
    }
    if (count($update))
    {
      dbUpdate($update, 'storage', '`storage_id` = ?', array($storage_db['storage_id']));
      echo('U');
      log_event("Storage updated: index $storage_index, mib $storage_mib, descr $storage_descr", $device, 'storage', $storage_db['storage_id']);
    } else {
      echo('.');
    }
  }
  $valid[$storage_mib][$storage_index] = 1;
}

function discover_processor(&$valid, $device, $processor_oid, $processor_index, $processor_type, $processor_descr, $processor_precision = "1", $current = NULL, $entPhysicalIndex = NULL, $hrDeviceIndex = NULL)
{
  global $config;

  print_debug($device['device_id' ] . " -> $processor_oid, $processor_index, $processor_type, $processor_descr, $processor_precision, $current, $entPhysicalIndex, $hrDeviceIndex");

  // Check processor description
  if (!($processor_descr)) { return FALSE; }

  // Check processor ignore filters
  foreach ($config['ignore_processor'] as $bi)        { if (strcasecmp($bi, $processor_descr) == 0)   { print_debug("Skipped by equals: $bi, $processor_descr "); return FALSE; } }
  foreach ($config['ignore_processor_string'] as $bi) { if (stripos($processor_descr, $bi) !== FALSE) { print_debug("Skipped by strpos: $bi, $processor_descr "); return FALSE; } }
  foreach ($config['ignore_processor_regexp'] as $bi) { if (preg_match($bi, $processor_descr) > 0)    { print_debug("Skipped by regexp: $bi, $processor_descr "); return FALSE; } }

  $params       = array('processor_index', 'entPhysicalIndex', 'hrDeviceIndex', 'processor_oid', 'processor_type', 'processor_descr', 'processor_precision');
  $params_state = array('processor_usage');

  $processor_db = dbFetchRow("SELECT * FROM `processors` WHERE `device_id` = ? AND `processor_index` = ? AND `processor_type` = ?", array($device['device_id'], $processor_index, $processor_type));
  if (!isset($processor_db['processor_id']))
  {
    $update = array('device_id' => $device['device_id']);
    foreach ($params as $param) { $update[$param] = ($$param === NULL ? array('NULL') : $$param); }
    $id = dbInsert($update, 'processors');

    $update_state = array('processor_id' => $id);
    foreach ($params_state as $param) { $update_state[$param] = $$param; }
    dbInsert($update_state, 'processors-state');

    echo('+');
    log_event("Processor added: index $processor_index, type $processor_type, descr $processor_descr", $device, 'processor', $id);
  } else {
    $update = array();
    foreach ($params as $param)
    {
      if ($$param != $processor_db[$param] ) { $update[$param] = ($$param === NULL ? array('NULL') : $$param); }
    }
    if (count($update))
    {
      dbUpdate($update, 'processors', '`processor_id` = ?', array($processor_db['processor_id']));
      echo('U');
      log_event("Processor updated: index $processor_index, mib $processor_type, descr $processor_descr", $device, 'processor', $processor_db['processor_id']);
    } else {
      echo('.');
    }
  }

  $valid[$processor_type][$processor_index] = 1;
}

function discover_mempool(&$valid, $device, $mempool_index, $mempool_mib, $mempool_descr, $mempool_precision = 1, $mempool_total, $mempool_used, $mempool_hc = 0)
{
  global $config;

  print_debug($device['device_id']." -> $mempool_index, $mempool_mib, $mempool_descr, $mempool_precision, $mempool_total, $mempool_used");

  // Check mempool description
  if (!($mempool_descr)) { return FALSE; }

  // Check mempool ignore filters
  foreach ($config['ignore_mempool'] as $bi)        { if (strcasecmp($bi, $mempool_descr) == 0)   { print_debug("Skipped by equals: $bi, $mempool_descr "); return FALSE; } }
  foreach ($config['ignore_mempool_string'] as $bi) { if (stripos($mempool_descr, $bi) !== FALSE) { print_debug("Skipped by strpos: $bi, $mempool_descr "); return FALSE; } }
  foreach ($config['ignore_mempool_regexp'] as $bi) { if (preg_match($bi, $mempool_descr) > 0)    { print_debug("Skipped by regexp: $bi, $mempool_descr "); return FALSE; } }

  $params       = array('mempool_index', 'mempool_mib', 'mempool_descr', 'mempool_precision', 'mempool_hc');
  $params_state = array('mempool_total', 'mempool_used', 'mempool_free', 'mempool_perc');
  if (!$mempool_precision) { $mempool_precision = 1; }
  $mempool_mib  = strtolower($mempool_mib);
  $mempool_free = $mempool_total - $mempool_used;
  $mempool_perc = round($mempool_used / $mempool_total * 100, 2);
  $mempool_db   = dbFetchRow("SELECT * FROM `mempools` WHERE `device_id` = ? AND `mempool_index` = ? AND `mempool_mib` = ?", array($device['device_id'], $mempool_index, $mempool_mib));

  if (!isset($mempool_db['mempool_id']))
  {
    $update = array('device_id' => $device['device_id']);
    foreach ($params as $param) { $update[$param] = $$param; }
    $id = dbInsert($update, 'mempools');

    $update_state = array('mempool_id' => $id, 'mempool_polled' => time());
    foreach ($params_state as $param) { $update_state[$param] = $$param; }
    dbInsert($update_state, 'mempools-state');
    echo('+');
    log_event("Memory pool added: mib $mempool_mib, index $mempool_index, descr $mempool_descr", $device, 'mempool', $id);
  } else {
    $update = array();
    foreach ($params as $param)
    {
      if ($$param != $mempool_db[$param]) { $update[$param] = $$param; }
    }
    if (count($update))
    {
      dbUpdate($update, 'mempools', '`mempool_id` = ?', array($mempool_db['mempool_id']));
      echo('U');
      log_event("Memory pool updated: mib $mempool_mib, index $mempool_index, descr $mempool_descr", $device, 'mempool', $mempool_db['mempool_id']);
    } else {
      echo('.');
    }
  }

  $valid[$mempool_mib][$mempool_index] = 1;
}

function discover_toner(&$valid, $device, $toner_oid, $toner_index, $toner_type, $toner_descr, $toner_capacity_oid = NULL, $toner_capacity = NULL, $toner_current = NULL)
{
  global $config;

  print_debug($device['device_id'] . " -> $toner_oid, $toner_index, $toner_type, $toner_descr, $toner_capacity, $toner_capacity_oid, $toner_capacity, $toner_current");

  // Check toner description
  if (!($toner_descr)) { return FALSE; }

  // Check toner ignore filters
  foreach ($config['ignore_toner'] as $bi)        { if (strcasecmp($bi, $toner_descr) == 0)   { print_debug("Skipped by equals: $bi, $toner_descr "); return FALSE; } }
  foreach ($config['ignore_toner_string'] as $bi) { if (stripos($toner_descr, $bi) !== FALSE) { print_debug("Skipped by strpos: $bi, $toner_descr "); return FALSE; } }
  foreach ($config['ignore_toner_regexp'] as $bi) { if (preg_match($bi, $toner_descr) > 0)    { print_debug("Skipped by regexp: $bi, $toner_descr "); return FALSE; } }

  $params       = array('toner_index', 'toner_type', 'toner_descr', 'toner_capacity', 'toner_capacity_oid', 'toner_oid', 'toner_current');

  $toner_db   = dbFetchRow("SELECT * FROM `toner` WHERE `device_id` = ? AND `toner_index` = ? AND `toner_type` = ? AND `toner_capacity_oid` = ?", array($device['device_id'], $toner_index, $toner_type, $toner_capacity_oid));

  if (!isset($toner_db['toner_id']))
  {
    $update = array('device_id' => $device['device_id']);
    foreach ($params as $param) { $update[$param] = $$param; }
    $id = dbInsert($update, 'toner');

    echo('+');
    log_event("Toner added: type $toner_type, index $toner_index, descr $toner_descr", $device, 'toner', $id);
  } else {
    $update = array();
    foreach ($params as $param)
    {
      if ($$param != $toner_db[$param]) { $update[$param] = $$param; }
    }
    if (count($update))
    {
      dbUpdate($update, 'toner', '`toner_id` = ?', array($toner_db['toner_id']));
      echo('U');
      log_event("Toner updated: type $toner_type, index $toner_index, descr $toner_descr", $device, 'toner', $toner_db['toner_id']);
    } else {
      echo('.');
    }
  }

  $valid[$toner_type][$toner_index] = 1;
}

function discover_inventory(&$valid, $device, $index, $inventory_tmp, $mib = 'entPhysical')
{
  $entPhysical_oids = array('entPhysicalDescr', 'entPhysicalClass', 'entPhysicalName',
                            'entPhysicalHardwareRev', 'entPhysicalFirmwareRev', 'entPhysicalSoftwareRev',
                            'entPhysicalAlias', 'entPhysicalAssetID', 'entPhysicalIsFRU',
                            'entPhysicalModelName', 'entPhysicalVendorType', 'entPhysicalSerialNum',
                            'entPhysicalContainedIn', 'entPhysicalParentRelPos', 'entPhysicalMfgName',
                            'ifIndex');
  $numeric_oids     = array('entPhysicalContainedIn', 'entPhysicalParentRelPos', 'ifIndex'); // DB type 'int'

  if (!is_array($inventory_tmp) || !is_numeric($index)) { return FALSE; }
  $inventory = array('entPhysicalIndex' => $index);
  foreach ($entPhysical_oids as $oid)
  {
    $inventory[$oid] = str_replace(array('"', "'"), '', $inventory_tmp[$oid]);
  }

  if (!isset($inventory['entPhysicalModelName'])) { $inventory['entPhysicalModelName'] = $inventory['entPhysicalName']; }

  $query = 'SELECT * FROM `entPhysical` WHERE `device_id` = ? AND `entPhysicalIndex` = ?'; // AND `inventory_mib` = $inventory_mib
  $inventory_db = dbFetchRow($query, array($device['device_id'], $index));

  if (!is_array($inventory_db))
  {
    $inventory['device_id'] = $device['device_id'];
    $id = dbInsert($inventory, 'entPhysical');
    log_event('Inventory added: class '.$inventory['entPhysicalClass'].', name '.$inventory['entPhysicalName'].', index '.$index, $device, 'inventory', $id);
    echo('+');
  } else {
    foreach ($entPhysical_oids as $oid)
    {
      if ($inventory[$oid] != $inventory_db[$oid])
      {
        if (in_array($oid, $numeric_oids) && $inventory[$oid] == '')
        {
          $update[$oid] = array('NULL');
        } else {
          $update[$oid] = $inventory[$oid];
        }
      }
    }
    if (count($update))
    {
      $id = $inventory_db['entPhysical_id'];
      dbUpdate($update, 'entPhysical', '`device_id` = ? AND `entPhysicalIndex` = ?', array($device['device_id'], $index));
      if (!(isset($update['ifIndex']) && count($update) === 1)) // Do not eventlog if changed ifIndex only
      {
        log_event('Inventory updated: class '.$inventory['entPhysicalClass'].', name '.$inventory['entPhysicalName'].', index '.$index, $device, 'inventory', $id);
      }
      echo('U');
    } else {
      echo('.');
    }
  }
  $valid[$mib][$index] = 1;
}

function check_valid_inventory($device, $valid_tmp)
{
  // Note. For now $valid mib type not used
  $valid = array();
  foreach ($valid_tmp as $mib => $array)
  {
    $valid += $array;
  }

  $query = 'SELECT * FROM `entPhysical` WHERE `device_id` = ?'; // AND `inventory_mib` = $inventory_mib
  $entries = dbFetchRows($query, array($device['device_id']));

  if (count($entries))
  {
    foreach ($entries as $entry)
    {
      $index = $entry['entPhysicalIndex'];
      if (!$valid[$index])
      {
        echo("-");
        dbDelete('entPhysical', "`entPhysical_id` = ?", array($entry['entPhysical_id']));
        log_event('Inventory deleted: class '.$entry['entPhysicalClass'].', name '.$entry['entPhysicalName'].', index '.$index, $device, 'inventory', $entry['entPhysical_id']);
      }
    }
  }
}

function is_bad_xdp($hostname)
{
  global $config;

  if (is_array($config['bad_xdp']))
  {
    foreach ($config['bad_xdp'] as $bad_xdp)
    {
      if (strstr($hostname, $bad_xdp))
      {
        return TRUE;
      }
    }
  }

  if (is_array($config['bad_xdp_regexp']))
  {
    foreach ($config['bad_xdp_regexp'] as $bad_xdp)
    {
      if (preg_match($bad_xdp ."i", $hostname))
      {
        return TRUE;
      }
    }
  }

  return FALSE;
}

// EOF
