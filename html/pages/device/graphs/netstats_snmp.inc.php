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

if (is_file(get_rrd_path($device, "netstats-snmp.rrd")))
{
  $graph_title = "SNMP Packets Statistics";
  $graph_type = "device_snmp_packets";

  include("includes/print-device-graph.php");

  $graph_title = "SNMP Message Type Statistics";
  $graph_type = "device_snmp_statistics";

  include("includes/print-device-graph.php");
}

// EOF
