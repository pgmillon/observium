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

$mib = 'EMBEDDED-NGX-MIB';

$mempool['free']  = snmp_get($device, "swMemRamFree.0",  "-OQUvs", $mib, mib_dirs('checkpoint'));
$mempool['total'] = snmp_get($device, "swMemRamTotal.0", "-OQUvs", $mib, mib_dirs('checkpoint'));
$mempool['used']  = $mempool['total'] - $mempool['free'];

// EOF
