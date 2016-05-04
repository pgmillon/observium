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

// FIXME : bit hacky, I think.

if($vars['id'] == OBS_VAR_UNSET) { $vars['id'] = ''; }

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
