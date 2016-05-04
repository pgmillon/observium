<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

echo(" Q-BRIDGE-MIB ");

$vtpdomain_id = "1";
//$q_bridge_index = snmpwalk_cache_oid($device, "dot1qPortVlanTable", array(), "Q-BRIDGE-MIB");
$vlans = snmpwalk_cache_oid($device, "dot1qVlanStaticTable", array(), "Q-BRIDGE-MIB", mib_dirs(), OBS_SNMP_ALL | OBS_QUOTES_STRIP | OBS_SNMP_CONCAT);
if ($vlans)
{
  $vlan_ifindex_map = snmpwalk_cache_oid($device, "dot1dBasePortIfIndex", array(), "Q-BRIDGE-MIB", mib_dirs());
  $vlan_ifindex_min = $vlan_ifindex_map[key($vlan_ifindex_map)]['dot1dBasePortIfIndex'];
}

if (is_device_mib($device, 'JUNIPER-VLAN-MIB')) // Unsure if other Juniper platforms "affected"
{
  // Fetch Juniper VLAN table for correct tag
  $vlans = snmpwalk_cache_oid($device, "jnxExVlanTable", $vlans, "JUNIPER-VLAN-MIB");
}

foreach ($vlans as $vlan_id => $vlan)
{
  if ($device['os'] == 'ftos')
  {
    $vlan_id = rewrite_ftos_vlanid($device, $vlan_id);
  }

  if (isset($vlan['jnxExVlanTag']))
  {
    $vlan_id = $vlan['jnxExVlanTag'];
  }

  unset ($vlan_update);

  if (is_array($vlans_db[$vtpdomain_id][$vlan_id]) && $vlans_db[$vtpdomain_id][$vlan_id]['vlan_name'] != $vlan['dot1qVlanStaticName'])
  {
    $vlan_update['vlan_name'] = $vlan['dot1qVlanStaticName'];
  }

  if (is_array($vlans_db[$vtpdomain_id][$vlan_id]) && $vlans_db[$vtpdomain_id][$vlan_id]['vlan_status'] != $vlan['dot1qVlanStaticRowStatus'])
  {
    $vlan_update['vlan_status'] = $vlan['dot1qVlanStaticRowStatus'];
  }

  echo(" $vlan_id");
  if (is_array($vlan_update))
  {
    dbUpdate($vlan_update, 'vlans', 'vlan_id = ?', array($vlans_db[$vtpdomain_id][$vlan_id]['vlan_id']));
    $module_stats[$vlan_id]['V'] = 'U';
  }
  elseif (is_array($vlans_db[$vtpdomain_id][$vlan_id]))
  {
    $module_stats[$vlan_id]['V'] = '.';
    //echo(".");
  } else {
    dbInsert(array('device_id' => $device['device_id'], 'vlan_domain' => $vtpdomain_id, 'vlan_vlan' => $vlan_id, 'vlan_name' => $vlan['dot1qVlanStaticName'], 'vlan_type' => array('NULL')), 'vlans');
    $module_stats[$vlan_id]['V'] = '+';
  }
  $device['vlans'][$vtpdomain_id][$vlan_id] = $vlan_id;

  //Set Q-BRIDGE ports Vlan table (not work on FTOS for now)
  if ($device['os'] != 'ftos')
  {
    $parts = explode(' ', $vlan['dot1qVlanStaticEgressPorts']);
    $binary = '';
    foreach ($parts as $part)
    {
      $binary .= zeropad(base_convert($part, 16, 2), 8);
    }
    $length = strlen($binary);
    for ($i = 0; $i < $length; $i++)
    {
      if ($binary[$i])
      {
        $port = get_port_by_index_cache($device, $i + $vlan_ifindex_min);
        if (!is_array($port)) { continue; } // Port not founded, skip

        if (isset($ports_vlans_db[$port['port_id']][$vlan_id]))
        {
          $ports_vlans[$port['port_id']][$vlan_id] = $ports_vlans_db[$port['port_id']][$vlan_id]['port_vlan_id'];
          $module_stats[$vlan_id]['P'] = '.';
        } else {
          $db_w = array('device_id' => $device['device_id'],
                        'port_id'   => $port['port_id'],
                        'vlan'      => $vlan_id);
          $id = dbInsert($db_w, 'ports_vlans');
          $module_stats[$vlan_id]['P'] = '+';
          $ports_vlans[$port['port_id']][$vlan_id] = $id;
        }
      }
    }
  }
}

echo(PHP_EOL);

// EOF
