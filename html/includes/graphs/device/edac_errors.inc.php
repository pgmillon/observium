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

$scale_min = 0;
$colours   = "mixed";
$unit_text = "Errors";
$nototal   = 1;

$i         = 0;

$colourset = "mixed";

$rownames  = unserialize(get_dev_attrib($device, 'edac_rownames'));

foreach ($rownames as $mc => $data)
{
  $data['rowname']['unknown']['unknown'] = 'unknown'; // Add 'unknown' line to every memory controller; we don't have unknown-unknown but unknown-all is added below

  foreach ($data['rowname'] as $row => $channels)
  {
    array_unshift($channels, 'all'); // Add 'all' to front of array
    foreach ($channels as $channel)
    {
      foreach (array('ue','ce') as $errortype)
      {
        switch ((string)$row) // Need to cast, or PHP thinks string 'unknown' means 0. Dafuq.
        {
          case 'unknown':
          case 'all':
            $row_id = $row;
            break;
          default:
            $row_id = "csrow$row";
            break;
        }

        $rrd_filename = get_rrd_path($device, "edac-errors-$mc-$row_id-$channel-$errortype.rrd");

        if (is_file($rrd_filename))
        {
          $rrd_list[$i]['filename'] = $rrd_filename;
          $rrd_list[$i]['descr'] = strtoupper($errortype) . " $mc $row_id" . ($channel != 'all' ? " $channel" : "");
          $rrd_list[$i]['ds'] = "errors";

          if (!isset($config['graph_colours'][$colourset][$iter])) { $iter = 0; }
          $rrd_list[$i]['colour'] = $config['graph_colours'][$colourset][$iter];
          $iter++;

          $i++;
        }
      }
    }
  }
}

include("includes/graphs/generic_multi_line.inc.php");

// EOF
