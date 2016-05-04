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

echo(' CISCO-FIREWALL-MIB');

if ($device['os'] == 'asa')
{
  $status_array = snmpwalk_cache_oid($device, 'cfwHardwareStatusTable', array(), 'CISCO-FIREWALL-MIB');

  if ($status_array['netInterface']['cfwHardwareStatusValue'] != 'down')
  {
    // device is configured for failover

    $descr = 'Failover ' . strtolower($status_array['primaryUnit']['cfwHardwareInformation']);
    $oid = '.1.3.6.1.4.1.9.9.147.1.2.1.1.1.3.6';
    $value = $status_array['primaryUnit']['cfwHardwareStatusValue'];
    discover_status($device, $oid, 'cfwHardwareStatusValue.primaryUnit', 'cisco-firewall-hardware-primary-state', $descr, $value, array('entPhysicalClass' => 'other'));

    $descr = 'Failover ' . strtolower($status_array['secondaryUnit']['cfwHardwareInformation']);
    $oid = '.1.3.6.1.4.1.9.9.147.1.2.1.1.1.3.7';
    $value = $status_array['secondaryUnit']['cfwHardwareStatusValue'];
    discover_status($device, $oid, 'cfwHardwareStatusValue.secondaryUnit', 'cisco-firewall-hardware-secondary-state', $descr, $value, array('entPhysicalClass' => 'other'));
  }
}

// EOF
