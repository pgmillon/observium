<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package        observium
 * @subpackage     functions
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

/**
 * Build devices where array
 *
 * This function returns an array of "WHERE" statements from a $vars array.
 * The returned array can be implode()d and used on the devices table.
 * Originally extracted from the /devices/ page
 *
 * @param array $vars
 * @return array
 */
function build_devices_where_array($vars)
{
  $where_array = array();
  foreach ($vars as $var => $value)
  {
    if ($value != '')
    {
      switch ($var)
      {
        case 'group':
          $values = get_group_entities($value);
          $where_array[$var] = generate_query_values($values, 'device_id');
          break;
        case 'hostname':
        case 'sysname':
          $where_array[$var] = generate_query_values($value, $var, '%LIKE%');
          break;
        case 'location_text':
          $where_array[$var] = generate_query_values($value, 'devices.location', '%LIKE%');
          break;
        case 'location':
          $where_array[$var] = generate_query_values($value, 'devices.location');
          break;
        case 'location_lat':
        case 'location_lon':
        case 'location_country':
        case 'location_state':
        case 'location_county':
        case 'location_city':
          if ($GLOBALS['config']['geocoding']['enable'])
          {
            $where_array[$var] = generate_query_values($value, 'devices_locations.' . $var);
          }
          break;
        case 'os':
        case 'version':
        case 'hardware':
        case 'features':
        case 'type':
        case 'status':
        case 'ignore':
        case 'disabled':
          $where_array[$var] = generate_query_values($value, $var);
          break;
        case 'graph':
          $where_array[$var] = generate_query_values(devices_with_graph($value), "devices.device_id");
     }
    }
  }

  return $where_array;
}

function devices_with_graph($graph)
{

  $devices = array();

  $sql = "SELECT `device_id` FROM `device_graphs` WHERE `graph` = ? AND `enabled` = '1'";
  foreach(dbFetchRows($sql, array($graph)) AS $entry)
  {
    $devices[$entry['device_id']] = $entry['device_id'];
  }

  return $devices;

}

function build_devices_sort($vars)
{
  $order = '';
  switch ($vars['sort'])
  {
    case 'uptime':
    case 'location':
    case 'version':
    case 'features':
    case 'type':
    case 'os':
    case 'device_id':
      $order = ' ORDER BY `devices`.`'.$vars['sort'].'`';
      if(isset($vars['sort_desc']) && $vars['sort_desc']) { $order .= " DESC"; }
      break;
    default:
      $order = ' ORDER BY `devices`.`hostname`';
      break;
  }
  return $order;
}

// DOCME needs phpdoc block
function print_device_header($device, $args = array())
{
  global $config;

  if (!is_array($device)) { print_error("Invalid device passed to print_device_header()!"); }

  if ($device['status'] == '0') {  $class = "div-alert"; } else {   $class = "div-normal"; }
  if ($device['ignore'] == '1')
  {
    $class = "div-ignore-alert";
    if ($device['status'] == '1')
    {
      $class = "div-ignore";
    }
  }

  if ($device['disabled'] == '1')
  {
    $class = "div-disabled";
  }

  $type = strtolower($device['os']);

  echo '<div class="box box-solid"><table class=" table table-hover table-condensed '.$args['class'].'" style="vertical-align: middle; margin-bottom: 10px; min-height: 70px; border-radius: 2px;">';
  echo '
              <tr class="'.$device['html_row_class'].'" style="vertical-align: middle;">
               <td class="state-marker"></td>
               <td style="width: 70px; text-align: center; vertical-align: middle;">'.get_device_icon($device).'</td>
               <td style="vertical-align: middle;"><span style="font-size: 20px;">' . generate_device_link($device) . '</span>
               <br /><a href="'.generate_location_url($device['location']).'">' . escape_html($device['location']) . '</a></td>
               ';


  if(device_permitted($device) && !$args['no_graphs'])
  {

    echo '<td>';

    // Only show graphs for device_permitted(), don't show device graphs to users who can only see a single entity.

    if (isset($config['os'][$device['os']]['graphs']))
    {
      $graphs = $config['os'][$device['os']]['graphs'];
    }
    else if (isset($device['os_group']) && isset($config['os'][$device['os_group']]['graphs']))
    {
      $graphs = $config['os'][$device['os_group']]['graphs'];
    } else {
      $graphs = $config['os']['default']['graphs'];
    }

    $graph_array = array();
    $graph_array['height'] = "100";
    $graph_array['width']  = "310";
    $graph_array['to']     = $config['time']['now'];
    $graph_array['device'] = $device['device_id'];
    $graph_array['type']   = "device_bits";
    $graph_array['from']   = $config['time']['day'];
    $graph_array['legend'] = "no";

    $graph_array['height'] = "45";
    $graph_array['width']  = "150";
    $graph_array['bg']     = "FFFFFF00";

    // Preprocess device graphs array
    foreach ($device['graphs'] as $graph)
    {
      $graphs_enabled[] = $graph['graph'];
    }

    foreach ($graphs as $entry)
    {
      if ($entry && in_array(str_replace('device_', '', $entry), $graphs_enabled) !== FALSE)
      {
        $graph_array['type'] = $entry;

        preg_match('/^(?P<type>[a-z0-9A-Z-]+)_(?P<subtype>[a-z0-9A-Z-_]+)/', $entry, $graphtype);

        if (isset($graphtype['type']) && isset($graphtype['subtype']))
        {
          $type = $graphtype['type'];
          $subtype = $graphtype['subtype'];

          $text = $config['graph_types'][$type][$subtype]['descr'];
        } else {
          $text = nicecase($subtype); // Fallback to the type itself as a string, should not happen!
        }

        echo '<div class="pull-right" style="padding: 2px; margin: 0;">';
        echo generate_graph_tag($graph_array);
        echo '<div style="padding: 0px; font-weight: bold; font-size: 7pt; text-align: center;">'.$text.'</div>';
        echo '</div>';
      }
    }

  echo '    </td>';

  } // Only show graphs for device_permitted()

  echo('
   </tr>
 </table>
</div>');
}

function print_device_row($device, $vars = array('view' => 'basic'))
{
  global $config;

  if (!is_array($device)) { print_error("Invalid device passed to print_device_hostbox()!"); }

  if (!is_array($vars)) { $vars = array('view' => $vars); } // For compatability
  if ($device['os'] == "ios") { formatCiscoHardware($device, TRUE); }
  humanize_device($device);

  $tags = array(
      'html_row_class'  => $device['html_row_class'],
      'device_id'     => $device['device_id'],
      'device_link'   => generate_device_link($device),
      'hardware'      => escape_html($device['hardware']),
      'features'      => escape_html($device['features']),
      'os_text'       => $device['os_text'],
      'version'       => escape_html($device['version']),
      'sysName'       => escape_html($device['sysName']),
      'device_uptime' => deviceUptime($device, 'short'),
      'location'      => escape_html(truncate($device['location'], 32, ''))
  );

  switch ($vars['view'])
  {
    case 'detail':
    case 'details':
      $tags['device_image']  = get_device_icon($device);
      $tags['ports_count']   = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE `device_id` = ?;", array($device['device_id']));
      $tags['sensors_count'] = dbFetchCell("SELECT COUNT(*) FROM `sensors` WHERE `device_id` = ?;", array($device['device_id']));
      $hostbox = '
  <tr class="'.$tags['html_row_class'].'" onclick="location.href=\'device/device='.$tags['device_id'].'/\'" style="cursor: pointer;">
    <td class="state-marker"></td>
    <td style="width: 64px; text-align: center; vertical-align: middle;">'.$tags['device_image'].'</td>
    <td style="width: 300px;"><span class="entity-title">'.$tags['device_link'].'</span><br />'.$tags['location'].'</td>
    <td style="width: 55px;">';
      if ($tags['ports_count'])
      {
        $hostbox .= '<i class="oicon-network-ethernet"></i> '.$tags['ports_count'];
      }
      $hostbox .= '<br />';
      if ($tags['sensors_count'])
      {
        $hostbox .= '<i class="oicon-dashboard"></i> '.$tags['sensors_count'];
      }
      $hostbox .= '</td>
    <td>'.$tags['hardware'].'<br />'.$tags['features'].'</td>
    <td>'.$tags['os_text'].'<br />'.$tags['version'].'</td>
    <td>'.$tags['device_uptime'].'<br />'.$tags['sysName'].'</td>
  </tr>';
      break;
    case 'perf':
      if ($_SESSION['userlevel'] >= "10")
      {
        $tags['device_image']  = get_device_icon($device);
        $graph_array = array(
            'type'   => 'device_poller_perf',
            'device' => $device['device_id'],
            'operation' => 'poll',
            'legend'    => 'no',
            'width'  => 600,
            'height' => 90,
            'from'   => $config['time']['week'],
            'to'     => $config['time']['now'],
        );

        $hostbox = '
  <tr class="'.$tags['html_row_class'].'" onclick="location.href=\'device/device='.$tags['device_id'].'/tab=perf/\'" style="cursor: pointer;">
    <td class="state-marker"></td>
    <td style="width: 64px; text-align: center; vertical-align: middle;">'.$tags['device_image'].'</td>
    <td style="width: 300px; vertical-align: middle;"><span class="entity-title">' . $tags['device_link'] . '</span><br />'.$tags['location'].'</td>
    <td><div class="pull-right" style="height: 100px; padding: 2px; margin: 0;">' . generate_graph_tag($graph_array) . '</div></td>
  </tr>';
      }
      break;
    case 'status':
      $tags['device_image']  = get_device_icon($device);

      // Graphs
      $graph_array = array();
      $graph_array['height'] = "100";
      $graph_array['width']  = "310";
      $graph_array['to']     = $config['time']['now'];
      $graph_array['device'] = $device['device_id'];
      $graph_array['type']   = "device_bits";
      $graph_array['from']   = $config['time']['day'];
      $graph_array['legend'] = "no";
      $graph_array['height'] = "45";
      $graph_array['width']  = "175";
      $graph_array['bg']     = "FFFFFF00";

      if (isset($config['os'][$device['os']]['graphs']))
      {
        $graphs = $config['os'][$device['os']]['graphs'];
      }
      else if (isset($device['os_group']) && isset($config['os'][$device['os_group']]['graphs']))
      {
        $graphs = $config['os'][$device['os_group']]['graphs'];
      } else {
        $graphs = $config['os']['default']['graphs'];
      }

      // Preprocess device graphs array
      foreach ($GLOBALS['cache']['devices']['id'][$device['device_id']]['graphs'] as $graph)
      {
        $graphs_enabled[] = $graph['graph'];
      }

      foreach ($graphs as $entry)
      {
        if ($entry && in_array(str_replace("device_", "", $entry), $graphs_enabled))
        {
          $graph_array['type'] = $entry;
          if(isset($config['graph_types']['device'][$entry]['name']))
          {
            $graph_array['popup_title'] = $config['graph_types']['device'][$entry]['name'];
          } else {
            $graph_array['popup_title'] = nicecase(str_replace("_", " ", str_replace("device_", "", $entry)));
          }
          $tags['graphs'][] = '<div class="pull-right" style="margin: 5px; margin-bottom: 0px;">'. generate_graph_popup($graph_array) .'<br /><div style="text-align: center; padding: 0px; font-size: 7pt; font-weight: bold;">'.$graph_array['popup_title'].'</div></div>';
        }
      }

      $hostbox = '
  <tr class="'.$tags['html_row_class'].'" onclick="location.href=\'device/device='.$tags['device_id'].'/\'" style="cursor: pointer;">
    <td class="state-marker"></td>
    <td style="width: 64px; text-align: center; vertical-align: middle;">'.$tags['device_image'].'</td>
    <td style="width: 300px;"><span class="entity-title">'.$tags['device_link'].'</span><br />'.$tags['location'].'</td>
    <td>';
      if ($tags['graphs'])
      {
        $hostbox .= '' . implode($tags['graphs']) . '';
      }
      $hostbox .= '</td>
  </tr>';
      break;
    default: // basic
      $table_cols = 6;
      $tags['device_image']  = get_device_icon($device);
      $tags['ports_count']   = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE `device_id` = ?;", array($device['device_id']));
      $tags['sensors_count'] = dbFetchCell("SELECT COUNT(*) FROM `sensors` WHERE `device_id` = ?;", array($device['device_id']));
      $hostbox = '
  <tr class="'.$tags['html_row_class'].'" onclick="location.href=\'device/device='.$tags['device_id'].'/\'" style="cursor: pointer;">
    <td class="state-marker"></td>
    <td style="width: 64px; text-align: center; vertical-align: middle;">'.$tags['device_image'].'</td>
    <td style="width: 300;"><span class="entity-title">'.$tags['device_link'].'</span><br />'.$tags['location'].'</td>
    <td>'.$tags['hardware'].' '.$tags['features'].'</td>
    <td>'.$tags['os_text'].' '.$tags['version'].'</td>
    <td>'.$tags['device_uptime'].'</td>
  </tr>';
  }


  // If we're showing graphs, generate the graph

  if ($vars['graph'])
  {
    $hostbox .= '<tr><td colspan="'.$table_cols.'">';

    $graph_array['to']     = $config['time']['now'];
    $graph_array['device']     = $device['device_id'];
    $graph_array['type']   = 'device_'.$vars['graph'];

    $hostbox .= generate_graph_row($graph_array);

    $hostbox .= '</td></tr>';

  }

  echo($hostbox);
}


/**
 * Returns icon tag (by default) or icon name for current device array
 *
 * @param array $device Array with device info (from DB)
 * @param bool $base_icon Return complete img tag with icon (by default) or just base icon name
 *
 * @return string Img tag with icon or base icon name
 */
function get_device_icon($device, $base_icon = FALSE)
{
  global $config;

  $icon = 'generic';
  $device['os'] = strtolower($device['os']);
  $model = $config['os'][$device['os']]['model'];

  if ($device['icon'] && is_file($config['html_dir'] . '/images/os/' . $device['icon'] . '.png'))
  {
    // Custom device icon from DB
    $icon  = $device['icon'];
  }
  else if ($model && isset($config['model'][$model][$device['sysObjectID']]['icon']) &&
           is_file($config['html_dir'] . '/images/os/' . $config['model'][$model][$device['sysObjectID']]['icon'] . '.png'))
  {
    // Per model icon
    $icon  = $config['model'][$model][$device['sysObjectID']]['icon'];
  }
  else if ($config['os'][$device['os']]['icon'] && is_file($config['html_dir'] . '/images/os/' . $config['os'][$device['os']]['icon'] . '.png'))
  {
    // Icon defined in os definition
    $icon  = $config['os'][$device['os']]['icon'];
  } else {
    if ($device['distro'])
    {
      // Icon by distro name
      $distro = safename(strtolower(trim($device['distro'])));
      if (is_file($config['html_dir'] . '/images/os/' . $distro . '.png'))
      {
        $icon  = $distro;
      }
    }

    if ($icon == 'generic' && is_file($config['html_dir'] . '/images/os/' . $device['os'] . '.png'))
    {
      // Icon by OS name
      $icon  = $device['os'];
    }
  }
  if ($icon == 'generic' && $config['os'][$device['os']]['vendor'])
  {
    // Icon by vendor name
    $vendor = safename(strtolower(trim($config['os'][$device['os']]['vendor'])));
    if (is_file($config['html_dir'] . '/images/os/' . $vendor . '.png'))
    {
      $icon  = $vendor;
    }
  }

  if ($base_icon)
  {
    // return base name for os icon
    return $icon;
  } else {
    // return image html tag
    $srcset = '';
    if (is_file($config['html_dir'] . '/images/os/' . $icon . '_2x.png')) // HiDPI imag exist?
    {
      // Detect allowed screen ratio for current browser
      $ua_info = detect_browser();

      if ($ua_info['screen_ratio'] > 1)
      {
        $srcset = ' srcset="' .$config['base_url'] . '/images/os/' . $icon . '_2x.png'.' 2x"';
      }
    }
    // Image tag -- FIXME re-engineer this code to do this properly. This is messy.
    $image =  '<img src="' . $config['base_url'] . '/images/os/' . $icon . '.png"' . $srcset . ' alt="" />';
    return $image;
  }
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_device_url($device, $vars = array())
{
  return generate_url(array('page' => 'device', 'device' => $device['device_id']), $vars);
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_device_popup_header($device, $vars = array())
{
  global $config;

  humanize_device($device);

  if ($device['os'] == "ios")
  {
    formatCiscoHardware($device, TRUE);
  } // FIXME or generic function for more than just IOS? [and/or do this at poll time]
  $contents = generate_box_open() . '
<table class="table table-striped table-rounded table-condensed">
  <tr class="' . $device['html_row_class'] . '" style="font-size: 10pt;">
    <td class="state-marker"></td>
    <td style="width: 64px; text-align: center; vertical-align: middle;">' . get_device_icon($device) . '</td>
    <td width="200px"><a href="#" class="' . device_link_class($device) . '" style="font-size: 15px; font-weight: bold;">' . escape_html($device['hostname']) . '</a><br />' . escape_html(truncate($device['location'], 64, '')) . '</td>
    <td>' . escape_html($device['hardware']) . ' <br /> ' . $device['os_text'] . ' ' . escape_html($device['version']) . '</td>
    <td>' . deviceUptime($device, 'short') . '<br />' . escape_html($device['sysName']) . '
  </tr>
</table>
' . generate_box_close();

  return $contents;
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_device_popup($device, $vars = array(), $start = NULL, $end = NULL)
{
  global $config;

  if (!$start)
  {
    $start = $config['time']['day'];
  }
  if (!$end)
  {
    $end = $config['time']['now'];
  }

  $content = generate_device_popup_header($device, $vars = array());

  if (isset($config['os'][$device['os']]['graphs']))
  {
    $graphs = $config['os'][$device['os']]['graphs'];
  }
  elseif (isset($device['os_group']) && isset($config['os'][$device['os_group']]['graphs']))
  {
    $graphs = $config['os'][$device['os_group']]['graphs'];
  }
  else
  {
    $graphs = $config['os']['default']['graphs'];
  }

  // Preprocess device graphs array
  foreach ($device['graphs'] as $graph)
  {
    $graphs_enabled[] = $graph['graph'];
  }

  foreach ($graphs as $entry)
  {
    $graph = $entry;

    if ($graph && in_array(str_replace('device_', '', $graph), $graphs_enabled) !== FALSE)
    {
      // No text provided for the minigraph, fetch from array
      preg_match('/^(?P<type>[a-z0-9A-Z-]+)_(?P<subtype>[a-z0-9A-Z-_]+)/', $graph, $graphtype);

      if (isset($graphtype['type']) && isset($graphtype['subtype']))
      {
        $type = $graphtype['type'];
        $subtype = $graphtype['subtype'];

        $text = $config['graph_types'][$type][$subtype]['descr'];
      }
      else
      {
        $text = nicecase($subtype); // Fallback to the type itself as a string, should not happen!
      }

      // FIXME -- function!


      $graph_array = array();
      $graph_array['height'] = "100";
      $graph_array['width']  = "275";
      $graph_array['to']     = $config['time']['now'];
      $graph_array['device'] = $device['device_id'];
      $graph_array['type']   = $graph;
      $graph_array['from']   = $config['time']['day'];
      $graph_array['legend'] = "no";
      $graph_array['bg']     = "FFFFFF";

      $content .= '<div style="width: 730px; white-space: nowrap;">';
      $content .= "<div class=entity-title><h4>" . $text . "</h4></div>";
      /*
      $content .= generate_box_open(array('title' => $text,
                                          'body-style' => 'white-space: nowrap;'));
      */
      $content .= generate_graph_tag($graph_array);

      $graph_array['from']   = $config['time']['week'];
      $content .= generate_graph_tag($graph_array);

      $content .= '</div>';
      //$content .= generate_box_close();
    }
  }

  //r($content);
  return $content;
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_device_link($device, $text = NULL, $vars = array(), $escape = TRUE)
{
  if (is_array($device) && !($device['hostname'] && isset($device['status'])))
  {
    $device = device_by_id_cache($device['device_id']);
  }
  if (!device_permitted($device['device_id']))
  {
    $text = ($escape ? escape_html($device['hostname']) : $device['hostname']);

    return $text;
  }

  $class = device_link_class($device);
  if (!$text)
  {
    $text = $device['hostname'];
  }

  $url = generate_device_url($device, $vars);
  //$link = overlib_link($url, $text, $contents, $class, $escape);

  if ($escape)
  {
    $text = escape_html($text);
  }

  return '<a href="' . $url . '" class="entity-popup ' . $class . '" data-eid="' . $device['device_id'] . '" data-etype="device">' . $text . '</a>';
}

// EOF
