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

  133         DEF:cachehits=pdns_recursor.rrd:cache-hits:AVERAGE  \
  134         DEF:cachemisses=pdns_recursor.rrd:cache-misses:AVERAGE  \
  135         DEF:packetcachehits=pdns_recursor.rrd:packetcache-hits:AVERAGE  \
  136         DEF:packetcachemisses=pdns_recursor.rrd:packetcache-misses:AVERAGE  \
  137         CDEF:perc=cachehits,100,*,cachehits,cachemisses,+,/ \
  138         CDEF:packetperc=packetcachehits,100,*,packetcachehits,packetcachemisses,+,/ \
  139         LINE1:perc#0000ff:"percentage cache hits"  \
  140         LINE1:packetperc#ff00ff:"percentage packetcache hits"  \
  141         COMMENT:"\l" \
  142         COMMENT:"Cache hits " \
  143         GPRINT:perc:AVERAGE:"avg %-3.1lf%%\t" \
  144         GPRINT:perc:LAST:"last %-3.1lf%%\t" \
  145         GPRINT:perc:MAX:"max %-3.1lf%%" \
  146         COMMENT:"\l" \
  147         COMMENT:"Pkt hits   " \
  148         GPRINT:packetperc:AVERAGE:"avg %-3.1lf%%\t" \
  149         GPRINT:packetperc:LAST:"last %-3.1lf%%\t" \
  150         GPRINT:packetperc:MAX:"max %-3.1lf%%" \
  151         COMMENT:"\l"
  152
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
