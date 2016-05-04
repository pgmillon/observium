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

include_once($config['html_dir']."/includes/graphs/common.inc.php");

if ($width > 500)
{
  $descr_len = 22;
} else {
  $descr_len = 12;
}
$descr_len += round(($width - 250) / 8);

$iter = 0;
$colours = 'mixed-10c';

$rrd_options .= " COMMENT:'".str_pad('Size      Used    %used', $descr_len+31, ' ', STR_PAD_LEFT)."\\\l'";

foreach (dbFetchRows("SELECT * FROM `mempools` where `device_id` = ?", array($device['device_id'])) as $mempool)
{
  if (!$config['graph_colours'][$colours][$iter]) { $iter = 0; }
  $colour=$config['graph_colours'][$colours][$iter];

  $descr = rrdtool_escape(rewrite_hrDevice($mempool['mempool_descr']), $descr_len);
  if (isset($mempool['mempool_type'])) { $mempool['mempool_mib'] = $mempool['mempool_type']; }

  $rrd_filename = get_rrd_path($device, "mempool-".$mempool['mempool_mib']."-".$mempool['mempool_index'].".rrd");

  if (is_file($rrd_filename))
  {
    $rrd_options .= " DEF:".$mempool['mempool_id']."used=$rrd_filename:used:AVERAGE";
    $rrd_options .= " DEF:".$mempool['mempool_id']."free=$rrd_filename:free:AVERAGE";
    $rrd_options .= " CDEF:".$mempool['mempool_id']."size=".$mempool['mempool_id']."used,".$mempool['mempool_id']."free,+";
    $rrd_options .= " CDEF:".$mempool['mempool_id']."perc=".$mempool['mempool_id']."used,".$mempool['mempool_id']."size,/,100,*";
    $rrd_options .= " AREA:".$mempool['mempool_id']."perc#" . $colour . "05";
    $rrd_options .= " LINE1.25:".$mempool['mempool_id']."perc#" . $colour . ":'$descr'";
    $rrd_options .= " GPRINT:".$mempool['mempool_id']."size:LAST:%6.2lf%sB";
    $rrd_options .= " GPRINT:".$mempool['mempool_id']."used:LAST:%6.2lf%sB";
    $rrd_options .= " GPRINT:".$mempool['mempool_id']."perc:LAST:%5.2lf%%\\\l";
    $iter++;
  }
}

// EOF
