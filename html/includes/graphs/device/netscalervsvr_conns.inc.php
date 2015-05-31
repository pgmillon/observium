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

$ds_in  = "CurClntConnections";
$ds_out = "CurSrvrConnections";

$unit_text = "Connections";

include("netscalervsvr.inc.php");

$units ='pps';
$total_units ='Pkts';
$multiplier = 1;
$colours_in ='purples';
$colours_out = 'oranges';

#$nototal = 1;

$graph_title .= "::connections";

include("includes/graphs/generic_multi_separated.inc.php");

// EOF
