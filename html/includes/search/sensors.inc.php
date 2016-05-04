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

/// SEARCH SENSORS
$results = dbFetchRows("SELECT * FROM `sensors`
                        LEFT JOIN `devices` USING (`device_id`)
                        WHERE `sensor_descr` LIKE ? $query_permitted_device
                        ORDER BY `sensor_descr` LIMIT $query_limit", array($query_param));

if (count($results))
{
  foreach ($results as $result)
  {
    $name = $result['sensor_descr'];
    if (strlen($name) > 35) { $name = substr($name, 0, 35) . "..."; }

    /// FIXME: once we have alerting, colour this to the sensor's status
    $tab_colour = '#194B7F'; // FIXME: This colour pulled from functions.inc.php humanize_device, maybe set it centrally in definitions?

    $sensor_search_results[] = array('url' => 'graphs/type=sensor_'  . $result['sensor_class'] . '/id=' . $result['sensor_id'] . '/',
      'name' => $name, 'colour' => $tab_colour,
      'icon' => '<i class="'.$config['sensor_types'][$result['sensor_class']]['icon'].'"></i>',
      'data' => array(
        escape_html($result['hostname']),
        highlight_search(escape_html($result['location'])) . ' | ' . nicecase($result['sensor_class']).' sensor'),
    );

  }

  $search_results['sensors'] = array('descr' => 'Sensors found', 'results' => $sensor_search_results);
}

// EOF