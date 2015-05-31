<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// Generate a list of ports and then call the multi_bits grapher to generate from the list

$ds_in  = "INOCTETS";
$ds_out = "OUTOCTETS";

$graph_return = array('descr' => 'Device total traffic in bits/sec.');

foreach (dbFetchRows("SELECT * FROM `ports` WHERE `device_id` = ?", array($device['device_id'])) as $port)
{
  $ignore = 0;
  if (is_array($config['device_traffic_iftype']))
  {
    foreach ($config['device_traffic_iftype'] as $iftype)
    {
      if (preg_match($iftype ."i", $port['ifType']))
      {
        $ignore = 1;
      }
    }
  }
  if (is_array($config['device_traffic_descr']))
  {
    foreach ($config['device_traffic_descr'] as $ifdescr)
    {
      if (preg_match($ifdescr."i", $port['ifDescr']))
      {
        if ($debug) { echo("[".$port['ifIndex'].":ifDescr ignored]"); }
        $ignore = 1;
      } elseif (preg_match($ifdescr."i", $port['ifName']))
      {
        if ($debug) { echo("[".$port['ifIndex'].":ifName ignored(".$ifdescr."||".$port['ifName'].")]"); }
        $ignore = 1;
      } elseif (preg_match($ifdescr."i", $port['portName']))
      {
        if ($debug) { echo("[".$port['ifIndex'].":portName ignored]"); }
        $ignore = 1;
      }

    }
  }

  $rrd_filename = get_port_rrdfilename($port, NULL, TRUE);
  if ($ignore != 1 && is_file($rrd_filename))
  {
    humanize_port($port);   // Fix Labels! ARGH. This needs to be in the bloody database!

    $rrd_filenames[] = $rrd_filename;
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr']    = short_ifname($port['label'], NULL, FALSE); // Options sets for skip htmlentities
    $rrd_list[$i]['descr_in'] = short_ifname($port['label'], NULL, FALSE); // Options sets for skip htmlentities
    $rrd_list[$i]['descr_out'] = $port['ifAlias'];
    $rrd_list[$i]['ds_in'] = $ds_in;
    $rrd_list[$i]['ds_out'] = $ds_out;
    $i++;
  }

  unset($ignore);
}

$units ='b';
$total_units ='B';
$colours_in ='greens';
$multiplier = "8";
$colours_out = 'blues';

#$nototal = 1;

$ds_in  = "INOCTETS";
$ds_out = "OUTOCTETS";

$graph_title .= "::bits";

$colour_line_in = "006600";
$colour_line_out = "000099";
$colour_area_in = "91B13C";
$colour_area_out = "8080BD";

include("includes/graphs/generic_multi_separated.inc.php");

#include("includes/graphs/generic_multi_bits_separated.inc.php");
#include("includes/graphs/generic_multi_data_separated.inc.php");

?>
