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

$units ='bps';
$total_units ='B';
$colours_in ='greens';
$multiplier = "8";
$colours_out = 'blues';

$nototal = 1;
$ds_in  = "read";
$ds_out = "written";

include("includes/graphs/device/diskio_common.inc.php");

include("includes/graphs/generic_multi_bits_separated.inc.php");

// EOF
