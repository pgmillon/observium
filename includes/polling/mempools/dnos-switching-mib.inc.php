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

$mib = 'DNOS-SWITCHING-MIB';

$mempool['total'] = snmp_get($device, 'agentSwitchCpuProcessMemAvailable.0', '-OUvq', $mib);
$mempool['free']  = snmp_get($device, 'agentSwitchCpuProcessMemFree.0', '-OUvq', $mib);
$mempool['used']  = $mempool['total'] - $mempool['free'];

// EOF
