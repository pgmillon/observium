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

$mib = 'JUNIPER-IVE-MIB';
echo("$mib ");

$percent = snmp_get($device, "iveMemoryUtil.0", "-OvQ", $mib, mib_dirs('juniper'));

if (is_numeric($percent))
{
  discover_mempool($valid['mempool'], $device, 0, $mib, "Memory", 1, 100, $percent);
}
unset($percent);

// EOF
