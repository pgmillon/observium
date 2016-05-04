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

if ($device['os'] == "screenos" && is_file(get_rrd_path($device, "screenos-sessions.rrd")))
{
  $graph_title = "Firewall Sessions";
  $graph_type = "screenos_sessions";

  include("includes/print-device-graph.php");
}

// EOF
