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

if ($device['type'] == 'wireless')
{
  $radios = dbFetchRows("SELECT * FROM `wifi_accesspoints`, `wifi_radios` WHERE  `wifi_accesspoints`.`wifi_accesspoint_id` = `wifi_radios`.`radio_ap` AND `device_id` = ? AND `wifi_accesspoints`.`wifi_accesspoint_id` = ?  ORDER BY `radio_number` ASC", array($device['device_id'], $vars['accesspoint']));

  echo('<table class="table table-striped  table-condensed ">');

  echo('<tr><td style="width: 350px;">');
  echo("<span class='entity-title'>" . $radios[0]['name'] . "</span><br /><span class=small>" . $radios[0]['ap_number'] . "</span></br>");
  echo('</td><td style="white-space: nowrap;"><span>');
  echo($radios[0]['model'] .'</span></br><span>' . $radios[0]['location']. '</span></td>');

  echo("<td style=white-space: nowrap;><span>". $radios[0]['serial'] ." </span></br><span>" . $radios[0]['fingerprint']. "</span></td></tr>");

  echo("</table>");

  echo('<table class="table table-striped ">');

  foreach ($radios as $radio)
  {
    $rrdfile = get_rrd_path($device, "wifi-radio-". $radio['serial'] . '-' . $radio['radio_number'].".rrd");
    if (is_file($rrdfile))
    {
      $iid = $id;
      echo('<tr><td>');
      echo('<h3> Radio'. $radio['radio_number'] .' - User sessions</h4>');
      $graph_array['type'] = "wifi_usersessions";
      $graph_array['to']   = $config['time']['now'];
      $graph_array['id']   = $radio['wifi_radio_id'];
      print_graph_row($graph_array);
      echo('</td></tr>');

      echo('<tr><td>');
      echo('<h3> Radio'. $radio['radio_number'] .' - Retransmit octets</h4>');
      $graph_array['type'] = "wifi_retransmitoctet";
      $graph_array['to']   = $config['time']['now'];
      $graph_array['id']   = $radio['wifi_radio_id'];
      print_graph_row($graph_array);
      echo('</td></tr>');

      echo('<tr><td>');
      echo('<h3> Radio'. $radio['radio_number'] .' - Noise Floor</h4>');
      $graph_array['type'] = "wifi_noisefloor";
      $graph_array['to']   = $config['time']['now'];
      $graph_array['id']   = $radio['wifi_radio_id'];
      print_graph_row($graph_array);
      echo('</td></tr>');

      echo('<tr><td>');
      echo('<h3> Radio'. $radio['radio_number'] .' - Reset</h4>');
      $graph_array['type'] = "wifi_resetcount";
      $graph_array['to']   = $config['time']['now'];
      $graph_array['id']   = $radio['wifi_radio_id'];
      print_graph_row($graph_array);
      echo('</td></tr>');

      echo('<tr><td>');
      echo('<h3> Radio'. $radio['radio_number'] .' - Transmit retries</h4>');
      $graph_array['type'] = "wifi_txretriescount";
      $graph_array['to']   = $config['time']['now'];
      $graph_array['id']   = $radio['wifi_radio_id'];
      print_graph_row($graph_array);
      echo('</td></tr>');

      echo('<tr><td>');
      echo('<h3> Radio'. $radio['radio_number'] .' - Failed client associations</h4>');
      $graph_array['type'] = "wifi_clientfailedassociations";
      $graph_array['to']   = $config['time']['now'];
      $graph_array['id']   = $radio['wifi_radio_id'];
      print_graph_row($graph_array);
      echo('</td></tr>');

      echo('<tr><td>');
      echo('<h3> Radio'. $radio['radio_number'] .' - Refused connections</h4>');
      $graph_array['type'] = "wifi_refusedconnectioncount";
      $graph_array['to']   = $config['time']['now'];
      $graph_array['id']   = $radio['wifi_radio_id'];
      print_graph_row($graph_array);
      echo('</td></tr>');
    }
  }

  echo('</table>');
}

// EOF
