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
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

/// FIXME. Deprecated.
function discover_new_device_ip($host, $source = 'xdp', $protocol = NULL, $device = NULL, $snmp_port = NULL)
{
  print_error("Function discover_new_device_ip() is deprecated, use discover_new_device().");
  discover_new_device($host, $source, $protocol, $device, $snmp_port);
}

function discover_new_device($hostname, $source = 'xdp', $protocol = NULL, $device = NULL, $snmp_port = 161)
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
      $ip = $hostname;
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
        $ip = ($ip_version == 4 ? $ip : Net_IPv6::uncompress($ip, TRUE));
        $db = dbFetchRow('SELECT D.`hostname` FROM ipv'.$ip_version.'_addresses AS A
                         LEFT JOIN `ports`   AS P ON A.`port_id`   = P.`port_id`
                         LEFT JOIN `devices` AS D ON D.`device_id` = P.`device_id`
                         WHERE D.`disabled` = 0 AND A.`ipv'.$ip_version.'_address` = ?', array($ip));
        if ($db)
        {
          print_debug('Already have device '.$db['hostname']." with same $ip");
          return FALSE;
        }

        // Detect snmp transport
        $snmp_transport = ($ip_version == 4 ? 'udp' : 'udp6');

        $new_device = detect_device_snmpauth($ip, $snmp_port, $snmp_transport);
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
            $snmp_v3 = array();
            if ($new_device['snmp_version'] === 'v3')
            {
              $snmp_v3['snmp_authlevel']  = $new_device['snmp_authlevel'];
              $snmp_v3['snmp_authname']   = $new_device['snmp_authname'];
              $snmp_v3['snmp_authpass']   = $new_device['snmp_authpass'];
              $snmp_v3['snmp_authalgo']   = $new_device['snmp_authalgo'];
              $snmp_v3['snmp_cryptopass'] = $new_device['snmp_cryptopass'];
              $snmp_v3['snmp_cryptoalgo'] = $new_device['snmp_cryptoalgo'];
            }
            $remote_device_id = createHost($new_device['hostname'], $new_device['snmp_community'], $new_device['snmp_version'], $new_device['snmp_port'], $new_device['snmp_transport'], $snmp_v3);

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

  // Initialise variables
  $valid           = array(); // Reset $valid array
  $cache_discovery = array(); // Specific discovery cache for exchange snmpwalk data betwen modules (memory/storage/sensors/etc)
  $attribs         = get_dev_attribs($device['device_id']);
  $device_start    = utime(); // Start counting device poll time

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
      log_event('OS changed: '.$old_os.' -> '.$device['os'], $device, 'device', $device['device_id'], 'warning');
      dbUpdate(array('os' => $device['os']), 'devices', '`device_id` = ?', array($device['device_id']));
    }
  }

  if ($config['os'][$device['os']]['group'])
  {
    $device['os_group'] = $config['os'][$device['os']]['group'];
    echo(" (".$device['os_group'].")");
  }

  echo(PHP_EOL);

  // If we've specified a module, use that, else walk the modules array
  if ($options['m'])
  {
    foreach (explode(",", $options['m']) as $module)
    {
      if (is_file("includes/discovery/".$module.".inc.php"))
      {
        $m_start = utime();
        $GLOBALS['module_stats'][$module] = array();

        include("includes/discovery/".$module.".inc.php");

        $m_end   = utime();
        $GLOBALS['module_stats'][$module]['time'] = round($m_end - $m_start, 4);
        print_module_stats($device, $module);
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
          $GLOBALS['module_stats'][$module] = array();

          include("includes/discovery/$module.inc.php");

          $m_end   = utime();
          $GLOBALS['module_stats'][$module]['time'] = round($m_end - $m_start, 4);
          print_module_stats($device, $module);
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

  dbUpdate(array('last_discovered' => array('NOW()'), 'type' => $device['type'], 'last_discovered_timetaken' => $device_time, 'force_discovery' => 0), 'devices', '`device_id` = ?', array($device['device_id']));

  // put performance into devices_perftimes

  dbInsert(array('device_id' => $device['device_id'], 'operation' => 'discover', 'start' => $device_start, 'duration' => $device_run), 'devices_perftimes');

  print_message("Device [ ".$device['hostname']." ] discovered in $device_time seconds");

  // not worth putting discovery data into rrd. it's not done every 5 mins :)

  echo(PHP_EOL);
  $discovered_devices++;
  
  // Clean
  unset($cache_discovery);
}

// Discover status (called from discover_sensor)
function discover_status($device, $oid, $index, $type, $status_descr, $current = NULL, $options = array(), $poller_type = 'snmp')
{
  global $config;

  // Init main
  $param_main = array('oid' => 'status_oid', 'status_descr' => 'status_descr');

  // Check state value
  if ($current !== NULL)
  {
    $state = state_string_to_numeric($type, $current);
    if ($state === FALSE)
    {
      print_debug("Skipped by unknown state value: $current, $status_descr ");
      return FALSE;
    }
    else if ($config['status_states'][$type][$state]['event'] == 'ignore')  // FIXME -- status_states -> STATUS_STATES
    {
      print_debug("Skipped by ignored state value: ".$config['status_states'][$type][$state]['name'].", $status_descr ");
      return FALSE;
    }
    $current = $state;
  }

  // Init optional
  $param_opt = array('entPhysicalIndex', 'entPhysicalClass', 'entPhysicalIndex_measured', 'measured_class', 'measured_entity');
  foreach ($param_opt as $key)
  {
    $$key = ($options[$key] ? $options[$key] : NULL);
  }

  print_debug("Discover status: ".$device['hostname'].", $oid, $index, $type, $status_descr, $current, $poller_type, $entPhysicalIndex, $entPhysicalClass");

  // Check sensor ignore filters
  foreach ($config['ignore_sensor'] as $bi)        { if (strcasecmp($bi, $status_descr) == 0)   { print_debug("Skipped by equals: $bi, $status_descr "); return FALSE; } }
  foreach ($config['ignore_sensor_string'] as $bi) { if (stripos($status_descr, $bi) !== FALSE) { print_debug("Skipped by strpos: $bi, $status_descr "); return FALSE; } }
  foreach ($config['ignore_sensor_regexp'] as $bi) { if (preg_match($bi, $status_descr) > 0)    { print_debug("Skipped by regexp: $bi, $status_descr "); return FALSE; } }

  if (dbFetchCell('SELECT COUNT(`status_id`) FROM `status`
                   WHERE `device_id` = ? AND `status_type` = ? AND `status_index` = ? AND `poller_type`= ?;',
      array($device['device_id'], $type, $index, $poller_type)) == '0')
  {
    $status_insert = array('poller_type'  => $poller_type, 'device_id'   => $device['device_id'],
                           'status_index' => $index,       'status_type' => $type);

    foreach ($param_main as $key => $column)
    {
      $status_insert[$column] = $$key;
    }

    foreach ($param_opt as $key)
    {
      if (is_null($$key)) { $$key = array('NULL'); } // If param null, convert to array(NULL) for dbFacile
      $status_insert[$key] = $$key;
    }

    $status_id = dbInsert($status_insert, 'status');

    $status_insert = array('status_id' => $status_id, 'status_value' => $current, 'status_polled' => 'NOW()');
    dbInsert($status_insert, 'status-state');

    print_debug("( $status_id inserted )");
    echo("+");
    if ($poller_type != 'ipmi')
    {
      // Suppress events for IPMI, see: http://jira.observium.org/browse/OBSERVIUM-959
      log_event("Status added: $class $type $index $status_descr", $device, 'status', $status_id);
    }
  } else {
    $status_entry = dbFetchRow("SELECT * FROM `status` WHERE `device_id` = ? AND `status_type` = ? AND `status_index` = ? AND `poller_type`= ?;", array($device['device_id'], $type, $index, $poller_type));

    // FIXME. Remove in r7000
    /* DS also changed.. fuck.
    $old_rrd = $config['rrd_dir'] . "/".$device['hostname']."/" . get_sensor_rrd($device, array('sensor_class' => 'state', 'sensor_type' => $type, 'sensor_index' => $index, 'sensor_descr' => $status_entry['status_descr'], 'poller_type' => $poller_type));
    $new_rrd = $config['rrd_dir'] . "/".$device['hostname']."/" . get_status_rrd($device, $status_entry);
    if (is_file($old_rrd)) { rename($old_rrd, $new_rrd); print_warning("Moved RRD"); }
    */

    $update = array();
    foreach ($param_main as $key => $column)
    {
      if ($$key != $status_entry[$column])
      {
        $update[$column] = $$key;
      }
    }
    foreach ($param_opt as $key)
    {
      if ($$key != $status_entry[$key])
      {
        $update[$key] = $$key;
      }
    }

    if (count($update))
    {
      $updated = dbUpdate($update, 'status', '`status_id` = ?', array($status_entry['status_id']));
      echo("U");
      log_event("Status updated: $type $index $status_descr", $device, 'status', $status_entry['status_id']);
    } else {
      echo(".");
    }
  }
  $GLOBALS['valid']['status'][$type][$index] = 1;
}

// Discover sensors
function discover_sensor(&$valid, $class, $device, $oid, $index, $type, $sensor_descr, $scale = 1, $current = NULL, $options = array(), $poller_type = 'snmp')
{
  global $config;

  // If this is actually a status indicator, pass it off to discover_status() then return.
  if ($class == 'state' || $class == 'status')
  {
    print_debug("Redirect call to discover_status().");
    $return = discover_status($device, $oid, $index, $type, $sensor_descr, $current, $options, $poller_type);
    return $return;
  }

  // Init main
  $param_main = array('oid' => 'sensor_oid', 'sensor_descr' => 'sensor_descr', 'scale' => 'sensor_multiplier');

  // Init numeric values
  if (!is_numeric($scale) || $scale == 0) { $scale = 1; }

  // Skip discovery sensor if value not numeric or null (default)
  if ($current !== NULL)
  {
    // Some retarded devices report data with spaces and commas
    // STRING: "  20,4"
    $current = snmp_fix_numeric($current);
  }

  if (is_numeric($current))
  {
    $f2c = FALSE;
    if ($class == 'temperature')
    {
      // This is weird hardcode for convert Fahrenheit to Celsius
      foreach (array(1, 0.1) as $scale_f2c)
      {
        if (float_cmp($scale, $scale_f2c * 5/9) === 0)
        {
          //$scale = $scale_tmp;
          $f2c = TRUE;
          break;
        }
      }
    }
    if ($f2c)
    {
      $current = f2c($current * $scale_f2c);
      print_debug('TEMPERATURE sensor: Fahrenheit -> Celsius');
    } else {
      $current *= $scale;
    }
  }
  else if ($current !== NULL)
  {
    print_debug("Sensor skipped by not numeric value: $current, $sensor_descr ");
    return FALSE;
  }

  $param_limits = array('limit_high' => 'sensor_limit',     'limit_high_warn' => 'sensor_limit_warn',
                        'limit_low'  => 'sensor_limit_low', 'limit_low_warn'  => 'sensor_limit_low_warn');
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

  print_debug("Discover sensor: $class, ".$device['hostname'].", $oid, $index, $type, $sensor_descr, SCALE: $scale, LIMITS: ($limit_low, $limit_low_warn, $limit_high_warn, $limit_high), CURRENT: $current, $poller_type, $entPhysicalIndex, $entPhysicalClass");

  // Check sensor ignore filters
  foreach ($config['ignore_sensor'] as $bi)        { if (strcasecmp($bi, $sensor_descr) == 0)   { print_debug("Skipped by equals: $bi, $sensor_descr "); return FALSE; } }
  foreach ($config['ignore_sensor_string'] as $bi) { if (stripos($sensor_descr, $bi) !== FALSE) { print_debug("Skipped by strpos: $bi, $sensor_descr "); return FALSE; } }
  foreach ($config['ignore_sensor_regexp'] as $bi) { if (preg_match($bi, $sensor_descr) > 0)    { print_debug("Skipped by regexp: $bi, $sensor_descr "); return FALSE; } }

  if (!is_null($limit_low_warn) && !is_null($limit_high_warn) && ($limit_low_warn > $limit_high_warn))
  {
    // Fix high/low thresholds (i.e. on negative numbers)
    list($limit_high_warn, $limit_low_warn) = array($limit_low_warn, $limit_high_warn);
  }

  if (dbFetchCell('SELECT COUNT(`sensor_id`) FROM `sensors`
                   WHERE `poller_type`= ? AND `sensor_class` = ? AND `device_id` = ? AND `sensor_type` = ? AND `sensor_index` = ?',
                   array($poller_type, $class, $device['device_id'], $type, $index)) == '0')
  {
    if (!$limit_high) { $limit_high = sensor_limit_high($class, $current); }
    if (!$limit_low)  { $limit_low  = sensor_limit_low($class, $current); }

    if (!is_null($limit_low) && !is_null($limit_high) && ($limit_low > $limit_high))
    {
      // Fix high/low thresholds (i.e. on negative numbers)
      list($limit_high, $limit_low) = array($limit_low, $limit_high);
      print_debug("High/low limits swapped.");
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
    if ($poller_type != 'ipmi')
    {
      // Suppress events for IPMI, see: http://jira.observium.org/browse/OBSERVIUM-959
      log_event("Sensor added: $class $type $index $sensor_descr", $device, 'sensor', $sensor_id);
    }
  } else {
    $sensor_entry = dbFetchRow("SELECT * FROM `sensors` WHERE `sensor_class` = ? AND `device_id` = ? AND `sensor_type` = ? AND `sensor_index` = ?", array($class, $device['device_id'], $type, $index));

    // Limits
    if (!$sensor_entry['sensor_custom_limit'])
    {
      if (!is_numeric($limit_high) && !is_numeric($limit_low))
      {
        if ($sensor_entry['sensor_limit'] !== '')
        {
          // Calculate a reasonable limit
          $limit_high = sensor_limit_high($class, $current);
        } else {
          // Use existing limit. (this is wrong! --mike)
          $limit_high = $sensor_entry['sensor_limit'];
        }

        if ($sensor_entry['sensor_limit_low'] !== '')
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
        print_debug("High/low limits swapped.");
      }

      // Update limits
      $update = array();
      $update_msg = array();
      $debug_msg = 'Current sensor value: "'.$current.'", scale: "'.$scale.'"'.PHP_EOL;
      foreach ($param_limits as $key => $column)
      {
        // $key - param name, $$key - param value, $column - column name in DB for $key
        $debug_msg .= '  '.$key.': "'.$sensor_entry[$column].'" -> "'.$$key.'"'.PHP_EOL;
        //convert strings/numbers to identical type (float) or to array('NULL') for correct comparison
        $$key = ($$key === NULL ? array('NULL') : (float)$$key);
        $sensor_entry[$column] = ($sensor_entry[$column] === NULL ? array('NULL') : (float)$sensor_entry[$column]);
        if (float_cmp($$key, $sensor_entry[$column], 0.1) !== 0)
        {
          $update[$column] = $$key;
          $update_msg[] = $key.' -> "'.(is_array($$key) ? 'NULL' : $$key).'"';
        }
      }
      if (count($update))
      {
        echo("L");
        print_debug($debug_msg);
        log_event('Sensor updated (limits): '.implode(', ', $update_msg), $device, 'sensor', $sensor_entry['sensor_id']);
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
  $entries = dbFetchRows("SELECT * FROM `sensors` WHERE `device_id` = ? AND `sensor_class` = ? AND `poller_type` = ?", array($device['device_id'], $class, $poller_type));

  if (count($entries))
  {
    foreach ($entries as $entry)
    {
      $index = $entry['sensor_index'];
      $type  = $entry['sensor_type'];
      if (!$valid[$class][$type][$index])
      {
        echo("-");
        print_debug("Sensor deleted: $index -> $type");
        dbDelete('sensors',       "`sensor_id` = ?", array($entry['sensor_id']));
        dbDelete('sensors-state', "`sensor_id` = ?", array($entry['sensor_id']));
        if ($poller_type != 'ipmi')
        {
          // Suppress events for IPMI, see: http://jira.observium.org/browse/OBSERVIUM-959
          log_event("Sensor deleted: ".$entry['sensor_class']." ".$entry['sensor_type']." ". $entry['sensor_index']." ".$entry['sensor_descr'], $device, 'sensor', $entry['sensor_id']);
        }
      }
    }
  }
}

function check_valid_status($device, $valid, $poller_type = 'snmp')
{
  $entries = dbFetchRows("SELECT * FROM `status` WHERE `device_id` = ? AND `poller_type` = ?", array($device['device_id'], $poller_type));

  if (count($entries))
  {
    foreach ($entries as $entry)
    {
      $index = $entry['status_index'];
      $type  = $entry['status_type'];
      if (!$valid[$type][$index])
      {
        echo("-");
        print_debug("Status deleted: $index -> $type");
        dbDelete('status',       "`status_id` = ?", array($entry['status_id']));
        dbDelete('status-state', "`status_id` = ?", array($entry['status_id']));
        if ($poller_type != 'ipmi')
        {
          // Suppress events for IPMI, see: http://jira.observium.org/browse/OBSERVIUM-959
          log_event("Status deleted: ".$entry['status_class']." ".$entry['status_type']." ". $entry['status_index']." ".$entry['status_descr'], $device, 'status', $entry['status_id']);
        }
      }
    }
  }
}

function discover_juniAtmVp(&$valid, $port_id, $vp_id, $vp_descr)
{
  global $config;

  if (dbFetchCell("SELECT COUNT(*) FROM `juniAtmVp` WHERE `port_id` = ? AND `vp_id` = ?", array($port_id, $vp_id)) == "0")
  {
     $inserted = dbInsert(array('port_id' => $port_id,'vp_id' => $vp_id,'vp_descr' => $vp_descr), 'juniAtmVp');

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
  global $config;

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
  if (!($processor_descr))
  {
    print_debug("Skipped by empty description: $processor_descr ");
    return FALSE;
  } else {
    $processor_descr = substr($processor_descr, 0, 64); // Limit descr to 64 chars accordingly as in DB
  }
  // Skip discovery processor if value not numeric or null(default)
  if ($current !== NULL) { $current = snmp_fix_numeric($current); } // Remove unnecessary spaces
  if (!(is_numeric($current) || $current === NULL))
  {
    print_debug("Skipped by not numeric value: $current, $processor_descr ");
    return FALSE;
  }
  
  // Check processor ignore filters
  foreach ($config['ignore_processor'] as $bi)        { if (strcasecmp($bi, $processor_descr) == 0)   { print_debug("Skipped by equals: $bi, $processor_descr "); return FALSE; } }
  foreach ($config['ignore_processor_string'] as $bi) { if (stripos($processor_descr, $bi) !== FALSE) { print_debug("Skipped by strpos: $bi, $processor_descr "); return FALSE; } }
  foreach ($config['ignore_processor_regexp'] as $bi) { if (preg_match($bi, $processor_descr) > 0)    { print_debug("Skipped by regexp: $bi, $processor_descr "); return FALSE; } }

  $params       = array('processor_index', 'entPhysicalIndex', 'hrDeviceIndex', 'processor_oid', 'processor_type', 'processor_descr', 'processor_precision');
  //$params_state = array('processor_usage');

  $processor_db = dbFetchRow("SELECT * FROM `processors` WHERE `device_id` = ? AND `processor_index` = ? AND `processor_type` = ?", array($device['device_id'], $processor_index, $processor_type));
  if (!isset($processor_db['processor_id']))
  {
    $update = array('device_id' => $device['device_id']);
    foreach ($params as $param) { $update[$param] = ($$param === NULL ? array('NULL') : $$param); }
    $id = dbInsert($update, 'processors');

    $update_state = array('processor_id' => $id, 'processor_usage' => $current);
    //foreach ($params_state as $param) { $update_state[$param] = $$param; }
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
  if (!($mempool_descr))
  {
    return FALSE;
  } else {
    $mempool_descr = substr($mempool_descr, 0, 64); // Limit descr to 64 chars accordingly as in DB
  }

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
    print_debug('Inventory added: class '.$inventory['entPhysicalClass'].', name '.$inventory['entPhysicalName'].', index '.$index);
    $GLOBALS['module_stats']['inventory']['added']++; //echo('+');
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
      print_debug('Inventory updated: class '.$inventory['entPhysicalClass'].', name '.$inventory['entPhysicalName'].', index '.$index);
      $GLOBALS['module_stats']['inventory']['updated']++; //echo('U');
    } else {
      $GLOBALS['module_stats']['inventory']['unchanged']++; //echo('.');
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
        dbDelete('entPhysical', "`entPhysical_id` = ?", array($entry['entPhysical_id']));
        print_debug('Inventory deleted: class '.$entry['entPhysicalClass'].', name '.$entry['entPhysicalName'].', index '.$index);
        $GLOBALS['module_stats']['inventory']['deleted']++; //echo('-');
      }
    }
  }
}

function is_bad_xdp($hostname, $platform = '')
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

  if ($platform)
  {
    foreach ($config['bad_xdp_platform'] as $bad_xdp)
    {
      if (stripos($platform, $bad_xdp) !== FALSE)
      {
        return TRUE;
      }
    }
  }

  return FALSE;
}

// EOF
