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
                  'name'    => 'Device',
                  'id'      => 'device_id',
                  'value'   => $vars['device_id'],
                  'values'  => $devices_array);
//Interface field
$search[] = array('type'    => 'select',
                  'name'    => 'Interface',
                  'id'      => 'interface',
                  'width'   => '130px',
                  'value'   => $vars['interface'],
                  'values'  => array('' => 'All Interfaces', 'Lo' => 'Loopbacks', 'Vlan' => 'Vlans'));
//Networks
$search[] = array('type'    => 'text',
                  'name'    => 'IP Network',
                  'id'      => 'network',
                  'ajax'    => TRUE,
                  'ajax_vars' => array('field' => 'ipv6_network'),
                  'value'   => $vars['network']);
////IP version field
//$search[] = array('type'    => 'select',
//                  'name'    => 'IP',
//                  'id'      => 'ip_version',
//                  'width'   => '120px',
//                  'value'   => $vars['ip_version'],
//                  'values'  => array('' => 'IPv4 & IPv6', '4' => 'IPv4 only', '6' => 'IPv6 only'));
//IP address field
$search[] = array('type'    => 'text',
                  'name'    => 'IP Address',
                  'id'      => 'address',
                  'placeholder' => TRUE,
                  'submit_by_key' => TRUE,
                  'value'   => $vars['address']);

print_search($search, 'IPv6', NULL, 'search/search=ipv6/');

// Pagination
$vars['pagination'] = TRUE;

// Print addresses
print_addresses($vars);

$page_title[] = "IPv6 address search";

?>

  </div> <!-- col-md-12 -->

</div> <!-- row -->

<?php

// EOF
