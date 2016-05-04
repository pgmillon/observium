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
foreach (dbFetchRows("SELECT * FROM `ports` AS I, `devices` AS D WHERE `port_descr_type` = 'cust' AND `port_descr_descr` = ? AND D.device_id = I.device_id", array($vars['id'])) as $port)
{
  $rrd_filename = get_port_rrdfilename($port, NULL, TRUE);
  if (is_file($rrd_filename))
  {
    $rrd_list[$i]['filename']  = $rrd_filename;
    $rrd_list[$i]['descr']     = $port['hostname'] ."-". $port['ifDescr'];
    $rrd_list[$i]['descr_in']  = short_hostname($port['hostname']);
    $rrd_list[$i]['descr_out'] = short_ifname($port['ifDescr'], NULL, FALSE); // Options sets for skip htmlentities
    $i++;
  }
}

$units ='b';
$total_units ='B';
$colours_in ='greens';
$multiplier = "8";
$colours_out = 'blues';

$nototal = 1;

$ds_in  = "INOCTETS";
$ds_out = "OUTOCTETS";

include("includes/graphs/generic_multi_bits_separated.inc.php");

// EOF
