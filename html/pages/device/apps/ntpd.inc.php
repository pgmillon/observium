<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2014, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage applications
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// Set variables
$rrd_server      = get_rrd_path($device, "app-ntpd-server-".$app['app_id'].".rrd");
$rrd_client      = get_rrd_path($device, "app-ntpd-client-".$app['app_id'].".rrd");
$ntpd_type       = (file_exists($rrd_server) ? "server" : "client");

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
