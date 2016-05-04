<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage functions
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

// Observium Includes

include($config['install_dir'] . "/includes/common.inc.php");
include($config['install_dir'] . "/includes/rrdtool.inc.php");
include($config['install_dir'] . "/includes/billing.inc.php");
include($config['install_dir'] . "/includes/syslog.inc.php");
include($config['install_dir'] . "/includes/rewrites.inc.php");
include($config['install_dir'] . "/includes/snmp.inc.php");
include($config['install_dir'] . "/includes/services.inc.php");
include($config['install_dir'] . "/includes/dbFacile.php");
include($config['install_dir'] . "/includes/entities.inc.php");
include($config['install_dir'] . "/includes/wifi.inc.php");
include($config['install_dir'] . "/includes/geolocation.inc.php");

if (OBSERVIUM_EDITION != 'community')
{
  include($config['install_dir'] . "/includes/alerts.inc.php");
  include($config['install_dir'] . "/includes/groups.inc.php");
} else {
  include($config['install_dir'] . "/includes/community.inc.php");
}

// StatsD export class
// This is not currently in SVN, do not enable it.
if ($config['statsd']['enable'] && is_file($config['install_dir'] . "/includes/statsd.inc.php"))
{
  include($config['install_dir'] . "/includes/statsd.inc.php");
}

if (OBSERVIUM_EDITION != 'community' && $config['email']['enable'])
{
  // Use Pear::Mail and Pear::Mail_Mime for email alerts
  include("Mail/Mail.php");
  include("Mail/mime.php");
}

// Include file for custom functions, i.e. short_hostname
if (is_file($config['install_dir'] . "/includes/custom.inc.php"))
{
  include($config['install_dir'] . "/includes/custom.inc.php");
}

// DOCME needs phpdoc block
// Send to AMQP via UDP-based python proxy.
// TESTME needs unit testing
// MOVEME to includes/common.inc.php
function messagebus_send($message)
{
  global $config;

  if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))
  {
    $message = json_encode($message);
    print_debug('Sending JSON via AQMP: ' . $message);
    socket_sendto($socket, $message, strlen($message), 0, $config['amqp']['proxy']['host'], $config['amqp']['proxy']['port']);
    socket_close($socket);
    return TRUE;
  } else {
    print_error("Failed to create UDP socket towards AQMP proxy.");
    return FALSE;
  }
}

// DOCME needs phpdoc block
// Sorts an $array by a passed field.
// TESTME needs unit testing
// MOVEME to includes/common.inc.php
function array_sort($array, $on, $order='SORT_ASC')
{
  $new_array = array();
  $sortable_array = array();

  if (count($array) > 0)
  {
    foreach ($array as $k => $v)
    {
      if (is_array($v))
      {
        foreach ($v as $k2 => $v2)
        {
          if ($k2 == $on)
          {
            $sortable_array[$k] = $v2;
          }
        }
      } else {
        $sortable_array[$k] = $v;
      }
    }

    switch ($order)
    {
      case 'SORT_ASC':
        asort($sortable_array);
        break;
      case 'SORT_DESC':
        arsort($sortable_array);
        break;
    }

    foreach ($sortable_array as $k => $v)
    {
      $new_array[$k] = $array[$k];
    }
  }

  return $new_array;
}

// Another sort array function
// http://php.net/manual/en/function.array-multisort.php#100534
// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/common.inc.php
function array_sort_by()
{
  $args = func_get_args();
  $data = array_shift($args);
  foreach ($args as $n => $field)
  {
    if (is_string($field))
    {
      $tmp = array();
      foreach ($data as $key => $row)
      {
        $tmp[$key] = $row[$field];
      }
      $args[$n] = $tmp;
    }
  }
  $args[] = &$data;
  call_user_func_array('array_multisort', $args);
  return array_pop($args);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function include_wrapper($filename)
{
  global $config;

  include($filename);
}

// Strip all non-alphanumeric characters from a string.
// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/common.inc.php
function only_alphanumeric($string)
{
  return preg_replace('/[^a-zA-Z0-9]/', '', $string);
}

// Detect the device's OS
// DOCME needs phpdoc block
function get_device_os($device)
{
  global $config;

  // If $recheck sets as TRUE, verified that 'os' corresponds to the old value.
  $recheck = (strlen($device['os']) == 0 || $device['os'] == 'generic') ? FALSE : TRUE;

  $sysDescr     = snmp_get($device, "sysDescr.0", "-Ovq", "SNMPv2-MIB", mib_dirs());
  $sysObjectId  = snmp_get($device, "sysObjectID.0", "-Ovqn", "SNMPv2-MIB", mib_dirs());
  if (strpos($sysObjectId, 'Wrong Type') !== FALSE)
  {
    // Wrong Type (should be OBJECT IDENTIFIER): "1.3.6.1.4.1.25651.1.2"
    list(, $sysObjectId) = explode(':', $sysObjectId);
    $sysObjectId = '.'.trim($sysObjectId, ' ."');
  }

  print_debug("| $sysDescr | $sysObjectId | ");

  if (strlen($sysObjectId) > 4)
  {
    $sysObjectID_def = array();
    // Generate full array with sysObjectId from definitions
    foreach (array_keys($config['os']) as $cos)
    {
      foreach ($config['os'][$cos]['sysObjectID'] as $oid)
      {
        $oid = trim($oid);
        if (isset($sysObjectID_def[$oid]) && strpos($sysObjectID_def[$oid], 'test_') !== 0)
        {
          print_error("Duplicate sysObjectID '$oid' in definitions for OSes: ".$sysObjectID_def[$oid]." and $cos!");
          continue;
        }
        $sysObjectID_def[$oid] = $cos;
      }
    }
    krsort($sysObjectID_def); // Resort array by key with high to low order!
    //var_dump($sysObjectID_def);
  }

  // By first check all sysObjectID, this is preferable!
  foreach ($sysObjectID_def as $oid => $cos)
  {
    if (substr($oid, -1) === '.')
    {
      // Use wildcard compare if sysObjectID definition have '.' at end
      if (strpos($sysObjectId, $oid) === 0) { $os = $cos; break; }
    } else {
      // Use exact match sysObjectID definition or wildcard compare with '.' at end
      if ($sysObjectId === $oid || strpos($sysObjectId, $oid.'.') === 0) { $os = $cos; break; }
    }
  }

  if ($recheck)
  {
    $old_os = $device['os'];

    if (empty($sysDescr))
    {
      // If sysDescr empty - return old os, because some snmp error
      return $old_os;
    }

    // Recheck by sysObjectId
    if ($os)
    {
      // If OS detected by sysObjectId just return it
      if ($os != $old_os) { print_warning("OS CHANGED: $old_os -> $os"); }
      return $os;
    }

    // Recheck by sysDescr from definitions
    foreach ($config['os'][$old_os]['sysDescr'] as $oid)
    {
      if (preg_match($oid, $sysDescr)) { return $old_os; }
    }

    // Recheck by include file
    if (is_file($config['install_dir'] . "/includes/discovery/os/$old_os.inc.php"))
    {
      $file = $config['install_dir'] . "/includes/discovery/os/$old_os.inc.php";
    }
    else if (isset($config['os'][$old_os]['discovery_os']) && is_file($config['install_dir'] . '/includes/discovery/os/'.$config['os'][$old_os]['discovery_os'] . '.inc.php'))
    {
      $file = $config['install_dir'] . '/includes/discovery/os/'.$config['os'][$old_os]['discovery_os'] . '.inc.php';
    }
    if ($file)
    {
      print_debug("Including $file");

      include($file);

      if ($os && $os == $old_os) { return $old_os; } else { print_warning("OS CHANGED: $old_os -> $os"); }
    }

    // Else full recheck 'os'!
    unset($os, $file);
  }

  if (!$os && $sysDescr)
  {
    // Check by sysDescr from definitions
    foreach (array_keys($config['os']) as $cos)
    {
      foreach ($config['os'][$cos]['sysDescr'] as $oid)
      {
        if (preg_match($oid, $sysDescr)) { $os = $cos; break 2; }
      }
    }
  }

  if (!$os)
  {
    // Check by include file
    $path = $config['install_dir'] . "/includes/discovery/os";
    $dir_handle = @opendir($path) or die("Unable to open $path");
    while ($file = readdir($dir_handle))
     {
      if (preg_match("/.php$/", $file))
      {
        print_debug("Including $file");

        include($config['install_dir'] . "/includes/discovery/os/" . $file);
      }
    }
    closedir($dir_handle);
  }

  if ($os) { return $os; } else { return "generic"; }
}

// Fetch the number of input/output errors on an interface for $period.
// DOCME needs phpdoc block
// TESTME needs unit testing
function interface_errors($rrd_file, $period = '-1d') // Returns the last in/out errors value in RRD
{
  global $config;

  $cmd = $config['rrdtool']." fetch -s $period -e -300s $rrd_file AVERAGE | grep : | cut -d\" \" -f 4,5";
  $data = trim(shell_exec($cmd));
  foreach (explode("\n", $data) as $entry)
  {
    list($in, $out) = explode(" ", $entry);
    $in_errors += ($in * 300);
    $out_errors += ($out * 300);
  }
  $errors['in'] = round($in_errors);
  $errors['out'] = round($out_errors);

  return $errors;
}

// Rename a device
// DOCME needs phpdoc block
// TESTME needs unit testing
function renamehost($id, $new, $source = 'console')
{
  global $config;

  // Test if new host exists in database
  if (dbFetchCell('SELECT COUNT(device_id) FROM `devices` WHERE `hostname` = ?', array($new)) == 0)
  {
    $transport = strtolower(dbFetchCell("SELECT `transport` FROM `devices` WHERE `device_id` = ?", array($id)));
    $try_a = !($transport == 'udp6' || $transport == 'tcp6'); // Use IPv6 only if transport 'udp6' or 'tcp6'
    // Test DNS lookup.
    if (gethostbyname6($new, $try_a))
    {
      // Test reachability
      if (isPingable($new, $try_a))
      {
        // Test directory mess in /rrd/
        if (!file_exists($config['rrd_dir'].'/'.$new))
        {
          $host = dbFetchCell("SELECT `hostname` FROM `devices` WHERE `device_id` = ?", array($id));
          if (!file_exists($config['rrd_dir'].'/'.$host))
          {
            print_warning("Old RRD directory not exist, rename skipped.");
          }
          else if (!rename($config['rrd_dir'].'/'.$host, $config['rrd_dir'].'/'.$new))
          {
            print_error("NOT renamed. Error of renaming of RRD directory.");
            return FALSE;
          }
          $return = dbUpdate(array('hostname' => $new), 'devices', 'device_id=?', array($id));
          log_event("Device hostname changed: $host -> $new", $id, 'device', $id, 5); // severity 5, for logging user/console info
          return TRUE;
        } else {
          // directory already exists
          print_error("NOT renamed. Directory rrd/$new already exists");
        }
      } else {
        // failed Reachability
        print_error("NOT renamed. Could not ping $new");
      }
    } else {
      // Failed DNS lookup
      print_error("NOT renamed. Could not resolve $new");
    }
  } else {
    // found in database
    print_error("NOT renamed. Already got host $new");
  }
  return FALSE;
}

// Deletes device from database and RRD dir.
// DOCME needs phpdoc block
// TESTME needs unit testing
function delete_device($id, $delete_rrd = FALSE)
{
  global $config;

  $ret = PHP_EOL;
  $device = device_by_id_cache($id);
  $host = $device['hostname'];

  if ($host == '')
  {
    $ret .= "Error finding host in the database.";
  } else {
    $ports = dbFetchRows("SELECT * FROM `ports` WHERE `device_id` = ?", array($id));
    if (!empty($ports))
    {
      $ret .= ' * Deleted interfaces: ';
      foreach ($ports as $int_data)
      {
        $int_if = $int_data['ifDescr'];
        $int_id = $int_data['port_id'];
        delete_port($int_id, $delete_rrd);
        $deleted_ports[] = "id=$int_id ($int_if)";
      }
      $ret .= implode(', ', $deleted_ports).PHP_EOL;
    }

    $ret .= ' * Deleted device entries from tables: ';
    foreach ($config['device_tables'] as $table)
    {
      $where = '`device_id` = ?';
      if ($table == 'entity_permissions')
      {
        $where = "`entity_type` = 'device' AND `entity_id` = ?";
      }
      $table_status = dbDelete($table, $where, array($id));
      if ($table_status) { $deleted_tables[] = $table; }
    }
    $ret .= implode(', ', $deleted_tables).PHP_EOL;

    if ($delete_rrd)
    {
      $device_rrd = rtrim(get_rrd_path($device, ''), '/');
      if (is_file($device_rrd.'/status.rrd'))
      {
        external_exec("rm -rf ".escapeshellarg($device_rrd));
        $ret .= ' * Deleted device RRDs dir: ' . $device_rrd . PHP_EOL;
      }

    }

    $ret .= " * Deleted device: $host";
  }

  return $ret;
}

// Delete port from database and associated rrd files
// DOCME needs phpdoc block
// TESTME needs unit testing
function delete_port($int_id, $delete_rrd = TRUE)
{
  global $config;

  $port = dbFetchRow("SELECT * FROM `ports` AS P, `devices` AS D WHERE P.`port_id` = ? AND D.`device_id` = P.`device_id`", array($int_id));
  $ret = "> Deleted interface from ".$port['hostname'].": id=$int_id (".$port['ifDescr'].")\n";

  $port_tables = array('bill_ports', 'eigrp_ports', 'ipv4_addresses', 'ipv6_addresses',
                       'ip_mac', 'juniAtmVp', 'mac_accounting', 'ospf_nb', 'ospf_ports',
                       'ports_adsl', 'ports_cbqos', 'ports_vlans', 'pseudowires', 'vlans_fdb',
                       'ports');
  foreach ($port_tables as $table)
  {
    $table_status = dbDelete($table, "`port_id` = ?", array($int_id));
    if ($table_status) { $deleted_tables[] = $table; }
  }

  $table_status = dbDelete('ports_stack', "`port_id_high` = ?  OR `port_id_low` = ?",    array($int_id, $int_id));
  if ($table_status) { $deleted_tables[] = 'ports_stack'; }
  $table_status = dbDelete('links',       "`local_port_id` = ? OR `remote_port_id` = ?", array($int_id, $int_id));
  if ($table_status) { $deleted_tables[] = 'links'; }
  $table_status = dbDelete('entity_permissions', "`entity_type` = 'port' AND `entity_id` = ?", array($int_id));
  if ($table_status) { $deleted_tables[] = 'entity_permissions'; }
  $table_status = dbDelete('alert_table', "`entity_type` = 'port' AND `entity_id` = ?", array($int_id));
  if ($table_status) { $deleted_tables[] = 'alert_table'; }
  $table_status = dbDelete('group_table', "`entity_type` = 'port' AND `entity_id` = ?", array($int_id));
  if ($table_status) { $deleted_tables[] = 'group_table'; }

  $ret .= '> Deleted interface entries from tables: '.implode(', ', $deleted_tables).PHP_EOL;

  if ($delete_rrd)
  {
    $rrd_types = array('adsl', 'dot3', 'fdbcount', 'poe', NULL);
    foreach ($rrd_types as $type)
    {
      $rrdfile = get_port_rrdfilename($port, $type, TRUE);
      if (is_file($rrdfile))
      {
        unlink($rrdfile);
        $deleted_rrds[] = $rrdfile;
      }
    }
    $ret .= '> Deleted interface RRD files: ' . implode(', ', $deleted_rrds) . PHP_EOL;
  }

  return $ret;
}

/**
 * Adds the new device to the database.
 *
 * Before adding the device, checks duplicates in the database and the availability of device over a network.
 *
 * @param string $hostname Device hostname
 * @param string|array $snmp_version SNMP version(s) (default: $config['snmp']['version'])
 * @param string $snmp_port SNMP port (default: 161)
 * @param string $snmp_transport SNMP transport (default: udp)
 * @param array $options Additional options can be passed ('break' - for break recursion, 'test' - for skip adding, only test device availability)
 *
 * @return mixed Returns $device_id number if added, 0 (zero) if device not accessible with current auth and FALSE if device complete not accessible by network. When testing, returns -1 if the device is available.
 */
// TESTME needs unit testing
function add_device($hostname, $snmp_version = array(), $snmp_port = 161, $snmp_transport = 'udp', $options = array())
{
  global $config;

  // If $options['break'] set as TRUE, break recursive function execute
  if (isset($options['break']) && $options['break']) { return FALSE; }
  $return = FALSE; // By default return FALSE

  // Reset snmp timeout and retries options for speedup device adding
  unset($config['snmp']['timeout'], $config['snmp']['retries']);

  $hostname = trim($hostname);
  list($hostshort) = explode(".", $hostname);
  // Test if host exists in database
  if (dbFetchCell("SELECT COUNT(*) FROM `devices` WHERE `hostname` = ?", array($hostname)) == '0')
  {
    $snmp_transport = strtolower($snmp_transport);
    $try_a = !($snmp_transport == 'udp6' || $snmp_transport == 'tcp6'); // Use IPv6 only if transport 'udp6' or 'tcp6'
    // Test DNS lookup.
    $ip = gethostbyname6($hostname, $try_a);
    if ($ip)
    {
      $ip_version = get_ip_version($ip);
      // Test reachability
      if (isPingable($hostname, $try_a))
      {
        // Test directory exists in /rrd/
        if (!$config['rrd_override'] && file_exists($config['rrd_dir'].'/'.$hostname))
        {
          print_error("Directory <observium>/rrd/$hostname already exists.");
          return FALSE;
        }

        // Detect snmp transport
        if (stripos($snmp_transport, 'tcp') !== FALSE)
        {
          $snmp_transport = ($ip_version == 4 ? 'tcp' : 'tcp6');
        } else {
          $snmp_transport = ($ip_version == 4 ? 'udp' : 'udp6');
        }
        // Detect snmp port
        if (!is_numeric($snmp_port) || $snmp_port < 1 || $snmp_port > 65535)
        {
          $snmp_port = 161;
        } else {
          $snmp_port = (int)$snmp_port;
        }
        // Detect snmp version
        if (empty($snmp_version))
        {
          // Here set default snmp version order
          $i = 1;
          $snmp_version_order = array();
          foreach (array('v2c', 'v3', 'v1') as $tmp_version)
          {
            if ($config['snmp']['version'] == $tmp_version)
            {
              $snmp_version_order[0]  = $tmp_version;
            } else {
              $snmp_version_order[$i] = $tmp_version;
            }
            $i++;
          }
          ksort($snmp_version_order);

          foreach ($snmp_version_order as $tmp_version)
          {
            $ret = add_device($hostname, $tmp_version, $snmp_port, $snmp_transport, $options);
            if ($ret === FALSE)
            {
              // Set $options['break'] for break recursive
              $options['break'] = TRUE;
            }
            else if (is_numeric($ret) && $ret != 0)
            {
              return $ret;
            }
          }
        }
        else if ($snmp_version === "v3")
        {
          // Try each set of parameters from config
          foreach ($config['snmp']['v3'] as $snmp_v3)
          {
            $device = build_initial_device_array($hostname, NULL, $snmp_version, $snmp_port, $snmp_transport, $snmp_v3);

            print_message("Trying v3 parameters " . $device['snmp_authname'] . "/" .  $device['snmp_authlevel'] . " ... ");
            if (isSNMPable($device))
            {
              if (!check_device_duplicated($device))
              {
                if (isset($options['test']) && $options['test'])
                {
                  print_message('%WDevice "'.$hostname.'" has successfully been tested and available by '.strtoupper($snmp_transport).' transport with SNMP '.$snmp_version.' credentials.%n', 'color');
                  $device_id = -1;
                } else {
                  $device_id = createHost($hostname, NULL, $snmp_version, $snmp_port, $snmp_transport, $snmp_v3);
                }
                return $device_id;
              }
            } else {
              print_warning("No reply on credentials " . $device['snmp_authname'] . "/" .  $device['snmp_authlevel'] . " using $snmp_version.");
            }
          }
        }
        else if ($snmp_version === "v2c" || $snmp_version === "v1")
        {
          // Try each community from config
          foreach ($config['snmp']['community'] as $snmp_community)
          {
            $device = build_initial_device_array($hostname, $snmp_community, $snmp_version, $snmp_port, $snmp_transport);
            print_message("Trying $snmp_version community $snmp_community ...");
            if (isSNMPable($device))
            {
              if (!check_device_duplicated($device))
              {
                if (isset($options['test']) && $options['test'])
                {
                  print_message('%WDevice "'.$hostname.'" has successfully been tested and available by '.strtoupper($snmp_transport).' transport with SNMP '.$snmp_version.' credentials.%n', 'color');
                  $device_id = -1;
                } else {
                  $device_id = createHost($hostname, $snmp_community, $snmp_version, $snmp_port, $snmp_transport);
                }
                return $device_id;
              }
            } else {
              print_warning("No reply on community $snmp_community using $snmp_version.");
              $return = 0; // Return zero for continue trying next auth
            }
          }
        } else {
          print_error("Unsupported SNMP Version \"$snmp_version\".");
          $return = 0; // Return zero for continue trying next auth
        }

        if (!$device_id)
        {
          // Failed SNMP
          print_error("Could not reach $hostname with given SNMP parameters using $snmp_version.");
          $return = 0; // Return zero for continue trying next auth
        }
      } else {
        // failed Reachability
        print_error("Could not ping $hostname.");
      }
    } else {
      // Failed DNS lookup
      print_error("Could not resolve $hostname.");
    }
  } else {
    // found in database
    print_error("Already got device $hostname.");
  }

  return $return;
}

// Check duplicated devices in DB by snmpEngineID and sysName
// If found duplicate devices return TRUE, in other cases return FALSE
// DOCME needs phpdoc block
// TESTME needs unit testing
function check_device_duplicated($device)
{
  // Hostname should be uniq
  if ($device['hostname'] && dbFetchCell("SELECT COUNT(*) FROM `devices` WHERE `hostname` = ?", array($device['hostname'])) != '0')
  {
    // Retun TRUE if have device with same hostname in DB
    print_error("Already got device with hostname (".$device['hostname'].").");
    return TRUE;
  }

  $snmpEngineID = snmp_cache_snmpEngineID($device);
  $sysName      = snmp_get($device, "sysName.0", "-Oqv", "SNMPv2-MIB", mib_dirs());
  if (empty($sysName) || strpos($sysName, '.') === FALSE) { $sysName = FALSE; }

  if (!empty($snmpEngineID))
  {
    $test_devices = dbFetchRows('SELECT * FROM `devices` WHERE `disabled` = 0 AND `snmpEngineID` = ?', array($snmpEngineID));
    foreach ($test_devices as $test)
    {
      if ($test['sysName'] === $sysName)
      {
        // Retun TRUE if have same snmpEngineID && sysName in DB
        print_error("Already got device with SNMP-read sysName ($sysName) and 'snmpEngineID' = $snmpEngineID (".$test['hostname'].").");
        return TRUE;
      }
    }
  } else {
    // If snmpEngineID empty, check only by sysName
    if ($sysName !== FALSE && dbFetchCell('SELECT COUNT(*) FROM `devices` WHERE `disabled` = 0 AND `sysName` = ?', array($sysName)) > 0)
    {
      // Retun TRUE if have same sysName in DB
      print_error("Already got device with SNMP-read sysName ($sysName).");
      return TRUE;
    }
  }

  // In all other cases return FALSE
  return FALSE;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/common.inc.php
function scanUDP($host, $port, $timeout)
{
  $handle = fsockopen($host, $port, $errno, $errstr, 2);
  socket_set_timeout($handle, $timeout);
  $write = fwrite($handle,"\x00");
  if (!$write) { next; }
  $startTime = time();
  $header = fread($handle, 1);
  $endTime = time();
  $timeDiff = $endTime - $startTime;

  if ($timeDiff >= $timeout)
  {
    fclose($handle); return 1;
  } else { fclose($handle); return 0; }
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function build_initial_device_array($hostname, $snmp_community, $snmp_version, $snmp_port = 161, $snmp_transport = 'udp', $snmp_v3 = array())
{
  $device = array();
  $device['hostname']       = $hostname;
  $device['snmp_port']      = $snmp_port;
  $device['snmp_transport'] = $snmp_transport;
  $device['snmp_version']   = $snmp_version;

  if ($snmp_version === "v2c" || $snmp_version === "v1")
  {
    $device['snmp_community'] = $snmp_community;
  }
  else if ($snmp_version == "v3")
  {
    $device['snmp_authlevel']  = $snmp_v3['authlevel'];
    $device['snmp_authname']   = $snmp_v3['authname'];
    $device['snmp_authpass']   = $snmp_v3['authpass'];
    $device['snmp_authalgo']   = $snmp_v3['authalgo'];
    $device['snmp_cryptopass'] = $snmp_v3['cryptopass'];
    $device['snmp_cryptoalgo'] = $snmp_v3['cryptoalgo'];
  }

  if (OBS_DEBUG > 1)
  {
    var_dump($device);
  }
  return $device;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/common.inc.php
function netmask2cidr($netmask)
{
  $addr = Net_IPv4::parseAddress("1.2.3.4/$netmask");
  return $addr->bitmask;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/common.inc.php
function cidr2netmask($cidr)
{
  return (long2ip(ip2long("255.255.255.255") << (32-$cidr)));
}

// Detect SNMP auth params without adding device by hostname or IP
// if SNMP auth detected return array with auth params or FALSE if not detected
// DOCME needs phpdoc block
// TESTME needs unit testing
function detect_device_snmpauth($hostname, $snmp_port = 161, $snmp_transport = 'udp', $detect_ip_version = FALSE)
{
  global $config;

  // Additional checks for IP version
  if ($detect_ip_version)
  {
    $ip_version = get_ip_version($hostname);
    if (!$ip_version)
    {
      $ip = gethostbyname6($hostname);
      $ip_version = get_ip_version($ip);
    }
    // Detect snmp transport
    if (stripos($snmp_transport, 'tcp') !== FALSE)
    {
      $snmp_transport = ($ip_version == 4 ? 'tcp' : 'tcp6');
    } else {
      $snmp_transport = ($ip_version == 4 ? 'udp' : 'udp6');
    }
  }
  // Detect snmp port
  if (!is_numeric($snmp_port) || $snmp_port < 1 || $snmp_port > 65535)
  {
    $snmp_port = 161;
  } else {
    $snmp_port = (int)$snmp_port;
  }

  // Here set default snmp version order
  $i = 1;
  $snmp_version_order = array();
  foreach (array('v2c', 'v3', 'v1') as $tmp_version)
  {
    if ($config['snmp']['version'] == $tmp_version)
    {
      $snmp_version_order[0]  = $tmp_version;
    } else {
      $snmp_version_order[$i] = $tmp_version;
    }
    $i++;
  }
  ksort($snmp_version_order);

  foreach ($snmp_version_order as $snmp_version)
  {
    if ($snmp_version === 'v3')
    {
      // Try each set of parameters from config
      foreach ($config['snmp']['v3'] as $snmp_v3)
      {
        $device = build_initial_device_array($hostname, NULL, $snmp_version, $snmp_port, $snmp_transport, $snmp_v3);
        print_message("Trying v3 parameters " . $device['snmp_authname'] . "/" .  $device['snmp_authlevel'] . " ... ");

        if (isSNMPable($device))
        {
          return $device;
        } else {
          print_warning("No reply on credentials " . $device['snmp_authname'] . "/" .  $device['snmp_authlevel'] . " using $snmp_version.");
        }
      }
    } else { // if ($snmp_version === "v2c" || $snmp_version === "v1")
      // Try each community from config
      foreach ($config['snmp']['community'] as $snmp_community)
      {
        $device = build_initial_device_array($hostname, $snmp_community, $snmp_version, $snmp_port, $snmp_transport);
        print_message("Trying $snmp_version community $snmp_community ...");
        if (isSNMPable($device))
        {
          return $device;
        } else {
          print_warning("No reply on community $snmp_community using $snmp_version.");
        }
      }
    }
  }

  return FALSE;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function isSNMPable($device)
{
  global $config;

  $time_start = microtime(true);
  $pos = snmp_get_multi($device, 'sysObjectID.0 sysUpTime.0', '-OQUst', 'SNMPv2-MIB', mib_dirs()); // sysObjectID and sysUpTime
  $time_end = microtime(true);

  if (is_array($pos[0]) && count($pos[0]))
  {
    $time_snmp = $time_end - $time_start;
    $time_snmp *= 1000;
    // SNMP response time in milliseconds.
    /// Note, it's full SNMP get/response time (not only UDP request).
    $time_snmp = number_format($time_snmp, 2, '.', '');
    return $time_snmp;
  }
  return 0;
}

/**
 *
 * It's fully BOOLEAN safe function.
 *
 */
// DOCME needs phpdoc block
function isPingable($hostname, $try_a = TRUE)
{
  global $config;

  $timeout = (isset($config['ping']['timeout']) ? (int)$config['ping']['timeout'] : 500);
  if ($timeout < 50)
  {
    $timeout = 50;
  }
  else if ($timeout > 2000)
  {
    $timeout = 2000;
  }
  $retries = (isset($config['ping']['retries']) ? (int)$config['ping']['retries'] : 3);
  if ($retries < 1)
  {
    $retries = 1;
  }
  else if ($retries > 10)
  {
    $retries = 10;
  }
  $sleep = floor(1000000 / $retries); // interval between retries, max 1 sec

  $ping_debug = isset($config['ping']['debug']) && $config['ping']['debug'];

  if (Net_IPv4::validateIP($hostname))
  {
    if (!$try_a)
    {
      logfile('debug.log', __FUNCTION__ . "() | DEVICE: $hostname | Passed IPv4 address but device use IPv6 transport");
      print_debug('Into function ' . __FUNCTION__ . '() passed IPv4 address ('.$hostname.'but device use IPv6 transport');
      return 0;
    }
    // Forced check for actual IPv4 address
    $cmd = $config['fping'] . " -t $timeout -c 1 -q $hostname 2>&1";
  }
  else if (Net_IPv6::checkIPv6($hostname))
  {
    // Forced check for actual IPv6 address
    $cmd = $config['fping6'] . " -t $timeout -c 1 -q $hostname 2>&1";
  } else {
    // First try IPv4
    $ip = ($try_a ? gethostbyname($hostname) : FALSE); // Do not check IPv4 if transport IPv6
    if ($ip && $ip != $hostname)
    {
      $cmd = $config['fping'] . " -t $timeout -c 1 -q $ip 2>&1";
    } else {
      $ip = gethostbyname6($hostname, FALSE);
      // Second try IPv6
      if ($ip)
      {
        $cmd = $config['fping6'] . " -t $timeout -c 1 -q $ip 2>&1";
      } else {
        // No DNS records
        logfile('debug.log', __FUNCTION__ . "() | DEVICE: $hostname | NO DNS record found");
        return 0;
      }
    }
  }

  for ($i=1; $i <= $retries; $i++)
  {
    $output = external_exec($cmd);
    if ($GLOBALS['exec_status']['exitcode'] === 0)
    {
      // normal $output = '8.8.8.8 : xmt/rcv/%loss = 1/1/0%, min/avg/max = 1.21/1.21/1.21'
      $tmp = explode('/', $output);
      $ping = $tmp[7];
      if (!$ping) { $ping = 0.01; } // Protection from zero (exclude false status)
    } else {
      $ping = 0;
    }
    if ($ping) { break; }

    if ($ping_debug)
    {
      logfile('debug.log', __FUNCTION__ . "() | DEVICE: $hostname | FPING OUT ($i): " . $output[0]);
      if ($i == $retries)
      {
        $mtr = $config['mtr'] . " -r -n -c 5 $ip";
        logfile('debug.log', __FUNCTION__ . "() | DEVICE: $hostname | MTR OUT:\n" . external_exec($mtr));
      }
    }

    if ($i < $retries) usleep($sleep);
  }

  return $ping;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/common.inc.php
function is_odd($number)
{
  return $number & 1; // 0 = even, 1 = odd
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function createHost($hostname, $snmp_community = NULL, $snmp_version, $snmp_port = 161, $snmp_transport = 'udp', $snmp_v3 = array())
{
  $hostname = trim(strtolower($hostname));

  $device = array('hostname'       => $hostname,
                  'sysName'        => $hostname,
                  'status'         => '1',
                  'snmp_community' => $snmp_community,
                  'snmp_port'      => $snmp_port,
                  'snmp_transport' => $snmp_transport,
                  'snmp_version'   => $snmp_version
            );

  // Add snmp v3 auth params
  foreach (array('authlevel', 'authname', 'authpass', 'authalgo', 'cryptopass', 'cryptoalgo') as $v3_key)
  {
    if (isset($snmp_v3['snmp_'.$v3_key]))
    {
      // Or $snmp_v3['snmp_authlevel']
      $device['snmp_'.$v3_key] = $snmp_v3['snmp_'.$v3_key];
    }
    else if (isset($snmp_v3[$v3_key]))
    {
      // Or $snmp_v3['authlevel']
      $device['snmp_'.$v3_key] = $snmp_v3[$v3_key];
    }
  }

  // This is compatibility code after refactor in r6306, for keep devices up before DB updated
  if (get_db_version() < 189)
  {
    // FIXME. Remove this in r7000
    $device['snmpver'] = $device['snmp_version'];
    unset($device['snmp_version']);
    foreach (array('transport', 'port', 'timeout', 'retries', 'community',
                   'authlevel', 'authname', 'authpass', 'authalgo', 'cryptopass', 'cryptoalgo') as $old_key)
    {
      if (isset($device['snmp_'.$old_key]))
      {
        // Convert to old device snmp keys
        $device[$old_key] = $device['snmp_'.$old_key];
        unset($device['snmp_'.$old_key]);
      }
    }
  }

  $device['os']           = get_device_os($device);
  $device['snmpEngineID'] = snmp_cache_snmpEngineID($device);
  $device['sysName']      = snmp_get($device, "sysName.0", "-Oqv", "SNMPv2-MIB", mib_dirs());
  $device['location']     = snmp_get($device, "sysLocation.0", "-Oqv", "SNMPv2-MIB", mib_dirs());
  $device['sysContact']   = snmp_get($device, "sysContact.0", "-Oqv", "SNMPv2-MIB", mib_dirs());

  if ($device['os'])
  {
    $device_id = dbInsert($device, 'devices');
    if ($device_id)
    {
      log_event("Device added: $hostname", $device_id, 'device', $device_id, 5); // severity 5, for logging user/console info
      if (is_cli())
      {
        print_success("Now discovering ".$device['hostname']." (id = ".$device_id.")");
        $device['device_id'] = $device_id;
        // Discover things we need when linking this to other hosts.
        discover_device($device, $options = array('m' => 'ports'));
        discover_device($device, $options = array('m' => 'ipv4-addresses'));
        discover_device($device, $options = array('m' => 'ipv6-addresses'));
        log_event("snmpEngineID -> ".$device['snmpEngineID'], $device, 'device', $device['device_id']);
        // Reset `last_discovered` for full rediscover device by cron
        dbUpdate(array('last_discovered' => 'NULL'), 'devices', '`device_id` = ?', array($device_id));
        array_push($GLOBALS['devices'], $device_id);
      }
      return($device_id);
    } else {
      return FALSE;
    }
  } else {
    return FALSE;
  }
}

// BOOLEAN safe function to check if hostname resolves as IPv4 or IPv6 address
// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/common.inc.php
function isDomainResolves($hostname)
{
  return (TRUE && gethostbyname6($hostname));
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function hoststatus($id)
{
  return dbFetchCell("SELECT `status` FROM `devices` WHERE `device_id` = ?", array($id));
}

// Returns IP version for string or FALSE if string not an IP
// get_ip_version('127.0.0.1')   === 4
// get_ip_version('::1')         === 6
// get_ip_version('my_hostname') === FALSE
// DOCME needs phpdoc block
// TESTME needs unit testing
function get_ip_version($address)
{
  $address_version = FALSE;
  if (Net_IPv4::validateIP($address))    { $address_version = 4; }
  elseif (Net_IPv6::checkIPv6($address)) { $address_version = 6; }
  return $address_version;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function is_ipv4_valid($ipv4_address, $ipv4_prefixlen = NULL)
{
  if (strpos($ipv4_address, '/') !== FALSE) { list($ipv4_address, $ipv4_prefixlen) = explode('/', $ipv4_address); }
  if (strpos($ipv4_prefixlen, '.')) { $ipv4_prefixlen = netmask2cidr($ipv4_prefixlen); }
  // False if prefix less or equal 0 and more 32
  if (is_numeric($ipv4_prefixlen) && ($ipv4_prefixlen < '0' || $ipv4_prefixlen > '32')) { return FALSE; }
  // False if invalid IPv4 syntax
  if (!Net_IPv4::validateIP($ipv4_address)) { return FALSE; }
  // False if 0.0.0.0
  if ($ipv4_address == '0.0.0.0') { return FALSE; }
  return TRUE;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function is_ipv6_valid($ipv6_address, $ipv6_prefixlen = NULL)
{
  if (strpos($ipv6_address, '/') !== FALSE) { list($ipv6_address, $ipv6_prefixlen) = explode('/', $ipv6_address); }
  // False if prefix less or equal 0 and more 128
  if (is_numeric($ipv6_prefixlen) && ($ipv6_prefixlen < '0' || $ipv6_prefixlen > '128')) { return FALSE; }
  // False if invalid IPv6 syntax
  if (!Net_IPv6::checkIPv6($ipv6_address)) { return FALSE; }
  $ipv6_type = Net_IPv6::getAddressType($ipv6_address);
  // False if link-local
  if ($ipv6_type == NET_IPV6_LOCAL_LINK || $ipv6_type == NET_IPV6_UNSPECIFIED) { return FALSE; }
  return TRUE;
}

// Determines whether or not the supplied IP address is within the supplied network.
// BOOLEAN safe, support IPv4 and IPv6
// DOCME needs phpdoc block
function match_network($ip, $nets, $first = FALSE)
{
  $return = FALSE;
  $ip_version = get_ip_version($ip);
  if ($ip_version)
  {
    if (!is_array($nets)) { $nets = array($nets); }
    foreach ($nets as $net)
    {
      $ip_in_net = FALSE;

      $revert    = (preg_match("/^\!/", $net) ? TRUE : FALSE); // NOT match network
      $net       = preg_replace("/^\!/", "", $net);

      if ($ip_version == 4)
      {
        if (strpos($net, '.') === FALSE) { continue; }      // NOT IPv4 net, skip
        if (strpos($net, '/') === FALSE) { $net .= '/32'; } // NET without mask as single IP
        $ip_in_net = Net_IPv4::ipInNetwork($ip, $net);
      } else {
        if (strpos($net, ':') === FALSE) { continue; }
        if (strpos($net, '/') === FALSE) { $net .= '/128'; } // NET without mask as single IP
        $ip_in_net = Net_IPv6::isInNetmask($ip, $net);
      }

      if ($revert && $ip_in_net) { return FALSE; } // Return FALSE if IP founded in network where should NOT match
      if ($first  && $ip_in_net) { return TRUE; }  // Return TRUE if IP founded in first match
      $return = $return || $ip_in_net;
    }
  }

  return $return;
}

/**
 * Convert HEX encoded IP value to pretty IP string
 *
 * Examples:
 *  IPv4 "C1 9C 5A 26" => "193.156.90.38"
 *  IPv4 "J}4:"        => "74.125.52.58"
 *  IPv6 "20 01 07 F8 00 12 00 01 00 00 00 00 00 05 02 72" => "2001:07f8:0012:0001:0000:0000:0005:0272"
 *  IPv6 "20:01:07:F8:00:12:00:01:00:00:00:00:00:05:02:72" => "2001:07f8:0012:0001:0000:0000:0005:0272"
 *
 * @param string $ip_hex HEX encoded IP address
 * @return string IP address or original input string if not contains IP address
 */
function hex2ip($ip_hex)
{
  $ip  = trim($ip_hex, "\"\t\n\r\0\x0B");
  if (strlen($ip) === 4)
  {
    // IPv4 hex string converted to SNMP string
    $ip = str2hex($ip);
  }

  $ip  = str_replace(' ', '', $ip);
  $len = strlen($ip);
  if ($len > 8)
  {
    // For IPv6
    $ip = str_replace(':', '', $ip);
    $len = strlen($ip);
  }
  
  if (!ctype_xdigit($ip))
  {
    return $ip_hex;
  }

  if ($len === 8)
  {
    // IPv4
    $ip_array = array();
    foreach (str_split($ip, 2) as $entry)
    {
      $ip_array[] = hexdec($entry);
    }
    $separator = '.';
  }
  else if ($len === 32)
  {
    // IPv6
    $ip_array = str_split(strtolower($ip), 4);
    $separator = ':';
  } else {
    return $ip_hex;
  }
  $ip = implode($separator, $ip_array);

  return $ip;
}

/**
 * Convert IP string to HEX encoded value.
 *
 * Examples:
 *  IPv4 "193.156.90.38" => "C1 9C 5A 26"
 *  IPv6 "2001:07f8:0012:0001:0000:0000:0005:0272" => "20 01 07 f8 00 12 00 01 00 00 00 00 00 05 02 72"
 *  IPv6 "2001:7f8:12:1::5:0272" => "20 01 07 f8 00 12 00 01 00 00 00 00 00 05 02 72"
 *
 * @param string $ip IP address string
 * @param string $separator Separator for HEX parts
 * @return string HEX encoded address
 */
function ip2hex($ip, $separator = ' ')
{
  $ip_hex     = trim($ip, " \"\t\n\r\0\x0B");
  $ip_version = get_ip_version($ip_hex);

  if ($ip_version === 4)
  {
    // IPv4
    $ip_array = array();
    foreach (explode('.', $ip_hex) as $entry)
    {
      $ip_array[] = zeropad(dechex($entry));
    }
  }
  else if ($ip_version === 6)
  {
    // IPv6
    $ip_hex   = str_replace(':', '', Net_IPv6::uncompress($ip_hex, TRUE));
    $ip_array = str_split($ip_hex, 2);
  } else {
    return $ip;
  }
  $ip_hex = implode($separator, $ip_array);

  return $ip_hex;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function snmp2ipv6($ipv6_snmp)
{
  $ipv6 = explode('.',$ipv6_snmp);

  // Workaround stupid Microsoft bug in Windows 2008 -- this is fixed length!
  // < fenestro> "because whoever implemented this mib for Microsoft was ignorant of RFC 2578 section 7.7 (2)"
  if (count($ipv6) == 17 && $ipv6[0] == 16)
  {
    array_shift($ipv6);
  }

  for ($i = 0;$i <= 15;$i++) { $ipv6[$i] = zeropad(dechex($ipv6[$i])); }
  for ($i = 0;$i <= 15;$i+=2) { $ipv6_2[] = $ipv6[$i] . $ipv6[$i+1]; }

  return implode(':',$ipv6_2);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function ipv62snmp($ipv6)
{
  $ipv6_ex = explode(':',Net_IPv6::uncompress($ipv6));
  for ($i = 0;$i < 8;$i++) { $ipv6_ex[$i] = zeropad($ipv6_ex[$i],4); }
  $ipv6_ip = implode('',$ipv6_ex);
  for ($i = 0;$i < 32;$i+=2) $ipv6_split[] = hexdec(substr($ipv6_ip,$i,2));

  return implode('.',$ipv6_split);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/common.inc.php
function get_astext($asn)
{
  global $config, $cache;

  // Fetch pre-set AS text from config first
  if (isset($config['astext'][$asn]))
  {
    return $config['astext'][$asn];
  } else {
    // Not preconfigured, check cache before doing a new DNS request
    if (isset($cache['astext'][$asn]))
    {
      return $cache['astext'][$asn];
    } else {
      $result = dns_get_record("AS$asn.asn.cymru.com",DNS_TXT);
      $txt = explode('|',$result[0]['txt']);
      $result = trim(str_replace('"', '', $txt[4]));
      $cache['astext'][$asn] = $result;

      return $result;
    }
  }
}

// Use this function to write to the eventlog table
// DOCME needs phpdoc block
// TESTME needs unit testing
function log_event($text, $device = NULL, $type = NULL, $reference = NULL, $severity = 6)
{
  if (!is_array($device)) { $device = device_by_id_cache($device); }
  if ($device['ignore'] && $type != 'device') { return FALSE; } // Do not log events if device ignored
  if ($type == 'port')
  {
    if (is_array($reference))
    {
      $port      = $reference;
      $reference = $port['port_id'];
    } else {
      $port = get_port_by_id_cache($reference);
    }
    if ($port['ignore']) { return FALSE; } // Do not log events if interface ignored
  }

  $severity = priority_string_to_numeric($severity); // Convert named severities to numeric
  if (($type == 'device' && $severity == 5) || isset($_SESSION['username'])) // Severity "Notification" additional log info about username or cli
  {
    $severity = ($severity == 6 ? 5 : $severity); // If severity default, change to notification
    if (isset($_SESSION['username']))
    {
      $text .= ' (by user: '.$_SESSION['username'].')';
    }
    else if (is_cli())
    {
      if (is_cron())
      {
        $text .= ' (by cron)';
      } else {
        $text .= ' (by console)';
      }
    }
  }

  $insert = array('device_id' => ($device['device_id'] ? $device['device_id'] : "NULL"),
                  'entity_id' => (is_numeric($reference) ? $reference : array('NULL')),
                  'entity_type' => ($type ? $type : array('NULL')),
                  'timestamp' => array("NOW()"),
                  'severity' => $severity,
                  'message' => $text);

  $id = dbInsert($insert, 'eventlog');

  return $id;
}

// Parse string with emails. Return array with email (as key) and name (as value)
// DOCME needs phpdoc block
// MOVEME to includes/common.inc.php
function parse_email($emails)
{
  $result = array();
  $regex = '/^\s*[\"\']?\s*([^\"\']+)?\s*[\"\']?\s*<([^@]+@[^>]+)>\s*$/';
  if (is_string($emails))
  {
    $emails = preg_split('/[,;]\s{0,}/', $emails);
    foreach ($emails as $email)
    {
      $email = trim($email);
      if (preg_match($regex, $email, $out))
      {
        $email = trim($out[2]);
        $name  = trim($out[1]);
        $result[$email] = (!empty($name) ? $name : NULL);
      }
      else if (strpos($email, "@") && !preg_match('/\s/', $email))
      {
        $result[$email] = NULL;
      } else {
        return FALSE;
      }
    }
  } else {
    // Return FALSE if input not string
    return FALSE;
  }
  return $result;
}

/**
 * Converting string to hex
 *
 * By Greg Winiarski of ditio.net
 * http://ditio.net/2008/11/04/php-string-to-hex-and-hex-to-string-functions/
 * We claim no copyright over this function and assume that it is free to use.
 *
 * @param string $string
 * @return string
 */
// MOVEME to includes/common.inc.php
function str2hex($string)
{
  $hex='';
  for ($i=0; $i < strlen($string); $i++)
  {
    $hex .= dechex(ord($string[$i]));
  }
  return $hex;
}

/**
 * Converting hex to string
 *
 * By Greg Winiarski of ditio.net
 * http://ditio.net/2008/11/04/php-string-to-hex-and-hex-to-string-functions/
 * We claim no copyright over this function and assume that it is free to use.
 *
 * @param string $hex
 * @return string
 */
// TESTME needs unit testing
// MOVEME to includes/common.inc.php
function hex2str($hex)
{
  $string='';

  for ($i = 0; $i < strlen($hex)-1; $i+=2)
  {
    $string .= chr(hexdec($hex[$i].$hex[$i+1]));
  }

  return $string;
}

/**
 * Convert an SNMP hex string to regular string
 *
 * @param string $string
 * @return string
 */
// MOVEME to includes/snmp.inc.php
function snmp_hexstring($string)
{
  if (isHexString($string))
  {
    return hex2str(str_replace(' ', '', str_replace(' 00', '', $string)));
  } else {
    return $string;
  }
}

// Check if the supplied string is a hex string
// FIXME This is test for SNMP hex string, for just hex string use ctype_xdigit()
// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/snmp.inc.php
function isHexString($str)
{
  return (preg_match("/^[a-f0-9][a-f0-9]( [a-f0-9][a-f0-9])*$/is", trim($str)) ? TRUE : FALSE);
}

// Include all .inc.php files in $dir
// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/common.inc.php
function include_dir($dir, $regex = "")
{
  global $device, $config, $valid;

  if ($regex == "")
  {
    $regex = "/\.inc\.php$/";
  }

  if ($handle = opendir($config['install_dir'] . '/' . $dir))
  {
    while (false !== ($file = readdir($handle)))
    {
      if (filetype($config['install_dir'] . '/' . $dir . '/' . $file) == 'file' && preg_match($regex, $file))
      {
        print_debug("Including: " . $config['install_dir'] . '/' . $dir . '/' . $file);

        include($config['install_dir'] . '/' . $dir . '/' . $file);
      }
    }

    closedir($handle);
  }
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function is_port_valid($port, $device)
{
  global $config;

  $valid = TRUE;

  if (isset($port['ifOperStatus']) && $port['ifOperStatus'] == 'notPresent')
  {
    // See http://tools.cisco.com/Support/SNMP/do/BrowseOID.do?objectInput=ifOperStatus
    $valid = FALSE;
  }

  $if = ($config['os'][$device['os']]['ifname'] ? $port['ifName'] : $port['ifDescr']);

  if ($valid && is_array($config['bad_if']))
  {
    foreach ($config['bad_if'] as $bi)
    {
      if (stripos($if, $bi) !== FALSE)
      {
        $valid = FALSE;
        print_debug("ignored (by descr): $if [ $bi ]");
        break;
      }
    }
  }

  if ($valid && is_array($config['bad_if_regexp']))
  {
    foreach ($config['bad_if_regexp'] as $bi)
    {
      if (preg_match($bi . 'i', $if))
      {
        $valid = FALSE;
        print_debug("ignored (by regexp): $if [ $bi ]");
        break;
      }
    }
  }

  if ($valid && is_array($config['bad_iftype']))
  {
    foreach ($config['bad_iftype'] as $bi)
    {
      if (strpos($port['ifType'], $bi) !== FALSE)
      {
        $valid = FALSE;
        print_debug("ignored (by ifType): ".$port['ifType']." [ $bi ]");
        break;
      }
    }
  }
  if ($valid && empty($port['ifDescr']) && empty($port['ifName']))
  {
    $valid = FALSE;
    print_debug("ignored (by empty ifDescr and ifName).");
  }
  if ($valid && $device['os'] == 'catos' && strstr($if, "vlan")) { $valid = FALSE; }

  return $valid;
}

# Parse CSV files with or without header, and return a multidimensional array
// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/common.inc.php
function parse_csv($content, $has_header = 1, $separator = ",")
{
  $lines = explode("\n", $content);
  $result = array();

  # If the CSV file has a header, load up the titles into $headers
  if ($has_header)
  {
    $headcount = 1;
    $header = array_shift($lines);
    foreach (explode($separator,$header) as $heading)
    {
      if (trim($heading) != "")
      {
        $headers[$headcount] = trim($heading);
        $headcount++;
      }
    }
  }

  # Process every line
  foreach ($lines as $line)
  {
    if ($line != "")
    {
      $entrycount = 1;
      foreach (explode($separator,$line) as $entry)
      {
        # If we use header, place the value inside the named array entry
        # Otherwise, just stuff it in numbered fields in the array
        if (trim($entry) != "")
        {
          if ($has_header)
          {
            $line_array[$headers[$entrycount]] = trim($entry);
          } else {
            $line_array[] = trim($entry);
          }
        }
        $entrycount++;
      }

      # Add resulting line array to final result
      $result[] = $line_array; unset($line_array);
    }
  }

  return $result;
}

/**
 * Converts named oid values to numerical interpretation based on oid descriptions and stored in definitions
 *
 * @param string $type Sensor type which has definition in $config['status_states'][$type]
 * @param mixed $value Value which must be converted
 * @return integer Note, if definition not found or incorrect value, returns FALSE
 */
function state_string_to_numeric($type, $value)
{
  global $config;

  if (is_numeric($value))
  {
    // Return value if already numeric
    if ($value == (int)$value && isset($config['status_states'][$type][(int)$value]))
    {
      return (int)$value;
    } else {
      return FALSE;
    }
  }
  foreach ($config['status_states'][$type] as $index => $content)
  {
    if (strcasecmp($content['name'], trim($value)) == 0) { return $index; }
  }

  return FALSE;
}

// Convert Fahrenheit -> Celsius
// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/common.inc.php
function f2c($fahrenheit)
{
  if (is_numeric($fahrenheit)) { return ($fahrenheit - 32) * (5/9); }
  else                         { return $fahrenheit; }
}

// Convert Celsius -> Fahrenheit
// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/common.inc.php
function c2f($celsius)
{
  if (is_numeric($celsius)) { return $celsius * (9/5) + 32; }
  else                      { return $celsius; }
}

// Convert SI scales to scalar scale. Example return:
// si_to_scale('milli');    // return 0.001
// si_to_scale('femto', 8); // return 1.0E-23
// si_to_scale('-2');       // return 0.01
// DOCME needs phpdoc block
// MOVEME to includes/common.inc.php
function si_to_scale($si = 'units', $precision = NULL)
{
  // See all scales here: http://tools.cisco.com/Support/SNMP/do/BrowseOID.do?local=en&translate=Translate&typeName=SensorDataScale
  $si       = strtolower($si);
  $si_array = array('yocto' => -24, 'zepto' => -21, 'atto'  => -18,
                    'femto' => -15, 'pico'  => -12, 'nano'  => -9,
                    'micro' => -6,  'milli' => -3,  'units' => 0,
                    'kilo'  => 3,   'mega'  => 6,   'giga'  => 9,
                    'tera'  => 12,  'exa'   => 15,  'peta'  => 18,
                    'zetta' => 21,  'yotta' => 24);
  $exp = 0;
  if (isset($si_array[$si]))
  {
    $exp = $si_array[$si];
  }
  else if (is_numeric($si))
  {
    $exp = (int)$si;
  }

  if (is_numeric($precision) && $precision > 0)
  {
    /**
     * NOTES. For EntitySensorPrecision:
     *  If an object of this type contains a value in the range 1 to 9, it represents the number of decimal places in the
     *  fractional part of an associated EntitySensorValue fixed-point number.
     *  If an object of this type contains a value in the range -8 to -1, it represents the number of accurate digits in the
     *  associated EntitySensorValue fixed-point number.
     */
    $exp -= (int)$precision;
  }

  $scale = pow(10, $exp);

  return $scale;
}


/**
 * Compare variables considering epsilon for float numbers
 * returns: 0 - variables same, 1 - $a greater than $b, -1 - $a less than $b
 *
 * @param mixed $a Fist compare number
 * @param mixed $b Second compare number
 * @param float $epsilon
 * @return integer $compare
 */
// MOVEME to includes/common.inc.php
function float_cmp($a, $b, $epsilon = NULL)
{
  $epsilon = (is_numeric($epsilon) ? (float)$epsilon : 0.00001); // Default epsilon for float compare
  $compare = FALSE;
  $both    = 0;
  // Convert to float if possible
  if (is_numeric($a)) { $a = (float)$a; $both++; }
  if (is_numeric($b)) { $b = (float)$b; $both++; }

  if ($both === 2)
  {
    // Compare numeric variables as float numbers
    if (abs(($a - $b) / $b) < $epsilon)
    {
      $compare = 0;  // Float numbers same
    }
    if (OBS_DEBUG > 1)
    {
      print_debug('Compare float numbers: "'.$a.'" with "'.$b.'", epsilon: "'.$epsilon.'", comparision: "'.abs(($a - $b) / $b).' < '.$epsilon.'", numbers: '.($compare === 0 ? 'SAME' : 'DIFFERENT'));
    }
  } else {
    // All other compare as usual
    if ($a === $b)
    {
      $compare = 0; // Variables same
    }
  }
  if ($compare === FALSE)
  {
    // Compare if variables not same
    if ($a > $b)
    {
      $compare = 1;  // $a greater than $b
    } else {
      $compare = -1; // $a less than $b
    }
  }

  return $compare;
}

// Translate syslog priorities from string to numbers
// ie: ('emerg','alert','crit','err','warning','notice') >> ('0', '1', '2', '3', '4', '5')
// Note, this is safe function, for unknown data return 15
// DOCME needs phpdoc block
function priority_string_to_numeric($value)
{
  $priority = 15; // Default priority for unknown data
  if (!is_numeric($value))
  {
    foreach ($GLOBALS['config']['syslog']['priorities'] as $pri => $entry)
    {
      if (stripos($entry['name'], substr($value, 0, 3)) === 0) { $priority = $pri; break; }
    }
  }
  else if ($value == (int)$value && $value >= 0 && $value < 16)
  {
    $priority = (int)$value;
  }

  return $priority;
}

// Merge 2 arrays by their index, ie:
//  Array( [1] => [TestCase] = '1' ) + Array( [1] => [Bananas] = 'Yes )
// becomes
//  Array( [1] => [TestCase] = '1', [Bananas] = 'Yes' )
//
// array_merge_recursive() only works for string keys, not numeric as we get from snmp functions.
//
// Accepts infinite parameters.
//
// Currently not used. Does not cope well with multilevel arrays.
// DOCME needs phpdoc block
// MOVEME to includes/common.inc.php
function array_merge_indexed()
{
  $array = array();

  foreach (func_get_args() as $array2)
  {
    if (count($array2) == 0) continue; // Skip for loop for empty array, infinite loop ahead.
    for ($i = 0; $i <= count($array2); $i++)
    {
      foreach (array_keys($array2[$i]) as $key)
      {
        $array[$i][$key] = $array2[$i][$key];
      }
    }
  }

  return $array;
}

// EOF
