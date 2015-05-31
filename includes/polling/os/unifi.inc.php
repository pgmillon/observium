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

// IEEE802dot11-MIB::dot11manufacturerProductName.5 = STRING: UAP-LR
// IEEE802dot11-MIB::dot11manufacturerProductVersion.5 = STRING: BZ.ar7240.v3.1.9.2442.131217.1549

$hardware = trim(snmp_get($device, "dot11manufacturerProductName.5", "-Ovq", "IEEE802dot11-MIB", mib_dirs()));
if (!$hardware) { $hardware = trim(snmp_get($device, "dot11manufacturerProductName.2", "-Ovq", "IEEE802dot11-MIB", mib_dirs())); }
$hardware = "Unifi ".$hardware;

$version  = trim(snmp_get($device, "dot11manufacturerProductVersion.5", "-Ovq", "IEEE802dot11-MIB", mib_dirs()));
list(,$version) = preg_split('/\.v/',$version);

// EOF
