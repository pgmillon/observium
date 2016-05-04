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

//  Lives at /opt/observium/html/pages/device/graphs/sonicwall-sessions.inc.php

if ($device['os'] == "sonicwall")
{
  $graph_title = "Firewall Sessions";
  $graph_type = "netscreen_sessions";

  include("includes/print-device-graph.php");
}

// EOF
