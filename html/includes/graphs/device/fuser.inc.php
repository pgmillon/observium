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

$rrd_filename = get_rrd_path($device, "fuser.rrd");

if (is_file($rrd_filename))
{
  $rrd_options .= " COMMENT:'                           Cur   Min  Max\\n'";

  $rrd_options .= " DEF:level=".$rrd_filename.":level:AVERAGE ";
  $rrd_options .= " LINE1:level#CC0000:'Fuser                ' ";
  $rrd_options .= " GPRINT:level:LAST:%3.0lf%% ";
  $rrd_options .= " GPRINT:level:MIN:%3.0lf%% ";
  $rrd_options .= " GPRINT:level:MAX:%3.0lf%%\\\l ";
}

// EOF
