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

#  $rrd_list[1]['filename'] = get_port_rrdfilename($port, NULL, TRUE);
#  $rrd_list[1]['descr'] = $int['ifDescr'];
#  $rrd_list[1]['ds_in'] = "INNUCASTPKTS";
#  $rrd_list[1]['ds_out'] = "OUTNUCASTPKTS";
#  $rrd_list[1]['descr']   = "NonUnicast";
#  $rrd_list[1]['colour_area_in'] = "BB77BB";
#  $rrd_list[1]['colour_area_out'] = "FFDD88";

$rrd_list[2]['filename'] = get_port_rrdfilename($port, NULL, TRUE);
$rrd_list[2]['descr'] = $int['ifDescr'];
$rrd_list[2]['ds_in'] = "INBROADCASTPKTS";
$rrd_list[2]['ds_out'] = "OUTBROADCASTPKTS";
$rrd_list[2]['descr']   = "Broadcast";
$rrd_list[2]['colour_area_in'] = "905090";
$rrd_list[2]['colour_area_out'] = "CCA514";

$rrd_list[4]['filename'] = get_port_rrdfilename($port, NULL, TRUE);
$rrd_list[4]['descr'] = $int['ifDescr'];
$rrd_list[4]['ds_in'] = "INMULTICASTPKTS";
$rrd_list[4]['ds_out'] = "OUTMULTICASTPKTS";
$rrd_list[4]['descr']   = "Multicast";
$rrd_list[4]['colour_area_in'] = "DC91DC";
$rrd_list[4]['colour_area_out'] = "FFE940";

$units='';
$unit_text='Packets/sec';
$colours_in='purples';
$multiplier = "1";
$colours_out = 'oranges';

$args['nototal'] = 1; $print_total = 0; $nototal = 1;

include("includes/graphs/generic_multi_separated.inc.php");

?>
