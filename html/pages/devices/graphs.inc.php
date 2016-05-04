<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */


foreach ($devices as $device)
{
  if (is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  if (device_permitted($device['device_id']))
  {
    if (!$location_filter || $device['location'] == $location_filter)
    {
      $graph_type = "device_".$vars['graph'];

      $graph_array           = array();

      if ($_SESSION['widescreen'])
      {
        if ($_SESSION['big_graphs'])
        {
          $width_div = 586;
          $width = 508;
          $height = 149;
          $height_div = 220;
        } else {
          $width_div=350;
          $width=276;
          $height = 109;
          $height_div = 180;
        }
      } else {
        if ($_SESSION['big_graphs'])
        {
          $width_div = 611;
          $width = 528;
          $height = 159;
          $height_div = 218;
        } else {
          $width_div=294;
          $width=219;
          $height = 100;
          $height_div = 158;
        }
      }

      $graph_array['height'] = 100;
      $graph_array['width']  = 212;
      if (is_numeric($vars['to']))   { $graph_array['to']   = $vars['to'];   } else { $graph_array['to']     = $config['time']['now']; }
      if (is_numeric($vars['from'])) { $graph_array['from'] = $vars['from']; } else { $graph_array['from']   = $config['time']['day']; }

      $graph_array['device']     = $device['device_id'];
      $graph_array['type']   = $graph_type;
      $graph_array['legend'] = "no";

      $link_array = $graph_array;
      $link_array['page'] = "graphs";
      unset($link_array['height'], $link_array['width'], $link_array['legend']);
      $link = generate_url($link_array);
      $overlib_content = generate_overlib_content($graph_array, $device['hostname']);
      //$graph_array['title']  = "yes";
      $graph_array['width'] = $width;
      $graph_array['height'] = $height;
      $graph =  generate_graph_tag($graph_array);

      echo generate_box_open(array('title' => $device['hostname'],
                                   'url' => generate_device_url($device),
                                   'header-border' => TRUE,
                                   'box-style' => 'float: left; margin-right: 10px; margin-bottom: 10px;  width:'.$width_div.'px; min-width: '.$width_div.'px; max-width:'.$width_div.'px; min-height:'.$height_div.'px; max-height:'.$height_div.';'));

      echo(overlib_link($link, $graph, $overlib_content));

      echo generate_box_close();
    }
  }
}

// EOF
