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

$ds_in  = "TotalRequests";
$ds_out = "TotalResponses";

$unit_text = "Requests";

include("netscalersvc.inc.php");

$units ='pps';
$total_units ='Pkts';
$multiplier = 1;
$colours_in ='purples';
$colours_out = 'oranges';

#$nototal = 1;

$graph_title .= "::reqs";

include("includes/graphs/generic_multi_separated.inc.php");

// EOF
