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

$datas = array(
  'Traffic' => 'nfsen_traffic',
  'Packets' => 'nfsen_packets',
  'Flows' => 'nfsen_flows'
);

foreach ($datas as $name=>$type)
{
  $graph_title = $name;
  $graph_array['type'] = "device_".$type;

  include("includes/print-device-graph.php");
}

$pagetitle[] = "Netflow";

// EOF
