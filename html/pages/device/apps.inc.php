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

$link_array = array('page' => 'device', 'device'  => $device['device_id'], 'tab' => 'apps');

$navbar = array();
$navbar['brand'] = "Apps";
$navbar['class'] = "navbar-narrow";

// Group all apps by app_type in an array
$device_app_types = array();
foreach (dbFetchRows("SELECT * FROM `applications` WHERE `device_id` = ?", array($device['device_id'])) as $app)
{
  $device_app_types[$app['app_type']][] = $app;
}

// Iterate through each app type and its apps
foreach ($device_app_types as $type_key => $type_data)
{
  foreach ($type_data as $app)
  {
    // Set default app and instance if none given (ie. when user first visits the device's app tab)
    if (!$vars['app']) { $vars['app'] = $app['app_type']; }

    $url = generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'apps', 'app' => $app['app_type'], 'instance' => $app['app_id'] ));

    // Check if an app name was inserted into mysql->observium.applications.app_name
    if (!empty($app['app_name']))
    {
      $name = $app['app_name'];
    }
    else
    {
      $name = nicecase($app['app_type']);
    }

    // Determine if this is a named instance of app_type
    if (!empty($app['app_instance']))
    {
      $instance = " (".$app['app_instance'].")";
    }

    // If there is only one instance of the current app type, simply include it as a standard nav link
    if (count($device_app_types[$type_key]) == 1)
    {
      // If the current page is the app type that's being displayed, highlight the nav link
      if ($vars['app'] == $app['app_type'])
      {
        $navbar['options'][$app['app_type']]['class'] = "active";
      }
      $navbar['options'][$app['app_type']]['text'] = $name;
      $navbar['options'][$app['app_type']]['url'] = $url;

      $image = $config['html_dir'].'/images/icons/'.$app['app_type'].'.png';
      $icon = (is_file($image) ? $app['app_type'] : 'apps');
      $navbar['options'][$app['app_type']]['image'] = 'images/icons/'.$icon.'.png';
    }

    // If there is more than one instance of the current app type we need to determine how to render the navbar
    else
    {
      // If the current app type and instance is the one being displayed, highlight the navbar root link and show which app/instance
      if ($vars['app'] == $app['app_type'] && $vars['instance'] == $app['app_id'])
      {
        $navbar['options'][$app['app_type']]['class'] = "active";
        $navbar['options'][$app['app_type']]['text'] = $name . $instance;
      }

      // If the current app type is not active then we need to simply add the root nav link to the bar as inactive
      else
      {
        if (!isset($navbar['options'][$app['app_type']]['text']))
        {
          $navbar['options'][$app['app_type']]['text'] = $name;
        }
      }

      // Add all instances of the app type under the submenu for the app type
      $navbar['options'][$app['app_type']]['suboptions'][$app['app_id']]['text'] = $name . $instance;
      $navbar['options'][$app['app_type']]['suboptions'][$app['app_id']]['url']  = $url;
    }
  }
}
print_navbar($navbar);
unset($navbar, $name, $url, $device_app_types);

$where_array = array($device['device_id'], $vars['app']);
if ($vars['instance'])
{
  $where = " AND `app_id` = ?";
  $where_array[] = $vars['instance'];
}

$app = dbFetchRow("SELECT * FROM `applications` WHERE `device_id` = ? AND `app_type` = ?".$where, $where_array);

$app_filename = $config['html_dir'] . '/pages/device/apps/'.$vars['app'].'.inc.php';
if (is_file($app_filename))
{
  // Include app code to output data
  include($app_filename);

  // If an $app_sections array has been returned, build a menu
  if (isset($app_sections) && is_array($app_sections))
  {
    $navbar['brand'] = nicecase($vars['app']);
    $navbar['class'] = "navbar-narrow";

    foreach ($app_sections as $app_section => $text)
    {
      // Set the chosen app to be this one if it's not already set.
      if (!$vars['app_section']) { $vars['app_section'] = $app_section; }
      if ($vars['app_section'] == $app_section) { $navbar['options'][$app_section]['class'] = "active"; }

      $navbar['options'][$app_section]['url']  = generate_url($vars, array('app_section' => $app_section));
      $navbar['options'][$app_section]['text'] = $text;
    }
    print_navbar($navbar);
    unset($navbar);
  } else {
    // It appears this app doesn't have multiple sections. We set app_section to default here.
    $vars['app_section'] = 'default';
  }

  // If a matching app_section array exists within app_graphs, print the graphs.
  if (isset($app_graphs[$vars['app_section']]) && is_array($app_graphs[$vars['app_section']]))
  {
    echo generate_box_open();

    echo '<table class="table table-striped table-hover  table-condensed">';

    foreach ($app_graphs[$vars['app_section']] as $key => $text)
    {
      $graph_type            = $key;
      $graph_array['to']     = $config['time']['now'];
      $graph_array['id']     = $app['app_id'];
      $graph_array['type']   = "application_".$key;
      echo '<tr><td>';
      echo '<h3>',$text,'</h4>';

      print_graph_row($graph_array);

      echo '</td></tr>';
    }
    echo '</table>';

    generate_box_close();
  }
}

$page_title[] = "Apps";

// EOF
