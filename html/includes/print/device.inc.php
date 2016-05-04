<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

// DOCME needs phpdoc block
function print_device_header($device)
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

  echo('<table class="table table-hover table-striped table-bordered table-condensed table-rounded" style="vertical-align: middle; margin-bottom: 10px;">');
  echo('
              <tr class="'.$device['html_row_class'].'" style="vertical-align: middle;">
               <td class="state-marker"></td>
               <td style="width: 70px; text-align: center; vertical-align: middle;">'.get_device_icon($device).'</td>
               <td style="vertical-align: middle;"><span style="font-size: 20px;">' . generate_device_link($device) . '</span>
               <br /><a href="'.generate_location_url($device['location']).'">' . escape_html($device['location']) . '</a></td>
               <td>');

  if (isset($config['os'][$device['os']]['over']))
  {
    $graphs = $config['os'][$device['os']]['over'];
  }
  else if (isset($device['os_group']) && isset($config['os'][$device['os_group']]['over']))
  {
    $graphs = $config['os'][$device['os_group']]['over'];
  } else {
    $graphs = $config['os']['default']['over'];
  }

  $graph_array = array();
  $graph_array['height'] = "100";
  $graph_array['width']  = "310";
  $graph_array['to']     = $config['time']['now'];
  $graph_array['device'] = $device['device_id'];
  $graph_array['type']   = "device_bits";
  $graph_array['from']   = $config['time']['day'];
  $graph_array['legend'] = "no";
  $graph_array['popup_title'] = $descr;

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
    if ($entry['graph'] && in_array(str_replace('device_', '', $entry['graph']), $graphs_enabled) !== FALSE)
    {
      $graph_array['type'] = $entry['graph'];

      if (isset($entry['text']))
      {
        // Text is provided in the array, this overrides the default
        $text = $entry['text'];
      } else {
        // No text provided for the minigraph, fetch from array
        preg_match('/^(?P<type>[a-z0-9A-Z-]+)_(?P<subtype>[a-z0-9A-Z-_]+)/', $entry['graph'], $graphtype);

        if (isset($graphtype['type']) && isset($graphtype['subtype']))
        {
          $type = $graphtype['type'];
          $subtype = $graphtype['subtype'];
        
          $text = $config['graph_types'][$type][$subtype]['descr'];
        } else {
          $text = nicecase($subtype); // Fallback to the type itself as a string, should not happen!
        }
      }

      echo('<div class="pull-right" style="padding: 2px; margin: 0;">');
      print_graph_popup($graph_array);
      echo("<div style='padding: 0px; font-weight: bold; font-size: 7pt; text-align: center;'>$text</div>");
      echo("</div>");
    }
  }

  echo('    </td>
   </tr>
 </table>');
}

function print_device_hostbox($device, $mode = 'basic')
{
  global $config;

  if (!is_array($device)) { print_error("Invalid device passed to print_device_hostbox()!"); }

  if ($device['os'] == "ios") { formatCiscoHardware($device, TRUE); }
  humanize_device($device);

  $hostbox_tags = array(
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

  switch ($mode)
  {
    case 'detail':
    case 'details':
      $hostbox_tags['device_image']  = get_device_icon($device);
      $hostbox_tags['ports_count']   = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE `device_id` = ?;", array($device['device_id']));
      $hostbox_tags['sensors_count'] = dbFetchCell("SELECT COUNT(*) FROM `sensors` WHERE `device_id` = ?;", array($device['device_id']));
      $hostbox = '
  <tr class="'.$hostbox_tags['html_row_class'].'" onclick="location.href=\'device/device='.$hostbox_tags['device_id'].'/\'" style="cursor: pointer;">
    <td class="state-marker"></td>
    <td style="width: 64px; text-align: center; vertical-align: middle;">'.$hostbox_tags['device_image'].'</td>
    <td style="width: 300px;"><span class="entity-title">'.$hostbox_tags['device_link'].'</span><br />'.$hostbox_tags['location'].'</td>
    <td style="width: 55px;">';
      if ($hostbox_tags['ports_count'])
      {
        $hostbox .= '<i class="oicon-network-ethernet"></i> '.$hostbox_tags['ports_count'];
      }
      $hostbox .= '<br />';
      if ($hostbox_tags['sensors_count'])
      {
        $hostbox .= '<i class="oicon-dashboard"></i> '.$hostbox_tags['sensors_count'];
      }
      $hostbox .= '</td>
    <td>'.$hostbox_tags['hardware'].'<br />'.$hostbox_tags['features'].'</td>
    <td>'.$hostbox_tags['os_text'].'<br />'.$hostbox_tags['version'].'</td>
    <td>'.$hostbox_tags['device_uptime'].'<br />'.$hostbox_tags['sysName'].'</td>
  </tr>';
      break;
    case 'status':
      $hostbox_tags['device_image']  = get_device_icon($device);

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

      if (isset($config['os'][$device['os']]['over']))
      {
        $graphs = $config['os'][$device['os']]['over'];
      }
      else if (isset($device['os_group']) && isset($config['os'][$device['os_group']]['over']))
      {
        $graphs = $config['os'][$device['os_group']]['over'];
      } else {
        $graphs = $config['os']['default']['over'];
      }
      // Preprocess device graphs array
      foreach ($GLOBALS['device_graphs'][$device['device_id']] as $graph)
      {
        $graphs_enabled[] = $graph['graph'];
      }
      foreach ($graphs as $entry)
      {
        if ($entry['graph'] && in_array(str_replace('device_', '', $entry['graph']), $graphs_enabled))
        {
          $graph_array['type'] = $entry['graph'];
          $graph_array['popup_title'] = $entry['text'];

          $hostbox_tags['graphs'][] = generate_graph_popup($graph_array);
        }
      }

      $hostbox = '
  <tr class="'.$hostbox_tags['html_row_class'].'" onclick="location.href=\'device/device='.$hostbox_tags['device_id'].'/\'" style="cursor: pointer;">
    <td class="state-marker"></td>
    <td style="width: 64px; text-align: center; vertical-align: middle;">'.$hostbox_tags['device_image'].'</td>
    <td style="width: 300px;"><span class="entity-title">'.$hostbox_tags['device_link'].'</span><br />'.$hostbox_tags['location'].'</td>
    <td>';
      if ($hostbox_tags['graphs'])
      {
        $hostbox .= '<div class="pull-right" style="height: 50px; padding: 2px; margin: 0;">' . implode($hostbox_tags['graphs']) . '</div>';
      }
      $hostbox .= '</td>
  </tr>';
      break;
    default: // basic
      $hostbox = '
  <tr class="'.$hostbox_tags['html_row_class'].'" onclick="location.href=\'device/device='.$hostbox_tags['device_id'].'/\'" style="cursor: pointer;">
    <td style="width: 300;"><span class="entity-title">'.$hostbox_tags['device_link'].'</span><br />'.$hostbox_tags['location'].'</td>
    <td>'.$hostbox_tags['hardware'].' '.$hostbox_tags['features'].'</td>
    <td>'.$hostbox_tags['os_text'].' '.$hostbox_tags['version'].'</td>
    <td>'.$hostbox_tags['device_uptime'].'</td>
  </tr>';
  }

  echo($hostbox);
}

// EOF
