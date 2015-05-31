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

echo("FDB Tables\n");

// Build ifIndex > port and port-id > port cache table
$port_ifIndex_table = array();
$port_table = array();
foreach (dbFetchRows("SELECT `ifIndex`,`port_id`,`ifDescr` FROM `ports` WHERE `device_id` = ?", array($device['device_id'])) as $cache_port)
{
  $port_ifIndex_table[$cache_port['ifIndex']] = $cache_port;
  $port_table[$cache_port['port_id']] = $cache_port;
}

// Build dot1dBasePort > port cache table because people in the '80s were dicks
$dot1dBasePort_table = array();

// Build table of existing vlan/mac table
$fdbs_db = array();
$fdbs_q = dbFetchRows("SELECT * FROM `vlans_fdb` WHERE `device_id` = ?", array($device['device_id']));
foreach ($fdbs_q as $fdb_db) { $fdbs_db[$fdb_db['vlan_id']][$fdb_db['mac_address']] = $fdb_db; }

// Fetch data and build array of data for each vlan&mac
if ($device['os_group'] == 'cisco')
{
  // Fetch list of active VLANs
  foreach (dbFetchRows('SELECT `vlan_vlan` FROM `vlans` WHERE (`vlan_status` = ? OR `vlan_status` = ?) AND `device_id` = ?', array('active', 'operational', $device['device_id'])) as $cisco_vlan)
  {
    list($ios_version) = explode('(', $device['version']);
    // vlan context not worked on Cisco IOS <= 12.1 (SNMPv3)
    if ($device['snmpver'] == 'v3' && $device['os'] == "ios" && ($ios_version * 10) <= 121)
    {
      print_error("ERROR: For proper work please use SNMP v2/v1 for this device.");
      break;
    }

    $vlan = $cisco_vlan['vlan_vlan'];
    if (!is_numeric($vlan) || ($vlan >= 1002 && $vlan <= 1005)) { continue; }
    $device_context = $device;
    $device_context['snmpcontext'] = $vlan; // Add vlan context for snmp auth
    $device_context['retries'] = 0;         // Set retries to 0 for speedup walking

    //dot1dTpFdbAddress[0:7:e:6d:55:41] 0:7:e:6d:55:41
    //dot1dTpFdbPort[0:7:e:6d:55:41] 28
    //dot1dTpFdbStatus[0:7:e:6d:55:41] learned
    $dot1dTpFdbEntry_table = snmp_walk($device_context, 'dot1dTpFdbEntry', '-OqsX', 'BRIDGE-MIB', mib_dirs());
    // Detection shit snmpv3 authorization errors for contexts
    if ($exec_status['exitcode'] != 0)
    {
      unset($device_context);
      if ($device['snmpver'] == 'v3')
      {
        print_error("ERROR: For proper work of 'vlan-' context on cisco device with SNMPv3, it is necessary to add 'match prefix' in snmp-server config.");
      } else {
        print_error("ERROR: Device does not support per-VLAN community.");
      }
      break;
    }
    elseif ($GLOBALS['snmp_status'] === FALSE)
    {
      // Continue if no entries for vlan
      unset($device_context);
      continue;
    }

    //dot1dBasePortIfIndex.28 = 10128
    $dot1dBasePortIfIndex = snmpwalk_cache_oid($device_context, 'dot1dBasePortIfIndex', $port_stats, 'BRIDGE-MIB', mib_dirs());
    unset($device_context);

    foreach ($dot1dBasePortIfIndex as $dot1dbaseport => $data)
    {
      $dot1dBasePort_table[$dot1dbaseport] = $port_ifIndex_table[$data['dot1dBasePortIfIndex']];
    }

    foreach (explode("\n", $dot1dTpFdbEntry_table) as $text)
    {
      list(,$value) = explode(' ', $text);
      if (!empty($value))
      {
        preg_match('/(\w+)\[([a-f0-9:]+)\]/', $text, $oid);
        $mac = '';
        foreach (explode(':', $oid[2]) as $m) { $mac .= zeropad($m); }
        $fdbs[$vlan][$mac][$oid[1]] = $value;
      }
    }
  }
} else {
  //dot1qTpFdbPort[1][0:0:5e:0:1:1] 50
  //dot1qTpFdbStatus[1][0:0:5e:0:1:1] learned
  $dot1qTpFdbEntry_table = snmp_walk($device, 'dot1qTpFdbEntry', '-OqsX', 'Q-BRIDGE-MIB');
  if ($GLOBALS['snmp_status'] !== FALSE)
  {
    // Build dot1dBasePort
    foreach (snmpwalk_cache_oid($device, "dot1dBasePortIfIndex", $port_stats, "BRIDGE-MIB") as $dot1dbaseport => $data)
    {
      $dot1dBasePort_table[$dot1dbaseport] = $port_ifIndex_table[$data['dot1dBasePortIfIndex']];
    }

    foreach (explode("\n", $dot1qTpFdbEntry_table) as $text) {
      list($oid, $value) = explode(" ", $text);
      preg_match('/(\w+)\[(\d+)\]\[([a-f0-9:]+)\]/', $text, $oid);
      if (!empty($value)) {
        $vlan = $oid[2];
        $mac = '';
        foreach (explode(':', $oid[3]) as $m) { $mac .= zeropad($m); }
        $fdbs[$vlan][$mac][$oid[1]] = $value;
      }
    }
  }
}

if (count($fdbs))
{
  echo(str_pad("Vlan", 8) . " | " . str_pad("MAC",12) . " | " .  "Port                  (dot1d|ifIndex)" ." | ". str_pad("Status",16) . "\n".
  str_pad("", 90, "-")."\n");
}

$fdb_portcount = array();
$fdb_count = 0;
// Loop vlans
foreach ($fdbs as $vlan => $macs)
{
  // Loop macs
  foreach ($macs as $mac => $data)
  {
    if ($device['os_group'] == 'cisco')
    {
      $fdb_port = $data['dot1dTpFdbPort'];
      $fdb_status = $data['dot1dTpFdbStatus'];
    } else {
      $fdb_port = $data['dot1qTpFdbPort'];
      $fdb_status = $data['dot1qTpFdbStatus'];
    }
    $port_id = $dot1dBasePort_table[$fdb_port]['port_id'];
    $ifIndex = $dot1dBasePort_table[$fdb_port]['ifIndex'];
    $port_name = $dot1dBasePort_table[$fdb_port]['ifDescr'];
    echo(str_pad($vlan, 8) . " | " . str_pad($mac,12) . " | " .  str_pad($port_name."|".$port_id,18) . str_pad("(".$fdb_port."|".$ifIndex.")",19," ",STR_PAD_LEFT) ." | ". str_pad($fdb_status,10));

    // if entry already exists
    if (!is_array($fdbs_db[$vlan][$mac]))
    {
      dbInsert(array('device_id' => $device['device_id'], 'vlan_id' => $vlan, 'port_id' => $port_id, 'mac_address' => $mac, 'fdb_status' => $fdb_status), 'vlans_fdb');
      echo("+");
    } else {
      unset($q_update);
      // if port/status are different, build an update array and update the db
      if ($fdbs_db[$vlan][$mac]['port_id'] != $port_id)                    { $q_update['port_id'] = $port_id; }
      if ($fdbs_db[$vlan][$mac]['fdb_status'] != $fdb_status) { $q_update['fdb_status'] = $data['fdb_status']; }
      if (is_array($q_update))
      {
        dbUpdate($q_update, 'vlans_fdb', '`device_id` = ? AND `vlan_id` = ? AND `mac_address` = ?', array($device['device_id'], $vlan, $mac));
        echo("U");
      } else {
      }
      // remove it from the existing list
      unset ($fdbs_db[$vlan][$mac]);
    }
    $fdb_count++;
    $fdb_portcount[$port_id]++;
    echo(PHP_EOL);
  }
}

// FDB count for HP ProCurve
if (!$fdb_count && $device['os'] == 'procurve')
{
  $fdb_count = trim(snmp_get($device, "hpSwitchFdbAddressCount.0", "-Ovqn", "STATISTICS-MIB", mib_dirs('hp')), '"');
}

if (is_numeric($fdb_count) && $fdb_count > 0)
{
  $rrd_file = "fdb_count.rrd";
  rrdtool_create($device, $rrd_file, "DS:value:GAUGE:600:0:U ");
  rrdtool_update($device, $rrd_file, "N:".$fdb_count);
  $graphs['fdb_count'] = TRUE;
} else {
  $graphs['fdb_count'] = FALSE;
}

$fdbcount_module = 'enable_ports_fdbcount';
if ($attribs[$fdbcount_module] || ($config[$fdbcount_module] && !isset($attribs[$fdbcount_module])))
{
  foreach ($fdb_portcount as $port => $count)
  {
    $port_info = $port_table[$port];
    if (!$port_info)
    {
      print_debug("No entry in port table for $port");
      continue;
    }
    $rrd_file = get_port_rrdfilename($port_info, "fdbcount");
    rrdtool_create($device, $rrd_file, "DS:value:GAUGE:600:0:U ");
    rrdtool_update($device, $rrd_file, "N:".$count);
    $graphs['port_fdb_count'] = TRUE;
  }
}

// Loop the existing list and delete anything remaining
foreach ($fdbs_db as $vlan => $fdb_macs)
{
  foreach ($fdb_macs as $mac => $data)
  {
    echo(str_pad($vlan, 8) . " | " . str_pad($mac,12) . " | " .  str_pad($data['port_id'],25) ." | ". str_pad($data['fdb_status'],16));
    echo("-\n");
    dbDelete('vlans_fdb', '`device_id` = ? AND `vlan_id` = ? AND `mac_address` = ?', array($device['device_id'], $vlan, $mac));
  }
}

// EOF
