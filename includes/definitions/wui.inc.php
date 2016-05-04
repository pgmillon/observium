<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// Definitions related to the Web UI

// WUI specific definitions, but can used in other code, like alert notifications

// Specific string for detect empty variable in web queries
define('OBS_VAR_UNSET', '[UNSET]');

// Default classes
define('OBS_CLASS_BOX',                'box box-solid');
define('OBS_CLASS_TABLE',              'table table-condensed');
// Combination of classes
define('OBS_CLASS_TABLE_BOX',          OBS_CLASS_BOX . ' ' . OBS_CLASS_TABLE);
define('OBS_CLASS_TABLE_STRIPED',      OBS_CLASS_TABLE . ' table-striped');
define('OBS_CLASS_TABLE_STRIPED_TWO',  OBS_CLASS_TABLE . ' table-striped-two');
define('OBS_CLASS_TABLE_STRIPED_MORE', OBS_CLASS_TABLE . ' table-condensed-more table-striped');

/* After this line keep only WUI specific definitions, not required in cli! */
//if (is_cli()) { return; }

// Refresh pages definitions
$config['wui']['refresh_times']       = array(0, 60, 120, 300, 900, 1800); // Allowed refresh times in seconds
// $vars array combination where auto-refresh page disabled by default
$config['wui']['refresh_disabled'][]  = array('page' => 'add_alert_check');
$config['wui']['refresh_disabled'][]  = array('page' => 'alert_check');
$config['wui']['refresh_disabled'][]  = array('page' => 'alert_regenerate');
$config['wui']['refresh_disabled'][]  = array('page' => 'alert_maintenance_add');
$config['wui']['refresh_disabled'][]  = array('page' => 'group_add');
$config['wui']['refresh_disabled'][]  = array('page' => 'groups_regenerate');
$config['wui']['refresh_disabled'][]  = array('page' => 'contact');
$config['wui']['refresh_disabled'][]  = array('page' => 'contacts');
$config['wui']['refresh_disabled'][]  = array('page' => 'bills', 'view' => 'add');
$config['wui']['refresh_disabled'][]  = array('page' => 'bill', 'view' => 'edit');
$config['wui']['refresh_disabled'][]  = array('page' => 'bill', 'view' => 'delete');
$config['wui']['refresh_disabled'][]  = array('page' => 'device', 'tab' => 'data');
$config['wui']['refresh_disabled'][]  = array('page' => 'device', 'tab' => 'edit');
$config['wui']['refresh_disabled'][]  = array('page' => 'device', 'tab' => 'port', 'view' => 'realtime');
$config['wui']['refresh_disabled'][]  = array('page' => 'device', 'tab' => 'showconfig');
$config['wui']['refresh_disabled'][]  = array('page' => 'device', 'tab' => 'entphysical'); // Inventory
$config['wui']['refresh_disabled'][]  = array('page' => 'addhost');
$config['wui']['refresh_disabled'][]  = array('page' => 'delhost');
$config['wui']['refresh_disabled'][]  = array('page' => 'delsrv');
$config['wui']['refresh_disabled'][]  = array('page' => 'deleted-ports');
$config['wui']['refresh_disabled'][]  = array('page' => 'adduser');
$config['wui']['refresh_disabled'][]  = array('page' => 'edituser');
$config['wui']['refresh_disabled'][]  = array('page' => 'settings');
$config['wui']['refresh_disabled'][]  = array('page' => 'preferences');
$config['wui']['refresh_disabled'][]  = array('page' => 'logout');
$config['wui']['refresh_disabled'][]  = array('page' => 'customoids');

// Search modules used by the ajax search, in order.
$config['wui']['search_modules'] = array('devices', 'ports', 'sensors', 'status', 'accesspoints', 'ip-addresses');

// Default groups list (on status page and default panel)
//$config['wui']['groups_list'] = array('device', 'port', 'processor', 'mempool', 'sensor', 'bgp_peer');
$config['wui']['groups_list'] = array('device', 'port', 'processor', 'mempool', 'sensor');

// Page configuration

$config['pages']['device']['custom_panel'] = TRUE;
$config['pages']['devices']['custom_panel'] = TRUE;
$config['pages']['ports']['custom_panel'] = TRUE;
$config['pages']['neighbours']['custom_panel'] = TRUE;

// EOF
