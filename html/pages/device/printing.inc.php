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

echo generate_box_open();

echo('<table class="table table-condensed table-striped  table-striped">');

$graph_title = "Toner";
$graph_type = "device_toner";

include("includes/print-device-graph.php");

unset($graph_array);

echo '</table>';

echo generate_box_close();

print_toner_table($vars);

echo generate_box_open();

echo('<table class="table table-condensed table-striped  table-striped">');

if (get_dev_attrib($device, "pagecount_oid"))
{
  $graph_title = "Pagecounter";
  $graph_type = "device_pagecount";

  include("includes/print-device-graph.php");
}

unset($graph_array);

if (get_dev_attrib($device, "imagingdrum_c_oid"))
{
  $graph_title = "Imaging Drums";
  $graph_type = "device_imagingdrums";

  include("includes/print-device-graph.php");
}
elseif (get_dev_attrib($device, "imagingdrum_oid"))
{
  $graph_title = "Imaging Drum";
  $graph_type = "device_imagingdrum";

  include("includes/print-device-graph.php");
}

unset($graph_array);

if (get_dev_attrib($device, "fuser_oid"))
{
  $graph_title = "Fuser";
  $graph_type = "device_fuser";

  include("includes/print-device-graph.php");
}

unset($graph_array);

if (get_dev_attrib($device, "transferroller_oid"))
{
  $graph_title = "Transfer Roller";
  $graph_type = "device_transferroller";

  include("includes/print-device-graph.php");
}

unset($graph_array);

if (get_dev_attrib($device, "wastebox_oid"))
{
  $graph_title = "Waste Toner Box";
  $graph_type = "device_wastebox";

  include("includes/print-device-graph.php");
}

echo('</table>');

echo generate_box_close();

$page_title[] = "Printing";

// EOF
