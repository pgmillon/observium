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

$mysql_rrd = get_rrd_path($device, "app-mysql-".$app['app_id'].".rrd");

if (is_file($mysql_rrd))
{
  $rrd_filename = $mysql_rrd;
}

$rrd_options .= ' DEF:a='.$rrd_filename.':IBIRd:AVERAGE ';
$rrd_options .= ' DEF:b='.$rrd_filename.':IBIWr:AVERAGE ';
$rrd_options .= ' DEF:c='.$rrd_filename.':IBILg:AVERAGE ';
$rrd_options .= ' DEF:d='.$rrd_filename.':IBIFSc:AVERAGE ';

$rrd_options .= 'COMMENT:"    Current    Average   Maximum\n" ';

$rrd_options .= 'LINE1:a#22FF22:"File Reads  "    ';
$rrd_options .= 'GPRINT:a:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:MAX:"%6.2lf %s\\n"  ';

$rrd_options .= 'LINE1:b#0022FF:"File Writes  "  ';
$rrd_options .= 'GPRINT:b:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:MAX:"%6.2lf %s\\n"  ';

$rrd_options .= 'LINE1:c#FF0000:"Log Writes  "  ';
$rrd_options .= 'GPRINT:c:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:c:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:c:MAX:"%6.2lf %s\\n"  ';

$rrd_options .= 'LINE1:d#00AAAA:"File syncs  "  ';
$rrd_options .= 'GPRINT:d:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:d:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:d:MAX:"%6.2lf %s\\n"  ';

// EOF
