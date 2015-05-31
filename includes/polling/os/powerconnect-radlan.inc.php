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

// Dell-Vendor-MIB::productIdentificationDisplayName.0 = STRING: PowerConnect 5324
// Dell-Vendor-MIB::productIdentificationDescription.0 = STRING: Neyland 24T
// Dell-Vendor-MIB::productIdentificationVersion.0 = STRING: 2.0.1.3
// Dell-Vendor-MIB::productIdentificationServiceTag.1 = STRING: 8D4XY51
$hardware = "Dell ".snmp_get($device, "productIdentificationDisplayName.0", "-Ovq", "Dell-Vendor-MIB", mib_dirs('dell'));
$version  = snmp_get($device, "productIdentificationVersion.0", "-Ovq", "Dell-Vendor-MIB", mib_dirs('dell'));
$serial   = implode(", ",explode("\n",snmp_walk($device, "productIdentificationServiceTag", "-Ovq", "Dell-Vendor-MIB", mib_dirs('dell'))));
$icon     = 'dell';

$features = snmp_get($device, "rndBaseBootVersion.00", "-Ovq", "RADLAN-MIB", mib_dirs('radlan'));

$version  = trim($version,'"');
$features = trim($features,'"');
$hardware = trim($hardware,'"');

// EOF
