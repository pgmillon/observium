<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage search
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

/// SEARCH DEVICES
$results = dbFetchRows("SELECT * FROM `devices`
                        WHERE (`hostname` LIKE ? OR `location` LIKE ?) $query_permitted_device
                        ORDER BY `hostname` LIMIT $query_limit", array($query_param, $query_param));
if (count($results))
{
  foreach ($results as $result)
  {
    humanize_device($result);

    $name = $result['hostname'];
    if (strlen($name) > 35) { $name = substr($name, 0, 35) . "..."; }

    $num_ports = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ?", array($result['device_id']));
    
    $device_search_results[] = array(
      'url'    => generate_device_url($result),
      'name'   => $name,
      'colour' => $result['html_tab_colour'], // FIXME. this colour removed from humanize_device in r6280
      'icon'   => get_device_icon($result),
      'data'   => array(
        escape_html($result['hardware'] . ' | ' . $config['os'][$result['os']]['text'] . ' ' . $result['version']),
        highlight_search(escape_html($result['location'])) . ' | ' . $num_ports . ' ports'),
    );
  }
  
  $search_results['devices'] = array('descr' => 'Devices found', 'results' => $device_search_results);
}

// EOF
