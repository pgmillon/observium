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

unset($search);

if (!is_array($alert_rules)) { $alert_rules = cache_alert_rules(); }

// Check Field
foreach (dbFetchRows('SELECT `alert_test_id` FROM `alert_log` WHERE `device_id` = ? ' .
                     'GROUP BY `alert_test_id`', array($vars['device'])) as $data)
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
                  'width'   => '125px',
                  'value'   => $vars['alert_test_id'],
                  'values'  => $alert_test_array);

#ALERT_NOTIFY,FAIL,FAIL_DELAYED,FAIL_SUPPRESSED,OK,RECOVER_NOTIFY,RECOVER_SUPPRESSED

// Status Field
foreach (array('ALERT_NOTIFY','FAIL','FAIL_DELAYED','FAIL_SUPPRESSED','OK','RECOVER_NOTIFY') AS $status_type)
{
  $status_types[$status_type] = $status_type;
}
$search[] = array('type'    => 'multiselect',
                  'name'    => 'Status Type',
                  'id'      => 'log_type',
                  'width'   => '125px',
//                  'subtext' => TRUE,
                  'value'   => $vars['log_type'],
                  'values'  => $status_types);

//Message field
#$search[] = array('type'    => 'text',
#                  'name'    => 'Message',
#                  'id'      => 'message',
#                  'width'   => '130px',
#                  'value'   => $vars['message']);

// $search[] = array('type'    => 'newline',
//                  'hr'      => TRUE);
$search[] = array('type'    => 'datetime',
                  'id'      => 'timestamp',
                  'presets' => TRUE,
                  'min'     => dbFetchCell('SELECT `timestamp` FROM `alert_log` WHERE `device_id` = ? ORDER BY `timestamp` LIMIT 0,1;', array($vars['device'])),
                  'max'     => dbFetchCell('SELECT `timestamp` FROM `alert_log` WHERE `device_id` = ? ORDER BY `timestamp` DESC LIMIT 0,1;', array($vars['device'])),
                  'from'    => $vars['timestamp_from'],
                  'to'      => $vars['timestamp_to']);

print_search($search, 'Alert log', 'search');

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
