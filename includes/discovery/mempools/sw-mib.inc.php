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

$mib = 'SW-MIB';
echo("$mib ");

// Hardcoded for VDX switches that has 2GB of RAM includes all the current models.
$total   = 2147483648;
$percent = snmp_get($device, "swMemUsage.0", "-Ovq", $mib, mib_dirs('brocade'));
$used    = $total * $percent / 100;

if (is_numeric($percent))
{
  discover_mempool($valid['mempool'], $device, 0, $mib, "Memory", 1, $total, $used);
}
unset ($total, $used, $percent);

// EOF
