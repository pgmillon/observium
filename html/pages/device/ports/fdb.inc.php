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
unset($search, $vlans, $vlan_names, $port_names);

// Select ports only present in FDB tables
foreach (dbFetchRows('SELECT `port_id`, `device_id`, `ifDescr`, `ifName`, `ifAlias`
                        FROM `vlans_fdb` AS F
                        LEFT JOIN `ports` as P USING (`port_id`, `device_id`)
                        WHERE `device_id` = ? AND `port_id` != 0 GROUP BY `port_id`;', array($device['device_id'])) as $data)
{
  humanize_port($data);
  $port_ids[$data['port_id']] = $data['port_label'];
}
natcasesort($port_ids);
// Ports names field
$search[] = array('type'    => 'multiselect',
                  'width'   => '160px',
                  'name'    => 'Ports',
                  'id'      => 'port',
                  'value'   => $vars['port'],
                  'values'  => $port_ids);
// Select vlans only with FDB tables
foreach (dbFetchRows('SELECT `vlan_vlan`, `vlan_name`
                     FROM `vlans_fdb` AS F
                     LEFT JOIN `vlans` as V ON V.`vlan_vlan` = F.`vlan_id` AND V.`device_id` = F.`device_id`
                     WHERE F.`device_id` = ?
                     GROUP BY `vlan_vlan`', array($device['device_id'])) as $data)
{
  $vlans[$data['vlan_vlan']] = 'Vlan' . $data['vlan_vlan'];
  $vlan_names[$data['vlan_name']] = $data['vlan_name'];
}
//Vlans field
ksort($vlans);
$search[] = array('type'    => 'multiselect',
                  'name'    => 'VLANs',
                  'id'      => 'vlan_id',
                  'value'   => $vars['vlan_id'],
                  'values'  => $vlans);
//Vlan names field
natcasesort($vlan_names);
$search[] = array('type'    => 'multiselect',
                  'width'   => '160px',
                  'name'    => 'VLAN names',
                  'id'      => 'vlan_name',
                  'value'   => $vars['vlan_name'],
                  'values'  => $vlan_names);
//MAC address field
$search[] = array('type'    => 'text',
                  'name'    => 'MAC Address',
                  'id'      => 'address',
                  'value'   => $vars['address']);

print_search($search, NULL, 'search', 'device/device='.$device['device_id'].'/tab=ports/view=fdb/');

// Pagination
$vars['pagination'] = TRUE;

print_fdbtable($vars);

?>

  </div> <!-- col-md-12 -->
</div> <!-- row -->
<?php

// EOF
