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

$i = 1;

foreach ($vars['id'] as $ifid)
{
  if (strstr($ifid, "!"))
  {
    $rrd_inverted[$i] = TRUE;
    $ifid = str_replace("!", "", $ifid);
  }

  $int = dbFetchRow("SELECT `ifIndex`, `hostname`, D.`device_id` FROM `ports` AS I, devices AS D WHERE I.port_id = ? AND I.device_id = D.device_id", array($ifid));
  $rrd_file = get_port_rrdfilename($int, NULL, TRUE);
  if (is_file($rrd_file))
  {
    $rrd_filenames[$i] = $rrd_file;
    $i++;
  }
}

$ds_in  = "INOCTETS";
$ds_out = "OUTOCTETS";

$colour_line_in = "006600";
$colour_line_out = "000099";
$colour_area_in = "91B13C";
$colour_area_out = "8080BD";

include("includes/graphs/generic_multi_data.inc.php");

// EOF
