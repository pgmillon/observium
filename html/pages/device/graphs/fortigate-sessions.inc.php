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

if ($device['os'] == "fortigate" || $device['os_group'] == "fortigate")
{
  $graph_title = "Firewall Sessions";
  $graph_type = "fortigate_sessions";

  include("includes/print-device-graph.php");
}

// EOF
