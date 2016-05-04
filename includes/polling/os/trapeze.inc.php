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

// "Juniper Networks, Inc WLC880R 8.0.3.15 REL"
list(,,,$hardware,$version,) = explode(" ", $poll_device['sysDescr']);

// TRAPEZE-NETWORK-ROOT-MIB::trpzSerialNumber.0 = STRING: "JJ01234567"
$serial = trim(snmp_get($device, ".1.3.6.1.4.1.14525.4.2.1.1.0", "-OQv", "TRAPEZE-NETWORK-ROOT-MIB", $config['mib_dir'].':'.mib_dirs('trapeze')),'"');

// TRAPEZE-NETWORK-ROOT-MIB::trpzVersionString.0 = STRING: "8.0.3.15.0"
$version = trim(snmp_get($device, ".1.3.6.1.4.1.14525.4.2.1.4.0", "-OQv", "TRAPEZE-NETWORK-ROOT-MIB", $config['mib_dir'].':'.mib_dirs('trapeze')),'"');

$domain = trim(snmp_get($device, ".1.3.6.1.4.1.14525.4.2.2.1.0", "-OQv", "TRAPEZE-NETWORK-ROOT-MIB", $config['mib_dir'].':'.mib_dirs('trapeze')),'"');

if ($domain)
{
  $features="Cluster: $domain";
}

// EOF
