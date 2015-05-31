<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

$hardware = snmp_get($device, "sysObjectID.0", "-OQsv", "FOUNDRY-SN-AGENT-MIB:FOUNDRY-SN-ROOT-MIB", mib_dirs("foundry"));
$hardware = rewrite_ironware_hardware($hardware);

$version = snmp_get($device, "snAgBuildVer.0", "-OQsv", "FOUNDRY-SN-AGENT-MIB:FOUNDRY-SN-ROOT-MIB", mib_dirs("foundry"));
$version = str_replace(array('V', '"'), '', $version);

$serial = trim(snmp_get($device, "snChasSerNum.0", "-OQsv", "FOUNDRY-SN-AGENT-MIB:FOUNDRY-SN-ROOT-MIB", mib_dirs("foundry")),'"');

// EOF
