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
          <th class="state-marker"></th>
          <th>WLAN Name<br />SSID</th>
          <th>WLAN Port<br />VLAN ID</th>
          <th width="150">BSSID<br />BSS Type</th>
          <th width="150">Radio Mode<br />Radio Channel</th>
          <th width="150">Protection<br />IGMP Snooping</th>
          <th width="120">Beacon Period<br />DTIM Period</th>
          <th width="120">Frag Threshold<br />RTS Threshold</th>
        </tr></thead>');

$i = "1";

$wlans = dbFetchRows("SELECT * FROM `wifi_wlans` WHERE  `device_id` = ?  ORDER BY `wlan_index` ASC", array($device['device_id']));

foreach ($wlans as $wlan)
{

  switch ($wlan['wlan_radio_mode'])
  {
    case 'ieee802dot11a':
      $wlan['type'] = "802.11a";
      $wlan['freq'] = $config['wifi']['channels']['5'][$wlan['wlan_channel']];
      break;
    case 'ieee802dot11b':
      $wlan['type'] = "802.11b";
      $wlan['freq'] = $config['wifi']['channels']['2.4'][$wlan['wlan_channel']];
      break;
    case 'ieee802dot11g':
      $wlan['type'] = "802.11g";
      $wlan['freq'] = $config['wifi']['channels']['2.5'][$wlan['wlan_channel']];
      break;
    case 'ieee802dot11na':
      $wlan['type'] = "802.11n (5GHz)";
      $wlan['freq'] = $config['wifi']['channels']['5'][$wlan['wlan_channel']];
      break;
    case 'ieee802dot11ng':
      $wlan['type'] = "802.11n (2.4GHz)";
      $wlan['freq'] = $config['wifi']['channels']['2.4'][$wlan['wlan_channel']];
      break;
    case 'ieee802dot11ac':
      $wlan['type'] = "802.11ac";
      $wlan['freq'] = $config['wifi']['channels']['5'][$wlan['wlan_channel']];
      break;
    default:
      $wlan['type'] = "Unknown";
      $wlan['freq'] = $config['wifi']['channels']['5'][$wlan['wlan_channel']];
      break;
  }

  if ($wlan['wlan_admin_status'] == "1")
  {
    $wlan['row_class'] = "up";
  }
  else
  {
    $wlan['row_class'] = "disabled";
  }

  if ($wlan['wlan_igmp_snoop'] == "1")
  {
    $wlan['igmp_label'] = '<span class="label label-success">IGMP Snooping</span>';
  }
  else
  {
    $wlan['igmp_label'] = '<span class="label label-disabled">Disabled</span>';
  }

  if ($wlan['wlan_ssid_bcast'] == "0")
  {
    $wlan['ssid_bcast_label'] = ' <span class="pull-right label label-disabled">Hidden SSID</span>';
  }
  else
  {
    $wlan['ssid_bcast_label'] = '';
  }

  if ($port = get_port_by_ifIndex($device['device_id'], $wlan['wlan_index']))
  {
    $wlan['port_link'] = generate_entity_link('port', $port);
  }

  echo '<tr class="' . $wlan['row_class'] . '">
         <td class="state-marker"></td>';

  echo '<td><span class="entity">' . generate_entity_link('wifi_wlan', $wlan) . $wlan['ssid_bcast_label'] . '</span><br />' . $wlan['wlan_ssid'] . '</td>';
  echo '<td><span class="entity">' . $wlan['port_link'] . '</span></td>';
  echo '<td>' . $wlan['wlan_bssid'] . '<br />' . $wlan['wlan_bss_type'] . '</td>';
  echo '<td>' . $wlan['type'] . '<br />Ch.' . $wlan['wlan_channel'] . ' (' . $wlan['freq'] . 'MHz)</td>';
  echo '<td>' . $wlan['wlan_prot_mode'] . '<br />' . $wlan['igmp_label'] . '</td>';
  echo '<td>' . $wlan['wlan_beacon_period'] . 'ms<br />' . $wlan['wlan_dtim_period'] . '</td>';
  echo '<td>' . $wlan['wlan_frag_thresh'] . 'B<br />' . $wlan['wlan_rts_thresh'] . 'B</td>';

  echo('</tr>');
}

echo("</table>");

$pagetitle[] = "Radios";

// EOF

// FIXME wot? vv
function humanize_wifi_wlan(&$wlan)
{

}
