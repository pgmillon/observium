<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

if (is_file(get_rrd_path($device, "ipSystemStats-ipv6.rrd")))
{
  $graph_title = "IPv6 IP Packet Statistics";
  $graph_type = "device_ipSystemStats_v6";

  include("includes/print-device-graph.php");

  $graph_title = "IPv6 IP Fragmentation Statistics";
  $graph_type = "device_ipSystemStats_v6_frag";

  include("includes/print-device-graph.php");
}

if (is_file(get_rrd_path($device, "ipSystemStats-ipv4.rrd")))
{
  $graph_title = "IPv4 IP Packet Statistics";
  $graph_type = "device_ipSystemStats_v4";

  include("includes/print-device-graph.php");
}

if (is_file(get_rrd_path($device, "netstats-ip.rrd")))
{
  $graph_title = "IP Statistics";
  $graph_type = "device_ip";

  include("includes/print-device-graph.php");

  $graph_title = "IP Fragmented Statistics";
  $graph_type = "device_ip_fragmented";

  include("includes/print-device-graph.php");
}

if (is_file(get_rrd_path($device, "netstats-tcp.rrd")))
{
  $graph_title = "TCP Statistics";
  $graph_type = "device_tcp";

  include("includes/print-device-graph.php");
}

if (is_file(get_rrd_path($device, "netstats-udp.rrd")))
{
  $graph_title = "UDP Statistics";
  $graph_type = "device_udp";

  include("includes/print-device-graph.php");
}

if (is_file(get_rrd_path($device, "netstats-snmp.rrd")))
{
  $graph_title = "SNMP Packets Statistics";
  $graph_type = "device_snmp_packets";

  include("includes/print-device-graph.php");

  $graph_title = "SNMP Message Type Statistics";
  $graph_type = "device_snmp_statistics";

  include("includes/print-device-graph.php");
}

if (is_file(get_rrd_path($device, "netstats-icmp.rrd")))
{
  $graph_title = "ICMP Statistics";
  $graph_type = "device_icmp";

  include("includes/print-device-graph.php");

  $graph_title = "ICMP Informational Statistics";
  $graph_type = "device_icmp_informational";

  include("includes/print-device-graph.php");
}

// EOF
