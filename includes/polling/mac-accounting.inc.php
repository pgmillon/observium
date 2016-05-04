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

// FIXME -- we're walking, so we can discover here too.

/// FIXME FIXME REWRITE ME please ;)

print_cli_data_field("MIBs", 2);

#dbQuery("TRUNCATE TABLE `mac_accounting`");
#dbQuery("TRUNCATE TABLE `mac_accounting-state`");

// Cache DB entries
$sql  = "SELECT * FROM `mac_accounting`";
$sql .= " LEFT JOIN `mac_accounting-state` USING(`ma_id`)";
$sql .= " WHERE `device_id` = ?";

$acc_id_db = array();
foreach (dbFetchRows($sql, array($device['device_id'])) as $acc)
{
  $port = get_port_by_id($acc['port_id']);
  if (is_array($port))
  {
    $acc['ifIndex'] = $port['ifIndex'];
    unset($port);
    $ma_db_array[$acc['ifIndex'].'-'.$acc['vlan_id'].'-'.$acc['mac']] = $acc;
  }
  $acc_id_db[$acc['ma_id']] = $acc['ma_id'];
}

if (OBS_DEBUG > 1 && count($ma_db_array))
{
  print_vars($ma_db_array);
}

if (is_device_mib($device, 'JUNIPER-MAC-MIB'))
{
  $datas = snmp_walk($device, "jnxMacStatsEntry", "-OUqsX", "JUNIPER-MAC-MIB", mib_dirs("juniper"));
  if ($GLOBALS['snmp_status'])
  {
    foreach (explode("\n", $datas) as $data)
    {
      list($oid,$ifIndex,$vlan,$mac,$value) = parse_oid2($data);
      list($a_a, $a_b, $a_c, $a_d, $a_e, $a_f) = explode(":", $mac);
      $ah_a = zeropad($a_a); $ah_b = zeropad($a_b); $ah_c = zeropad($a_c); $ah_d = zeropad($a_d); $ah_e = zeropad($a_e); $ah_f = zeropad($a_f);
      $mac = "$ah_a$ah_b$ah_c$ah_d$ah_e$ah_f";
      if ($mac == '000000000000') { continue; } // Skip entries with "zero" mac

      $oid = str_replace(array("cipMacSwitchedBytes", "cipMacSwitchedPkts"), array("bytes", "pkts"), $oid);

      if ($oid == "jnxMacHCOutFrames") { $oid = "pkts"; $dir = "output"; }
      if ($oid == "jnxMacHCInFrames")  { $oid = "pkts"; $dir = "input"; }
      if ($oid == "jnxMacHCOutOctets") { $oid = "bytes"; $dir = "output"; }
      if ($oid == "jnxMacHCInOctets")  { $oid = "bytes"; $dir = "input"; }

      $ma_array[$ifIndex.'-'.$vlan.'-'.$mac]['ifIndex'] = $ifIndex;
      $ma_array[$ifIndex.'-'.$vlan.'-'.$mac]['vlan'] = $vlan;
      $ma_array[$ifIndex.'-'.$vlan.'-'.$mac]['mac'] = $mac;
      $ma_array[$ifIndex.'-'.$vlan.'-'.$mac][$oid][$dir] = $value;
    }
  }
}

// Cisco MAC Accounting
// FIXME. Rewrite
if (is_device_mib($device, 'CISCO-IP-STAT-MIB'))
{
  echo("Cisco ");

  $device_context = $device;
  if (!count($ma_db_array))
  {
    // Set retries to 0 for speedup first walking, only if previously polling also empty (DB empty)
    $device_context['snmp_retries'] = 0;
  }
  $datas32 = snmp_walk($device_context, "cipMacSwitchedBytes", "-OUqsX", "CISCO-IP-STAT-MIB", mib_dirs('cisco'));
  unset($device_context);
  if ($GLOBALS['snmp_status'])
  {
    $datas = snmp_walk($device, "cipMacHCSwitchedBytes", "-OUqsX", "CISCO-IP-STAT-MIB", mib_dirs('cisco'));
    if ($GLOBALS['snmp_status'])
    {
      $datas .= "\n".snmp_walk($device, "cipMacHCSwitchedPkts", "-OUqsX", "CISCO-IP-STAT-MIB", mib_dirs('cisco'));
    } else {
      // No 64-bit counters? Try 32-bit. How necessary is this? How lacking is 64-bit support?
      $datas = $datas32;
      $datas .= "\n".snmp_walk($device, "cipMacSwitchedPkts", "-OUqsX", "CISCO-IP-STAT-MIB", mib_dirs('cisco'));
    }

    foreach (explode("\n", $datas) as $data)
    {
      list($oid,$ifIndex,$dir,$mac,$value) = parse_oid2($data);
      list($a_a, $a_b, $a_c, $a_d, $a_e, $a_f) = explode(":", $mac);
      $ah_a = zeropad($a_a); $ah_b = zeropad($a_b); $ah_c = zeropad($a_c); $ah_d = zeropad($a_d); $ah_e = zeropad($a_e); $ah_f = zeropad($a_f);
      $mac = "$ah_a$ah_b$ah_c$ah_d$ah_e$ah_f";
      if ($mac == '000000000000') { continue; } // Skip entries with "zero" mac

      // Cisco isn't per-VLAN.
      $vlan = "0";

      $oid = str_replace(array("cipMacSwitchedBytes", "cipMacSwitchedPkts", "cipMacHCSwitchedBytes", "cipMacHCSwitchedPkts"), array("bytes", "pkts", "bytes", "pkts"), $oid);
      $ma_array[$ifIndex.'-'.$vlan.'-'.$mac]['ifIndex'] = $ifIndex;
      $ma_array[$ifIndex.'-'.$vlan.'-'.$mac]['vlan'] = $vlan;
      $ma_array[$ifIndex.'-'.$vlan.'-'.$mac]['mac'] = $mac;
      $ma_array[$ifIndex.'-'.$vlan.'-'.$mac][$oid][$dir] = $value;
    }
  }
}

// Below this should be MIB / vendor agnostic.

#function array_defuffle_three($array)
#{
#  foreach ($array as $key_a => $a)
#  {
#    foreach ($a as $key_b => $b)
#    {
#      foreach ($b as $key_c => $c)
#      {
#        $new_array[] = array($key_a, $key_b, $key_c, $c);
#      }
#    }
#  }
#  return $new_array;
#}

$acc_id = array(); // Count exist ma_ids
if (count($ma_array))
{
  if (OBS_DEBUG > 1) { print_vars($ma_array); }
  $polled = time();
  $mac_entries = 0;
  echo("Entries: ".count($ma_array).PHP_EOL);

  foreach ($ma_array as $id => $ma)
  {
    $port = get_port_by_index_cache($device['device_id'], $ma['ifIndex']);

    echo(' '.$id.' ');

    if (!is_array($ma_db_array[$id]))
    {
      $ma_id = dbInsert(array('port_id' => $port['port_id'], 'device_id' => $device['device_id'], 'vlan_id' => $ma['vlan'], 'mac' => $ma['mac'] ), 'mac_accounting');
      if ($ma_id)
      {
        //$ma_id = dbFetchCell("SELECT * FROM mac_accounting WHERE port_id = ? AND device_id = ? AND vlan_id = ? AND mac = ?", array($port['port_id'], $device['device_id'], $ma['vlan'], $ma['mac']));
        dbInsert(array('ma_id' => $ma_id), 'mac_accounting-state');
        echo("+");
        $acc_id[$ma_id] = $ma_id;
      } else {
        echo("-");
        continue; // wrong adding to DB, not exist id - delete
      }
    } else {
      echo(".");
      $ma_db = $ma_db_array[$id];
      $acc_id[$ma_db['ma_id']] = $ma_db['ma_id'];
    }

    $polled_period = $polled - $acc['poll_time'];

    if (OBS_DEBUG > 1) { print_vars($ma_array[$ifIndex][$vlan_id][$mac]); }

    $ma['update']['poll_time'] = $polled;
    $ma['update']['poll_period'] = $polled_period;
    $mac_entries++;
    $b_in = $ma['bytes']['input'];
    $b_out = $ma['bytes']['output'];
    $p_in = $ma['pkts']['input'];
    $p_out = $ma['pkts']['output'];

    echo(" ".$port['ifDescr']."(".$ifIndex.") -> ".$mac);

    // Update metrics
    foreach (array('bytes','pkts') as $oid)
    {
      foreach (array('input','output') as $dir)
      {
        $oid_dir = $oid . "_" . $dir;
        $ma['update'][$oid_dir] = $ma[$oid][$dir];

        if ($ma[$oid][$dir] && $ma_db[$oid_dir])
        {
          $oid_diff = $ma[$oid][$dir] - $ma_db[$oid_dir];
          $oid_rate  = $oid_diff / $polled_period;
          $ma['update'][$oid_dir.'_rate'] = $oid_rate;
          $ma['update'][$oid_dir.'_delta'] = $oid_diff;
          print_debug("\n $oid_dir ($oid_diff B) $oid_rate Bps $polled_period secs");
        }
      }

      print_debug($ma['hostname']." ".$ma['ifDescr'] . "  $mac -> $b_in:$b_out:$p_in:$p_out ");

      $rrdfile = "mac_acc-" . $port['ifIndex'] . "-" . $ma['vlan'] ."-" . $ma['mac'] . ".rrd";

      rrdtool_create($device, $rrdfile,"DS:IN:COUNTER:600:0:12500000000 \
          DS:OUT:COUNTER:600:0:12500000000 \
          DS:PIN:COUNTER:600:0:12500000000 \
          DS:POUT:COUNTER:600:0:12500000000 " );

      // FIXME - use memory tables to make sure these values don't go backwards?
      $rrdupdate = array($b_in, $b_out, $p_in, $p_out);
      rrdtool_update($device, $rrdfile, $rrdupdate);

      if (OBS_DEBUG > 1) { print_vars($ma['update']); }

      if (is_array($ma['update']))
      { // Do Updates
        if (empty($ma_db['poll_time']))
        {
          $insert = dbInsert(array('ma_id' => $ma_db['ma_id']), 'mac_accounting-state');
        }
        dbUpdate($ma['update'], 'mac_accounting-state', '`ma_id` = ?', array($ma_db['ma_id']));
      } // End Updates
    }
  }

  unset($ma_array);

  if ($mac_entries) { echo(" $mac_entries MAC accounting entries\n"); }

  echo(PHP_EOL);
}

// CLEAN not exist entries
foreach ($acc_id_db as $ma_id => $entry)
{
  if (!isset($acc_id[$ma_id]))
  {
    dbDelete('mac_accounting', '`ma_id` = ?', array($ma_id));
    dbDelete('mac_accounting-state', '`ma_id` = ?', array($ma_id));
  }
}
echo(PHP_EOL);

// EOF
