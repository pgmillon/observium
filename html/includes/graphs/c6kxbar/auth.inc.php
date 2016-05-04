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

include("includes/graphs/device/auth.inc.php");

if ($auth && is_numeric($_GET['mod']) && is_numeric($_GET['chan']))
{

  $entity = dbFetchRow("SELECT * FROM entPhysical WHERE device_id = ? AND entPhysicalIndex = ?", array($device['device_id'], $_GET['mod']));

  $title .= " :: ".$entity['entPhysicalName'];
  $title .= " :: Fabric ".$_GET['chan'];

  $graph_title = short_hostname($device['hostname']) . "::" . $entity['entPhysicalName']. "::Fabric".$_GET['chan'];

  $rrd_filename = get_rrd_path($device, "c6kxbar-" . $_GET['mod']. "-".$_GET['chan']. ".rrd");
}

?>
