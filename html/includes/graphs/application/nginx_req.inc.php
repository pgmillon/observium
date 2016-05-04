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

$rrd_filename = get_rrd_path($device, "app-nginx-".$app['app_id'].".rrd");

if (is_file($rrd_filename))
{

  $rrd_options .= " -b 1000 ";
  $rrd_options .= " -l 0 ";
  $rrd_options .= " DEF:a=".$rrd_filename.":Requests:AVERAGE";

  $rrd_options .= " COMMENT:'Requests    Current    Average   Maximum\\n'";

  $rrd_options .= " AREA:a#98FB98";
  $rrd_options .= " LINE1.5:a#006400:'Requests  '";
  $rrd_options .= " GPRINT:a:LAST:'%6.2lf %s'";
  $rrd_options .= " GPRINT:a:AVERAGE:'%6.2lf %s'";
  $rrd_options .= " GPRINT:a:MAX:'%6.2lf %s\\n'";
} else {
  $error_msg = "Missing RRD";
}

// EOF
