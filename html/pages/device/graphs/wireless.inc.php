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

if (is_file(get_rrd_path($device, "wificlients-radio1.rrd")))
{
  $graph_title = "Wireless clients";
  $graph_type = "device_wificlients";

  include("includes/print-device-graph.php");
}

// EOF
