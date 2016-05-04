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

$rrd_filename = get_rrd_path($device, "app-vmwaretools-".$app['app_id'].".rrd");

if (is_file($rrd_filename))
{
  $rrd_options .= " '-b 1024'";

  $rrd_options .= " 'DEF:vmtotalmem=$rrd_filename:vmtotalmem:AVERAGE'";
  $rrd_options .= " 'DEF:vmmemlimit=$rrd_filename:vmmemlimit:AVERAGE'";
  $rrd_options .= " 'DEF:vmmemres=$rrd_filename:vmmemres:AVERAGE'";
  $rrd_options .= " 'DEF:vmswap=$rrd_filename:vmswap:AVERAGE'";
  $rrd_options .= " 'DEF:vmballoon=$rrd_filename:vmballoon:AVERAGE'";

  $rrd_options .= " CDEF:vmtotalmemG=vmtotalmem,1048576,*";
  $rrd_options .= " CDEF:vmmemresG=vmmemres,1048576,*";
  $rrd_options .= " CDEF:vmmemlimitG=vmmemlimit,1048576,*";
  $rrd_options .= " CDEF:vmswapG=vmswap,1048576,*";
  $rrd_options .= " CDEF:vmballoonG=vmballoon,1048576,*";

  $rrd_options .= " 'COMMENT:Bytes             Current   Average   Maximum\\n'";

  $rrd_options .= " 'AREA:vmtotalmemG#EEEEEE'";
  $rrd_options .= " 'LINE1.25:vmtotalmemG#000000:Total      '";

  $rrd_options .= " 'GPRINT:vmtotalmemG:LAST:%6.1lf%sB'";
  $rrd_options .= " 'GPRINT:vmtotalmemG:AVERAGE:%6.1lf%sB'";
  $rrd_options .= " 'GPRINT:vmtotalmemG:MAX:%6.1lf%sB\\n'";

  $rrd_options .= " 'AREA:vmswapG#ff1a00:vSwap Used '";
  $rrd_options .= " 'GPRINT:vmswapG:LAST:%6.1lf%sB'";
  $rrd_options .= " 'GPRINT:vmswapG:AVERAGE:%6.1lf%sB'";
  $rrd_options .= " 'GPRINT:vmswapG:MAX:%6.1lf%sB\\n'";

  $rrd_options .= " 'AREA:vmballoonG#f0e0a0:Balloon    :STACK'";

  $rrd_options .= " 'LINE1.25:vmswapG#cc0000:'";
  $rrd_options .= " 'LINE1.25:vmballoonG#d0b080::STACK'";

  $rrd_options .= " 'GPRINT:vmballoonG:LAST:%6.1lf%sB'";
  $rrd_options .= " 'GPRINT:vmballoonG:AVERAGE:%6.1lf%sB'";
  $rrd_options .= " 'GPRINT:vmballoonG:MAX:%6.1lf%sB\\n'";

  //$rrd_options .= " 'AREA:vmmemresG#f0e0a0'";
  $rrd_options .= " 'LINE1.25:vmmemresG#008000:Reservation'";
  $rrd_options .= " 'GPRINT:vmmemresG:LAST:%6.1lf%sB'";
  $rrd_options .= " 'GPRINT:vmmemresG:AVERAGE:%6.1lf%sB'";
  $rrd_options .= " 'GPRINT:vmmemresG:MAX:%6.1lf%sB\\n'";

  //$rrd_options .= " 'AREA:vmmemlimitG#ffaa66:Limit   '";
  $rrd_options .= " 'LINE1.25:vmmemlimitG#800080:Limit      '";
  $rrd_options .= " 'GPRINT:vmmemlimitG:LAST:%6.1lf%sB'";
  $rrd_options .= " 'GPRINT:vmmemlimitG:AVERAGE:%6.1lf%sB'";
  $rrd_options .= " 'GPRINT:vmmemlimitG:MAX:%6.1lf%sB\\n'";

  $rrd_option .= " 'HRULE:0:#00000'";

  $graph_return   = array('descr' => 'Reservation shows the guaranteed memory allocation on this virtual machine, Limit is the upper limit for memory useage on this virtual machine, vSwap show the memory swapped to the ESX host from this virtual machine, Balloon show how much memory is being ballooned on this virtual machine.', 'rrds' => array($rrd_filename));

}

// EOF
