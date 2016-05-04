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

$scale_min = 0;

include_once($config['html_dir'].'/includes/graphs/common.inc.php');

$rrd = get_port_rrdfilename($port, 'fdbcount', TRUE);
if (is_file($rrd))
{
  $rrd_filename = $rrd;
}

$ds = 'value';

$colour_area = 'EEEEEE';
$colour_line = '36393D';
$colour_area_max = 'FFEE99';
$unit_text = 'MACs';
$unit_integer = TRUE;
$line_text = 'Count';

include_once($config['html_dir'].'/includes/graphs/generic_simplex.inc.php');

// EOF
