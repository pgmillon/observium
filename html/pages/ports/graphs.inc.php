<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

/// FIXME. Ohh noo.. someone FIX this huuuuuuge page

foreach ($ports as $port)
{
#  if (is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  $speed = humanspeed($port['ifSpeed']);
  $type  = rewrite_iftype($port['ifType']);

  $port['in_rate'] = formatRates($port['ifInOctets_rate'] * 8);
  $port['out_rate'] = formatRates($port['ifOutOctets_rate'] * 8);

  if ($port['in_errors'] > 0 || $port['out_errors'] > 0)
  {
    $error_img = generate_port_link($port, "<img src='images/16/chart_curve_error.png' alt='Interface Errors' border=0>", 'errors');

  } else {
    $error_img = '';
  }

  humanize_port($port);

  $graph_type = "port_" . $vars['graph'];

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

  if (isset($vars['from']) && is_numeric($vars['from']) && isset($vars['to']) && is_numeric($vars['to']))
  {
    $graph_array['from'] = $vars['from'];
    $graph_array['to'] = $vars['to'];
  } else {
    $graph_array['from'] = $config['time']['day'];
    $graph_array['to'] = $config['time']['now'];
  }

  $graph_array['height'] = 100;
  $graph_array['width']  = 210;
  $graph_array['id']     = $port['port_id'];
  $graph_array['type']   = $graph_type;
  $graph_array['legend'] = "no";

  $link_array = $graph_array;
  $link_array['page'] = "graphs";
  unset($link_array['height'], $link_array['width'], $link_array['legend']);
  $link = generate_url($link_array);
  $overlib_content = generate_overlib_content($graph_array, $port['hostname'] . ' - ' . rewrite_ifname($port['port_label'], FALSE));
  $graph_array['title']  = "yes";
  $graph_array['width']  = $width;
  $graph_array['height'] = $height;
  $graph =  generate_graph_tag($graph_array);

  echo("<div style='display: block; padding: 1px; margin: 2px; min-width: ".$width_div."px; max-width:".$width_div."px; min-height:".$height_div."px; max-height:".$height_div."; text-align: center; float: left; background-color: #f5f5f5;'>");
  echo(overlib_link($link, $graph, $overlib_content));
  echo("</div>");

#    echo("<div style='display: block; padding: 1px; margin: 2px; min-width: 393px; max-width:393px; min-height:180px; max-height:180px; text-align: center; float: left; background-color: #f5f5f5;'>
#    <a href='".generate_port_url($port)."/' onmouseover=\"return overlib('\
#    <div style=\'font-size: 16px; padding:5px; font-weight: bold; color: #e5e5e5;\'>".$device['hostname']." - ".$port['ifDescr']."</div>\
#    <img src=\'graph.php?type=$graph_type&amp;id=".$port['port_id']."&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=450&amp;height=150&amp;title=yes\'>\
#    ', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".
#    "<img src='graph.php?type=$graph_type&amp;id=".$port['port_id']."&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=315&amp;height=110&amp;legend=no&amp;title=yes'>
#    </a>
#    </div>");
}

// EOF
