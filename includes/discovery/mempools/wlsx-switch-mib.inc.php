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

//WLSX-SWITCH-MIB::sysXMemorySize.1 = INTEGER: 1535900
//WLSX-SWITCH-MIB::sysXMemoryUsed.1 = INTEGER: 412060
//WLSX-SWITCH-MIB::sysXMemoryFree.1 = INTEGER: 1123840

$mib = 'WLSX-SWITCH-MIB';
echo("$mib ");

$descr  = "Memory";
$used   = snmp_get($device, "sysXMemoryUsed.1", "-OQUvs", $mib, mib_dirs('aruba'));
$total  = snmp_get($device, "sysXMemorySize.1", "-OQUvs", $mib, mib_dirs('aruba'));

if (is_numeric($used) && is_numeric($total))
{
  //$used  *= 1024;
  //$total *= 1024;
  discover_mempool($valid['mempool'], $device, 0, $mib, $descr, 1024, $total, $used);
}
unset ($descr, $total, $used);

// EOF
