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

// FIXME -- bit derpy, maybe replace this.

include_once($config['html_dir']."/includes/graphs/common.inc.php");

$scale_min = "0";
$scale_max = "100";

if ($width > 500)
{
  $descr_len = 22;
} else {
  $descr_len = 12;
}
$descr_len += round(($width - 250) / 8);

$iter = 0;
$colours = 'mixed';

$rrd_options .= " COMMENT:'".str_pad('Size      Used    %used', $descr_len+31, ' ', STR_PAD_LEFT)."\\\l'";


foreach ($vars['id'] as $storage_id)
{

  $storage = dbFetchRow("SELECT * FROM `storage` WHERE `storage_id` = ?", array($storage_id));
  $device = device_by_id_cache($storage['device_id']);
  $rrd_filename = get_rrd_path($device, "storage-".$storage['storage_mib']."-".$storage['storage_descr'].".rrd");

  if (!$config['graph_colours'][$colours][$iter]) { $iter = 0; }
  $colour=$config['graph_colours'][$colours][$iter];

  $descr = rrdtool_escape(rewrite_hrDevice($storage['storage_descr']), $descr_len);
  if (isset($storage['storage_type'])) { $storage['storage_mib'] = $storage['storage_type']; }

  if (is_file($rrd_filename))
  {
    $rrd_options .= " DEF:".$storage['storage_id']."used=$rrd_filename:used:AVERAGE";
    $rrd_options .= " DEF:".$storage['storage_id']."free=$rrd_filename:free:AVERAGE";
    $rrd_options .= " CDEF:".$storage['storage_id']."size=".$storage['storage_id']."used,".$storage['storage_id']."free,+";
    $rrd_options .= " CDEF:".$storage['storage_id']."perc=".$storage['storage_id']."used,".$storage['storage_id']."size,/,100,*";
    $rrd_options .= " AREA:".$storage['storage_id']."perc#" . $colour . "10";
    $rrd_options .= " LINE1.25:".$storage['storage_id']."perc#" . $colour . ":'$descr'";
    $rrd_options .= " GPRINT:".$storage['storage_id']."size:LAST:%6.2lf%sB";
    $rrd_options .= " GPRINT:".$storage['storage_id']."used:LAST:%6.2lf%sB";
    $rrd_options .= " GPRINT:".$storage['storage_id']."perc:LAST:%5.2lf%%\\\l";
    $iter++;
  } else { echo($rrd_filename); }
}

// EOF

