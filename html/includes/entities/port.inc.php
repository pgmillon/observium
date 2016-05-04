<?php
/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package        observium
 * @subpackage     functions
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_port_popup_header($port)
{
  global $config;

  // Push through processing function to set attributes
  humanize_port($port);

  $contents = '
      <table style="margin-top: 10px; margin-bottom: 10px;" class="table table-striped table-bordered table-rounded table-condensed">
        <tr class="' . $port['row_class'] . '" style="font-size: 10pt;">
          <td class="state-marker"></td>
          <td style="width: 10px;"></td>
          <td width="250"><a href="#" class="' . $port['html_row_class'] . '" style="font-size: 15px; font-weight: bold;">' . rewrite_ifname($port['label']) . '</a><br />' . htmlentities($port['ifAlias']) . '</td>
          <td width="100">' . $port['human_speed'] . '<br />' . $port['ifMtu'] . '</td>
          <td>' . $port['human_type'] . '<br />' . $port['human_mac'] . '</td>
        </tr>
          </table>';

  return $contents;
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_port_popup($port, $text = NULL, $type = NULL)
{
  global $config;

  if (!isset($port['os']))
  {
    $port = array_merge($port, device_by_id_cache($port['device_id']));
  }

  humanize_port($port);

  if (!$text)
  {
    $text = rewrite_ifname($port['label']);
  }
  if ($type)
  {
    $port['graph_type'] = $type;
  }
  if (!isset($port['graph_type']))
  {
    $port['graph_type'] = 'port_bits';
  }

  $class = ifclass($port['ifOperStatus'], $port['ifAdminStatus']);

  if (!isset($port['os']))
  {
    $port = array_merge($port, device_by_id_cache($port['device_id']));
  }

  $content  = generate_device_popup_header($port);
  $content .= generate_port_popup_header($port);

  $content .= '<div style="width: 700px">';
  $graph_array['type'] = $port['graph_type'];
  $graph_array['legend'] = "yes";
  $graph_array['height'] = "100";
  $graph_array['width'] = "275";
  $graph_array['to'] = $config['time']['now'];
  $graph_array['from'] = $config['time']['day'];
  $graph_array['id'] = $port['port_id'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from'] = $config['time']['week'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from'] = $config['time']['month'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from'] = $config['time']['year'];
  $content .= generate_graph_tag($graph_array);
  $content .= "</div>";

  return $content;
}

// Note, by default text NOT escaped.
// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_port_link($port, $text = NULL, $type = NULL, $escape = FALSE)
{
  global $config;

  humanize_port($port);

  //if (!isset($port['html_class'])) { $port['html_class'] = ifclass($port['ifOperStatus'], $port['ifAdminStatus']); }
  //if (!isset($text)) { $text = rewrite_ifname($port['label'], !$escape); } // Negative escape flag for exclude double escape

  // Fixme -- does this function even need alternative $text? I think not. It's a hangover from before label.
  if (!isset($text))
  {
    $text = $port['label'];
  }

  if (port_permitted($port['port_id'], $port['device_id']))
  {
    $url = generate_port_url($port);
    if ($escape)
    {
      $text = escape_html($text);
    }

    return '<a href="' . $url . '" class="entity-popup ' . $port['html_class'] . '" data-eid="' . $port['port_id'] . '" data-etype="port">' . $text . '</a>';
  }
  else
  {
    return rewrite_ifname($text);
  }
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_port_url($port, $vars = array())
{
  return generate_url(array('page' => 'device', 'device' => $port['device_id'], 'tab' => 'port', 'port' => $port['port_id']), $vars);
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_port_thumbnail($args, $echo = TRUE)
{
  if (!$args['bg'])
  {
    $args['bg'] = "FFFFFF";
  }
  $args['content'] = "<img src='graph.php?type=" . $args['graph_type'] . "&amp;id=" . $args['port_id'] . "&amp;from=" . $args['from'] . "&amp;to=" . $args['to'] . "&amp;width=" . $args['width'] . "&amp;height=" . $args['height'] . "&amp;bg=" . $args['bg'] . "'>";
  $img = generate_port_link($args, $args['content']);
  if ($echo)
  {
    echo($img);
  }
  else
  {
    return $img;
  }
}
