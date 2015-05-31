<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

$row = 1;

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
          $width_div = 585;
          $width = 507;
          $height = 149;
          $height_div = 220;
        } else {
          $width_div=349;
          $width=275;
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
          $width_div=303;
          $width=226;
          $height = 102;
          $height_div = 158;
        }
      }

      $graph_array['height'] = 100;
      $graph_array['width']  = 210;
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
      $graph_array['title']  = "yes";
      $graph_array['width'] = $width;
      $graph_array['height'] = $height;
      $graph =  generate_graph_tag($graph_array);

      echo("<div style='display: block; padding: 1px; margin: 2px; min-width: ".$width_div."px; max-width:".$width_div."px; min-height:".$height_div."px; max-height:".$height_div."; text-align: center; float: left; background-color: #f5f5f5;'>");
      echo(overlib_link($link, $graph, $overlib_content));
      echo("</div>");
    }
  }
}

// EOF
