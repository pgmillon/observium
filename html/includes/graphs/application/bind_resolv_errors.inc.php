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
$rrd_filename = get_rrd_path($device, "app-bind-".$app['app_id']."-resolver-default.rrd");

$array = array(
  'EDNS0Fail' => array('descr' => "EDNS(0) query failures", 'colour' => '87cefa'),
  'Mismatch' => array('descr' => "Mismatch responses received", 'colour' => '00bfff'),
  'Truncated' => array('descr' => "Truncated responses received", 'colour' => 'ff69b4'),
  'Lame' => array('descr' => "Lame delegations received", 'colour' => 'ff1493'),
  'Retry' => array('descr' => "Retried queries", 'colour' => 'ffa07a'),
  'QueryAbort' => array('descr' => "Aborted due to quota", 'colour' => 'ff6533'),
  'QuerySockFail' => array('descr' => "Socket errors", 'colour' => 'ff8c00'),
  'QueryTimeout' => array('descr' => "Timeouts", 'colour' => 'ff0000'),
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

include("includes/graphs/generic_multi_line.inc.php");

// EOF
