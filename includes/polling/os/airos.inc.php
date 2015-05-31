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

# IEEE802dot11-MIB::dot11manufacturerProductName.5 = STRING: NanoBridge M5
# IEEE802dot11-MIB::dot11manufacturerProductVersion.5 = STRING: XM.ar7240.v5.5.2.14175.120816.1340

$hardware = "Ubiquiti ".trim(snmp_get($device, "dot11manufacturerProductName.5", "-Ovq", "IEEE802dot11-MIB", mib_dirs()));

$version  = trim(snmp_get($device, "dot11manufacturerProductVersion.5", "-Ovq", "IEEE802dot11-MIB", mib_dirs()));
list(, $version) = preg_split('/\.v/',$version);

// EOF
