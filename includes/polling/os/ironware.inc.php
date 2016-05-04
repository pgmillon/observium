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

$hardware = snmp_get($device, "sysObjectID.0", "-OQsv", "FOUNDRY-SN-AGENT-MIB:FOUNDRY-SN-ROOT-MIB", mib_dirs("foundry"));

if (isset($rewrite_ironware_hardware[$poll_device['sysObjectID']]))
{
  $hardware = $rewrite_ironware_hardware[$poll_device['sysObjectID']];
}

$version = trim(snmp_get($device, "snAgImgVer.0", "-OQsv", "FOUNDRY-SN-AGENT-MIB:FOUNDRY-SN-ROOT-MIB", mib_dirs("foundry")), ' "');
//$version = snmp_get($device, "snAgBuildVer.0", "-OQsv", "FOUNDRY-SN-AGENT-MIB:FOUNDRY-SN-ROOT-MIB", mib_dirs("foundry"));
//$version = str_replace(array('V', '"'), '', $version);

$serial = trim(snmp_get($device, "snChasSerNum.0", "-OQsv", "FOUNDRY-SN-AGENT-MIB:FOUNDRY-SN-ROOT-MIB", mib_dirs("foundry")), ' "');

// EOF
