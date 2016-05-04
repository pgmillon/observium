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

echo(" CISCO-VTP-MIB ");

// Not sure why we check for VTP, but this data comes from that MIB, so...
$vtpversion = snmp_get($device, "vtpVersion.0"  , "-OnvQ", "CISCO-VTP-MIB");

switch ($vtpversion)
{
  case 'none':
  case '1':
  case '2':
  case '3':
  case 'one':
  case 'two':
  case 'three':
    // FIXME - can have multiple VTP domains.
    $vtpdomains = snmpwalk_cache_oid($device, "vlanManagementDomains", array(), "CISCO-VTP-MIB", mib_dirs('cisco'));
    $vlans = snmpwalk_cache_twopart_oid($device, "vtpVlanEntry", array(), "CISCO-VTP-MIB", mib_dirs('cisco'));

    foreach ($vtpdomains as $vtpdomain_id => $vtpdomain)
    {
      if ($vtpdomain['managementDomainName'])
      {
        echo("(Domain $vtpdomain_id ".$vtpdomain['managementDomainName'].")");
      } else {
        echo("(Domain $vtpdomain_id".")");
      }
      foreach ($vlans[$vtpdomain_id] as $vlan_id => $vlan)
      {
        unset ($vlan_update);

        if (is_array($vlans_db[$vtpdomain_id][$vlan_id]) && $vlans_db[$vtpdomain_id][$vlan_id]['vlan_name'] != $vlan['vtpVlanName'])
        {
          $vlan_update['vlan_name'] = $vlan['vtpVlanName'];
        }

        if (is_array($vlans_db[$vtpdomain_id][$vlan_id]) && $vlans_db[$vtpdomain_id][$vlan_id]['vlan_mtu'] != $vlan['vtpVlanMtu'])
        {
          $vlan_update['vlan_mtu'] = $vlan['vtpVlanMtu'];
        }

        if (is_array($vlans_db[$vtpdomain_id][$vlan_id]) && $vlans_db[$vtpdomain_id][$vlan_id]['vlan_status'] != $vlan['vtpVlanState'])
        {
          $vlan_update['vlan_status'] = $vlan['vtpVlanState'];
        }

        echo(" $vlan_id");
        if (is_array($vlan_update))
        {
          dbUpdate($vlan_update, 'vlans', 'vlan_id = ?', array($vlans_db[$vtpdomain_id][$vlan_id]['vlan_id']));
          $module_stats[$vlan_id]['V'] = 'U';
        } elseif (is_array($vlans_db[$vtpdomain_id][$vlan_id]))
        {
          $module_stats[$vlan_id]['V'] = '.';
        } else {
          dbInsert(array('device_id' => $device['device_id'], 'vlan_domain' => $vtpdomain_id, 'vlan_vlan' => $vlan_id, 'vlan_name' => $vlan['vtpVlanName'], 'vlan_mtu' => $vlan['vtpVlanMtu'], 'vlan_type' => $vlan['vtpVlanType']), 'vlans');
          $module_stats[$vlan_id]['V'] = '+';
        }
        $device['vlans'][$vtpdomain_id][$vlan_id] = $vlan_id;
      }
    }
    break;
}

echo(PHP_EOL);

// EOF
