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

# DeltaUPS-MIB::dupsIdentManufacturer.0 = STRING: "Socomec"
# DeltaUPS-MIB::dupsIdentModel.0 = STRING: "NETYS RT 1/1 UPS"
$hardware = trim(snmp_get($device, "dupsIdentManufacturer.0", "-OQv", "DeltaUPS-MIB", ""),'" ');
$hardware .= ' ' . trim(snmp_get($device, "dupsIdentModel.0", "-OQv", "DeltaUPS-MIB", ""),'" ');

# DeltaUPS-MIB::dupsIdentUPSSoftwareVersion.0 = STRING: "0.4"
$version = trim(snmp_get($device, "dupsIdentUPSSoftwareVersion.0", "-OQv", "DeltaUPS-MIB", ""),'" ');

# DeltaUPS-MIB::dupsIdentAgentSoftwareVersion.0 = STRING: "2.0h "

// EOF
