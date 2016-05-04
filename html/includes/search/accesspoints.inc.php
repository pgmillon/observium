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

/// SEARCH ACCESSPOINTS
$results = dbFetchRows("SELECT * FROM `wifi_accesspoints`
                        WHERE `name` LIKE ? $query_permitted_device
                        ORDER BY `name` LIMIT $query_limit", array($query_param));

if (count($results))
{
  foreach ($results as $result)
  {
    $name = $result['name'];
    if (strlen($name) > 35) { $name = substr($name, 0, 35) . "..."; }

    /// FIXME: once we have alerting, colour this to the sensor's status
    $tab_colour = '#194B7F'; // FIXME: This colour pulled from functions.inc.php humanize_device, maybe set it centrally in definitions?

    $ap_search_results[] = array(
      'url'    => generate_url(array('page' => 'device', 'device' => $result['device_id'], 'tab' => 'wifi', 'view' => 'accesspoint', 'accesspoint' => $result['wifi_accesspoint_id'])),
      'name'   => $name,
      'colour' => $tab_colour,
      'icon'   => '<img src="images/icons/wifi.png" />',
      'data'   => array(
        $result['hostname'],
        escape_html($result['location']) . ' | Access point'),
    );

  }

  $search_results['accesspoints'] = array('descr' => 'APs found', 'results' => $ap_search_results);
}

// EOF
