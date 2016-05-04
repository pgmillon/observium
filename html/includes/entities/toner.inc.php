<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package        observium
 * @subpackage     webui
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

function build_toner_query($vars)
{

  $sql = 'SELECT * FROM `toner`';
  $sql .= ' WHERE 1' . generate_query_permitted(array('device'));

  // Build query
  foreach ($vars as $var => $value)
  {
    switch ($var)
    {
      case "group":
      case "group_id":
        $values = get_group_entities($value);
        $sql .= generate_query_values($values, 'toner.toner_id');
        break;
      case "device":
      case "device_id":
        $sql .= generate_query_values($value, 'toner.device_id');
        break;
    }
  }

  return $sql;

}


function print_toner_table($vars)
{

  $toners = array();
  foreach (dbFetchRows(build_toner_query($vars)) as $toner)
  {

    global $cache;

    if (isset($cache['devices']['id'][$toner['device_id']]))
    {
      $toner['hostname'] = $cache['devices']['id'][$toner['device_id']]['hostname'];
      $toner['html_row_class'] = $cache['devices']['id'][$toner['device_id']]['html_row_class'];
      $toners[] = $toner;
    }
  }
  $toners = array_sort_by($toners, 'hostname', SORT_ASC, SORT_STRING, 'toner_descr', SORT_ASC, SORT_STRING);
  $toners_count = count($toners);

  echo generate_box_open();

  // Pagination
  $pagination_html = pagination($vars, $toners_count);
  echo $pagination_html;

  if ($vars['pageno'])
  {
    $toners = array_chunk($toners, $vars['pagesize']);
    $toners = $toners[$vars['pageno'] - 1];
  }
  // End Pagination

  if ($vars['view'] == "graphs")
  {
    $stripe_class = "table-striped-two";
  }
  else
  {
    $stripe_class = "table-striped";
  }

  // Allow the table to be printed headerless for use in some places.
  if ($vars['headerless'] != TRUE)
  {
    echo('<table class="table ' . $stripe_class . '  table-condensed">');
    echo('  <thead>');

    echo '<tr class="strong"></th>';
    echo '<th class="state-marker"></th>';
    echo '<th></th>';
    if ($vars['page'] != "device" && $vars['popup'] != TRUE )
    {
      echo('      <th style="width: 250px;">Device</th>');
    }
    echo '<th>Toner</th>';
    echo '<th></th>';
    echo '<th>Level</th>';
    echo '<th>Remaining</th>';
    echo '</tr>';

    echo '</thead>';
  }

  foreach ($toners as $toner)
  {
    print_toner_row($toner, $vars);
  }

  echo("</table>");

  echo generate_box_close();

  echo $pagination_html;
}

function print_toner_row($toner, $vars)
{

  echo generate_toner_row($toner, $vars);

}

function generate_toner_row($toner, $vars)
{

  $graph_type = "toner_usage";

  $table_cols = 5;

  $total = $toner['toner_capacity'];
  $perc = $toner['toner_current'];

  $graph_array['type'] = $graph_type;
  $graph_array['id'] = $toner['toner_id'];
  $graph_array['from'] = $GLOBALS['config']['time']['day'];
  $graph_array['to'] = $GLOBALS['config']['time']['now'];
  $graph_array['height'] = "20";
  $graph_array['width'] = "80";

  $background = toner_to_colour($toner['toner_descr'], $perc);

  /// FIXME - popup for toner entity.

  $output .= '<tr class="' . $toner['html_row_class'] . '">';
  $output .= '<td class="state-marker"></td>';
  if ($vars['popup'] == TRUE )
  {
    $output .= '<td width="40" style="text-align: center;"><i class="'.$GLOBALS['config']['entities']['toner']['icon'].'"></i></td>';
  } else {
    $output .= '<td width="1px"></td>';
  }
  if ($vars['page'] != "device" && $vars['popup'] != TRUE )
  {
    $output .= '<td class="entity">' . generate_device_link($toner) . '</td>';
    $table_cols++;
  }
  $output .=  '<td class="entity">' . generate_entity_link('toner', $toner) . '</td>';
  $output .=  '<td style="width: 70px;">' . generate_graph_popup($graph_array) . '</td>';
  $output .=  '<td style="width: 200px;"><a href="' . $link . '">' . print_percentage_bar(400, 20, $perc, $perc . '%', 'ffffff', $background['right'], $free, "ffffff", $background['left']) . '</a></td>';
  $output .=  '<td style="width: 50px; text-align: right;"><span class="label">' . $perc . '%</span></td>';
  $output .=  '</tr>';

  if ($vars['view'] == "graphs")
  {
    $output .= '<tr class="' . $toner['html_row_class'] . '">';
    $output .= '<td class="state-marker"></td>';
    $output .=  '<td colspan='.$table_cols.'>';

    unset($graph_array['height'], $graph_array['width'], $graph_array['legend']);
    $graph_array['to'] = $config['time']['now'];
    $graph_array['id'] = $toner['toner_id'];
    $graph_array['type'] = $graph_type;

    $output .= generate_graph_row($graph_array, TRUE);

    $output .= "</td></tr>";
  } # endif graphs

  return $output;

}
