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

$hardware = "Dell ".snmp_get($device, "productIdentificationDisplayName.0", "-Ovq", "Dell-Vendor-MIB", mib_dirs('dell'));
$version  = snmp_get($device, "productIdentificationVersion.0", "-Ovq", "Dell-Vendor-MIB", mib_dirs('dell'));
$features = snmp_get($device, "productIdentificationDescription.0", "-Ovq", "Dell-Vendor-MIB", mib_dirs('dell'));
$serial   = implode(", ",explode("\n",snmp_walk($device, "productIdentificationServiceTag", "-Ovq", "Dell-Vendor-MIB", mib_dirs('dell'))));

if (strstr($hardware,"No Such Object available")) { $hardware = $poll_device['sysDescr']; }

// EOF
