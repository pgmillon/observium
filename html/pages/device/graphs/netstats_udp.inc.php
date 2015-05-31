<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2014, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

if (is_file(get_rrd_path($device, "netstats-udp.rrd")))
{
  $graph_title = "UDP Statistics";
  $graph_type = "device_udp";

  include("includes/print-device-graph.php");
}

// EOF
