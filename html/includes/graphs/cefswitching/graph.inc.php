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

// Cycle through dot3stats OIDs and build list of RRAs to pass to multi simplex grapher

$oids = array('drop', 'punt', 'hostpunt');
$i = 0;

if (is_file($rrd_filename))
{
  foreach ($oids as $oid)
  {
    $oid = str_replace("dot3Stats", "", $oid);
    $oid_ds = truncate($oid, 19, '');
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $oid;
    $rrd_list[$i]['ds'] = $oid_ds;
    $i++;
  }

  $colours   = "mixed";
  $nototal   = 1;
  $unit_text = "Errors";

  include("includes/graphs/generic_multi_simplex_separated.inc.php");
} else {
  graph_error($type.'_'.$subtype); // Graph Template Missing;
}

// EOF
