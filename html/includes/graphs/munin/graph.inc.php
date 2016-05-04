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

// Attempt to draw a graph out of DSes we've collected from Munin plugins.
// Reverse engineering ftw!

$scale_min = 0;

include_once($config['html_dir']."/includes/graphs/common.inc.php");

if($width > "500")
{
  $descr_len=24;
} else {
  $descr_len=14;
  $descr_len += round(($width - 230) / 8.2);
}

$mplug['mplug_vlabel'] = nicecase(str_replace('${graph_period}', 'sec', $mplug['mplug_vlabel']));

if($width > "500")
{
  $rrd_options .= " COMMENT:'".substr(str_pad($mplug['mplug_vlabel'], $descr_len),0,$descr_len)."   Current   Average  Maximum\l'";
  $rrd_options .= " COMMENT:'\l'";
} else {
  $rrd_options .= " COMMENT:'".substr(str_pad($mplug['mplug_vlabel'], $descr_len),0,$descr_len)."   Current   Average  Maximum\l'";
}

$i   = 0;
$c_i = 0;
$dbq = dbFetchRows("SELECT * FROM `munin_plugins_ds` WHERE `mplug_id` = ? ORDER BY ds_draw ASC, ds_label ASC", array($mplug['mplug_id']));

$plugfile = str_replace(".rrd", "", $plugfile);

foreach ($dbq as $ds)
{
  $ds_filename = $plugfile."_".$ds['ds_name'].".rrd";
  $ds_name = $ds['ds_name'];

  $cmd_def .= " DEF:".$ds['ds_name']."=".$ds_filename.":val:AVERAGE";

  if (!empty($ds['ds_cdef']))
  {
    $ds_name = $ds['ds_name']."_cdef";
    $cmd_cdef .= " CDEF:".$ds_name . "=". $ds['ds_cdef'] . " ";
  }

  if ($ds['ds_graph'] == "yes")
  {
    if (empty($ds['ds_colour']))
    {
      if (!$config['graph_colours']['mixed'][$c_i]) { $c_i = 0; }
      $colour=$config['graph_colours']['mixed'][$c_i]; $c_i++;
    } else {
      $colour = $ds['ds_colour'];
    }

    $descr      = rrdtool_escape($ds['ds_label'], $descr_len);

    if ($ds['ds_draw'] == "AREASTACK")
    {
      if ($i==0) {$ds['ds_draw'] = "AREA";}
      else $ds['ds_draw'] = "STACK";
    }
    elseif (preg_match("/^LINESTACK([0-9\.]*)/", $ds['ds_draw'], $m))
    {
      if ($i==0) {$data['ds_draw'] = "LINE$m[1]";}
      else $ds['ds_draw'] = "STACK";
    }
    $cmd_graph .= ' '.$ds['ds_draw'].':'.$ds_name.'#'.$colour.':"'.$descr.'"';
    $cmd_graph .= ' GPRINT:'.$ds_name.':LAST:"%6.2lf%s"';
    $cmd_graph .= ' GPRINT:'.$ds_name.':AVERAGE:"%6.2lf%s"';
    $cmd_graph .= ' GPRINT:'.$ds_name.':MAX:"%6.2lf%s\\n"';

  }

}

$rrd_options .= $cmd_def . $cmd_cdef . $cmd_graph;

// EOF
