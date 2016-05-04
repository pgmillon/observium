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

function generate_p2pradio_query($vars)
{
  $sql  = "SELECT * FROM `p2p_radios`";
  $sql .= ' WHERE 1' . generate_query_permitted(array('device'));

  // Build query
  foreach($vars as $var => $value)
  {
    switch ($var)
    {
      case "group":
      case "group_id":
        $values = get_group_entities($value);
        $sql .= generate_query_values($values, 'radio_id');
        break;
      case "device":
      case "device_id":
        $sql .= generate_query_values($value, 'device_id');
        break;
    }
  }

  return $sql;
}

function print_p2pradio_table_header($vars)
{
  echo('<thead><tr>
          <th class="state-marker"></th>
          <th width="1"></th>');
  if ($vars['page'] != "device" && $vars['popup'] != TRUE) { echo('      <th style="width: 200px;">Device</th>'); }
  echo('
          <th>Radio</th>
          <th>Modulation</th>
          <th>Capacity</th>
          <th>Max Capacity</th>
          <th>Max Eth Cap</th>
          <th># E1/T1</th>
          <th>Tx Power</th>
          <th>Rx Level</th>
          <th>Tx Freq</th>
          <th>Rx Freq</th>
        </tr></thead>');
}

function print_p2pradio_row($radio, $vars)
{
  echo generate_p2pradio_row($radio, $vars);
}

function print_p2pradio_table($vars)
{

  if ($vars['view'] == "graphs" || isset($vars['graph']))
  {
    $stripe_class = "table-striped-two";
  } else {
    $stripe_class = "table-striped";
  }

  echo generate_box_open();

  echo '<table class="table table-hover '.$stripe_class.'  table-condensed">';

  print_p2pradio_table_header($vars);

  $sql = generate_p2pradio_query($vars);

  $radios = dbFetchRows($sql);

  foreach($radios as $radio)
  {
    print_p2pradio_row($radio, $vars);
  }

  echo('</table>');

  echo generate_box_close();

}

function generate_p2pradio_row($radio, $vars)
{
  global $config;

  $table_cols = 12;
  if ($vars['page'] != "device" && $vars['popup'] != TRUE) { $table_cols++; } // Add a column for device.

  $row .= '<tr class="' . $radio['row_class'] . '">
         <td class="state-marker"></td>
         <td></td>';

  if ($vars['page'] != "device"  && $vars['popup'] != TRUE) { $row .=('<td class="entity">' . generate_device_link($radio) . '</td>'); }

  $row .= '
         <td class="entity">' . generate_entity_link('p2pradio', $radio) . '</td>
         <td width="100"><span class="label">' . strtoupper($radio['radio_modulation']) . '</span></td>
         <td width="90">' . format_si($radio['radio_cur_capacity']) . 'bps</td>
         <td width="100">' . format_si($radio['radio_total_capacity']) . 'bps</td>
         <td width="90">' . format_si($radio['radio_eth_capacity']) . 'bps</td>
         <td width="70">' . ($radio['radio_e1t1_channels'] ?: "N/A") . '</td>
         <td width="70"><span class="label label-error">' . $radio['radio_tx_power'] . 'dBm</span></td>
         <td width="70"><span class="label label-warning">' . $radio['radio_rx_level'] . 'dBm</span></td>
         <td width="90"><span class="label label-success">' . ($radio['radio_tx_freq'] / 1000000) . 'GHz</span></td>
         <td width="90"><span class="label label-info">' . ($radio['radio_rx_freq'] / 1000000) . 'GHz</span></td>
         ';
  $row .= '</tr>';

  if ($vars['view'] == "graphs")
  {
    $graphs = array('capacity', 'power', 'rxlevel', 'gain', 'rmse', 'symbol_rates');
    $show_graph_title = TRUE;
  } elseif (isset($vars['graph'])) { $graphs = explode(",", $vars['graph']); }

  if (is_array($graphs))
  {
    $row .= '<tr class="' . $radio['row_class'] . '">';
    $row .= '<td class="state-marker"></td>';
    $row .= '<td colspan=' . $table_cols . '>';

    unset($graph_array['height'], $graph_array['width'], $graph_array['legend']);
    $graph_array['to'] = $config['time']['now'];
    $graph_array['id'] = $radio['radio_id'];

    foreach($graphs as $graph_type)
    {
      $graph_array['type'] = 'p2pradio_' . $graph_type;

      if ($show_graph_title) { $row .= '<h3>'.$config['graph_types']['p2pradio'][$graph_type]['name'].'</h3>'; }

      $row .= generate_graph_row($graph_array, TRUE);
    }
    $row .= "</td>";
    $row .= "</tr>";
  }

  return $row;

}

// EOF
