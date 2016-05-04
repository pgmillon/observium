<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

//WLSX-SWITCH-MIB::sysXMemorySize.1 = INTEGER: 1535900
//WLSX-SWITCH-MIB::sysXMemoryUsed.1 = INTEGER: 412060
//WLSX-SWITCH-MIB::sysXMemoryFree.1 = INTEGER: 1123840

$mib = 'WLSX-SWITCH-MIB';
echo(" $mib ");

$mempool['used']   = snmp_get($device, "sysXMemoryUsed.1", "-OQUvs", $mib, mib_dirs('aruba'));
$mempool['total']  = snmp_get($device, "sysXMemorySize.1", "-OQUvs", $mib, mib_dirs('aruba'));

// EOF
