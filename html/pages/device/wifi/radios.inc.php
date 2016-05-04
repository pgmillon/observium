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

echo('<table class="table table-hover  table-condensed  table-striped"
             style="vertical-align: middle; margin-top: 5px; margin-bottom: 10px;">');

echo('<thead><tr>
          <th></th>
          <th></th>
          <th>AP</th>
          <th>Radio #</th>
          <th>Radio Type</th>
          <th>Channel</th>
          <th>Tx Power</th>
          <th>BSS Type</th>
          <th>Protection</th>
          <th>Status</th>
          <th>Clients</th>
        </tr></thead>');

$i = "1";

$radios = dbFetchRows("SELECT * FROM `wifi_radios` WHERE  `device_id` = ?  ORDER BY `radio_number` ASC", array($device['device_id']));

foreach ($radios as $radio)
{

  switch ($radio['radio_type'])
  {
    case 'ieee802dot11a':
      $radio['type'] = "802.11a";
      break;
    case 'ieee802dot11b':
      $radio['type'] = "802.11b";
      break;
    case 'ieee802dot11g':
      $radio['type'] = "802.11g";
      break;
    case 'ieee802dot11na':
      $radio['type'] = "802.11n (5GHz)";
      break;
    case 'ieee802dot11ng':
      $radio['type'] = "802.11n (2.4GHz)";
      break;
    case 'ieee802dot11ac':
      $radio['type'] = "802.11ac";
      break;
    default:
      $radio['type'] = "Unknown";
      break;
  }

  echo '<tr class="' . $radio['row_class'] . '">
         <td style="width: 1px; background-color: ' . $radio['table_tab_colour'] . '; margin: 0px; padding: 0px; width: 10px;"></td>
         <td style="width: 1px;"></td>';

  if ($radio['radio_ap'] == "0")
  {
    echo '<td><b>self</b></td>';
  }
  else
  {
    echo '<td><b>' . $radio['radio_ap'] . '</b></td>';
  }
  echo '<td><b>' . $radio['radio_number'] . '</b></td>';
  echo '<td>' . $radio['type'] . '</td>';
  echo '<td>' . $radio['radio_channel'] . '</td>';
  echo '<td>' . $radio['radio_txpower'] . '</td>';
  echo '<td>' . $radio['radio_bsstype'] . '</td>';
  echo '<td>' . $radio['radio_protection'] . '</td>';
  echo '<td>' . $radio['radio_status'] . '</td>';
  echo '<td>' . $radio['radio_clients'] . '</td>';

  echo('</tr>');

  echo '<tr>';
  echo '<td colspan="11">';

  $graph_array['type']        = "wifiradio_bits";
  $graph_array['id']          = $radio['wifi_radio_id'];
  echo('<h3>Traffic</h4>');

  print_graph_row($graph_array);

  $graph_array['type']        = "wifiradio_frames";
  $graph_array['id']          = $radio['wifi_radio_id'];
  echo('<h3>Frames</h4>');

  print_graph_row($graph_array);

  $graph_array['type']        = "wifiradio_rxerrors";
  $graph_array['id']          = $radio['wifi_radio_id'];
  echo('<h3>Receive Errors</h4>');

  print_graph_row($graph_array);

  $graph_array['type']        = "wifiradio_clients";
  $graph_array['id']          = $radio['wifi_radio_id'];
  echo('<h3>Clients</h4>');

  print_graph_row($graph_array);

  echo '</td>';
  echo '</tr>';

}

echo("</table>");

$pagetitle[] = "Radios";

// EOF
