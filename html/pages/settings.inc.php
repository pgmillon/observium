<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

if ($_SESSION['userlevel'] < 10)
{
  print_error_permission();
  return;
}

$page_title[] = "Settings";

/**
 * Convert amqp|conn|host into returning value of $arrayvar['amqp']['conn']['host']
 *
 * @param string $sqlname Variable name
 * @param array $arrayvar Array where to see param
 * @param Boolean $try_isset If True, return isset($sqlname) check, else return variable content
 * @return mixed
 */
function sql_to_array($sqlname, $arrayvar, $try_isset = TRUE)
{
  list($key, $pop_sqlname) = explode('|', $sqlname, 2);

  $isset = isset($arrayvar[$key]);
  if ($pop_sqlname === NULL)
  {
    // Reached the variable, return its content, or FALSE if it's not set
    if ($try_isset)
    {
      return $isset;
    } else {
      return ($isset ? $arrayvar[$key] : NULL);
    }
  }
  else if ($isset)
  {
    // Recurse to lower level
    return sql_to_array($pop_sqlname, $arrayvar[$key], $try_isset);
  }
  return FALSE;
}

$navbar['brand'] = "Settings";
$navbar['class'] = "navbar-narrow";

$formats = array('default' => 'Configuration',
                 'changed_config' => 'Changed Configuration',
                 'config' => 'Dump of Configuration');

if (isset($vars['format']) && $vars['format'] != 'default' && is_file($config['html_dir'] . '/pages/settings/'.$vars['format'].'.inc.php'))
{
  include($config['html_dir'] . '/pages/settings/'.$vars['format'].'.inc.php');

  return;
}

  print_warning("<strong>Experimental Feature!</strong> If you are uncomfortable using experimental code, please continue using config.php to configure Observium.");

  // Load config variable descriptions into memory
  include($config['install_dir'] . "/includes/config-variables.inc.php");

  // Loop all variables and build an array with sections, subsections and variables
  // This is only done on this page, so there is no performance issue for the rest of Observium
  foreach ($config_variable as $varname => $variable)
  {
    $config_subsections[$variable['section']][$variable['subsection']][$varname] = $variable;
  }

  if ($vars['submit'] == 'save')
  {
    //r($vars);
    $updates = 0;
    $deletes = array();
    $sets = array();
    $errors = array();

    // Submit button pressed
    foreach ($vars as $varname => $value)
    {
      if (substr($varname, 0, 7) == 'varset_')
      {
        $varname = substr($varname, 7);
        $sqlname = str_replace('__', '|', $varname);
        $content = $vars[$varname];
        $confname = '$config[\'' . implode("']['",explode('|',$sqlname)) . '\']';
        $section = $config_variable[$sqlname]['section'];

        if ($vars[$varname . '_custom'])
        {
          $ok = FALSE;

          if (isset($config_variable[$sqlname]['edition']) && $config_variable[$sqlname]['edition'] != OBSERVIUM_EDITION)
          {
            // Skip variables not allowed for current Observium edition
            continue;
          }
          else if (isset($config_sections[$section]['edition']) && $config_sections[$section]['edition'] != OBSERVIUM_EDITION)
          {
            // Skip sections not allowed for current Observium edition
            continue;
          }

          // Split enum|foo|bar into enum  foo|bar
          list($vartype, $varparams) = explode('|', $config_variable[$sqlname]['type'], 2);
          $params = array();

          // If a callback function is defined, use this to fill params.
          if ($config_variable[$sqlname]['params_call'] && function_exists($config_variable[$sqlname]['params_call']))
          {
            $params = call_user_func($config_variable[$sqlname]['params_call']);
          // Else if the params are defined directly, use these.
          } else if (is_array($config_variable[$sqlname]['params']))
          {
            $params = $config_variable[$sqlname]['params'];
          }
          // Else use parameters specified in variable type (e.g. enum|1|2|5|10)
          else if (!empty($varparams))
          {
            foreach (explode('|', $varparams) as $param)
            {
              $params[$param] = array('name' => nicecase($param));
            }
          }

          switch ($vartype)
          {
            case 'int':
            case 'float':
              if (is_numeric($content))
              {
                $ok = TRUE;
              } else {
                $errors[] = $config_variable[$sqlname]['name'] . " ($confname) should be of <strong>numeric</strong> type. Setting '" . escape_html($content) . "' ignored.";
              }
              break;
            case 'bool':
              switch ($content)
              {
                case 'on':
                case '1':
                  $content = 1;
                  $ok = TRUE;
                  break;
                case 'off': // Won't actually happen. When "unchecked" the field is simply not transmitted...
                case '0':
                case '':    // ... which we catch here.
                  $content = 0;
                  $ok = TRUE;
                  break;
                default:
                  $ok = FALSE;
                  $errors[] = $config_variable[$sqlname]['name'] . " ($confname) should be of type <strong>bool</strong>. Setting '" . escape_html($content) . "' ignored.";
              }
              break;
            case 'enum':
              if (!in_array($content, array_keys($params)))
              {
                $ok = FALSE;
                $errors[] = $config_variable[$sqlname]['name'] . " ($confname) should be one of <strong>" . implode(', ', $params) . "</strong>. Setting '" . escape_html($content) . "' ignored.";
              } else {
                $ok = TRUE;
              }
              break;
            case 'enum-array':
              //r($content);
              //r($params);
              foreach ($content as $value)
              {
                // Check all values
                if (!in_array($value, array_keys($params)))
                {
                  $ok = FALSE;
                  $errors[] = $config_variable[$sqlname]['name'] . " ($confname) all values should be one of this list <strong>" . implode(', ', $params) . "</strong>. Settings '" . implode(', ', $content) . "' ignored.";
                  break;
                } else {
                  $ok = TRUE;
                }
              }
              break;
            case 'password':
            case 'string':
              $ok = TRUE;
              break;
            default:
              $ok = FALSE;
              $errors[] = $config_variable[$sqlname]['name'] . " ($confname) is of unknown type (" . $config_variable[$sqlname]['type'] . ")";
              break;
          }

          if ($ok)
          {
            $sets[dbEscape($sqlname)] = $content;
          }
        } else {
          $deletes[] = "'".dbEscape($sqlname)."'";
        }
      }
    }

    // Set fields that were submitted with custom value
    if (count($sets))
    {
      // Escape variable names for save use inside below SQL IN query
      $sqlset = array(); foreach (array_keys($sets) as $var) { $sqlset[] = "'" . dbEscape($var) . "'"; }

      // Fetch current rows in config file so we know which one to UPDATE and which one to INSERT
      $in_db_rows = dbFetchRows("SELECT * FROM `config` WHERE `config_key` IN (".implode(",",$sqlset).")");
      foreach ($in_db_rows as $index => $row)
      {
        $in_db[$row['config_key']] = $row['config_value'];
      }

      foreach ($sets as $key => $value)
      {
        if (isset($in_db[$key]))
        {
          // Already present in DB, update row
          if (serialize($value) != $in_db[$key])
          {
            // Submitted value is different from current value
            dbUpdate(array('config_value' => serialize($value)), 'config', '`config_key` = ?', array($key));
            $updates++;
          }
        } else {
          // Not set in DB yet, insert row
          dbInsert(array('config_key' => $key, 'config_value' => serialize($value)), 'config');
          $updates++;
        }
      }
    }

    // Delete fields that were reset to default
    if (count($deletes))
    {
      dbDelete('config', "`config_key` IN (".implode(",",$deletes).")");
      $updates++;
    }

    // Print errors from validation above, if any
    foreach ($errors as $error)
    {
      print_error($error);
    }

    if ($updates)
    {
      print_success("Settings updated.");
      // Reload $config now, or form below will show old settings still
      include($config['install_dir']."/includes/sql-config.inc.php");
    } else {
      print_error("No changes made.");
    }
  }

  $link_array = array('page' => 'settings');

  foreach ($config_sections as $type => $section)
  {
    if (isset($section['edition']) && $section['edition'] != OBSERVIUM_EDITION)
    {
      // Skip sections not allowed for current Observium edition
      continue;
    }
    if (!isset($vars['section'])) { $vars['section'] = $type; }

    if ($vars['section'] == $type) { $navbar['options'][$type]['class'] = "active"; }
    $navbar['options'][$type]['url']  = generate_url($link_array, array('section' => $type));
    $navbar['options'][$type]['text'] = $section['text'];
  }

  $navbar['options_right']['all']['url']  = generate_url($link_array, array('section' => 'all'));
  $navbar['options_right']['all']['text'] = 'All';
  $navbar['class'] = "navbar-narrow";

  if ($vars['section'] == 'all') { $navbar['options_right']['all']['class'] = 'active'; }

  print_navbar($navbar);

  include($config['html_dir']."/pages/settings/default.inc.php");

// EOF