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

if (!is_array($vars['id'])) { $vars['id'] = array($vars['id']); }
if (!is_array($vars['idb'])) { $vars['idb'] = array($vars['idb']); }

$i = 0;

$groups[0]['ports']       =  $vars['id'];
$groups[0]['colours_in']  = 'oranges';
$groups[0]['colours_out'] = 'red2';

$groups[1]['ports']       = $vars['idb'];
$groups[1]['colours_in']  = 'greens';
$groups[1]['colours_out'] = 'blues';

foreach ($groups as $group_id => $group)
{
  $iter=0;
  foreach ($group['ports'] as $port_id)
  {
    $port = dbFetchRow("SELECT * FROM `ports` AS I, devices as D WHERE I.port_id = ? AND I.device_id = D.device_id", array($port_id));
    $rrdfile = get_port_rrdfilename($port, NULL, TRUE);
    if (is_file($rrdfile))
    {
      humanize_port($port);
      $rrd_list[$i]['filename']    = $rrdfile;
      $rrd_list[$i]['descr']       = $port['hostname'] . " " . $port['ifDescr'];
      $rrd_list[$i]['descr_in']    = $port['hostname'];
      $rrd_list[$i]['descr_out']   = $port['port_label_short'];

      if (!$config['graph_colours'][$group['colours_in']][$iter] || !$config['graph_colours'][$group['colours_out']][$iter]) { $iter = 0; }
      $rrd_list[$i]['colour_in']  = $config['graph_colours'][$group['colours_in']][$iter];
      $rrd_list[$i]['colour_out'] = $config['graph_colours'][$group['colours_out']][$iter];
      $i++; $iter++;

    }
  }
}

#echo("<pre>");
#print_vars($rrd_list);
#echo("</pre>");

$units = 'bps';
$total_units='B';
$multiplier = "8";

#$nototal = 1;

$ds_in  = "INOCTETS";
$ds_out = "OUTOCTETS";

include("includes/graphs/generic_multi_bits_separated.inc.php");

// EOF
