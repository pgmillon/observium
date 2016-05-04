<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2013 Adam Armstrong, 2013-2016 Observium Limited
 *
 */

echo(" COLUBRIS-USAGE-INFORMATION-MIB ");

$free  = snmp_get($device, ".1.3.6.1.4.1.8744.5.21.1.1.10.0", "-Ovq");
$total = snmp_get($device, ".1.3.6.1.4.1.8744.5.21.1.1.9.0", "-Ovq");
$used = $total - $free;

if (is_numeric($total) && is_numeric($used))
{
  discover_mempool($valid['mempool'], $device, 0, "COLUBRIS-USAGE-INFORMATION-MIB", "Memory", 1024, $total , $used );
}
unset ($total, $used, $free);

// EOF
