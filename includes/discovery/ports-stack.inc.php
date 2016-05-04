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

echo("Port Stacks: ");

// FIXME. Add here discovery CISCO-STACK-MIB::portTable, CISCO-PAGP-MIB::pagpProtocolConfigTable

$query = "SELECT * FROM `ports_stack` WHERE `device_id` = ?";
foreach (dbFetchRows($query, array($device['device_id'])) as $entry)
{
  $stack_db_array[$entry['port_id_high']][$entry['port_id_low']]['ifStackStatus'] = $entry['ifStackStatus'];
}

$stack_poll_array = snmpwalk_cache_twopart_oid($device, "ifStackStatus", array(), "IF-MIB", mib_dirs());

foreach ($stack_poll_array as $port_id_high => $entry_high)
{
  $port_high = get_port_by_index_cache($device, $port_id_high);
  if ($device['os'] == "ciscosb" && $port_high['ifType'] =='propVirtual') { continue; }       //Skip stacking on Vlan ports (F.u. Cisco SB)
  foreach ($entry_high as $port_id_low => $entry_low)
  {
    $port_low = get_port_by_index_cache($device, $port_id_low);
    if ($device['os'] == "ciscosb" && $port_low['ifType'] =='propVirtual') { continue; }      //Skip stacking on Vlan ports (F.u. Cisco SB)
    $ifStackStatus = $entry_low['ifStackStatus'];
    if (isset($stack_db_array[$port_id_high][$port_id_low]))
    {
      if ($stack_db_array[$port_id_high][$port_id_low]['ifStackStatus'] == $ifStackStatus)
      {
        echo(".");
      } else {
        $update_array = array('ifStackStatus' => $ifStackStatus);
        dbUpdate($update_array, 'ports_stack', "`device_id` = ? AND `port_id_high` = ? AND `port_id_low` = ?", array($device['device_id'], $port_id_high, $port_id_low));
        echo("U");
      }
      unset($stack_db_array[$port_id_high][$port_id_low]);
    } else {
      $update_array = array('device_id'     => $device['device_id'],
                            'port_id_high'  => $port_id_high,
                            'port_id_low'   => $port_id_low,
                            'ifStackStatus' => $ifStackStatus);
      dbInsert($update_array, 'ports_stack');
      echo("+");
    }
  }
}

foreach ($stack_db_array as $port_id_high => $array)
{
  foreach ($array as $port_id_low => $blah)
  {
    print_debug("DELETE STACK: ".$device['device_id']." ".$port_id_low." ".$port_id_high);
    dbDelete('ports_stack', "`device_id` =  ? AND port_id_high = ? AND port_id_low = ?", array($device['device_id'], $port_id_high, $port_id_low));
    echo("-");
  }
}

unset($update_array);

echo("\n");

// EOF
