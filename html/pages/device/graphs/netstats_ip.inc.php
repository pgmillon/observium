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

if (is_file(get_rrd_path($device, "netstats-ip.rrd")))
{
  $graph_title = "IP Statistics";
  $graph_type = "device_ip";

  include("includes/print-device-graph.php");

  $graph_title = "IP Fragmented Statistics";
  $graph_type = "device_ip_fragmented";

  include("includes/print-device-graph.php");
}

// EOF
