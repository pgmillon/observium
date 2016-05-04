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

$oids = array('TotMiss', 'TotHits');

$i = 0;

foreach ($oids as $oid)
{
  $oid_ds = truncate($oid, 19, '');
  $rrd_list[$i]['filename'] = $rrd_filename;
  $rrd_list[$i]['descr'] = $oid;
  $rrd_list[$i]['ds'] = $oid_ds;
  $i++;
}

$colours   = "mixed";
$nototal   = 1;
$unit_text = "";
$simple_rrd = 1;

include("includes/graphs/generic_multi_simplex_separated.inc.php");

?>
