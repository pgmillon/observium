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

$i = 0;

foreach ($ports as $port)
{
  // $device and $port are retrieved from the same query
  $rrdfile = get_port_rrdfilename($port, NULL, TRUE);

  if (is_file($rrdfile))
  {
    $rrd_list[$i]['filename'] = $rrdfile;
    $rrd_list[$i]['descr'] = $port['hostname'];
    $rrd_list[$i]['descr_out'] = $port['ifDescr'];
    if (isset($port['ifAlias']) && $port['ifAlias'] != $port['ifDescr'])
    {
      $rrd_list[$i]['descr_out'] .= ' ('.$port['ifAlias'].')';
    }
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

#print_vars($rates);

if($bill['bill_type'] == "cdr") {
   $custom_graph = " COMMENT:'\\r' ";
   $custom_graph .= " HRULE:" . $rates['rate_95th'] . "#cc0000:'95th %ile \: ".formatRates($rates['rate_95th'])." (".$rates['dir_95th'].") (CDR\: ".formatRates($bill['bill_cdr']).")'";
   $custom_graph .= " HRULE:" . $rates['rate_95th'] * -1 . "#cc0000";
} elseif($bill['bill_type'] == "quota") {
   $custom_graph = " COMMENT:'\\r' ";
   $custom_graph .= " HRULE:" . $rates['rate_average'] . "#cc0000:'Usage \: ".format_bytes_billing($rates['total_data'])." (".formatRates($rates['rate_average']).")'";
   $custom_graph .= " HRULE:" . $rates['rate_average'] * -1 . "#cc0000";
}

include("includes/graphs/generic_multi_bits_separated.inc.php");

?>
