<?php

//  Lives at /opt/observium/html/pages/device/graphs/sonicwall-sessions.inc.php

if ($device['os'] == "sonicwall")
{
  $graph_title = "Firewall Sessions";
  $graph_type = "netscreen_sessions";

  include("includes/print-device-graph.php");
}

?>
