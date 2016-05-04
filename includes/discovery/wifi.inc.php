<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

echo("Wifi: ");

// Build array of radios in the database

foreach (dbFetchRows("SELECT * FROM `wifi_radios` WHERE `device_id` = ?", array($device['device_id'])) as $radio)
{
  $GLOBALS['cache']['wifi_radios'][$radio['radio_ap']][$radio['radio_number']] = $radio;
}

foreach (dbFetchRows("SELECT * FROM `wifi_wlans` WHERE `device_id` = ?", array($device['device_id'])) as $wlan)
{
  $GLOBALS['cache']['wifi_wlans'][$wlan['wlan_index']] = $wlan;
}

// Include all discovery modules

$include_dir = 'includes/discovery/wifi';
include($config['install_dir'] . '/includes/include-dir-mib.inc.php');

// Remove non-valid things

// FIXME - Actually write this code :)

echo(PHP_EOL);

//EOF
