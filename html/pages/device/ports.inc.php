<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

if ($vars['view'] == 'graphs' || $vars['view'] == 'minigraphs')
{
  if (isset($vars['graph'])) { $graph_type = "port_" . $vars['graph']; } else { $graph_type = "port_bits"; }
}

if (!$vars['view']) { $vars['view'] = trim($config['ports_page_default'],'/'); }

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab' => 'ports');

$filters_array = (isset($vars['filters'])) ? $vars['filters'] : array('deleted' => TRUE);
$link_array['filters'] = $filters_array;

$navbar = array('brand' => "Ports", 'class' => "navbar-narrow");

$navbar['options']['basic']['text']   = 'Basic';
$navbar['options']['details']['text'] = 'Details';
$navbar['options']['arp']['text']     = 'ARP/NDP Table';

if(dbFetchCell("SELECT COUNT(*) FROM `vlans_fdb` WHERE `device_id` = ?", array($device['device_id'])))
{
  $navbar['options']['fdb']['text'] = 'FDB Table';
}

if (dbFetchCell("SELECT COUNT(*) FROM `neighbours` LEFT JOIN `ports` USING(`port_id`) WHERE `device_id` = ?;", array($device['device_id'])))
{
  $navbar['options']['neighbours']['text'] = 'Neighbours';
  $navbar['options']['map']['text']        = 'Map';
}

if (dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE `ifType` = 'adsl' AND `device_id` = ?", array($device['device_id'])))
{
  $navbar['options']['adsl']['text'] = 'ADSL';
}

$navbar['options']['graphs']     = array('text' => 'Graphs', 'class' => 'pull-right');
$navbar['options']['minigraphs'] = array('text' => 'Minigraphs', 'class' => 'pull-right');

foreach ($navbar['options'] as $option => $array)
{
  if ($vars['view'] == $option) { $navbar['options'][$option]['class'] .= " active"; }
  $navbar['options'][$option]['url'] = generate_url($link_array,array('view' => $option));
}

foreach (array('graphs', 'minigraphs') as $type)
{
  foreach ($config['graph_types']['port'] as $option => $data)
  {
    if ($vars['view'] == $type && $vars['graph'] == $option)
    {
      $navbar['options'][$type]['suboptions'][$option]['class'] = 'active';
      $navbar['options'][$type]['text'] .= ' ('.$data['name'].')';
    }
    $navbar['options'][$type]['suboptions'][$option]['text'] = $data['name'];
    $navbar['options'][$type]['suboptions'][$option]['url'] = generate_url($link_array, array('view' => $type, 'graph' => $option));
  }
}

// Quick filters
function is_filtered()
{
  global $filters_array, $port;

  return ($filters_array['up']       && $port['ifOperStatus'] == 'up' && $port['ifAdminStatus'] == 'up' && !$port['ignore'] && !$port['deleted']) ||
         ($filters_array['down']     && $port['ifOperStatus'] != 'up' && $port['ifAdminStatus'] == 'up') ||
         ($filters_array['shutdown'] && $port['ifAdminStatus'] == 'down') ||
         ($filters_array['ignored']  && $port['ignore']) ||
         ($filters_array['deleted']  && $port['deleted']);
}

if (isset($vars['view']) && ($vars['view'] == 'basic' || $vars['view'] == 'details' || $vars['view'] == 'graphs' || $vars['view'] == 'minigraphs'))
{
  // List filters
  $filter_options = array('up'       => 'Hide UP',
                          'down'     => 'Hide DOWN',
                          'shutdown' => 'Hide SHUTDOWN',
                          'ignored'  => 'Hide IGNORED',
                          'deleted'  => 'Hide DELETED');
  // To be or not to be
  $filters_array['all'] = TRUE;
  foreach ($filter_options as $option => $text)
  {
    $filters_array['all'] = $filters_array['all'] && $filters_array[$option];
    $option_all[$option] = TRUE;
  }
  $filter_options['all'] = ($filters_array['all']) ? 'Reset ALL' : 'Hide ALL';

  // Generate filtered links
  $navbar['options_right']['filters']['text'] = 'Quick Filters';
  foreach ($filter_options as $option => $text)
  {
    $option_array = array_merge($filters_array, array($option => TRUE));
    $navbar['options_right']['filters']['suboptions'][$option]['text'] = $text;
    if ($filters_array[$option])
    {
      $navbar['options_right']['filters']['class'] .= ' active';
      $navbar['options_right']['filters']['suboptions'][$option]['class'] = 'active';
      if ($option == 'all')
      {
        $option_array = array('disabled' => FALSE);
      } else {
        $option_array[$option] = FALSE;
      }
    } elseif ($option == 'all') {
      $option_array = $option_all;
    }
    $navbar['options_right']['filters']['suboptions'][$option]['url'] = generate_url($vars, array('filters' => $option_array));
  }
}

print_navbar($navbar);
unset($navbar);

if ($vars['view'] == 'minigraphs')
{
  $timeperiods = array('-1day','-1week','-1month','-1year');
  $from = '-1day';
  echo("<div style='display: block; clear: both; margin: auto; min-height: 500px;'>");
  unset ($seperator);

  // FIXME - FIX THIS. UGLY.
  foreach (dbFetchRows("SELECT * FROM ports WHERE device_id = ? ORDER BY ifIndex", array($device['device_id'])) as $port)
  {
    if (is_filtered()) { continue; }
    echo("<div style='display: block; padding: 3px; margin: 3px; min-width: 183px; max-width:183px; min-height:90px; max-height:90px; text-align: center; float: left; background-color: #e9e9e9;'>
    <div style='font-weight: bold;'>".short_ifname($port['ifDescr'])."</div>
    <a href=\"" . generate_port_url($port) . "\" onmouseover=\"return overlib('\
    <div style=\'font-size: 16px; padding:5px; font-weight: bold; color: #e5e5e5;\'>".$device['hostname']." - ".$port['ifDescr']."</div>\
    ".$port['ifAlias']." \
    <img src=\'graph.php?type=".$graph_type."&amp;id=".$port['port_id']."&amp;from=".$from."&amp;to=".$config['time']['now']."&amp;width=450&amp;height=150\'>\
    ', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".
    "<img src='graph.php?type=".$graph_type."&amp;id=".$port['port_id']."&amp;from=".$from."&amp;to=".$config['time']['now']."&amp;width=180&amp;height=45&amp;legend=no'>
    </a>
    <div style='font-size: 9px;'>".short_port_descr($port['ifAlias'])."</div>
    </div>");
  }
  echo("</div>");
}
else if (in_array($vars['view'], array("arp", "adsl", "neighbours", "fdb", "map")))
{
  include($config['html_dir']."/pages/device/ports/".$vars['view'].".inc.php");
} else {
  if ($vars['view'] == "details") { $port_details = 1; }

  if ($vars['view'] == "graphs") { $table_class = "table-striped-two"; } else { $table_class = "table-striped"; }
  echo '<div class="box box-solid">';
  echo('<table class="table table-hover  table-condensed  '.$table_class.'">');

  echo('  <thead>');
  echo('<tr>');

  // Define column headers for the table
  $cols = array(
                'state' => NULL,
                'BLANK' => NULL,
                'port' => 'Port',
                'graphs' => NULL,
                'traffic' => 'Traffic',
                'speed' => 'Speed',
                // 'media' => 'Media',
                'mac' => 'MAC Address',
                'details' => NULL);

  foreach ($cols as $sort => $col)
  {

    if ($sort == "state")
    {
        echo('<th class="state-marker"></th>');
    }
    elseif ($col == NULL)
    {
      echo('<th></th>');
    }
    elseif ($vars['sort'] == $sort)
    {
      echo('<th>'.$col.' *</th>');
    } else {
      echo('<th><a href="'. generate_url($vars, array('sort' => $sort)).'">'.$col.'</a></th>');
    }
  }

  echo('      </tr>');
  echo('  </thead>');

  $i = "1";

  // Make the port caches available easily to this code.
  global $port_cache, $port_index_cache;

  $sql  = "SELECT *, `ports`.`port_id` as `port_id`";
  $sql .= " FROM  `ports`";
  $sql .= " LEFT JOIN `ports-state` ON  `ports`.`port_id` =  `ports-state`.`port_id`";
  $sql .= " WHERE `device_id` = ? ORDER BY `ifIndex` ASC";
  $ports = dbFetchRows($sql, array($device['device_id']));

  // Sort ports, sharing code with global ports page.
  include($config['html_dir']."/includes/port-sort.inc.php");

  // As we've dragged the whole database, lets pre-populate our caches :)
  foreach ($ports as $port)
  {
    $port_cache[$port['port_id']] = $port;
    $port_index_cache[$port['device_id']][$port['ifIndex']] = $port;
  }

  // Collect port IDs and ifIndexes who has adsl/cbqos/pagp/ip and other.
  $cache['ports_option'] = array();
  $ext_tables = array('ports_adsl', 'ports_cbqos', 'mac_accounting', 'neighbours');
  if ($port_details)
  {
    $ext_tables = array_merge($ext_tables, array('ipv4_addresses', 'ipv6_addresses', 'pseudowires'));
    // Here stored ifIndex!
    $cache['ports_option']['ports_pagp']       = dbFetchColumn("SELECT `pagpGroupIfIndex` FROM `ports`   WHERE `device_id` = ? GROUP BY `pagpGroupIfIndex`", array($device['device_id']));
    $cache['ports_option']['ports_stack_low']  = dbFetchColumn("SELECT `port_id_low`  FROM `ports_stack` WHERE `device_id` = ? AND `port_id_high` != 0 GROUP BY `port_id_low`",  array($device['device_id']));
    $cache['ports_option']['ports_stack_high'] = dbFetchColumn("SELECT `port_id_high` FROM `ports_stack` WHERE `device_id` = ? AND `port_id_low`  != 0 GROUP BY `port_id_high`", array($device['device_id']));
  }

  //$where = ' IN ('.implode(',', array_keys($port_cache)).')';
  $where = generate_query_values(array_keys($port_cache), 'port_id');
  foreach ($ext_tables as $table)
  {
    // Here stored port_id!
    $cache['ports_option'][$table] = dbFetchColumn("SELECT DISTINCT `port_id` FROM `$table` WHERE 1 " . $where);
  }

  $cache['ports_vlan'] = array(); // Cache port vlans
  foreach (dbFetchRows('SELECT * FROM `ports_vlans` AS PV LEFT JOIN vlans AS V ON PV.`vlan` = V.`vlan_vlan` AND PV.`device_id` = V.`device_id`
                       WHERE PV.`device_id` = ? ORDER BY PV.`vlan`', array($device['device_id'])) as $entry)
  {
    $cache['ports_vlan'][$entry['port_id']][$entry['vlan']] = $entry;
  }

  foreach ($ports as $port)
  {
    if (is_filtered()) { continue; }

    print_port_row($port, $vars);
  }
  echo("</table>");
  echo '</div>';
}

$page_title[] = "Ports";

unset($where, $ext_tables, $cache['ports_option'], $cache['ports_vlan']);

// EOF
