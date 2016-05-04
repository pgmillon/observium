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

$rrd_list[1]['filename'] = $rrd_filename;
$rrd_list[1]['descr'] = $int['ifDescr'];
$rrd_list[1]['ds_in'] = "INERRORS";
$rrd_list[1]['ds_out'] = "OUTERRORS";
$rrd_list[1]['descr']   = "Errors";
$rrd_list[1]['colour_area_in'] = "FF3300";
$rrd_list[1]['colour_area_out'] = "FF6633";

$rrd_list[4]['filename'] = $rrd_filename;
$rrd_list[4]['descr'] = $int['ifDescr'];
$rrd_list[4]['ds_in'] = "INDISCARDS";
$rrd_list[4]['ds_out'] = "OUTDISCARDS";
$rrd_list[4]['descr']   = "Discards";
$rrd_list[4]['colour_area_in'] = "805080";
$rrd_list[4]['colour_area_out'] = "c0a060";

$units='';
$unit_text='Packets/sec';
$total_units='B';
$colours_in='greens';
$multiplier = "1";
$colours_out = 'blues';

$args['nototal'] = 1;

include("includes/graphs/generic_multi_separated.inc.php");

// EOF
