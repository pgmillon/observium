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

if (is_file(get_rrd_path($device, "ucd_mem.rrd")))
{
  $graph_title = "Memory Utilisation";
  $graph_type = "device_memory";

  include("includes/print-device-graph.php");
}

// EOF
