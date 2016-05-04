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

?>
<div class="row">
<div class="col-md-12">

<?php
unset($search, $devices_array);

//$devices_array[''] = 'All Devices';
foreach ($cache['devices']['hostname'] as $hostname => $device_id)
{
  if ($cache['devices']['id'][$device_id]['disabled'] && !$config['web_show_disabled']) { continue; }
  $devices_array[$device_id] = $hostname;
}
//Device field
$search[] = array('type'    => 'multiselect',
                  'name'    => 'Devices',
                  'id'      => 'device_id',
                  'width'   => '160px',
                  'value'   => $vars['device_id'],
                  'values'  => $devices_array);
//Interface field
$search[] = array('type'    => 'select',
                  'name'    => 'Interface',
                  'id'      => 'interface',
                  'width'   => '160px',
                  'value'   => $vars['interface'],
                  'values'  => array('' => 'All Interfaces', 'Loopback%' => 'Loopbacks', 'Vlan%' => 'Vlans'));
//MAC address field
$search[] = array('type'    => 'text',
                  'name'    => 'MAC Address',
                  'id'      => 'address',
                  'width'   => '200px',
                  'placeholder' => TRUE,
                  'submit_by_key' => TRUE,
                  'value'   => $vars['address']);

print_search($search, 'MAC Addresses', NULL, 'search/search=mac/');

// Pagination
$vars['pagination'] = TRUE;

// Print MAC addresses
print_mac_addresses($vars);

$page_title[] = 'MAC addresses';

?>

  </div> <!-- col-md-12 -->

</div> <!-- row -->

<?php

// EOF
