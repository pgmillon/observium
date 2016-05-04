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

?>
<div class="row">
<div class="col-md-12">

<?php
unset($search, $devices_array, $parts);

$where = ' WHERE 1 ';
$where .= generate_query_permitted(array('device'), array('device_table' => 'E'));

// Select devices only with Inventory parts
foreach (dbFetchRows('SELECT E.`device_id` AS `device_id`, `hostname`, `entPhysicalModelName`
                     FROM `entPhysical` AS E
                     INNER JOIN `devices` AS D ON D.`device_id` = E.`device_id`' . $where .
                    'GROUP BY `device_id`, `entPhysicalModelName`;') as $data)
{
  $device_id = $data['device_id'];
  $devices_array[$device_id] = $data['hostname'];
  if ($data['entPhysicalModelName'] != '')
  {
    $parts[$data['entPhysicalModelName']] = $data['entPhysicalModelName'];
  }
}

  $where_array = build_devices_where_array($vars);
  $query_permitted = generate_query_permitted(array('device'), array('device_table' => 'devices'));
  $where = ' WHERE 1 ';
  $where .= implode('', $where_array);

  // Generate array with form elements
  $search_items = array();
  //foreach (array('os', 'hardware', 'version', 'features', 'type') as $entry)
  foreach (array('os') as $entry)
  {
    $query  = "SELECT `$entry` FROM `devices`";
    if (isset($where_array[$entry]))
    {
      $tmp = $where_array[$entry];
      unset($where_array[$entry]);
      $query .= ' WHERE 1 ' . implode('', $where_array);
      $where_array[$entry] = $tmp;
    } else {
      $query .= $where;
    }
    $query .= " AND `$entry` != '' $query_permitted GROUP BY `$entry` ORDER BY `$entry`";
    foreach (dbFetchColumn($query) as $item)
    {
      if ($entry == 'os')
      {
        $name = $config['os'][$item]['text'];
      } else {
        $name = nicecase($item);
      }
      $search_items[$entry][$item] = $name;
    }
  }

//Device field
natcasesort($devices_array);
$search[] = array('type'    => 'multiselect',
                  'width'   => '160px',
                  'name'    => 'Devices',
                  'id'      => 'device_id',
                  'value'   => $vars['device_id'],
                  'values'  => $devices_array);

// Device OS field
$search[] = array('type'    => 'multiselect',
                  'name'    => 'Select OS',
                  'width'   => '180px',
                  'id'      => 'os',
                  'value'   => $vars['os'],
                  'values'  => $search_items['os']);

//Parts field
ksort($parts);
$search[] = array('type'    => 'multiselect',
                  'width'   => '160px',
                  'name'    => 'Part Numbers',
                  'id'      => 'parts',
                  'value'   => $vars['parts'],
                  'values'  => $parts);
//Serial field
$search[] = array('type'    => 'text',
                  'width'   => '160px',
                  'name'    => 'Serial',
                  'id'      => 'serial',
                  'value'   => $vars['serial']);
//Description field
$search[] = array('type'    => 'text',
                  'width'   => '160px',
                  'name'    => 'Desc',
                  'id'      => 'description',
                  'value'   => $vars['description']);

print_search($search, 'Inventory', 'search', 'inventory/');

// Pagination
$vars['pagination'] = TRUE;

print_inventory($vars);

$page_title[] = 'Inventory';

?>

  </div> <!-- col-md-12 -->
</div> <!-- row -->

<?php

// EOF
