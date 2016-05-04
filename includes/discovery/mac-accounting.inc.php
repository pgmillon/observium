<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$mac_list = array();

// Disabled because we can do this better in poller now without performance hit

if ($device['os_group'] == "cisco" && FALSE)
{
  echo("Cisco MAC Accounting : ");
  $datas = snmp_walk($device, "cipMacSwitchedBytes", "-OUqsX", "CISCO-IP-STAT-MIB", mib_dirs('cisco'));
  foreach (explode("\n", $datas) as $data) {
    list(,$ifIndex,$dir,$mac,) = parse_oid2($data);
    list($a_a, $a_b, $a_c, $a_d, $a_e, $a_f) = explode(":", $mac);
    $ah_a = zeropad($a_a);
    $ah_b = zeropad($a_b);
    $ah_c = zeropad($a_c);
    $ah_d = zeropad($a_d);
    $ah_e = zeropad($a_e);
    $ah_f = zeropad($a_f);
    $clean_mac = "$ah_a$ah_b$ah_c$ah_d$ah_e$ah_f";

    $mac_list[$ifIndex.'_'.$clean_mac] = array('ifIndex' => $ifIndex, 'mac' => $clean_mac);

  }

  foreach ($mac_list as $mac_entry)
  {
    $port = dbFetchRow("SELECT * FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ? LIMIT 1", array($device['device_id'], $mac_entry['ifIndex']));
    if ($port) {
      echo($port['ifDescr'] . ' ('.$mac_entry['ifIndex'].') -> '.$mac_entry['mac']);
      if (dbFetchCell("SELECT COUNT(*) from `mac_accounting` WHERE `port_id` = ? AND `mac` = ?", array($port['port_id'], $mac_entry['mac'])))
      {
        echo(".");
      } else {
        $ma_id = dbInsert(array('port_id' => $port['port_id'], 'device_id' => $device['device_id'], 'mac' => $mac_entry['mac'] ), 'mac_accounting');
        dbInsert(array('ma_id' => $ma_id), 'mac_accounting-state');
        echo("+");
      }
    } else {
      echo("Couldn't work out interface!");
    }
    echo("\n");
  }
  echo("\n");
} # os_group=cisco

// FIXME - NEEDS TO REMOVE STALE ENTRIES?? :O

// EOF
