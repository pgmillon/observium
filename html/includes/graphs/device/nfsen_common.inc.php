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

$simple_rrd = TRUE;

$rrd_filename = get_nfsen_filename($device['hostname']);

if ($rrd_filename)
{
  $flowtypes = array('tcp', 'udp', 'icmp', 'other');

  $rrd_list=array();
  $nfsen_iter=1;
  foreach ($flowtypes as $flowtype)
  {

    $rrd_list[$nfsen_iter]['filename'] = $rrd_filename;
    $rrd_list[$nfsen_iter]['descr'] = $flowtype;
    $rrd_list[$nfsen_iter]['ds'] = $dsprefix . $flowtype;

    # set a multiplier which in turn will create a CDEF if this var is set
    if ($dsprefix == "traffic_") { $multiplier = "8"; }

    $colours   = "blues";
    $nototal   = 0;
    $units="";
    $unit_text = $dsdescr;
    $scale_min = "0";

    if ($_GET['debug']) { print_vars($rrd_list); }
    $nfsen_iter++;
  }
}

include("includes/graphs/generic_multi_simplex_separated.inc.php");

?>
