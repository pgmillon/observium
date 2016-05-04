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

$mib = 'ADTRAN-AOSCPU';
echo("$mib ");

// ADTRAN-AOSCPU::adGenAOSMemPool.0 = Gauge32: 134217727
// ADTRAN-AOSCPU::adGenAOSHeapSize.0 = Gauge32: 103795696
// ADTRAN-AOSCPU::adGenAOSHeapFree.0 = Gauge32: 81300464

$total   = snmp_get($device, "adGenAOSHeapSize.0", "-Ovq", $mib);
$free    = snmp_get($device, "adGenAOSHeapFree.0", "-Ovq", $mib);
$used    = $total - $free;

if (is_numeric($total))
{
  discover_mempool($valid['mempool'], $device, 0, $mib, "Heap", 1, $total, $used);
}

unset ($total, $used, $free);

// EOF
