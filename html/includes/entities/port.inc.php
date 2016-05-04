<?php
/**
 * Observium
 *
 *   This file is part of Observium.
 *
 *   This file contains functions related to processing and displaying port entity data.
 *
 * @package        observium
 * @subpackage     functions
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

/**
 * Build ports WHERE array
 *
 * This function returns an array of "WHERE" statements from a $vars array.
 * The returned array can be implode()d and used on the ports table.
 * Originally extracted from the /ports/ page
 *
 * @param array $vars
 * @return array
 */
function build_ports_where_array($vars)
{
  $where = array();

  foreach($vars as $var => $value)
  {
    if ($value != '')
    {
      switch ($var)
      {
        case 'location':
          $where[] = generate_query_values($value, $var);
          break;
        case 'device_id':
          $where[] = generate_query_values($value, 'ports.device_id');
          break;
        case 'group':
          $values  = get_group_entities($value);
          $where[] = generate_query_values($values, 'ports.port_id');
          break;
        case 'disable':
          $var = 'disabled';
        case 'disabled':    // FIXME. 'disabled' column never used in ports..
        case 'deleted':
        case 'ignore':
        case 'ifSpeed':
        case 'ifType':
          $where[] = generate_query_values($value, 'ports.'.$var);
          break;
        case 'hostname':
        case 'ifAlias':
        case 'ifDescr':
          $where[] = generate_query_values($value, $var, '%LIKE%');
          break;
        case 'port_descr_type':
          $where[] = generate_query_values($value, $var, 'LIKE');
          break;
        case 'errors':
          if ($value == 1 || $value == "yes")
          {
            $where[] = " AND (`ifInErrors_delta` > '0' OR `ifOutErrors_delta` > '0')";
          }
          break;
        case 'alerted':
          if ($value == "yes")
          {
            $where[] = ' AND `ifAdminStatus` = "up" AND (`ifOperStatus` = "lowerLayerDown" OR `ifOperStatus` = "down")';
          }
        case 'state':
          if ($value == "down")
          {
            $where[] = 'AND `ifAdminStatus` = "up" AND (`ifOperStatus` = "lowerLayerDown" OR `ifOperStatus` = "down")';
          }
          else if ($value == "up")
          {
            $where[] = 'AND `ifAdminStatus` = "up" AND ( `ifOperStatus` = "up" OR `ifOperStatus` = "monitoring" )';
          }
          else if ($value == "admindown")
          {
            $where[] = 'AND `ifAdminStatus` = "down"';
          }
          break;
        case 'cbqos':
          if ($value && $value != 'no')
          {
            $where[] = generate_query_values($GLOBALS['cache']['ports']['cbqos'], 'ports.port_id');
          }
          break;
      }
    }
  }

  return $where;
}

/**
 * Returns a string containing an HTML table to be used in popups for the port entity type
 *
 * @param array $port array
 *
 * @return string Table containing port header for popups
 */
function generate_port_popup_header($port)
{
  // Push through processing function to set attributes
  humanize_port($port);

      $contents .= generate_box_open();
      $contents .= '<table class="'. OBS_CLASS_TABLE .'">
        <tr class="' . $port['row_class'] . '" style="font-size: 10pt;">
          <td class="state-marker"></td>
          <td style="width: 10px;"></td>
          <td style="width: 250px;"><a href="#" class="' . $port['html_class'] . '" style="font-size: 15px; font-weight: bold;">' . $port['port_label'] . '</a><br />' . escape_html($port['ifAlias']) . '</td>
          <td style="width: 100px;">' . $port['human_speed'] . '<br />' . $port['ifMtu'] . '</td>
          <td>' . $port['human_type'] . '<br />' . $port['human_mac'] . '</td>
        </tr>
          </table>';
     $contents .= generate_box_close();

  return $contents;
}

/**
 * Returns a string containing an HTML to be used as a port popups
 *
 * @param array $port array
 * @param string $text to be used as port label
 * @param string $type graph type to be used in graphs (bits, nupkts, etc)
 *
 * @return string HTML port popup contents
 */
function generate_port_popup($port, $text = NULL, $type = NULL)
{
  $time = $GLOBALS['config']['time'];

  if (!isset($port['os']))
  {
    $port = array_merge($port, device_by_id_cache($port['device_id']));
  }

  humanize_port($port);

  if (!$text)
  {
    $text = rewrite_ifname($port['port_label']);
  }
  if ($type)
  {
    $port['graph_type'] = $type;
  }
  if (!isset($port['graph_type']))
  {
    $port['graph_type'] = 'port_bits';
  }

  if (!isset($port['os']))
  {
    $port = array_merge($port, device_by_id_cache($port['device_id']));
  }

  $content  = generate_device_popup_header($port);
  $content .= generate_port_popup_header($port);

  $content .= '<div style="width: 700px">';
  //$content .= generate_box_open(array('body-style' => 'width: 700px;'));
  $graph_array['type'] = $port['graph_type'];
  $graph_array['legend'] = "yes";
  $graph_array['height'] = "100";
  $graph_array['width'] = "275";
  $graph_array['to'] = $time['now'];
  $graph_array['from'] = $time['day'];
  $graph_array['id'] = $port['port_id'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from'] = $time['week'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from'] = $time['month'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from'] = $time['year'];
  $content .= generate_graph_tag($graph_array);
  $content .= "</div>";
  //$content .= generate_box_close();

  return $content;
}

/**
 * Returns an HTML port page link with mouse-over popup to permitted users or a text label to non-permitted users
 *
 * @param array $port array
 * @param string $text text to be used as port label
 * @param string $type graph type to be used in graphs (bits, nupkts, etc)
 *
 * @return string HTML link or text string
 */
function generate_port_link($port, $text = NULL, $type = NULL, $escape = FALSE, $short = FALSE)
{
  humanize_port($port);

  //if (!isset($port['html_class'])) { $port['html_class'] = ifclass($port['ifOperStatus'], $port['ifAdminStatus']); }
  //if (!isset($text)) { $text = rewrite_ifname($port['port_label'], !$escape); } // Negative escape flag for exclude double escape

  // Fixme -- does this function even need alternative $text? I think not. It's a hangover from before label.
  if (!isset($text) && !$short)
  {
    $text = $port['port_label'];
  } elseif (!isset($text) && $short)
  {
    $text = $port['port_label_short'];
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

/**
 * Returns a string containing a page URL built from a $port array and an array of optional variables
 *
 * @param array $port array
 * @param array optional variables used when building the URL
 *
 * @return string port page URL
 */
function generate_port_url($port, $vars = array())
{
  return generate_url(array('page' => 'device', 'device' => $port['device_id'], 'tab' => 'port', 'port' => $port['port_id']), $vars);
}

/**
 * Returns or echos a port graph thumbnail
 *
 * @param array $args of arguments used to build the graph image tag URL
 * @param boolean $echo variable defining wether output should be returned or echoed
 *
 * @return string HTML port popup contents
 */
function generate_port_thumbnail($args, $echo = TRUE)
{
  if (!$args['bg'])
  {
    $args['bg'] = "FFFFFF";
  }

  $graph_array           = array();
  $graph_array['from']   = $args['from'];
  $graph_array['to']     = $args['to'];
  $graph_array['id']     = $args['port_id'];
  $graph_array['type']   = $args['graph_type'];
  $graph_array['width']  = $args['width'];
  $graph_array['height'] = $args['height'];
  $graph_array['bg']     = 'FFFFFF00'; # the 00 at the end makes the area transparent.

  $mini_graph = generate_graph_tag($graph_array);

  $img = generate_port_link($args, $mini_graph);

  if ($echo)
  {
    echo($img);
  } else {
    return $img;
  }
}

function print_port_row($port, $vars = array())
{
  echo generate_port_row($port, $vars);
}

function generate_port_row($port, $vars = array())
{
  global $config, $cache;

  $device = device_by_id_cache($port['device_id']);

  humanize_port($port);

  if (!isset($vars['view'])) { $vars['view'] = "basic"; }

  // Populate $port_adsl if the port has ADSL-MIB data
  if (!isset($cache['ports_option']['ports_adsl']) || in_array($port['port_id'], $cache['ports_option']['ports_adsl']))
  {
    $port_adsl = dbFetchRow("SELECT * FROM `ports_adsl` WHERE `port_id` = ?", array($port['port_id']));
  }

  // Populate $port['tags'] with various tags to identify port statuses and features
  // Port Errors
  if ($port['ifInErrors_delta'] > 0 || $port['ifOutErrors_delta'] > 0)
  {
    $port['tags'] .= generate_port_link($port, '<span class="label label-important">Errors</span>', 'port_errors');
  }

  // Port Deleted
  if ($port['deleted'] == '1')
  {
    $port['tags'] .= '<a href="'.generate_url(array('page' => 'deleted-ports')).'"><span class="label label-important">Deleted</span></a>';
  }

  // Port CBQoS
  if (isset($cache['ports_option']['ports_cbqos']))
  {
    if (in_array($port['port_id'], $cache['ports_option']['ports_cbqos']))
    {
      $port['tags'] .= '<a href="' . generate_port_url($port, array('view' => 'cbqos')) . '"><span class="label label-info">CBQoS</span></a>';
    }
  }
  else if (dbFetchCell("SELECT COUNT(*) FROM `ports_cbqos` WHERE `port_id` = ?", array($port['port_id'])))
  {
    $port['tags'] .= '<a href="' . generate_port_url($port, array('view' => 'cbqos')) . '"><span class="label label-info">CBQoS</span></a>';
  }

  // Port MAC Accounting
  if (isset($cache['ports_option']['mac_accounting']))
  {
    if (in_array($port['port_id'], $cache['ports_option']['mac_accounting']))
    {
      $port['tags'] .= '<a href="' . generate_port_url($port, array('view' => 'macaccounting')) . '"><span class="label label-info">MAC</span></a>';
    }
  }
  else if (dbFetchCell("SELECT COUNT(*) FROM `mac_accounting` WHERE `port_id` = ?", array($port['port_id'])))
  {
    $port['tags'] .= '<a href="' . generate_port_url($port, array('view' => 'macaccounting')) . '"><span class="label label-info">MAC</span></a>';
  }

  // Populated formatted versions of port rates.
  $port['bps_in']  = formatRates($port['ifInOctets_rate'] * 8);
  $port['bps_out'] = formatRates($port['ifOutOctets_rate'] * 8);

  $port['pps_in']  = format_si($port['ifInUcastPkts_rate'])."pps";
  $port['pps_out'] = format_si($port['ifOutUcastPkts_rate'])."pps";

  $string = '';

  if ($vars['view'] == "basic" || $vars['view'] == "graphs")  // Print basic view table row
  {
    $table_cols = '8';

    $string .= '<tr class="' . $port['row_class'] . '">
            <td class="state-marker"></td>
            <td style="width: 1px;"></td>';

    if ($vars['page'] != "device" && $vars['popup'] != TRUE) // Print device name link if we're not inside the device page hierarchy.
    {
      $table_cols++; // Increment table columns by one to make sure graph line draws correctly

      $string .= '    <td style="width: 200px;"><span class="entity">' . generate_device_link($device, short_hostname($device['hostname'], "20")) . '</span><br />
                <span class="em">' . escape_html(truncate($port['location'], 32, "")) . '</span></td>';
    }

    $string .= '    <td><span class="entity">' . generate_port_link($port, rewrite_ifname($port['port_label'])) . ' ' . $port['tags'] . '</span><br />
                <span class="em">' . escape_html(truncate($port['ifAlias'], 50, '')) . '</span></td>' .

      '<td style="width: 110px;"> <i class="icon-circle-arrow-down" style="' . $port['bps_in_style'] . '"></i>  <span class="small" style="' . $port['bps_in_style'] . '">' . formatRates($port['in_rate']) . '</span><br />' .
      '<i class="icon-circle-arrow-up" style="' . $port['bps_out_style'] . '"></i> <span class="small" style="' . $port['bps_out_style'] . '">' . formatRates($port['out_rate']) . '</span><br /></td>' .

      '<td style="width: 90px;"> <i class="icon-circle-arrow-down" style="' . $port['bps_in_style'] . '"></i>  <span class="small" style="' . $port['bps_in_style'] . '">' . $port['ifInOctets_perc'] . '%</span><br />' .
      '<i class="icon-circle-arrow-up" style="' . $port['bps_out_style'] . '"></i> <span class="small" style="' . $port['bps_out_style'] . '">' . $port['ifOutOctets_perc'] . '%</span><br /></td>' .

      '<td style="width: 110px;"><i class="icon-circle-arrow-down" style="' . $port['pps_in_style'] . '"></i>  <span class="small" style="' . $port['pps_in_style'] . '">' . format_bi($port['ifInUcastPkts_rate']) . 'pps</span><br />' .
      '<i class="icon-circle-arrow-up" style="' . $port['pps_out_style'] . '"></i> <span class="small" style="' . $port['pps_out_style'] . '">' . format_bi($port['ifOutUcastPkts_rate']) . 'pps</span></td>' .

      '<td style="width: 110px;"><small>' . $port['human_speed'] . '<br />' . $port['ifMtu'] . '</small></td>
            <td ><small>' . $port['human_type'] . '<br />' . $port['human_mac'] . '</small></td>
          </tr>';
  }
  else if ($vars['view'] == "details" || $vars['view'] == "detail") // Print detailed view table row
  {
    $table_cols = '9';

    $string .= '<tr class="' . $port['row_class'] . '"';
    if ($vars['tab'] != "port") { $string .= ' onclick="location.href=\'' . generate_port_url($port) . '\'" style="cursor: pointer;"'; }
    $string .= '>';
    $string .= '         <td class="state-marker"></td>
         <td style="width: 1px;"></td>';

    if ($vars['page'] != "device" && $vars['popup'] != TRUE) // Print device name link if we're not inside the device page hierarchy.
    {
      $table_cols++; // Increment table columns by one to make sure graph line draws correctly

      $string .= '    <td width="200"><span class="entity">' . generate_device_link($device, short_hostname($device['hostname'], "20")) . '</span><br />
                <span class="em">' . escape_html(truncate($port['location'], 32, "")) . '</span></td>';
    }

    $string .= '
         <td style="min-width: 250px;">';

    $string .= '        <span class="entity-title">
              ' . generate_port_link($port) . ' '.$port['tags'].'
           </span><br /><span class="small">'.escape_html($port['ifAlias']).'</span>';

    if ($port['ifAlias']) { $string .= '<br />'; }

    unset($break);

    if (!isset($cache['ports_option']['ipv4_addresses']) || in_array($port['port_id'], $cache['ports_option']['ipv4_addresses']))
    {
      foreach (dbFetchRows("SELECT * FROM `ipv4_addresses` WHERE `port_id` = ?", array($port['port_id'])) as $ip)
      {
        $string .= $break . generate_popup_link('ip', $ip['ipv4_address'].'/'.$ip['ipv4_prefixlen'], NULL, 'small');
        $break = "<br />";
      }
    }
    if (!isset($cache['ports_option']['ipv6_addresses']) || in_array($port['port_id'], $cache['ports_option']['ipv6_addresses']))
    {
      foreach (dbFetchRows("SELECT * FROM `ipv6_addresses` WHERE `port_id` = ?", array($port['port_id'])) as $ip6)
      {
        $string .= $break . generate_popup_link('ip', $ip6['ipv6_address'].'/'.$ip6['ipv6_prefixlen'], NULL, 'small');
        $break = "<br />";
      }
    }

    //$string .= '</span>';

    $string .= '</td>';

    // Print port graph thumbnails
    $string .= '<td style="width: 147px;">';
    $port['graph_type'] = "port_bits";

    $graph_array           = array();
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $port['port_id'];
    $graph_array['type']   = $port['graph_type'];
    $graph_array['width']  = 100;
    $graph_array['height'] = 20;
    $graph_array['bg']     = 'ffffff00'; # the 00 at the end makes the area transparent.
    $graph_array['from']   = $config['time']['day'];

    $string .= generate_port_link($port, generate_graph_tag($graph_array));

    $port['graph_type'] = "port_upkts";
    $graph_array['type']   = $port['graph_type'];
    $string .= generate_port_link($port, generate_graph_tag($graph_array));

    $port['graph_type'] = "port_errors";
    $graph_array['type']   = $port['graph_type'];
    $string .= generate_port_link($port, generate_graph_tag($graph_array));

    $string .= '</td>';

    $string .= '<td style="width: 100px; white-space: nowrap;">';

    if ($port['ifOperStatus'] == "up" || $port['ifOperStatus'] == "monitoring")
    {
      // Colours generated by humanize_port
      $string .= '<i class="icon-circle-arrow-down" style="'.$port['bps_in_style']. '"></i> <span class="small" style="'.$port['bps_in_style']. '">' . formatRates($port['in_rate']) . '</span><br />
      <i class="icon-circle-arrow-up"   style="'.$port['bps_out_style'].'"></i> <span class="small" style="'.$port['bps_out_style'].'">' . formatRates($port['out_rate']). '</span><br />
      <i class="icon-circle-arrow-down" style="'.$port['pps_in_style']. '"></i> <span class="small" style="'.$port['pps_in_style']. '">' . format_bi($port['ifInUcastPkts_rate']). 'pps</span><br />
      <i class="icon-circle-arrow-up"   style="'.$port['pps_out_style'].'"></i> <span class="small" style="'.$port['pps_out_style'].'">' . format_bi($port['ifOutUcastPkts_rate']).'pps</span>';
    }

    $string .= '</td><td style="width: 110px;">';
    if ($port['ifType'] && $port['ifType'] != "") { $string .= '<span class="small">' . $port['human_type'] . '</span>'; } else { $string .= '-'; }
    $string .= '<br />';
    if ($port['ifSpeed']) { $string .= '<span class="small">'.humanspeed($port['ifSpeed']).'</span>'; }
    if ($port['ifDuplex'] && $port['ifDuplex'] != "unknown") { $string .= '<span class="small"> (' . str_replace("Duplex", "", $port['ifDuplex']) . ')</span>'; }
    $string .= '<br />';
    if ($port['ifMtu'] && $port['ifMtu'] != "") { $string .= '<span class="small">MTU ' . $port['ifMtu'] . '</span>'; } else { $string .= '<span class="small">Unknown MTU</span>'; }
    // if ($ifHardType && $ifHardType != "") { $string .= '<span class="small">" . $ifHardType . "</span>"); } else { $string .= '-'; }

    //$string .= '<br />';

    // Set VLAN data if the port has ifTrunk populated
    if ($port['ifTrunk'])
    {
      if ($port['ifVlan'])
      {
        // Native VLAN
        if (!isset($cache['ports_vlan']))
        {
          $native_state = dbFetchCell('SELECT `state` FROM `ports_vlans` WHERE `device_id` = ? AND `port_id` = ?',    array($device['device_id'], $port['port_id']));
          $native_name  = dbFetchCell('SELECT `vlan_name` FROM vlans     WHERE `device_id` = ? AND `vlan_vlan` = ?;', array($device['device_id'], $port['ifVlan']));
        } else {
          $native_state = $cache['ports_vlan'][$port['port_id']][$port['ifVlan']]['state'];
          $native_name  = $cache['ports_vlan'][$port['port_id']][$port['ifVlan']]['vlan_name'];
        }
        switch ($native_state)
        {
          case 'blocking':   $class = 'text-error';   break;
          case 'forwarding': $class = 'text-success'; break;
          default:           $class = 'muted';
        }
        if (empty($native_name)) { $native_name = 'VLAN'.str_pad($port['ifVlan'], 4, '0', STR_PAD_LEFT); }
        $native_tooltip = 'NATIVE: <strong class='.$class.'>'.$port['ifVlan'].' ['.$native_name.']</strong><br />';
      }

      if (!isset($cache['ports_vlan']))
      {
        $vlans = dbFetchRows('SELECT * FROM `ports_vlans` AS PV
                         LEFT JOIN vlans AS V ON PV.`vlan` = V.`vlan_vlan` AND PV.`device_id` = V.`device_id`
                         WHERE PV.`port_id` = ? AND PV.`device_id` = ? ORDER BY PV.`vlan`;', array($port['port_id'], $device['device_id']));
      } else {
        $vlans = $cache['ports_vlan'][$port['port_id']];
      }
      $vlans_count = count($vlans);
      $rel = ($vlans_count || $native_tooltip) ? 'tooltip' : ''; // Hide tooltip for empty
      $string .= '<p class="small"><a class="label label-info" data-rel="'.$rel.'" data-tooltip="<div class=\'small\' style=\'max-width: 320px; text-align: justify;\'>'.$native_tooltip;
      if ($vlans_count)
      {
        $string .= 'ALLOWED: ';
        $vlans_aggr = array();
        foreach ($vlans as $vlan)
        {
          if ($vlans_count > 20)
          {
            // Aggregate VLANs
            $vlans_aggr[] = $vlan['vlan'];
          } else {
            // List VLANs
            switch ($vlan['state'])
            {
              case 'blocking':   $class = 'text-error'; break;
              case 'forwarding': $class = 'text-success';  break;
              default:           $class = 'muted';
            }
            if (empty($vlan['vlan_name'])) { 'VLAN'.str_pad($vlan['vlan'], 4, '0', STR_PAD_LEFT); }
            $string .= '<strong class='.$class.'>'.$vlan['vlan'] .' ['.$vlan['vlan_name'].']</strong><br />';
          }
        }
        if ($vlans_count > 20)
        {
          // End aggregate VLANs
          $string .= '<strong>'.range_to_list($vlans_aggr, ', ').'</strong>';
        }
      }
      $string .= '</div>">'.$port['ifTrunk'].'</a></p>';
    }
    else if ($port['ifVlan'])
    {
      if (!isset($cache['ports_vlan']))
      {
        $native_state = dbFetchCell('SELECT `state` FROM `ports_vlans` WHERE `device_id` = ? AND `port_id` = ?',    array($device['device_id'], $port['port_id']));
        $native_name  = dbFetchCell('SELECT `vlan_name` FROM vlans     WHERE `device_id` = ? AND `vlan_vlan` = ?;', array($device['device_id'], $port['ifVlan']));
      } else {
        $native_state = $cache['ports_vlan'][$port['port_id']][$port['ifVlan']]['state'];
        $native_name  = $cache['ports_vlan'][$port['port_id']][$port['ifVlan']]['vlan_name'];
      }
      switch ($vlan_state)
      {
        case 'blocking':   $class = 'label-error';   break;
        case 'forwarding': $class = 'label-success'; break;
        default:           $class = '';
      }
      $rel = ($native_name) ? 'tooltip' : ''; // Hide tooltip for empty
      $string .= '<br /><span data-rel="'.$rel.'" class="label '.$class.'"  data-tooltip="<strong class=\'small '.$class.'\'>'.$port['ifVlan'].' ['.$native_name.']</strong>">VLAN ' . $port['ifVlan'] . '</span>';
    }
    else if ($port['ifVrf']) // Print the VRF name if the port is assigned to a VRF
    {
      $vrf_name = dbFetchCell("SELECT `vrf_name` FROM `vrfs` WHERE `vrf_id` = ?", array($port['ifVrf']));
      $string .= '<br /><span class="small badge badge-success" data-rel="tooltip" data-tooltip="VRF">'.$vrf_name.'</span>';
    }

    $string .= '</td>';

    // If the port is ADSL, print ADSL port data.
    if ($port_adsl['adslLineCoding'])
    {
      $string .= '<td style="width: 200px;"><span class="small">';
      $string .= '<span class="label">'.$port_adsl['adslLineCoding'].'</span> <span class="label">' . rewrite_adslLineType($port_adsl['adslLineType']).'</span>';
      $string .= '<br />';
      $string .= 'SYN <i class="icon-circle-arrow-down green"></i> '.formatRates($port_adsl['adslAtucChanCurrTxRate']) . ' <i class="icon-circle-arrow-up blue"></i> '. formatRates($port_adsl['adslAturChanCurrTxRate']);
      $string .= '<br />';
      //$string .= 'Max:'.formatRates($port_adsl['adslAtucCurrAttainableRate']) . '/'. formatRates($port_adsl['adslAturCurrAttainableRate']);
      //$string .= '<br />';
      $string .= 'ATN <i class="icon-circle-arrow-down green"></i> '.$port_adsl['adslAtucCurrAtn'] . 'dBm <i class="icon-circle-arrow-up blue"></i> '. $port_adsl['adslAturCurrAtn'] . 'dBm';
      $string .= '<br />';
      $string .= 'SNR <i class="icon-circle-arrow-down green"></i> '.$port_adsl['adslAtucCurrSnrMgn'] . 'dB <i class="icon-circle-arrow-up blue"></i> '. $port_adsl['adslAturCurrSnrMgn']. 'dB';
      $string .= '</span>';
    } else {
      // Otherwise print normal port data
      $string .= '<td style="width: 150px;"><span class="small">';
      if ($port['ifPhysAddress'] && $port['ifPhysAddress'] != "") { $string .= $port['human_mac']; } else { $string .= '-'; }
      $string .= '<br />' . $port['ifLastChange'] . '</span>';
    }

    $string .= '</td>';
    $string .= '<td style="min-width: 200px" class="small">';


    if (strpos($port['port_label'], "oopback") === FALSE && !$graph_type)
    {
      unset($br);

      // Populate links array for ports with direct links
      if (!isset($cache['ports_option']['neighbours']) || in_array($port['port_id'], $cache['ports_option']['neighbours']))
      {
        foreach (dbFetchRows('SELECT * FROM `neighbours` WHERE `port_id` = ?', array($port['port_id'])) as $neighbour)
        {
          // print_r($link);
          if ($neighbour['remote_port_id']) {
            $int_links[$neighbour['remote_port_id']] = $neighbour['remote_port_id'];
            $int_links_phys[$neighbour['remote_port_id']] = 1;
          } else {
            $int_links_unknown[] = $neighbour;
          }
        }
      } else {  }

      // Populate links array for devices which share an IPv4 subnet
      if (!isset($cache['ports_option']['ipv4_addresses']) || in_array($port['port_id'], $cache['ports_option']['ipv4_addresses']))
      {
        foreach (dbFetchColumn('SELECT DISTINCT(`ipv4_network_id`) FROM `ipv4_addresses`
                                 LEFT JOIN `ipv4_networks` USING(`ipv4_network_id`)
                                 WHERE `port_id` = ? AND `ipv4_network` NOT IN (?);', array($port['port_id'], $config['ignore_common_subnet'])) as $network_id)
        {
          $sql = 'SELECT N.*, P.`port_id`, P.`device_id` FROM `ipv4_addresses` AS A, `ipv4_networks` AS N, `ports` AS P
                   WHERE A.`port_id` = P.`port_id` AND P.`device_id` != ?
                   AND A.`ipv4_network_id` = ? AND N.`ipv4_network_id` = A.`ipv4_network_id`
                   AND P.`ifAdminStatus` = "up"';

          $params = array($device['device_id'], $network_id);

          foreach (dbFetchRows($sql, $params) as $new)
          {
            if ($cache['devices']['id'][$new['device_id']]['disabled'] && !$config['web_show_disabled']) { continue; }
            $int_links[$new['port_id']] = $new['port_id'];
            $int_links_v4[$new['port_id']][] = $new['ipv4_network'];
          }
        }
      }

      // Populate links array for devices which share an IPv6 subnet
      if (!isset($cache['ports_option']['ipv6_addresses']) || in_array($port['port_id'], $cache['ports_option']['ipv6_addresses']))
      {
        foreach (dbFetchColumn("SELECT DISTINCT(`ipv6_network_id`) FROM `ipv6_addresses`
                                 LEFT JOIN `ipv6_networks` USING(`ipv6_network_id`)
                                 WHERE `port_id` = ? AND `ipv6_network` NOT IN (?);", array($port['port_id'], $config['ignore_common_subnet'])) as $network_id)
        {
          $sql = "SELECT P.`port_id`, P.`device_id` FROM `ipv6_addresses` AS A, `ipv6_networks` AS N, `ports` AS P
                   WHERE A.`port_id` = P.`port_id` AND P.device_id != ?
                   AND A.`ipv6_network_id` = ? AND N.`ipv6_network_id` = A.`ipv6_network_id`
                   AND P.`ifAdminStatus` = 'up' AND A.`ipv6_origin` != 'linklayer' AND A.`ipv6_origin` != 'wellknown'";

          $params = array($device['device_id'], $network_id);

          foreach (dbFetchRows($sql, $params) as $new)
          {
            if ($cache['devices']['id'][$new['device_id']]['disabled'] && !$config['web_show_disabled']) { continue; }
            $int_links[$new['port_id']] = $new['port_id'];
            $int_links_v6[$new['port_id']][] = $new['port_id'];
          }
        }
      }

      // Output contents of links array
      foreach ($int_links as $int_link)
      {
        $link_if  = get_port_by_id_cache($int_link);
        $link_dev = device_by_id_cache($link_if['device_id']);
        $string .= $br;

        if ($int_links_phys[$int_link]) { $string .= '<a data-alt="Directly connected" class="oicon-connect"></a> '; }
        else { $string .= '<a data-alt="Same subnet" class="oicon-network-hub"></a> '; }

        $string .= '<b>' . generate_port_link($link_if, $link_if['port_label_short']) . ' on ' . generate_device_link($link_dev, short_hostname($link_dev['hostname'])) . '</b>';

        ## FIXME -- do something fancy here.

        if ($int_links_v6[$int_link]) { $string .= '&nbsp;'.overlib_link('', '<span class="label label-success">IPv6</span>', implode("<br />", $int_links_v6[$int_link]), NULL); }
        if ($int_links_v4[$int_link]) { $string .= '&nbsp;'.overlib_link('', '<span class="label label-info">IPv4</span>', implode("<br />", $int_links_v4[$int_link]), NULL); }
        $br = "<br />";
      }

      // Output content of unknown links array (where ports don't exist in our database, or they weren't matched)

      foreach ($int_links_unknown as $int_link)
      {
        // FIXME -- Expose platform and version here.
        $string .= '<a data-alt="Directly connected" class="oicon-plug-connect"></a> ';
        $string .= '<b><i>'.short_ifname($int_link['remote_port']).'</i></b> on ';

        $string .= '<i><b>'.generate_tooltip_link(NULL, $int_link['remote_hostname'], '<div class="small" style="max-width: 500px;"><b>'.$int_link['remote_platform'].'</b><br />'.$int_link['remote_version'].'</div>').'</b></i>';
        $string .= '<br />';
      }
    }

    if (!isset($cache['ports_option']['pseudowires']) || in_array($port['port_id'], $cache['ports_option']['pseudowires']))
    {
      foreach (dbFetchRows("SELECT * FROM `pseudowires` WHERE `port_id` = ?", array($port['port_id'])) as $pseudowire)
      {
        //`port_id`,`peer_device_id`,`peer_ldp_id`,`pwID`,`pwIndex`
        #    $pw_peer_dev = dbFetchRow("SELECT * FROM `devices` WHERE `device_id` = ?", array($pseudowire['peer_device_id']));
        $pw_peer_int = dbFetchRow("SELECT * FROM `ports` AS I, `pseudowires` AS P WHERE I.`device_id` = ? AND P.`pwID` = ? AND P.`port_id` = I.`port_id`", array($pseudowire['peer_device_id'], $pseudowire['pwID']));

        #    $pw_peer_int = get_port_by_id_cache($pseudowire['peer_device_id']);
        $pw_peer_dev = device_by_id_cache($pseudowire['peer_device_id']);

        if (is_array($pw_peer_int))
        {
          humanize_port($pw_peer_int);
          $string .= $br.'<i class="oicon-arrow-switch"></i> <strong>' . generate_port_link($pw_peer_int, $pw_peer_int['port_label_short']) .' on '. generate_device_link($pw_peer_dev, short_hostname($pw_peer_dev['hostname'])) . '</strong>';
        } else {
          $string .= $br.'<i class="oicon-arrow-switch"></i> <strong> VC ' . $pseudowire['pwID'] .' on '. $pseudowire['peer_addr'] . '</strong>';
        }
        $string .= ' <span class="label">'.$pseudowire['pwPsnType'].'</span>';
        $string .= ' <span class="label">'.$pseudowire['pwType'].'</span>';
        $br = "<br />";
      }
    }

    if (!isset($cache['ports_option']['ports_pagp']) || in_array($port['ifIndex'], $cache['ports_option']['ports_pagp']))
    {
      foreach (dbFetchRows("SELECT * FROM `ports` WHERE `pagpGroupIfIndex` = ? AND `device_id` = ?", array($port['ifIndex'], $device['device_id'])) as $member)
      {
        humanize_port($member);
        $pagp[$device['device_id']][$port['ifIndex']][$member['ifIndex']] = TRUE;
        $string .= $br.'<i class="oicon-arrow-join"></i> <strong>' . generate_port_link($member) . ' [PAgP]</strong>';
        $br = "<br />";
      }
    }

    if ($port['pagpGroupIfIndex'] && $port['pagpGroupIfIndex'] != $port['ifIndex'])
    {
      $pagp[$device['device_id']][$port['pagpGroupIfIndex']][$port['ifIndex']] = TRUE;
      $parent = dbFetchRow("SELECT * FROM `ports` WHERE `ifIndex` = ? and `device_id` = ?", array($port['pagpGroupIfIndex'], $device['device_id']));
      humanize_port($parent);
      $string .= $br.'<i class="oicon-arrow-split"></i> <strong>' . generate_port_link($parent) . ' [PAgP]</strong>';
      $br = "<br />";
    }

    if (!isset($cache['ports_option']['ports_stack_low']) || in_array($port['ifIndex'], $cache['ports_option']['ports_stack_low']))
    {
      foreach (dbFetchRows("SELECT * FROM `ports_stack` WHERE `port_id_low` = ? and `device_id` = ?", array($port['ifIndex'], $device['device_id'])) as $higher_if)
      {
        if ($higher_if['port_id_high'])
        {
          if ($pagp[$device['device_id']][$higher_if['port_id_high']][$port['ifIndex']]) { continue; } // Skip if same PAgP port
          $this_port = get_port_by_index_cache($device['device_id'], $higher_if['port_id_high']);
          if (is_array($this_port))
          {
            $string .= $br.'<i class="oicon-arrow-split"></i> <strong>' . generate_port_link($this_port) . '</strong>';
            $br = "<br />";
          }
        }
      }
    }

    if (!isset($cache['ports_option']['ports_stack_high']) || in_array($port['ifIndex'], $cache['ports_option']['ports_stack_high']))
    {
      foreach (dbFetchRows("SELECT * FROM `ports_stack` WHERE `port_id_high` = ? and `device_id` = ?", array($port['ifIndex'], $device['device_id'])) as $lower_if)
      {
        if ($lower_if['port_id_low'])
        {
          if ($pagp[$device['device_id']][$port['ifIndex']][$lower_if['port_id_low']]) { continue; } // Skip if same PAgP ports
          $this_port = get_port_by_index_cache($device['device_id'], $lower_if['port_id_low']);
          if (is_array($this_port))
          {
            $string .= $br.'<i class="oicon-arrow-join"></i> <strong>' . generate_port_link($this_port) . '</strong>';
            $br = "<br />";
          }
        }
      }
    }

    unset($int_links, $int_links_v6, $int_links_v4, $int_links_phys, $br);

    $string .= '</td></tr>';
  } // End Detailed View

  // If we're showing graphs, generate the graph and print the img tags

  if ($vars['graph'] == "etherlike")
  {
    $graph_file = get_port_rrdfilename($port, "dot3", TRUE);
  } else {
    $graph_file = get_port_rrdfilename($port, NULL, TRUE);
  }

  if ($vars['graph'] && is_file($graph_file))
  {

    $string .= '<tr><td colspan="'.$table_cols.'">';

    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $port['port_id'];
    $graph_array['type']   = 'port_'.$vars['graph'];

    $string .= generate_graph_row($graph_array);

    $string .= '</td></tr>';

  }
  
  return $string;
}

// EOF
