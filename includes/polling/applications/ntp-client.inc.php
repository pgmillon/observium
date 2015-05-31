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

// Polls ntp-client statistics from script via SNMP
// FIXME do we still support this?

$rrd_filename = "app-ntpclient-".$app['app_id'].".rrd";
$options      = "-O qv";
$oid          = "nsExtendOutputFull.9.110.116.112.99.108.105.101.110.116";

$ntpclient    = snmp_get($device, $oid, $options);

echo(" ntp-client");

list ($offset, $frequency, $jitter, $noise, $stability) = explode("\n", $ntpclient);

rrdtool_create($device, $rrd_filename, " \
        DS:offset:GAUGE:600:-1000:1000 \
        DS:frequency:GAUGE:600:-1000:1000 \
        DS:jitter:GAUGE:600:-1000:1000 \
        DS:noise:GAUGE:600:-1000:1000 \
        DS:stability:GAUGE:600:-1000:1000 ");

rrdtool_update($device, $rrd_filename,  "N:$offset:$frequency:$jitter:$noise:$stability");

// EOF
