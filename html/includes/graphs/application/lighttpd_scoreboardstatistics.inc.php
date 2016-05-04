<?php

$scale_min = 0;

include("includes/graphs/common.inc.php");

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-lighttpd-".$app['app_id'].".rrd";

$array = array('connectionsp' => array('descr' => 'Connect', 'colour' => '750F7DFF'),
               'connectionsC' => array('descr' => 'Close', 'colour' => '00FF00FF'),
               'connectionsE' => array('descr' => 'Hard Error', 'colour' => '4444FFFF'),
               'connectionsk' => array('descr' => 'Keep-alive', 'colour' => '157419FF'),
               'connectionsr' => array('descr' => 'Read', 'colour' => 'FF0000FF'),
               'connectionsR' => array('descr' => 'Read-POST', 'colour' => '6DC8FEFF'),
               'connectionsW' => array('descr' => 'Write', 'colour' => 'FFAB00FF'),
               'connectionsh' => array('descr' => 'Handle-request', 'colour' => 'FFFF00FF'),
               'connectionsq' => array('descr' => 'Request-start', 'colour' => 'FF5576FF'),
               'connectionsQ' => array('descr' => 'Request-end', 'colour' => 'FF3005FF'),
               'connectionss' => array('descr' => 'Response-start', 'colour' => '800080'),
               'connectionsS' => array('descr' => 'Response-end', 'colour' => '959868'),
);

$i = 0;
if (is_file($rrd_filename))
{
  foreach ($array as $ds => $data)
  {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $data['descr'];
    $rrd_list[$i]['ds'] = $ds;
    $rrd_list[$i]['colour'] = $data['colour'];
    $i++;
  }
} else { echo("file missing: $file");  }

$colours   = "mixed";
$nototal   = 1;
$unit_text = "Workers";

include("includes/graphs/generic_multi_simplex_seperated.inc.php");

?>
