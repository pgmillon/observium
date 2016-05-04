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

$scale_min    = 0;
$colours      = "mixed";
$nototal      = (($width<224) ? 1 : 0);
$unit_text    = "Cache hits";
$rrd_filename = get_rrd_path($device, "app-powerdns-recursor-".$app['app_id'].".rrd");
$array        = array(
                      'throttleEntries'    => array('descr' => 'Throttle map entries', 'colour' => '00FFF0FF'),
                     );

/*
FIXME:

rrdtool graph $GRAPHOPTS --start -$1 $WWWPREFIX/cpuload-$2.png -w $WSIZE -h $HSIZE -l 0\
  154         -v "percentage" \
  155         -t "cpu load" \
  156         DEF:usermsec=pdns_recursor.rrd:user-msec:AVERAGE \
  157         DEF:sysmsec=pdns_recursor.rrd:sys-msec:AVERAGE \
  158         DEF:musermsec=pdns_recursor.rrd:user-msec:MAX \
  159         DEF:msysmsec=pdns_recursor.rrd:sys-msec:MAX \
  160         CDEF:userperc=usermsec,10,/ \
  161         CDEF:sysperc=sysmsec,10,/ \
  162         CDEF:totmperc=usermsec,sysmsec,+,10,/ \
  163         LINE1:totmperc#ffff00:"max cpu use" \
  164         AREA:userperc#ff0000:"user cpu percentage" \
  165         STACK:sysperc#00ff00:"system cpu percentage" \
  166         COMMENT:"\l" \
  167         COMMENT:"System cpu " \
  168         GPRINT:sysperc:AVERAGE:"avg %-3.1lf%%\t" \
  169         GPRINT:sysperc:LAST:"last %-3.1lf%%\t" \
  170         GPRINT:sysperc:MAX:"max %-3.1lf%%\t" \
  171         COMMENT:"\l" \
  172         COMMENT:"User cpu   " \
  173         GPRINT:userperc:AVERAGE:"avg %-3.1lf%%\t" \
  174         GPRINT:userperc:LAST:"last %-3.1lf%%\t" \
  175         GPRINT:userperc:MAX:"max %-3.1lf%%" \
  176         COMMENT:"\l"
*/

$i            = 0;

if (is_file($rrd_filename))
{
  foreach ($array as $ds => $data)
  {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $data['descr'];
    $rrd_list[$i]['ds'] = $ds;
    $rrd_list[$i]['colour'] = $data['colour'];
    $i++;
  }
} else {
  echo("file missing: $file");
}

include("includes/graphs/generic_multi_line.inc.php");

// EOF
