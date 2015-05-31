<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage web
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// This function print page refresh meta header,
// and return status for current page with current, list and allowed refresh array.
// Uses variables $vars['refresh'], $_SESSION['refresh'], $config['page_refresh']
function print_refresh($vars)
{
  $refresh_array = array(0, 60, 120, 300, 900, 1800); // Allowed refresh times
  $refresh_time  = 300;                           // Default page reload time (5 minutes)
  if (isset($vars['refresh']))
  {
    if (is_numeric($vars['refresh']) && in_array($vars['refresh'], $refresh_array))
    {
      $refresh_time = (int)$vars['refresh'];
      // Store for SESSION
      $_SESSION['page_refresh'] = $refresh_time;
    }
    // Unset refresh var after
    unset($GLOBALS['vars']['refresh']);
  }
  else if (isset($_SESSION['page_refresh']) && in_array($_SESSION['page_refresh'], $refresh_array))
  {
    $refresh_time = (int)$_SESSION['page_refresh'];
  }
  else if (is_numeric($GLOBALS['config']['page_refresh']) && in_array($GLOBALS['config']['page_refresh'], $refresh_array))
  {
    $refresh_time = (int)$GLOBALS['config']['page_refresh'];
  }

  // List vars where page refresh full disabled
  $refresh_disabled = array(
    array('page' => 'add_alert_check'),
    array('page' => 'alert_check'),
    array('page' => 'alert_regenerate'),
    array('page' => 'group_add'),
    array('page' => 'groups_regenerate'),
    array('page' => 'device', 'tab' => 'edit'),
    array('page' => 'device', 'tab' => 'port', 'view' => 'realtime'),
    array('page' => 'device', 'tab' => 'showconfig'),
    array('page' => 'addhost'),
    array('page' => 'delhost'),
    array('page' => 'adduser'),
    array('page' => 'edituser'),
    array('page' => 'settings'),
    array('page' => 'preferences'),
    array('page' => 'logout'),
  );

  $refresh_allowed = TRUE;
  foreach ($refresh_disabled as $var_test)
  {
    $var_count = count($var_test);
    foreach ($var_test as $key => $value)
    {
      if (isset($vars[$key]) && $vars[$key] == $value) { $var_count--; }
    }
    if ($var_count === 0)
    {
      $refresh_allowed = FALSE;
      break;
    }
  }

  $return = array('allowed' => $refresh_allowed,
                  'current' => $refresh_time,
                  'list'    => $refresh_array);

  if ($refresh_allowed && $refresh_time)
  {
    echo('  <meta http-equiv="refresh" content="'.$refresh_time.'" />' . "\n");
    $return['nexttime'] = time() + $refresh_time; // Add unixtime for next refresh
  }

  return $return;
}

// EOF
