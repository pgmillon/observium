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

$units = '';
$unit_text = 'Operations/sec';
$total_units = 'B';
$colours_in = 'greens';
$multiplier = "1";
$colours_out = 'blues';

$ds_in  = "reads";
$ds_out = "writes";

$nototal = 1;

include("includes/graphs/device/diskio_common.inc.php");

include("includes/graphs/generic_multi_separated.inc.php");

// EOF
