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

$page_title[] = "Netflow";

// EOF
