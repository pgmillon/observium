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

// No, this is not CIENA-TOPSECRET-MIB, but their MIBs are not available on the web,
// and their support team can only create deaffening silence. -TL

$mib = 'CIENA-TOPSECRET-MIB';
echo("$mib ");

$used  = snmp_get($device, ".1.3.6.1.4.1.6141.2.60.12.1.9.1.1.4.2", "-OvqU", mib_dirs());
$total = snmp_get($device, ".1.3.6.1.4.1.6141.2.60.12.1.9.1.1.2.2", "-OvQU", mib_dirs());

if (is_numeric($total) && is_numeric($used))
{
  discover_mempool($valid['mempool'], $device, 0, $mib, "Memory", 1, $total, $used);
}
unset ($total, $used);

// EOF
