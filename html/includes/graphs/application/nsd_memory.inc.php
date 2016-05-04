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
$colours      = "blue";
$nototal      = 1;
$unit_text    = "Bytes";
$rrd_filename = get_rrd_path($device, "app-nsd-memory.rrd");
$array        = array(
                  'memDBDisk' => array('descr' => 'DB Disk', 'colour' => '003366FF'),
                  'memDBMem' => array('descr' => 'DB Mem', 'colour' => '336699FF'),
                  'memXFRDMem' => array('descr' => 'XFRD Mem', 'colour' => '6699CCFF'),
                  'memConfDisk' => array('descr' => 'Conf disk', 'colour' => '99CCEEFF'),
                  'memConfMem' => array('descr' => 'Conf mem', 'colour' => '99CC00FF'),
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

include("includes/graphs/generic_multi_simplex_separated.inc.php");

// EOF
