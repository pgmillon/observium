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

if (is_file(get_rrd_path($device, "netstats-icmp.rrd")))
{
  $graph_title = "ICMP Informational Statistics";
  $graph_type = "device_icmp_informational";

  include("includes/print-device-graph.php");
}

// EOF
