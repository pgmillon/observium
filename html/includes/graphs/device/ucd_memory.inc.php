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

$rrd_filename = get_rrd_path($device, "ucd_mem.rrd");

if (is_file($rrd_filename))
{
  // Actually, the order of the stacks would be better reversed.
  // TrueUsed, with Cached stacked on top, with Buffers stacked on top.
  // This way, TrueUsed can be read off the graph, and the others fill up the graph up to RealUsed.
  // Alas, no time to redesign this entire thing.

  $rrd_options .= " '-b 1024'";

  $rrd_options .= " 'DEF:atotalswap=$rrd_filename:totalswap:AVERAGE'";
  $rrd_options .= " 'DEF:aavailswap=$rrd_filename:availswap:AVERAGE'";
  $rrd_options .= " 'DEF:atotalreal=$rrd_filename:totalreal:AVERAGE'";
  $rrd_options .= " 'DEF:aavailreal=$rrd_filename:availreal:AVERAGE'";
  $rrd_options .= " 'DEF:atotalfree=$rrd_filename:totalfree:AVERAGE'";
  $rrd_options .= " 'DEF:ashared=$rrd_filename:shared:AVERAGE'";
  $rrd_options .= " 'DEF:abuffered=$rrd_filename:buffered:AVERAGE'";
  $rrd_options .= " 'DEF:acached=$rrd_filename:cached:AVERAGE'";
  $rrd_options .= " 'CDEF:totalswap=atotalswap,1024,*'";
  $rrd_options .= " 'CDEF:availswap=aavailswap,1024,*'";
  $rrd_options .= " 'CDEF:totalreal=atotalreal,1024,*'";
  $rrd_options .= " 'CDEF:availreal=aavailreal,1024,*'";
  $rrd_options .= " 'CDEF:totalfree=atotalfree,1024,*'";
  $rrd_options .= " 'CDEF:shared=ashared,1024,*'";
  $rrd_options .= " 'CDEF:buffered=abuffered,1024,*'";
  $rrd_options .= " 'CDEF:cached=acached,1024,*'";
  $rrd_options .= " 'CDEF:usedreal=totalreal,availreal,-'";
  $rrd_options .= " 'CDEF:usedswap=totalswap,availswap,-'";
  $rrd_options .= " 'CDEF:trueused=usedreal,cached,-,buffered,-'";
  $rrd_options .= " 'CDEF:true_perc=trueused,totalreal,/,100,*'";

  $rrd_options .= " 'CDEF:swrl_perc=usedswap,totalreal,/,100,*'";

  $rrd_options .= " 'CDEF:swap_perc=usedswap,totalswap,/,100,*'";
  $rrd_options .= " 'CDEF:real_perc=usedreal,totalreal,/,100,*'";
  $rrd_options .= " 'CDEF:real_percf=100,real_perc,-'";
  $rrd_options .= " 'CDEF:shared_perc=shared,totalreal,/,100,*'";
  $rrd_options .= " 'CDEF:buffered_perc=buffered,totalreal,/,100,*'";
  $rrd_options .= " 'CDEF:cached_perc=cached,totalreal,/,100,*'";

  $rrd_options .= " 'CDEF:cusedswap=usedswap,-1,*'";
  $rrd_options .= " 'CDEF:cdeftot=availreal,buffered,usedreal,cached,usedswap,+,+,+,+'";
  $rrd_options .= " 'COMMENT:Memory        Current   Average   Maximum\\n'";

  $rrd_options .= " 'LINE1:usedreal#ffaa66:'";
  $rrd_options .= " 'AREA:usedreal#f0e0a0:RAM Used '";
  $rrd_options .= " 'GPRINT:usedreal:LAST:%6.2lf%sB'";
  $rrd_options .= " 'GPRINT:usedreal:AVERAGE:%6.2lf%sB'";
  $rrd_options .= " 'GPRINT:usedreal:MAX:%6.2lf%sB'";
  $rrd_options .= " 'GPRINT:real_perc:LAST:%3.0lf%%\\n'";

  $rrd_options .= " 'AREA:trueused#ffaa66:-Ca,Sh,Bu'";
  $rrd_options .= " 'GPRINT:trueused:LAST:%6.2lf%sB'";
  $rrd_options .= " 'GPRINT:trueused:AVERAGE:%6.2lf%sB'";
  $rrd_options .= " 'GPRINT:trueused:MAX:%6.2lf%sB'";
  $rrd_options .= " 'GPRINT:true_perc:LAST:%3.0lf%%\\n'";

  $rrd_options .= " 'AREA:availreal#e5e5e5:RAM Free :STACK'";
  $rrd_options .= " 'GPRINT:availreal:LAST:%6.2lf%sB'";
  $rrd_options .= " 'GPRINT:availreal:AVERAGE:%6.2lf%sB'";
  $rrd_options .= " 'GPRINT:availreal:MAX:%6.2lf%sB'";
  $rrd_options .= " 'GPRINT:real_percf:LAST:%3.0lf%%\\n'";

  $rrd_options .= " 'AREA:cusedswap#C3D9FF:Swap Used'";
  $rrd_options .= " 'LINE1.25:cusedswap#356AA0:'";
  $rrd_options .= " 'GPRINT:usedswap:LAST:%6.2lf%sB'";
  $rrd_options .= " 'GPRINT:usedswap:AVERAGE:%6.2lf%sB'";
  $rrd_options .= " 'GPRINT:usedswap:MAX:%6.2lf%sB'";
  $rrd_options .= " 'GPRINT:swap_perc:LAST:%3.0lf%%\\n'";

  $rrd_options .= " 'LINE1.25:usedswap#356AA0:% of RAM                                '";

  $rrd_options .= " 'GPRINT:swrl_perc:LAST:%3.0lf%%\\n'";

  $rrd_options .= " 'COMMENT: \\n'";

  $rrd_options .= " 'AREA:trueused#ffaa66:'";
  $rrd_options .= " 'AREA:buffered#cc0000::STACK'";
  $rrd_options .= " 'AREA:cached#f0e0a0::STACK'";

  $rrd_options .= " 'LINE1:usedreal#d0b080:'";

  $rrd_options .= " 'LINE1.25:trueused#ffaa66:'";

  $rrd_options .= " 'LINE1.25:buffered#cc0000:Buffers  :STACK'";
  $rrd_options .= " 'GPRINT:buffered:LAST:%6.2lf%sB'";
  $rrd_options .= " 'GPRINT:buffered:AVERAGE:%6.2lf%sB'";
  $rrd_options .= " 'GPRINT:buffered:MAX:%6.2lf%sB'";
  $rrd_options .= " 'GPRINT:buffered_perc:LAST:%3.0lf%%\\n'";

  $rrd_options .= " 'LINE1.25:cached#f0e0a0:Cached   :STACK'";
  $rrd_options .= " 'GPRINT:cached:LAST:%6.2lf%sB'";
  $rrd_options .= " 'GPRINT:cached:AVERAGE:%6.2lf%sB'";
  $rrd_options .= " 'GPRINT:cached:MAX:%6.2lf%sB'";
  $rrd_options .= " 'GPRINT:cached_perc:LAST:%3.0lf%%\\n'";

  $rrd_options .= " 'LINE1.25:shared#008fea:Shared   '";
  $rrd_options .= " 'GPRINT:shared:LAST:%6.2lf%sB'";
  $rrd_options .= " 'GPRINT:shared:AVERAGE:%6.2lf%sB'";
  $rrd_options .= " 'GPRINT:shared:MAX:%6.2lf%sB'";
  $rrd_options .= " 'GPRINT:shared_perc:LAST:%3.0lf%%\\n'";

  $rrd_options .= " 'LINE1:totalreal#050505:'";
  $rrd_options .= " 'LINE1:totalreal#050505:Total  '";
  $rrd_options .= " 'GPRINT:totalreal:AVERAGE:  %6.2lf%sB\\n'";

  $rrd_options .= " 'LINE1.25:usedswap#356AA0:'";

  $rrd_option .= " 'HRULE:0:#00000'";

  $graph_return   = array('descr' => 'This graph shows memory and swap usage for UNIX-like hosts.', 'rrds' => array($rrd_filename));

} else {
  // Fallback to mempools for UNIXes.
  include('includes/graphs/device/mempool.inc.php');
}

// EOF
