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

$mib = 'FORTINET-FORTIGATE-MIB';

$mempool['perc']  = snmp_get($device, "fgSysMemUsage.0",    "-OvQ", $mib, mib_dirs('fortinet'));
$mempool['total'] = snmp_get($device, "fgSysMemCapacity.0", "-OvQ", $mib, mib_dirs('fortinet'));

// EOF
