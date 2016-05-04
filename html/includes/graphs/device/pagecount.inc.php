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

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$rrd_options .= " -l 0 -E ";

$pagecount_rrd = get_rrd_path($device, "pagecount.rrd");

if (is_file($pagecount_rrd))
{
  $rrd_options .= " COMMENT:'                                      Cur\\n'";
  $rrd_options .= " DEF:pagecount=".$pagecount_rrd.":pagecount:AVERAGE ";
  $rrd_options .= " LINE1:pagecount#CC0000:'Pages printed                   ' ";
  $rrd_options .= " GPRINT:pagecount:LAST:%3.0lf\\\l";
}

// EOF
