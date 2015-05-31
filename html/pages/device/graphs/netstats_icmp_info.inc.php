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

if (is_file(get_rrd_path($device, "netstats-icmp.rrd")))
{
  $graph_title = "ICMP Informational Statistics";
  $graph_type = "device_icmp_informational";

  include("includes/print-device-graph.php");
}

// EOF
