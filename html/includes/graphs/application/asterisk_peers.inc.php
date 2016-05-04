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

$rrd_filename = get_rrd_path($device, "app-asterisk-".$app['app_id'].".rrd");

$array = array('sippeers' => array('descr' => 'SIP Peers', 'colour' => '750F7DFF'),
               'sippeersonline' => array('descr' => 'SIP Peers Active', 'colour' => '00FF00FF'),
               'iaxpeers' => array('descr' => 'IAX Peers', 'colour' => '4444FFFF'),
               'iaxpeersonline' => array('descr' => 'IAX Peers Active', 'colour' => '157419FF'),
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
$nototal   = 0;
$unit_text = "Peers";

#include("includes/graphs/generic_multi_simplex_separated.inc.php");
include("includes/graphs/generic_multi_line.inc.php");

// EOF
