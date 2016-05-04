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
  'Response' => array('descr' => "Responses sent", 'colour' => '999999'),
  'QrySuccess' => array('descr' => "Successful answers", 'colour' => '33cc33'),
  'QryAuthAns' => array('descr' => "Authoritative answer", 'colour' => '009900'),
  'QryNoauthAns' => array('descr' => "Non-authoritative answer", 'colour' => '336633'),
  'QryReferral' => array('descr' => "Referral answer", 'colour' => '996633'),
  'QryNxrrset' => array('descr' => "Empty answers", 'colour' => '36393d'),
  'QrySERVFAIL' => array('descr' => "SERVFAIL answer", 'colour' => 'ff3333'),
  'QryFORMERR' => array('descr' => "FORMERR answer", 'colour' => 'ffcccc'),
  'QryNXDOMAIN' => array('descr' => "NXDOMAIN answers", 'colour' => 'ff33ff'),
  'QryDropped' => array('descr' => "Dropped queries", 'colour' => '666666'),
  'QryFailure' => array('descr' => "Failed queries", 'colour' => 'ff0000'),
  'XfrReqDone' => array('descr' => "Transfers completed", 'colour' => '6666ff'),
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
