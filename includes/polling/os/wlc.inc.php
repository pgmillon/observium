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

//AIRESPACE-SWITCHING-MIB::agentInventorySysDescription.0 = STRING: Cisco Controller
//AIRESPACE-SWITCHING-MIB::agentInventoryMachineModel.0 = STRING: AIR-CT5508-K9
//AIRESPACE-SWITCHING-MIB::agentInventorySerialNumber.0 = STRING: FCW1546L0D6
//AIRESPACE-SWITCHING-MIB::agentInventoryProductName.0 = STRING: Cisco Controller
//AIRESPACE-SWITCHING-MIB::agentInventoryProductVersion.0 = STRING: 7.6.100.0

$data = snmp_get_multi($device, 'agentInventoryMachineModel.0 agentInventoryProductVersion.0 agentInventorySerialNumber.0', "-OQUs", "AIRESPACE-SWITCHING-MIB");

if (is_array($data[0]))
{
  $hardware = $data[0]['agentInventoryMachineModel'];
  $version  = $data[0]['agentInventoryProductVersion'];
  $serial   = $data[0]['agentInventorySerialNumber'];
}
else if ($entPhysical['entPhysicalModelName'])
{
  $hardware = $entPhysical['entPhysicalModelName'];
  $version  = $entPhysical['entPhysicalSoftwareRev'];
  $serial   = $entPhysical['entPhysicalSerialNum'];
}

if (empty($hardware) && $poll_device['sysObjectID'])
{
  // Try translate instead duplicate get sysObjectID
  $hardware = snmp_translate($poll_device['sysObjectID'], "SNMPv2-MIB:CISCO-PRODUCTS-MIB:CISCO-ENTITY-VENDORTYPE-OID-MIB");
}
if (empty($hardware))
{
  // If translate false, try get sysObjectID again
  $hardware = snmp_get($device, "sysObjectID.0", "-Osqv", "SNMPv2-MIB:CISCO-PRODUCTS-MIB:CISCO-ENTITY-VENDORTYPE-OID-MIB");
}

unset($data);

// EOF
