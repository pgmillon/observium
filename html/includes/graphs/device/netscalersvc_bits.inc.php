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

// Generate a list of svcs and then call the multi_bits grapher to generate from the list

$ds_in = "TotalRequestBytes";
$ds_out = "TotalResponseBytes";

include("netscalersvc.inc.php");

$units ='b';
$total_units ='B';
$colours_in ='greens';
$multiplier = "8";
$colours_out = 'blues';

#$nototal = 1;

$ds_in  = "INOCTETS";
$ds_out = "OUTOCTETS";

$graph_title .= "::bits";

$colour_line_in = "006600";
$colour_line_out = "000099";
$colour_area_in = "91B13C";
$colour_area_out = "8080BD";

include("includes/graphs/generic_multi_separated.inc.php");

// EOF
