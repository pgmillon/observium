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

// Generate a list of ports and then call the multi_bits grapher to generate from the list

$ds_in  = "INOCTETS";
$ds_out = "OUTOCTETS";

$i=1;
foreach ($devices as $device)
{
 foreach (dbFetchRows("SELECT * FROM `ports` WHERE `device_id` = ?", array($device['device_id'])) as $int)
 {
  $ignore = 0;
  if (is_array($config['device_traffic_iftype']))
  {
    foreach ($config['device_traffic_iftype'] as $iftype)
    {
      if (preg_match($iftype ."i", $int['ifType']))
      {
        $ignore = 1;
      }
    }
  }
  if (is_array($config['device_traffic_descr']))
  {
    foreach ($config['device_traffic_descr'] as $ifdescr)
    {
      if (preg_match($ifdescr."i", $int['ifDescr']) || preg_match($ifdescr."i", $int['ifName']) || preg_match($ifdescr."i", $int['portName']))
      {
        $ignore = 1;
      }
    }
  }

  $rrdfile = get_port_rrdfilename($int, NULL, TRUE);
  if (is_file($rrdfile) && ($ignore != 1))
  {
    $rrd_list[$i]['filename'] = $rrdfile;
    $rrd_list[$i]['descr']    = short_ifname($port['port_label'], NULL, FALSE); // Options sets for skip htmlentities
    $rrd_list[$i]['descr_in'] = $device['hostname'];
    $rrd_list[$i]['descr_out'] = $port['ifAlias'];
    $rrd_list[$i]['ds_in'] = $ds_in;
    $rrd_list[$i]['ds_out'] = $ds_out;
    $i++;
  }
  unset($ignore);
 }
}

$units ='b';
$total_units ='B';
$colours_in ='greens';
$multiplier = "8";
$colours_out = 'blues';

$nototal = 1;

$graph_title .= "::bits";

$colour_line_in = "006600";
$colour_line_out = "000099";
$colour_area_in = "CDEB8B";
$colour_area_out = "C3D9FF";

include("includes/graphs/generic_multi_bits_separated.inc.php");

?>
