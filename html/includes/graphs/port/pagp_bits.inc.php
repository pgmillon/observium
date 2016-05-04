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

$i=0;
foreach (dbFetchRows("SELECT * FROM `ports` WHERE `device_id` = ? AND `pagpGroupIfIndex` = ?", array($port['device_id'], $port['ifIndex'])) as $int)
{
  $rrdfile = get_port_rrdfilename($int, NULL, TRUE);
  if (is_file($rrdfile))
  {
    $rrd_list[$i]['filename'] = $rrdfile;
    $rrd_list[$i]['descr'] = $int['ifDescr'];
    $i++;
  }
}

$units='bps';
$total_units='B';
$colours_in='greens';
$multiplier = "8";
$colours_out = 'blues';

$nototal = 1;
$ds_in  = "INOCTETS";
$ds_out = "OUTOCTETS";

include("includes/graphs/generic_multi_bits_separated.inc.php");

?>
