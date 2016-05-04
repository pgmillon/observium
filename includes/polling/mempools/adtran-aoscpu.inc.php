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

$mib = 'ADTRAN-AOSCPU';

// ADTRAN-AOSCPU::adGenAOSMemPool.0 = Gauge32: 134217727
// ADTRAN-AOSCPU::adGenAOSHeapSize.0 = Gauge32: 103795696
// ADTRAN-AOSCPU::adGenAOSHeapFree.0 = Gauge32: 81300464

$mempool['total']   = snmp_get($device, "adGenAOSHeapSize.0", "-Ovq", $mib);
$mempool['free']    = snmp_get($device, "adGenAOSHeapFree.0", "-Ovq", $mib);
$mempool['used']    = $mempool['total'] - $mempool['free'];

// EOF
