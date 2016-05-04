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

if (!empty($agent_data['app']['apache']))
{
  $app_id = discover_app($device, 'apache');

  $rrd_filename = "app-apache-$app_id.rrd";

  list ($total_access, $total_kbyte, $cpuload, $uptime, $reqpersec, $bytespersec, $bytesperreq, $busyworkers, $idleworkers,
        $score_wait, $score_start, $score_reading, $score_writing, $score_keepalive, $score_dns, $score_closing, $score_logging,
        $score_graceful, $score_idle, $score_open) = explode("\n", $agent_data['app']['apache']);

  rrdtool_create($device, $rrd_filename, " \
        DS:access:DERIVE:600:0:125000000000 \
        DS:kbyte:DERIVE:600:0:125000000000 \
        DS:cpu:GAUGE:600:0:125000000000 \
        DS:uptime:GAUGE:600:0:125000000000 \
        DS:reqpersec:GAUGE:600:0:125000000000 \
        DS:bytespersec:GAUGE:600:0:125000000000 \
        DS:byesperreq:GAUGE:600:0:125000000000 \
        DS:busyworkers:GAUGE:600:0:125000000000 \
        DS:idleworkers:GAUGE:600:0:125000000000 \
        DS:sb_wait:GAUGE:600:0:125000000000 \
        DS:sb_start:GAUGE:600:0:125000000000 \
        DS:sb_reading:GAUGE:600:0:125000000000 \
        DS:sb_writing:GAUGE:600:0:125000000000 \
        DS:sb_keepalive:GAUGE:600:0:125000000000 \
        DS:sb_dns:GAUGE:600:0:125000000000 \
        DS:sb_closing:GAUGE:600:0:125000000000 \
        DS:sb_logging:GAUGE:600:0:125000000000 \
        DS:sb_graceful:GAUGE:600:0:125000000000 \
        DS:sb_idle:GAUGE:600:0:125000000000 \
        DS:sb_open:GAUGE:600:0:125000000000 ");

  rrdtool_update($device, $rrd_filename, "N:$total_access:$total_kbyte:$cpuload:$uptime:$reqpersec:$bytespersec:$bytesperreq:$busyworkers:$idleworkers:$score_wait:$score_start:$score_reading:$score_writing:$score_keepalive:$score_dns:$score_closing:$score_logging:$score_graceful:$score_idle:$score_open");
}

// EOF
