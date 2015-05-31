<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

?>
<div class="row">
<div class="col-md-12">

<?php
unset($search, $devices_array);

//$devices_array[''] = 'All Devices';
// Select the devices only in the wifi_sessions table
foreach (dbFetchRows('SELECT `device_id` FROM `wifi_sessions` GROUP BY `device_id`;') as $data)
{
  $device_id = $data['device_id'];
  if ($cache['devices']['id'][$device_id]['hostname'])
  {
    $devices_array[$device_id] = $cache['devices']['id'][$device_id]['hostname'];
  }
}
natcasesort($devices_array);
//Device field
$search[] = array('type'    => 'multiselect',
                  'name'    => 'Device',
                  'id'      => 'device_id',
                  'width'   => '130px',
                  'value'   => $vars['device_id'],
                  'values'  => $devices_array);
//Search by field
$search[] = array('type'    => 'select',
                  'name'    => 'Search By',
                  'id'      => 'searchby',
                  'width'   => '120px',
                  'value'   => $vars['searchby'],
                  'values'  => array('mac' => 'MAC Address', 'ip' => 'IP Address', 'username' => 'Username'));
//Address field
$search[] = array('type'    => 'text',
                  'name'    => 'Address',
                  'id'      => 'address',
                  'width'   => '120px',
                  'value'   => $vars['address']);

print_search($search, '802.1x', NULL, 'search/search=dot1x/');

// Pagination
$vars['pagination'] = TRUE;

print_dot1xtable($vars);

$pagetitle[] = '.1x Session Search';

?>

  </div> <!-- col-md-12 -->
</div> <!-- row -->
