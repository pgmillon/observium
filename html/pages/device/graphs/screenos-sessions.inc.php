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

if ($device['os'] == "netscreen" || $device['os_group'] == "netscreen")
{
  $graph_title = "Firewall Sessions";
  $graph_type = "netscreen_sessions";

  include("includes/print-device-graph.php");
}

// EOF
