<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

//SONICWALL-FIREWALL-IP-STATISTICS-MIB::sonicCurrentCPUUtil.0 = Wrong Type (should be Gauge32 or Unsigned32): Counter32: 2

echo("SONICWALL-FIREWALL-IP-STATISTICS-MIB ");

$descr = "Processor";
$oid   = ".1.3.6.1.4.1.8741.1.3.1.3.0";
$usage = snmp_get($device, "sonicCurrentCPUUtil.0", "-OUQnv", "SONICWALL-FIREWALL-IP-STATISTICS-MIB");

if (is_numeric($usage))
{
  discover_processor($valid['processor'], $device, $oid, 0, "sonicwall-firewall-ip-statistics-mib", $descr, 1, $usage);
}

unset ($descr, $oid, $usage);

// EOF
