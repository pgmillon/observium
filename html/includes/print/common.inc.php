<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage web
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
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

  $refresh_array = $GLOBALS['config']['wui']['refresh_times']; // Allowed refresh times
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

  // List vars where page refresh full disabled - FIXME move to definitions!
  $refresh_disabled = $GLOBALS['config']['wui']['refresh_disabled'];

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
function get_table_header($cols, &$vars = array())
{
  // Always clean sort vars
  $sort       = $vars['sort'];
  $sort_order = strtolower($vars['sort_order']);
  if (!in_array($sort_order, array('asc', 'desc', 'reset')))
  {
    $sort_order = 'acs';
  }
  unset($vars['sort'], $vars['sort_order']);

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
    if ($name == NULL)
    {
      $string .= '';         // Column without Name and without Sort
    }
    else if (is_int($id) || stristr($id, "!") != FALSE)
    {
      $string .= $name;      // Column without Sort
    }
    else if ($vars || $sort)
    {
      // Sort order cycle: asc -> desc -> reset
      if ($sort == $id)
      {
        switch ($sort_order)
        {
          case 'desc':
            $name .= '&nbsp;&uarr;';
            $sort_array = array();
            //$vars['sort_order'] = 'reset';
            break;
          case 'reset':
            //unset($vars['sort'], $vars['sort_order']);
            $sort_array = array();
            break;
          default:
            // ASC
            $name .= '&nbsp;&darr;';
            $sort_array = array('sort' => $id, 'sort_order' => 'desc');
            //$vars['sort_order'] = 'desc';
        }
      } else{
        $sort_array = array('sort' => $id);
      }
      $string .= '<a href="'. generate_url($vars, $sort_array).'">'.$name.'</a>'; // Column now sorted (selected)
    } else {
      $string .= $name;      // Sorting is not available (if vars empty or FALSE)
    }
    $string .= '</th>' . PHP_EOL;
  }
  $string .= '    </tr>' . PHP_EOL;
  $string .= '  </thead>' . PHP_EOL;

  return $string;
}

function print_error_permission($text = NULL, $escape = TRUE)
{
  if (empty($text))
  {
    $text = 'You have insufficient permissions to view this page.';
  }
  else if ($escape)
  {
    $text = escape_html($text);
  }
  echo('<div style="margin:auto; text-align: center; margin-top: 50px; max-width:600px">');
  print_error('<h4>Permission error</h4>' . PHP_EOL . $text);
  echo('</div>');
}

// EOF
