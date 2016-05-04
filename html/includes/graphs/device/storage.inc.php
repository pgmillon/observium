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

if($width > 500)
{
  $descr_len = 22;
} else {
  $descr_len = 12;
}
$descr_len += round(($width - 250) / 8);

$iter = 0;
$colours = 'mixed-10c';
$rrd_options .= " COMMENT:'".str_pad('Size      Used    %used', $descr_len+31, ' ', STR_PAD_LEFT)."\\l'";
//$rrd_options .= " COMMENT:'                    Size      Used    %age\\l'";

foreach (dbFetchRows("SELECT * FROM storage where device_id = ?", array($device['device_id'])) as $storage)
{
  if (!$config['graph_colours'][$colours][$iter]) { $iter = 0; }
  $colour=$config['graph_colours'][$colours][$iter];

  $descr = rrdtool_escape($storage['storage_descr'], $descr_len);
  $rrd = get_rrd_path($device, "storage-".$storage['storage_mib']."-".$storage['storage_descr'].".rrd");
  if (is_file($rrd))
  {
    $rrd_options .= " DEF:".$storage['storage_id']."used=$rrd:used:AVERAGE";
    $rrd_options .= " DEF:".$storage['storage_id']."free=$rrd:free:AVERAGE";
    $rrd_options .= " CDEF:".$storage['storage_id']."size=".$storage['storage_id']."used,".$storage['storage_id']."free,+";
    $rrd_options .= " CDEF:".$storage['storage_id']."perc=".$storage['storage_id']."used,".$storage['storage_id']."size,/,100,*";
    $rrd_options .= " LINE1.25:".$storage['storage_id']."perc#" . $colour . ":'$descr'";
    $rrd_options .= " GPRINT:".$storage['storage_id']."size:LAST:%6.2lf%sB";
    $rrd_options .= " GPRINT:".$storage['storage_id']."used:LAST:%6.2lf%sB";
    $rrd_options .= " GPRINT:".$storage['storage_id']."perc:LAST:%5.2lf%%\\\\l";
    $iter++;
  }
}

// EOF
