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

// Alert test display and editing page.

include($config['html_dir']."/includes/alerting-navbar.inc.php");

?>

<div class="row">
<div class="col-md-12">

<?php

unset($search, $devices_array, $priorities, $programs);

$where = ' WHERE 1 ' . generate_query_permitted();

//Device field
// Show devices only with syslog messages
foreach (dbFetchRows('SELECT `device_id` FROM `alert_log`' . $where .
                     'GROUP BY `device_id`') as $data)
{
  $device_id = $data['device_id'];
  if ($cache['devices']['id'][$device_id]['hostname'])
  {
    $devices_array[$device_id] = $cache['devices']['id'][$device_id]['hostname'];
  }
}
natcasesort($devices_array);
$search[] = array('type'    => 'multiselect',
                  'name'    => 'Devices',
                  'id'      => 'device_id',
                  'width'   => '150px',
                  'value'   => $vars['device_id'],
                  'values'  => $devices_array);

// Add device_id limit for other fields
if (isset($vars['device_id']))
{
  $where .= generate_query_values($vars['device_id'], 'device_id');
}

// Check Field
foreach (dbFetchRows('SELECT `alert_test_id` FROM `alert_log`' . $where .
                     'GROUP BY `alert_test_id`') as $data)
{
  $alert_test_id = $data['alert_test_id'];
  if (is_array($alert_rules[$alert_test_id]))
  {
    $alert_test_array[$alert_test_id] = $alert_rules[$alert_test_id]['alert_name'];
  }
}

natcasesort($alert_test_array);
$search[] = array('type'    => 'multiselect',
                  'name'    => 'Checkers',
                  'id'      => 'alert_test_id',
                  'width'   => '150px',
                  'value'   => $vars['alert_test_id'],
                  'values'  => $alert_test_array);

#ALERT_NOTIFY,FAIL,FAIL_DELAYED,FAIL_SUPPRESSED,OK,RECOVER_NOTIFY,RECOVER_SUPPRESSED

// Status Field
foreach (array('ALERT_NOTIFY','FAIL','FAIL_DELAYED','FAIL_SUPPRESSED','OK','RECOVER_NOTIFY') as $status_type)
{
  $status_types[$status_type] = $status_type;
}
$search[] = array('type'    => 'multiselect',
                  'name'    => 'Status Type',
                  'id'      => 'log_type',
                  'width'   => '150px',
//                  'subtext' => TRUE,
                  'value'   => $vars['log_type'],
                  'values'  => $status_types);

$search[] = array('type'    => 'datetime',
                  'id'      => 'timestamp',
                  'presets' => TRUE,
                  'min'     => dbFetchCell('SELECT `timestamp` FROM `alert_log`' . $where . ' ORDER BY `timestamp` LIMIT 0,1;'),
                  'max'     => dbFetchCell('SELECT `timestamp` FROM `alert_log`' . $where . ' ORDER BY `timestamp` DESC LIMIT 0,1;'),
                  'from'    => $vars['timestamp_from'],
                  'to'      => $vars['timestamp_to']);

print_search($search, 'Alert Log', 'search', 'alert_log/');

// Pagination
$vars['pagination'] = TRUE;

// Print Alert Log
print_alert_log($vars);

$page_title[] = 'Alert Log';

?>
  </div> <!-- col-md-12 -->

</div> <!-- row -->
<?php

// EOF
