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

$mib = 'CISCO-STACKWISE-MIB';

// CISCO-STACKWISE-MIB::cswSwitchNumCurrent.1001 = Gauge32: 1
// CISCO-STACKWISE-MIB::cswSwitchNumNextReload.1001 = Gauge32: 1
// CISCO-STACKWISE-MIB::cswSwitchRole.1001 = INTEGER: master(1)
// CISCO-STACKWISE-MIB::cswSwitchSwPriority.1001 = Gauge32: 1
// CISCO-STACKWISE-MIB::cswSwitchHwPriority.1001 = Gauge32: 5
// CISCO-STACKWISE-MIB::cswSwitchState.1001 = INTEGER: ready(4)
// CISCO-STACKWISE-MIB::cswSwitchMacAddress.1001 = STRING: 3c:5e:c3:cb:d3:0
// CISCO-STACKWISE-MIB::cswSwitchSoftwareImage.1001 = STRING: C2960X-UNIVERSALK9-M
// CISCO-STACKWISE-MIB::cswSwitchPowerBudget.1001 = Gauge32: 0 Watts
// CISCO-STACKWISE-MIB::cswSwitchPowerCommited.1001 = Gauge32: 0 Watts
// CISCO-STACKWISE-MIB::cswSwitchSystemPowerPriority.1001 = Gauge32: 0
// CISCO-STACKWISE-MIB::cswSwitchPoeDevicesLowPriority.1001 = Gauge32: 0
// CISCO-STACKWISE-MIB::cswSwitchPoeDevicesHighPriority.1001 = Gauge32: 0
// CISCO-STACKWISE-MIB::cswStackPortOperStatus.5180 = INTEGER: down(2)
// CISCO-STACKWISE-MIB::cswStackPortOperStatus.5181 = INTEGER: down(2)
// CISCO-STACKWISE-MIB::cswStackPortNeighbor.5180 = INTEGER: 0
// CISCO-STACKWISE-MIB::cswStackPortNeighbor.5181 = INTEGER: 0
$device_tmp = $device;
// Disable snmp bulk and retries, because some 2960S freeze on this walks
$device_tmp['snmp_retries'] = 1;
$device_tmp['snmp_nobulk'] = TRUE;
$stackredundant = snmp_get($device_tmp, 'cswRingRedundant.0', '-Oqv', $mib, mib_dirs('cisco'));
if ($GLOBALS['snmp_status'])
{
  $stackstatus   = snmpwalk_cache_multi_oid($device_tmp, 'cswSwitchInfoEntry', array(), $mib, mib_dirs('cisco'));
  $stackportoper = snmpwalk_cache_oid($device_tmp, 'cswStackPortOperStatus', array(), $mib, mib_dirs('cisco'));

  $ports_down = 0;
  foreach ($stackportoper as $entry)
  {
    // Count down ports for check if stack exist
    if ($entry['cswStackPortOperStatus'] == 'down') { $ports_down++; }
  }

  $stack_count = count($stackstatus); // Count stack members
  foreach ($stackstatus as $index => $entry)
  {
    $roleoid   = '.1.3.6.1.4.1.9.9.500.1.2.1.1.3.'.$index;
    $roledescr = 'Switch '.$entry['cswSwitchNumCurrent'].' stacking role';
    $stateoid  = '.1.3.6.1.4.1.9.9.500.1.2.1.1.6.'.$index;
    $statedescr = 'Switch '.$entry['cswSwitchNumCurrent'].' stacking state';

    if ($stack_count === 1 && $entry['cswSwitchNumCurrent'] == 1 && $stackredundant == 'false' &&
        $ports_down === 2 && $entry['cswSwitchRole'] == 'master' && $entry['cswSwitchState'] == 'ready')
    {
      // Heh, on IOS 15.x stacking is always enabled and does not have any way to detect if stack module exists and stacking is configured
      $stack_count = 0;
      print_debug("Stacking exists, but not configured and not active.");
      break; // exit foreach
    }

    if (!empty($entry['cswSwitchRole']))
    {
      discover_status($device,  $roleoid, "cswSwitchRole.$index",  'cisco-stackwise-member-state', $roledescr,  $entry['cswSwitchRole'], array('entPhysicalClass' => 'stack', 'entPhysicalIndex' => $index));
      discover_status($device, $stateoid, "cswSwitchState.$index", 'cisco-stackwise-switch-state', $statedescr, $entry['cswSwitchState'], array('entPhysicalClass' => 'stack', 'entPhysicalIndex' => $index));
    }
  }

  if ($stack_count)
  {
    $oid   = '.1.3.6.1.4.1.9.9.500.1.1.3.0';
    $descr = 'Stack is redundant';
    discover_status($device, $oid, "cswRingRedundant.0", 'cisco-stackwise-redundant-state', $descr, $stackredundant, array('entPhysicalClass' => 'stack'));

    foreach ($stackportoper as $index => $entry)
    {
      $oid   = '.1.3.6.1.4.1.9.9.500.1.2.2.1.1.'.$index;
      $port  = get_port_by_index_cache($device, $index);
      $descr = 'Stackport ' . $port['port_label'];

      if (!empty($entry['cswStackPortOperStatus']))
      {
        $options = array('entPhysicalClass' => 'port', 'entPhysicalIndex' => $index, 'measured_class' => 'port', 'measured_entity' => $port['port_id']);
        discover_status($device, $oid, "cswStackPortOperStatus.$index", 'cisco-stackwise-port-oper-state', $descr, $entry['cswStackPortOperStatus'], $options);
      }
    }
  }
}

unset($device_tmp);

// EOF
