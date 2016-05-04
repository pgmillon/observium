<?php
/*
  DS:shmemfragments:GAUGE:600:0:125000000000 \
  DS:shmemfreesize:GAUGE:600:0:125000000000 \
  DS:shmemmaxusedsize:GAUGE:600:0:125000000000 \
  DS:shmemrealusedsize:GAUGE:600:0:125000000000 \
  DS:shmemtotalsize:GAUGE:600:0:125000000000 \
  DS:shmemusedsize:GAUGE:600:0:125000000000 \
*/

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$rrd_filename = get_rrd_path($device, "app-kamailio-".$app['app_id'].".rrd");

if($width > 500)
{
  $descr_len = 22;
} else {
  $descr_len = 12;
}
$descr_len += round(($width - 150) / 8);

$iter = 0;
$colours = 'mixed';

$rrd_options .= " COMMENT:'".str_pad('Size   %used', $descr_len+20, ' ', STR_PAD_LEFT)."\\\l'";

if (is_file($rrd_filename))
{
  $colour=$config['graph_colours'][$colours][$iter];

  $descr = rrdtool_escape(rewrite_hrDevice('Max Used'), $descr_len);

  $rrd_options .= " DEF:".$iter."used=$rrd_filename:shmemmaxusedsize:AVERAGE";
  $rrd_options .= " DEF:".$iter."size=$rrd_filename:shmemtotalsize:AVERAGE";
  $rrd_options .= " CDEF:".$iter."free=".$iter."size,".$iter."used,-";
  $rrd_options .= " CDEF:".$iter."perc=".$iter."used,".$iter."size,/,100,*";
  $rrd_options .= " AREA:".$iter."used#" . $colour . "10";
  $rrd_options .= " LINE1.25:".$iter."used#" . $colour . ":'$descr'";
  $rrd_options .= " GPRINT:".$iter."used:LAST:%6.2lf%sB";
  $rrd_options .= " GPRINT:".$iter."perc:LAST:%5.2lf%%\\\l";
  $iter++;

  $colour=$config['graph_colours'][$colours][$iter];

  $descr = rrdtool_escape(rewrite_hrDevice('Used'), $descr_len);

  $rrd_options .= " DEF:".$iter."used=$rrd_filename:shmemusedsize:AVERAGE";
  $rrd_options .= " DEF:".$iter."free=$rrd_filename:shmemfreesize:AVERAGE";
  $rrd_options .= " DEF:".$iter."size=$rrd_filename:shmemtotalsize:AVERAGE";
  $rrd_options .= " CDEF:".$iter."perc=".$iter."used,".$iter."size,/,100,*";
  $rrd_options .= " AREA:".$iter."used#" . $colour . "10";
  $rrd_options .= " LINE1.25:".$iter."used#" . $colour . ":'$descr'";
  $rrd_options .= " GPRINT:".$iter."used:LAST:%6.2lf%sB";
  $rrd_options .= " GPRINT:".$iter."perc:LAST:%5.2lf%%\\\l";
  $iter++;

  $colour=$config['graph_colours'][$colours][$iter];

  $descr = rrdtool_escape(rewrite_hrDevice('Real Used'), $descr_len);

  $rrd_options .= " DEF:".$iter."used=$rrd_filename:shmemrealusedsize:AVERAGE";
  $rrd_options .= " DEF:".$iter."size=$rrd_filename:shmemtotalsize:AVERAGE";
  $rrd_options .= " CDEF:".$iter."free=".$iter."size,".$iter."used,-";
  $rrd_options .= " CDEF:".$iter."perc=".$iter."used,".$iter."size,/,100,*";
  $rrd_options .= " AREA:".$iter."used#" . $colour . "10";
  $rrd_options .= " LINE1.25:".$iter."used#" . $colour . ":'$descr'";
  $rrd_options .= " GPRINT:".$iter."used:LAST:%6.2lf%sB";
  $rrd_options .= " GPRINT:".$iter."perc:LAST:%5.2lf%%\\\l";
  $iter++;

  $colour=$config['graph_colours'][$colours][$iter];

  $descr = rrdtool_escape(rewrite_hrDevice('Fragments'), $descr_len);

  $rrd_options .= " DEF:".$iter."used=$rrd_filename:shmemfragments:AVERAGE";
  $rrd_options .= " DEF:".$iter."size=$rrd_filename:shmemtotalsize:AVERAGE";
  $rrd_options .= " CDEF:".$iter."free=".$iter."size,".$iter."used,-";
  $rrd_options .= " CDEF:".$iter."perc=".$iter."used,".$iter."size,/,100,*";
  $rrd_options .= " AREA:".$iter."used#" . $colour . "10";
  $rrd_options .= " LINE1.25:".$iter."used#" . $colour . ":'$descr'";
  $rrd_options .= " GPRINT:".$iter."used:LAST:%6.2lf%sB";
  $rrd_options .= " GPRINT:".$iter."perc:LAST:%5.2lf%%\\\l";
  $iter++;

  $colour=$config['graph_colours'][$colours][$iter];

  $descr = rrdtool_escape(rewrite_hrDevice('Total'), $descr_len);

  $rrd_options .= " DEF:".$iter."size=$rrd_filename:shmemtotalsize:AVERAGE";
  $rrd_options .= " LINE1.25:".$iter."size#" . $colour . ":'$descr'";
  $rrd_options .= " GPRINT:".$iter."size:LAST:%6.2lf%sB";
  $rrd_options .= "\\\l";
} else { echo("file missing: $file");  }

// EOF