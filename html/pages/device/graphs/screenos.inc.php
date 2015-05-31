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

if ($device['os'] == "screenos" && is_file(get_rrd_path($device, "screenos-sessions.rrd")))
{
  $graph_title = "Firewall Sessions";
  $graph_type = "screenos_sessions";

  include("includes/print-device-graph.php");
}

// EOF
