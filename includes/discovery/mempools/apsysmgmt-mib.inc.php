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

$mib = 'APSYSMGMT-MIB';
echo("$mib ");

// APSYSMGMT-MIB not have information about total memory size.
$percent = snmp_get($device, "apSysMemoryUtil.0", "-OvQ", $mib, mib_dirs('acme'));

if (is_numeric($percent))
{
  discover_mempool($valid['mempool'], $device, 0, $mib, "Memory", 1, 100, $percent);
}
unset ($percent);

// EOF
