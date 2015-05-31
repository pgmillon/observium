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

$ds_in  = "TotalPktsRecvd";
$ds_out = "TotalPktsSent";

$unit_text = "Packets";

include("netscalervsvr.inc.php");

$units ='pps';
$total_units ='Pkts';
$multiplier = 1;
$colours_in ='purples';
$colours_out = 'oranges';

#$nototal = 1;

$graph_title .= "::packets";

include("includes/graphs/generic_multi_separated.inc.php");

// EOF
