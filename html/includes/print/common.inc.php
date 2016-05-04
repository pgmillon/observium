<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage web
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

/**
 * Print refresh meta header
 * 
 * This function print refresh meta header and return status for current page
 * with refresh time, list and allowed refresh times.
 * Uses variables $vars['refresh'], $_SESSION['refresh'], $config['page_refresh']
 *
 * @global string $GLOBALS['config']['page_refresh']
 * @global string $_SESSION['refresh']
 * @param array $vars
 * @return array $return
 */
function print_refresh($vars)
{
  if (!$_SESSION['authenticated'])
  {
    // Do not print refresh header if not authenticated session, common use in logon page
    return array('allowed' => FALSE);
  }

  $refresh_array = array(0, 60, 120, 300, 900, 1800); // Allowed refresh times
  $refresh_time  = 300;                               // Default page reload time (5 minutes)
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
    array('page' => 'delsrv'),
    array('page' => 'deleted-ports'),
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

/**
 * Helper function for generate table header with sort links
 * This used in other print_* functions
 *
 * @param array $cols Array with column IDs, names and column styles
 * @param array $vars Array with current selected column ID and/or variables for generate column link
 * @return string $string
 */
function get_table_header($cols, $vars = array())
{
  $string  = '  <thead>' . PHP_EOL;
  $string .= '    <tr>' . PHP_EOL;
  foreach ($cols as $id => $col)
  {
    if (is_array($col))
    {
      $name  = $col[0];
      $style = ' '.$col[1]; // Column styles/classes
    } else {
      $name  = $col;
      $style = '';
    }
    $string .= '      <th'.$style.'>';
    if ($name == NULL)          { $string .= ''; }         // Column without Name and without Sort
    else if (is_int($id))       { $string .= $name; }      // Column without Sort
    else if ($vars)
    {
      if ($vars['sort'] == $id) { $string .= $name.' *'; } // Column without Sort (selected)
      else                      { $string .= '<a href="'. generate_url($vars, array('sort' => $id)).'">'.$name.'</a>'; } // Column without Sort
    } else {
      $string .= $name; // Sorting is not available (if vars empty or FALSE)
    }
    $string .= '</th>' . PHP_EOL;
  }
  $string .= '    </tr>' . PHP_EOL;
  $string .= '  </thead>' . PHP_EOL;

  return $string;
}

// EOF
