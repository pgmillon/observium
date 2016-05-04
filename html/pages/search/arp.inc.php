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

$where = ' WHERE 1 ';
$where .= generate_query_permitted(array('port'), array('port_table' => 'M'));

$devices_array = array();
//$devices_array[''] = 'All Devices';
// Select the devices only with ARP/NDP tables
foreach (dbFetchRows('SELECT `device_id` FROM `ip_mac` AS M
                     LEFT JOIN `ports` AS I ON I.`port_id` = M.`port_id`' .
                     $where . 'GROUP BY `device_id`;') as $data)
{
  $device_id = $data['device_id'];
  if ($cache['devices']['id'][$device_id]['hostname'])
  {
    $devices_array[$device_id] = $cache['devices']['id'][$device_id]['hostname'];
  }
}
natcasesort($devices_array);

$search = array();
//Device field
$search[] = array('type'    => 'multiselect',
                  'name'    => 'Device',
                  'id'      => 'device_id',
                  'width'   => '130px',
                  'value'   => $vars['device_id'],
                  'values'  => $devices_array);
//IP version field
$search[] = array('type'    => 'select',
                  'name'    => 'IP',
                  'id'      => 'ip_version',
                  'width'   => '120px',
                  'value'   => $vars['ip_version'],
                  'values'  => array('' => 'IPv4 & IPv6', '4' => 'IPv4 only', '6' => 'IPv6 only'));
//Search by field
$search[] = array('type'    => 'select',
                  'title'   => 'Search By',
                  'id'      => 'searchby',
                  'width'   => '120px',
                  'onchange' => "$('#address').prop('placeholder', $('#searchby option:selected').text())",
                  'value'   => $vars['searchby'],
                  'values'  => array('mac' => 'MAC Address', 'ip' => 'IP Address'));
//Address field
$search[] = array('type'    => 'text',
                  'name'    => ($vars['searchby'] == 'ip' ? 'IP Address' : 'MAC Address'),
                  'id'      => 'address',
                  'placeholder' => TRUE,
                  'submit_by_key' => TRUE,
                  'width'   => '200px',
                  'value'   => $vars['address']);

print_search($search, 'ARP/NDP', NULL, 'search/search=arp/');

// Pagination
$vars['pagination'] = TRUE;

print_arptable($vars);

$page_title[] = 'ARP/NDP Search';

?>

  </div> <!-- col-md-12 -->
</div> <!-- row -->

<?php

// EOF
