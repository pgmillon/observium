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

$scale_min = "0";
$scale_max = "100";

include($config['html_dir']."/includes/graphs/common.inc.php");

$rrd_options .= " -b 1024";

$iter = "1";

$rrd_options .= " COMMENT:'                    Size      Free   % Used\\n'";

$colour = "CC0000";
$colour_area = "ffaaaa";

$descr = rrdtool_escape($storage['storage_descr'], 12);

$percentage = round($storage['storage_perc'], 0);

$background = get_percentage_colours($percentage);

$rrd_options .= " DEF:used=$rrd_filename:used:AVERAGE";
$rrd_options .= " DEF:free=$rrd_filename:free:AVERAGE";
$rrd_options .= " CDEF:size=used,free,+";
$rrd_options .= " CDEF:perc=used,size,/,100,*";
$rrd_options .= " AREA:perc#" . $background['right'] . ":";
$rrd_options .= " LINE1.25:perc#" . $background['left'] . ":'$descr'";
$rrd_options .= " GPRINT:size:LAST:%6.2lf%sB";
$rrd_options .= " GPRINT:free:LAST:%6.2lf%sB";
$rrd_options .= " GPRINT:perc:LAST:%5.2lf%%\\\\n";

if ($_GET['trend'])
{
  $rrd_options .= " VDEF:slope=perc,LSLSLOPE ";
  $rrd_options .= " VDEF:cons=perc,LSLINT ";
  $rrd_options .= " CDEF:lsl2=perc,POP,slope,COUNT,*,cons,+ ";
  $rrd_options .= ' LINE1.25:lsl2#ff0000::dashes=2';
}

if ($_GET['previous'])
{
  $descr = rrdtool_escape("Prev ".$storage['storage_descr'], 12);

  $colour = "99999999";
  $colour_area = "66666666";

  $rrd_options .= " DEF:usedX=$rrd_filename:used:AVERAGE:start=".$prev_from.":end=".$from;
  $rrd_options .= " DEF:freeX=$rrd_filename:free:AVERAGE:start=".$prev_from.":end=".$from;
  $rrd_options .= " SHIFT:usedX:$period";
  $rrd_options .= " SHIFT:freeX:$period";
  $rrd_options .= " CDEF:sizeX=usedX,freeX,+";
  $rrd_options .= " CDEF:percX=usedX,sizeX,/,100,*";
  $rrd_options .= " AREA:percX#" . $colour_area . ":";
  $rrd_options .= " LINE1.25:percX#" . $colour . ":'$descr'";
  $rrd_options .= " GPRINT:sizeX:LAST:%6.2lf%sB";
  $rrd_options .= " GPRINT:freeX:LAST:%6.2lf%sB";
  $rrd_options .= " GPRINT:percX:LAST:%5.2lf%%\\\\n";
}

?>
