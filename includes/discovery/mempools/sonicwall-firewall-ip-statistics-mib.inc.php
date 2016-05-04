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

//SONICWALL-FIREWALL-IP-STATISTICS-MIB::sonicCurrentRAMUtil.0 = Wrong Type (should be Gauge32 or Unsigned32): Counter32: 98

echo("SONICWALL-FIREWALL-IP-STATISTICS-MIB ");

$percent = snmp_get($device, "sonicCurrentRAMUtil.0", "-OUQnv", "SONICWALL-FIREWALL-IP-STATISTICS-MIB");

if (is_numeric($percent))
{
  discover_mempool($valid['mempool'], $device, 0, "SONICWALL-FIREWALL-IP-STATISTICS-MIB", "Memory", 1, 100, $percent);
}

unset ($percent);

// EOF
