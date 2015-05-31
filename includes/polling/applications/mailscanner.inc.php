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

// Polls MailScanner statistics from script via SNMP
// FIXME do we still support this in some way?

$rrd_filename  = "app-mailscannerV2-" . $app['app_id'] . ".rrd";
$options       = "-O qv";
$oid           = "nsExtendOutputFull.11.109.97.105.108.115.99.97.110.110.101.114";

$mailscanner   = snmp_get($device, $oid, $options);

echo(" mailscanner");

list ($msg_recv, $msg_rejected, $msg_relay, $msg_sent, $msg_waiting, $spam, $virus) = explode("\n", $mailscanner);

rrdtool_create($device, $rrd_filename, " \
        DS:msg_recv:COUNTER:600:0:125000000000 \
        DS:msg_rejected:COUNTER:600:0:125000000000 \
        DS:msg_relay:COUNTER:600:0:125000000000 \
        DS:msg_sent:COUNTER:600:0:125000000000 \
        DS:msg_waiting:COUNTER:600:0:125000000000 \
        DS:spam:COUNTER:600:0:125000000000 \
        DS:virus:COUNTER:600:0:125000000000 ");

rrdtool_update($device, $rrd_filename,  "N:$msg_recv:$msg_rejected:$msg_relay:$msg_sent:$msg_waiting:$spam:$virus");

// EOF
