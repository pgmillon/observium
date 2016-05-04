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

$ds_in = "InPkts";
$ds_out = "OutPkts";

$colour_area_in = "AA66AA";
$colour_line_in = darken_color($colour_area_in);
//$colour_line_in = "330033";
$colour_area_out = "FFDD88";
$colour_line_out = "FF6600";

$colour_area_in_max = "cc88cc";
$colour_area_out_max = "FFefaa";

//$graph_max = 1;
$unit_text = "Packets/s";

$args['nototal'] = 1; $print_total = 0; $nototal = 1;

include($config['html_dir']."/includes/graphs/generic_duplex.inc.php");

// EOF
