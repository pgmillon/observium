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

$mib = 'AIRESPACE-SWITCHING-MIB';

$mempool['free']  = snmp_get($device, 'agentFreeMemory.0',  '-OQUvs', $mib);
$mempool['total'] = snmp_get($device, 'agentTotalMemory.0', '-OQUvs', $mib);
$mempool['used']  = $mempool['total'] - $mempool['free'];

// EOF
