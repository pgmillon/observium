<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage applications
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// Set variables
$rrd_server      = get_rrd_path($device, "app-ntpd-server-".$app['app_id'].".rrd");
$rrd_client      = get_rrd_path($device, "app-ntpd-client-".$app['app_id'].".rrd");
$ntpd_type       = (is_file($rrd_server) ? "server" : "client");

// Test if this is a server or client install and set app_sections accordingly
if ($ntpd_type == "server")
{
  $app_sections  = array('server' => "System",
                        'buffer' => "Buffer",
                        'packets' => "Packets");
}

$app_graphs['default'] = array('ntpd_stats'  => 'NTP Client - Statistics',
                          'ntpd_freq' => 'NTP Client - Frequency');

$app_graphs['server'] = array('ntpd_stats'  => 'NTPD Server - Statistics',
                          'ntpd_freq' => 'NTPD Server - Frequency',
                          'ntpd_uptime' => 'NTPD Server - Uptime',
                          'ntpd_stratum' => 'NTPD Server - Stratum');

$app_graphs['buffer'] = array('ntpd_buffer' => 'NTPD Server - Buffer');

$app_graphs['packets'] = array('ntpd_bits' => 'NTPD Server - Packets Sent/Received',
                           'ntpd_packets' => 'NTPD Server - Packets Dropped/Ignored');

// EOF
