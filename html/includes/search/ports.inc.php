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

/// SEARCH PORTS
$results = dbFetchRows("SELECT * FROM `ports`
                        LEFT JOIN `devices` USING (`device_id`)
                        WHERE (`ifAlias` LIKE ? OR `ifDescr` LIKE ?) $query_permitted_port
                        ORDER BY `ifDescr` LIMIT $query_limit", array($query_param, $query_param));

if (count($results))
{
  foreach ($results as $result)
  {
    humanize_port($result);

    $name = rewrite_ifname($result['ifDescr']);
    if (strlen($name) > 35) { $name = substr($name, 0, 35) . "..."; }
    $description = $result['ifAlias'];
    if (strlen($description) > 80) { $description = substr($description, 0, 80) . "..."; }
    $type = rewrite_iftype($result['ifType']);
    if ($description) { $type .= ' | '; }

    $port_search_results[] = array(
      'url'  => generate_port_url($result),
      'name' => $name,
      'colour' => $result['table_tab_colour'],
      'icon' => '<img src="images/icons/'.$result['icon'].'.png" />',
      'data' => array(
        escape_html($result['hostname']),
        $type . highlight_search(escape_html($description))),
    );
  }

  $search_results['ports'] = array('descr' => 'Ports found', 'results' => $port_search_results);
 }

// EOF
