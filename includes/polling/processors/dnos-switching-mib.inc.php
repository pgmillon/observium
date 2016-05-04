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

// DNOS-SWITCHING-MIB::agentSwitchCpuProcessTotalUtilization.0 = STRING: "    5 Secs (  6.510%)   60 Secs (  7.724%)  300 Secs (  6.3812%)"

$mib = 'DNOS-SWITCHING-MIB';
$oid = 'agentSwitchCpuProcessTotalUtilization.0';

$values = trim(snmp_get($device, $oid, '-OvQ', $mib),'" ');

if (preg_match('/300 Secs \(\s*(.*)%\)$/', $values, $matches))
{
  $proc = $matches[1];
}

unset($oid, $values, $matches);

// EOF
