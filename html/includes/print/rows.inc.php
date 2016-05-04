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

// These functions are used to generate our boxes. It's probably easier to put this into functions.

function generate_box_open($args = array())
{
  // r($args);

  $return = '<div ';
  if (isset($args['id'])) {  $return .= 'id="' . $args['id'] . '" '; }

  $return .= 'class="' . OBS_CLASS_BOX . '" '.($args['box-style'] ? 'style="'.$args['box-style'].'"' : ''). '>' . PHP_EOL;

  if (isset($args['title']))
  {
    $return .= '  <div class="box-header' . ($args['header-border'] ? ' with-border' : '') . '">'.PHP_EOL;
    if(isset($args['url'])) {  $return .= '<a href="'.$args['url'].'">'; }
    if(isset($args['icon'])) {  $return .= '<i class="'.$args['icon'].'"></i> '; }
    $return .= '    <h3 class="box-title">';
    $return .= $args['title'].'</h3>'.PHP_EOL;
    if(isset($args['url'])) {  $return .= '</a>'; }

    if (isset($args['header-controls']) && is_array($args['header-controls']['controls']))
    {
      $return .= '    <div class="box-tools pull-right">';

      foreach($args['header-controls']['controls'] as $control)
      {
        if (isset($control['anchor']) && $control['anchor'] == TRUE)
        {
          $return .= ' <a role="button"';
        } else {
          $return .= '<button type="button"';
        }
        if (isset($control['url']) && strlen($control['url']) && $control['url'] != '#')
        {
          $return .= ' href="'.$control['url'].'"';
        } else {
          $return .= ' onclick="return false;"';
        }

        $return .= ' class="btn btn-box-tool';
        if (isset($control['class'])) { $return .= ' '.$control['class']; }
        $return .= '"';

        if (isset($control['data']))  { $return .= ' '.$control['data']; }
        $return .= '>';

        if (isset($control['icon'])) { $return .= '<i class="'.$control['icon'].'"></i> '; }
        if (isset($control['text'])) { $return .= $control['text']; }

        if (isset($control['anchor']) && $control['anchor'] == TRUE)
        {
          $return .= '</a>';
        } else {
          $return .= '</button>';
        }
      }

      $return .= '    </div>';
    }
    $return .= '  </div>'.PHP_EOL;
  }

  $return .= '  <div class="box-body'.($args['padding'] ? '' : ' no-padding').'"';
  if (isset($args['body-style']))
  {
    $return .= 'style="'.$args['body-style'].'"';
  }
  $return .= '>'.PHP_EOL;
  return $return;

}

function generate_box_close($args = array())
{
  $return  = '  </div>' . PHP_EOL;

  if(isset($args['footer_content']))
  {
    $return .= '  <div class="box-footer no-padding';
    if(isset($args['footer_nopadding'])) { $return .= ' no-padding'; }
    $return .= '">';
    $return .= $args['footer_content'];
    $return .= '  </div>' . PHP_EOL;
  }

  $return .= '</div>' . PHP_EOL;
  return $return;
}


// DOCME needs phpdoc block
function print_graph_row_port($graph_array, $port)
{

  global $config;

  $graph_array['to']     = $config['time']['now'];
  $graph_array['id']     = $port['port_id'];

  print_graph_row($graph_array);
}

// DOCME needs phpdoc block
function generate_graph_row($graph_array, $state_marker = FALSE)
{
  global $config;

  if ($_SESSION['widescreen'])
  {
    if ($_SESSION['big_graphs'])
    {
      if (!$graph_array['height']) { $graph_array['height'] = "110"; }
      if (!$graph_array['width']) { $graph_array['width']  = "372"; }
      $periods = array('day', 'week', 'month', 'year');
    } else {
      if (!$graph_array['height']) { $graph_array['height'] = "110"; }
      if (!$graph_array['width']) { $graph_array['width']  = "287"; }
      $periods = array('day', 'week', 'month', 'year', 'twoyear');
    }
  } else {
    if ($_SESSION['big_graphs'])
    {
      if (!$graph_array['height']) { $graph_array['height'] = "100"; }
      if (!$graph_array['width']) { $graph_array['width']  = "323"; }
      $periods = array('day', 'week', 'month');
    } else {
      if (!$graph_array['height']) { $graph_array['height'] = "100"; }
      if (!$graph_array['width']) { $graph_array['width']  = "228"; }
      $periods = array('day', 'week', 'month', 'year');
    }
  }

  if ($graph_array['shrink']) { $graph_array['width'] = $graph_array['width'] - $graph_array['shrink']; }

  // If we're priting the row inside a table cell with "state-marker", we need to make the graphs a tiny bit smaller to fit
  if($state_marker) { $graph_array['width'] -= 2; }

  $graph_array['to']     = $config['time']['now'];

  $graph_rows = array();
  foreach ($periods as $period)
  {
    $graph_array['from']        = $config['time'][$period];
    $graph_array_zoom           = $graph_array;
    $graph_array_zoom['height'] = "175";
    $graph_array_zoom['width']  = "600";

    $link_array = $graph_array;
    $link_array['page'] = "graphs";
    unset($link_array['height'], $link_array['width']);
    $link = generate_url($link_array);

    $graph_rows[] = overlib_link($link, generate_graph_tag($graph_array), generate_graph_tag($graph_array_zoom),  NULL);
  }

  return implode(PHP_EOL, $graph_rows);
}

function print_graph_row($graph_array, $state_marker = FALSE)
{
  echo(generate_graph_row($graph_array, $state_marker));
}

// DOCME needs phpdoc block
function print_vm_row($vm, $device = NULL)
{
  echo('<tr>');

  echo('  <td>');
  // If we know this device by its vm name in our system, create a link to it, else just print the name.
  if (get_device_id_by_hostname($vm['vm_name']))
  {
    echo(generate_device_link(device_by_name($vm['vm_name'])));
  } else {
    echo $vm['vm_name'];
  }
  echo('  </td>');

  echo('  <td>' . nicecase($vm['vm_state']) . '</td>');

  switch ($vm['vm_guestos'])
  {
    case 'E: tools not installed':
      echo('  <td class="small">Unknown (VMware Tools not installed)</td>');
      break;
    case 'E: tools not running':
      echo('  <td class="small">Unknown (VMware Tools not running)</td>');
      break;
    case '':
      echo('  <td class="small"><i>(Unknown)</i></td>');
      break;
    default:
      if (isset($config['vmware_guestid'][$vm['vm_guestos']]))
      {
        echo('  <td>' . $config['vmware_guestid'][$vm['vm_guestos']] . '</td>');
      } else {
        echo('  <td>' . $vm['vm_guestos'] . '</td>');
      }
      break;
  }

  echo('  <td class="list">' . format_bi($vm['vm_memory'] * 1024 * 1024, 3, 3) . 'B</td>');

  echo('  <td>' . $vm['vm_cpucount'] . ' CPU</td>');
  echo('</tr>');
}

// EOF
