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

$lgpAgentDeviceId = snmp_get($device, 'lgpAgentDeviceId.1', '-Oqvs', 'LIEBERT-GP-AGENT-MIB', mib_dirs('liebert'));

if ($GLOBALS['snmp_status'])
{
  $hardware = rewrite_liebert_hardware($lgpAgentDeviceId);

  //var_dump($GLOBALS['rewrite_liebert_hardware'][$lgpAgentDeviceId]);
  switch ($GLOBALS['rewrite_liebert_hardware'][$lgpAgentDeviceId]['type'])
  {
    case 'ups':
      include("includes/polling/os/ups-mib.inc.php");
      break;
    case 'environment':
    case 'network':
      // Change type
      $type = $GLOBALS['rewrite_liebert_hardware'][$lgpAgentDeviceId]['type'];
    case 'pdu':
      // FIXME, PDU uses LIEBERT-GP-PDU-MIB
    default:
      //LIEBERT-GP-AGENT-MIB::lgpAgentIdentManufacturer.0 = STRING: Emerson Network Power
      //LIEBERT-GP-AGENT-MIB::lgpAgentIdentModel.0 = STRING: IS-UNITY-DP
      //LIEBERT-GP-AGENT-MIB::lgpAgentIdentFirmwareVersion.0 = STRING: 4.0.0.0
      //LIEBERT-GP-AGENT-MIB::lgpAgentIdentSerialNumber.0 = STRING: 417831G209J2014APR240173
      //LIEBERT-GP-AGENT-MIB::lgpAgentIdentPartNumber.0 = STRING: IS-UNITY_4.0.0.0_84525
      //LIEBERT-GP-AGENT-MIB::lgpAgentDeviceId.1 = OID: LIEBERT-GP-REGISTRATION-MIB::lgpIcomPAtypeDeluxeSys3
      $lgpAgentIdent = snmpwalk_cache_oid($device, 'lgpAgentIdent', array(), 'LIEBERT-GP-AGENT-MIB', mib_dirs('liebert'));

      $manufacturer = $lgpAgentIdent[0]['lgpAgentIdentManufacturer'];
      //$hardware     = $lgpAgentIdent[0]['lgpAgentIdentModel'];
      $version      = $lgpAgentIdent[0]['lgpAgentIdentFirmwareVersion'];
      $serial       = $lgpAgentIdent[0]['lgpAgentIdentSerialNumber'];
  }
} else {
  // Uses UPS-MIB
  include("includes/polling/os/ups-mib.inc.php");
}

// EOF
