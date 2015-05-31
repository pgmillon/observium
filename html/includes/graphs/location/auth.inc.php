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

foreach (dbFetchRows("SELECT * FROM `devices` WHERE `location` = ?", array($vars['id'])) as $device)
{
  if ($auth || device_permitted($device_id))
  {
    $devices[] = $device;
    $title = $vars['id'];
    $auth = TRUE;
  }
}

?>
