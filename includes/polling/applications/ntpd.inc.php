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

if (!empty($agent_data['app']['ntpd']))
{
  $ntpd = $agent_data['app']['ntpd'];

  $app_id = discover_app($device, 'ntpd');

  foreach (explode("\n",$ntpd) as $line)
  {
    list($item,$value) = explode(":",$line,2);
    $ntpd_data[trim($item)] = trim($value);
  }

  $ntpd_type = (isset($ntpd_data['server']) ? "server" : "client");
  $rrd_filename = "app-ntpd-".$ntpd_type."-$app_id.rrd";

  if ($ntpd_type == "server")
  {
    //list ($stratum, $offset, $frequency, $jitter, $noise, $stability, $uptime, $buffer_recv, $buffer_free, $buffer_used, $packets_drop, $packets_ignore, $packets_recv, $packets_sent) = explode("\n", $ntpdserver$
    rrdtool_create($device, $rrd_filename, " \
        DS:stratum:GAUGE:600:-1000:1000 \
        DS:offset:GAUGE:600:-1000:1000 \
        DS:frequency:GAUGE:600:-1000:1000 \
        DS:jitter:GAUGE:600:-1000:1000 \
        DS:noise:GAUGE:600:-1000:1000 \
        DS:stability:GAUGE:600:-1000:1000 \
        DS:uptime:GAUGE:600:0:125000000000 \
        DS:buffer_recv:GAUGE:600:0:100000 \
        DS:buffer_free:GAUGE:600:0:100000 \
        DS:buffer_used:GAUGE:600:0:100000 \
        DS:packets_drop:DERIVE:600:0:125000000000 \
        DS:packets_ignore:DERIVE:600:0:125000000000 \
        DS:packets_recv:DERIVE:600:0:125000000000 \
        DS:packets_sent:DERIVE:600:0:125000000000 ");

    $rrd_data = "N:".$ntpd_data['stratum'].":".$ntpd_data['offset'].":".$ntpd_data['frequency'].":".$ntpd_data['jitter'].":".$ntpd_data['noise'].":".$ntpd_data['stability'].":".$ntpd_data['uptime'].":".$ntpd_data['buffer_recv'].":".$ntpd_data['buffer_free'].":".$ntpd_data['buffer_used'].":".$ntpd_data['packets_drop'].":".$ntpd_data['packets_ignore'].":".$ntpd_data['packets_recv'].":".$ntpd_data['packets_sent'];
    rrdtool_update($device, $rrd_filename, $rrd_data);
  }

  if ($ntpd_type == "client")
  {
    //list ($offset, $frequency, $jitter, $noise, $stability) = explode("\n", $ntpclient);
    rrdtool_create($device, $rrd_filename, " \
        DS:offset:GAUGE:600:-1000:1000 \
        DS:frequency:GAUGE:600:-1000:1000 \
        DS:jitter:GAUGE:600:-1000:1000 \
        DS:noise:GAUGE:600:-1000:1000 \
        DS:stability:GAUGE:600:-1000:1000 ");

    $rrd_data = "N:".$ntpd_data['offset'].":".$ntpd_data['frequency'].":".$ntpd_data['jitter'].":".$ntpd_data['noise'].":".$ntpd_data['stability'];
    rrdtool_update($device, $rrd_filename, $rrd_data);
  }

  unset($ntpd_type,$rrd_data,$ntpd,$ntpd_data);
}

// EOF
