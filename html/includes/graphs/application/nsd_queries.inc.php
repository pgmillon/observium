<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

include("includes/graphs/common.inc.php");

$scale_min    = 0;
$colours      = "mixed";
$nototal      = 1;
$unit_text    = "Queries/sec";

$thread = 0;

$i = 0;

$queries_filename = get_rrd_path($device, "app-nsd-queries.rrd");

$rrd_list[$i]['filename'] = $queries_filename;
$rrd_list[$i]['descr']    = 'Total queries';
$rrd_list[$i]['ds']       = 'numQueries';
$rrd_list[$i]['colour']   = 'FF0000FF';
$i++;

$rrd_list[$i]['filename'] = $queries_filename;
$rrd_list[$i]['descr']    = 'Without AA bit';
$rrd_list[$i]['ds']       = 'numQueriesWoAA';
$rrd_list[$i]['colour']   = $config['graph_colours'][$colours][$i % count($config['graph_colours'][$colours])];
$i++;

$server = 0;
while (1)
{
  $rrd_filename = get_rrd_path($device, "app-nsd-server$server.rrd");
  if (file_exists($rrd_filename))
  {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr']    = "Server$server";
    $rrd_list[$i]['ds']       = "numQueries";
    $rrd_list[$i]['colour']   = $config['graph_colours'][$colours][$i % count($config['graph_colours'][$colours])];
    $i++;

    $server++;
  }
  else
  {
    break;
  }
}

$array        = array(
                      'numQueryTCP' => array('descr' => 'TCP', 'colour' => '00FF00FF'),
                      'numQueryUDP' => array('descr' => 'UDP', 'colour' => '0000FFFF'),
                      'numQueryTCP6' => array('descr' => 'TCP6', 'colour' => '00AA00FF'),
                      'numQueryUDP6' => array('descr' => 'UDP6', 'colour' => '0000AAFF'),
                      'numQueryEDNS' => array('descr' => 'EDNS', 'colour' => 'FFFF00FF'),
                      'numQueryEDNSErr' => array('descr' => 'EDNS Err', 'colour' => 'FFAA00FF'),
                      'numQueryRecieveErr' => array('descr' => 'Rec err', 'colour' => '00FFFFFF'),
                      'numQueryTransferErr' => array('descr' => 'Trans err', 'colour' => '00AAAAFF'),
                      'numQueryTruncated' => array('descr' => 'Trunc', 'colour' => 'FF00FFFF'),
                      'numQueryDropped' => array('descr' => 'Drop', 'colour' => 'AA00AAFF'),
                     );

if (is_file($queries_filename))
{
  foreach ($array as $ds => $data)
  {
    $rrd_list[$i]['filename'] = $queries_filename;
    $rrd_list[$i]['descr']    = $data['descr'];
    $rrd_list[$i]['ds']       = $ds;
    $rrd_list[$i]['colour']   = $data['colour'];
    $i++;
  }
} else {
  echo("file missing: $file");
}

include("includes/graphs/generic_multi_line.inc.php");

// EOF
