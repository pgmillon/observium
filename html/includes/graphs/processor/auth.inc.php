<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

$proc = dbFetchRow("SELECT * FROM `processors` where `processor_id` = ?", array($vars['id']));

if (is_numeric($proc['device_id']) && ($auth || device_permitted($proc['device_id'])))
{
  $device = device_by_id_cache($proc['device_id']);
  $rrd_filename  = get_rrd_path($device, "processor-" . $proc['processor_type'] . "-" . $proc['processor_index'] . ".rrd");
  $title  = generate_device_link($device);
  $title .= " :: Processor :: " . htmlentities($proc['processor_descr']);
  $auth = TRUE;
}

?>
