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

$ds_in  = "TotalPktsRecvd";
$ds_out = "TotalPktsSent";

$unit_text = "Packets";

include("netscalersvcgrpmem.inc.php");

$units ='pps';
$total_units ='Pkts';
$multiplier = 1;
$colours_in ='purples';
$colours_out = 'oranges';

#$nototal = 1;

$graph_title .= "::packets";

include("includes/graphs/generic_multi_separated.inc.php");

// EOF
