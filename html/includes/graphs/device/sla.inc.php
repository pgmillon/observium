<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$sla = dbFetchRow("SELECT * FROM `slas` WHERE `sla_id` = ?", array($vars['id']));
$device = device_by_id_cache($sla['device_id']);

#if ($_GET['width'] >= "450") { $descr_len = "48"; } else { $descr_len = "21"; }
$descr_len = intval($_GET['width'] / 8) * 0.8;

$unit_long = 'milliseconds';
$unit = 'ms';

$rrd_options .= " -l 0 -E ";
$rrd_options .= " COMMENT:'".str_pad($unit_long,$descr_len)."   Cur      Min     Max\\n'";

#$descr = "";
$descr = $sla['sla_nr']." ";
if ($sla['tag'])
  $descr .= $sla['tag'];
if ($sla['owner'])
  $descr .= " (Owner: ". $sla['owner'] .")";
$rrd_file  = get_rrd_path($device, "sla-" . $sla['sla_nr'] . ".rrd");

$rrd_options .= " DEF:rtt=$rrd_file:rtt:AVERAGE ";
$rrd_options .= " VDEF:avg=rtt,AVERAGE ";
$rrd_options .= " LINE1:avg#CCCCFF:'".str_pad('Average',$descr_len-3)."':dashes";
$rrd_options .= " GPRINT:rtt:AVERAGE:%4.1lf".$unit."\\\l ";
$rrd_options .= " LINE1:rtt#CC0000:'" . rrdtool_escape($descr) . "'";
$rrd_options .= " GPRINT:rtt:LAST:%4.1lf".$unit." ";
$rrd_options .= " GPRINT:rtt:MIN:%4.1lf".$unit." ";
$rrd_options .= " GPRINT:rtt:MAX:%4.1lf".$unit."\\\l ";

?>
