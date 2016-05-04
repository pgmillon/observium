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

// DOCME needs phpdoc block
function graph_from_definition($vars, $type, $subtype, $device)
{

  global $config, $graph_defs;

  $graph_def = $graph_defs[$type][$subtype];

include_once($config['html_dir']."/includes/graphs/common.inc.php");

// Here we scale the number of numerical columns shown to make sure we keep the text.

if ($width > 600) {
  $data_show = array('lst', 'avg', 'min', 'max', 'tot');
} elseif ($width > 400) {
  $data_show = array('lst', 'avg', 'max', 'tot');
} elseif ($width > 300) {
  $data_show = array('lst', 'avg', 'max', 'tot');
} else {
  $data_show = array('lst', 'avg', 'max');
}

// Drop total from view if requested not to show
if ($args['nototal'] || $nototal)
{
  if (($key = array_search('tot', $data_show)) !== FALSE)
  {
    unset($data_show[$key]);
  }
}

$data_len = count($data_show) * 8;

// Here we scale the length of the description to make sure we keep the numbers

if ($width > 600) {
  $descr_len = 40;
} elseif ($width > 300) {
  $descr_len = floor(($width + 42) / 8) - $data_len;
} else {
  $descr_len = floor(($width + 42) / 7) - $data_len;
}

// Build the legend headers using the length values previously calculated

if (!isset($unit_text))
{
  if ($format == "octets" || $format == "bytes")
  {
    $units = "Bps";
    $format = "bytes";
    $unit_text = "Bytes/s";
  } else {
    $units = "bps";
    $format = "bits";
    $unit_text = "Bits/s";
  }
}

if ($legend != 'no')
{
  $rrd_options .= " COMMENT:'".rrdtool_escape($unit_text, $descr_len)."'";
  if (in_array("lst", $data_show)) { $rrd_options .= " COMMENT:'   Now'"; }
  if (in_array("avg", $data_show)) { $rrd_options .= " COMMENT:'    Avg'"; }
  if (in_array("min", $data_show)) { $rrd_options .= " COMMENT:'    Min'"; }
  if (in_array("max", $data_show)) { $rrd_options .= " COMMENT:'    Max'"; }
  if (in_array("tot", $data_show)) { $rrd_options .= " COMMENT:'  Total'"; }
  $rrd_options .= " COMMENT:'\\l'";
}

  foreach ($graph_def['ds'] as $ds_name => $ds)
  {

    if (!isset($ds['file'])) {  $ds['file'] = $graph_def['file']; }
    if (!isset($ds['draw'])) {  $ds['draw'] = "LINE1.5"; }
    $ds['file'] = get_rrd_path($device, $ds['file']);

    $cmd_def .= " DEF:".$ds_name."=".$ds['file'].":".$ds_name.":AVERAGE";
    $cmd_def .= " DEF:".$ds_name."_min=".$ds['file'].":".$ds_name.":MIN";
    $cmd_def .= " DEF:".$ds_name."_max=".$ds['file'].":".$ds_name.":MAX";

    if (!empty($ds['cdef']))
    {
      $ds_name = $ds_name."_c";
      $cmd_cdef .= " CDEF:".$ds_name . "=". $ds['cdef'] . "";
      $cmd_cdef .= " CDEF:".$ds_name . "_min=". $ds['cdef'] . "";
      $cmd_cdef .= " CDEF:".$ds_name . "_max=". $ds['cdef'] . "";
    }

    if ($ds['ds_graph'] != "yes")
    {
      if (empty($ds['colour']))
      {
        if (!$config['graph_colours'][$graph_def['colours']][$c_i]) { $c_i = 0; }
        $colour=$config['graph_colours'][$graph_def['colours']][$c_i]; $c_i++;
      } else {
        $colour = $ds['colour'];
      }

      $descr      = rrdtool_escape($ds['label'], $descr_len);

      if ($ds['draw'] == "AREASTACK")
      {
        if ($i==0) {$ds['ds_draw'] = "AREA";}
        else $ds['ds_draw'] = "STACK";
      }
      elseif (preg_match("/^LINESTACK([0-9\.]*)/", $ds['ds_draw'], $m))
      {
        if ($i==0) {$data['ds_draw'] = "LINE$m[1]";}
        else $ds['draw'] = "STACK";
      }

      $cmd_graph .= ' '.$ds['draw'].':'.$ds_name.'#'.$colour.':"'.$descr.'"';
      $cmd_graph .= ' GPRINT:'.$ds_name.':LAST:"%6.2lf%s"';
      $cmd_graph .= ' GPRINT:'.$ds_name.':AVERAGE:"%6.2lf%s"';
      $cmd_graph .= ' GPRINT:'.$ds_name.':MAX:"%6.2lf%s\\n"';

    }

  }

  $rrd_options = $cmd_def . $cmd_cdef . $cmd_graph;

  return $rrd_options;
}

// DOCME needs phpdoc block
function graph_error($string)
{
  global $vars, $config, $graphfile;

  $vars['bg'] = "FFBBBB";

  include($config['html_dir']."/includes/graphs/common.inc.php");

  $rrd_options .= " HRULE:0#555555";
  $rrd_options .= " --title='".$string."'";
  $rrd_options = preg_replace('/ --(start|end)(\s+\d+)?/', '', $rrd_options); // Remove start/end from error graph

  if ($height > 99)
  {
    rrdtool_graph($graphfile, $rrd_options);
    //$woo = shell_exec($rrd_cmd);
    //if (OBS_DEBUG) { echo("<pre>".$rrd_cmd."</pre>"); }
    if (is_file($graphfile))
    {
      if (!OBS_DEBUG)
      {
        header('Content-type: image/png');
        header('Content-Length: ' . filesize($graphfile));
        header('Content-Disposition: inline; filename="'.basename($graphfile).'"');
        $fd = fopen($graphfile, 'r');
        fpassthru($fd);
        fclose($fd);
      } else {
        echo('<img src="'.data_uri($graphfile, 'image/png').'" alt="graph" />');
      }
      unlink($graphfile);
#      exit();
    }
  } else {
    if (!OBS_DEBUG)
    {
      $im     = imagecreate($width, $height);
      $orange = imagecolorallocate($im, 255, 225, 225);
      $px     = (imagesx($im) - 7.5 * strlen($string)) / 2;
      imagestring($im, 3, $px, $height / 2 - 8, $string, imagecolorallocate($im, 128, 0, 0));
      header('Content-type: image/png');
      imagepng($im);
      imagedestroy($im);
    }
#    exit();
  }
}

// EOF
