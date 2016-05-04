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

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$colours      = "mixed";
$nototal      = (($width<224) ? 1 : 0);
$unit_text    = "Count";
$rrd_filename = get_rrd_path($device, "app-bind-".$app['app_id']."-ns-stats.rrd");

$array = array(
  'UpdateDone' => array('descr' => "Completed", 'colour' => '228b22'),
  'UpdateFail' => array('descr' => "Failed", 'colour' => 'ff0000'),
  'UpdateRej' => array('descr' => "Rejected", 'colour' => 'cd853f'),
  'UpdateBadPrereq' => array('descr' => "Rejected due to prereq fail", 'colour' => 'ff8c00'),
  'UpdateReqFwd' => array('descr' => "Fwd request", 'colour' => '6495ed'),
  'UpdateRespFwd' => array('descr' => "Fwd response", 'colour' => '40e0d0'),
  'UpdateFwdFail' => array('descr' => "Fwd failed", 'colour' => 'ffd700'),
);
$i = 0;

if (is_file($rrd_filename))
{
    foreach ($array as $ds => $data)
    {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $data['descr'];
        $rrd_list[$i]['ds']       = $ds;
        $rrd_list[$i]['colour']   = $data['colour'];
        $i++;
    }
} else {
    echo("file missing: $file");
}

include("includes/graphs/generic_multi.inc.php");

// EOF
