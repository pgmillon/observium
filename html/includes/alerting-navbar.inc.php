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

if (!is_array($alert_rules)) { $alert_rules = cache_alert_rules(); }

$navbar['class'] = 'navbar-narrow';
$navbar['brand'] = 'Alerting';

$pages = array('alerts' => 'Alerts', 'alert_checks' => 'Alert Checkers', 'alert_log' => 'Alert Logging', 'alert_maintenance' => 'Scheduled Maintenance',  'contacts' => 'Contacts');

foreach ($pages as $page_name => $page_desc)
{
  if ($vars['page'] == $page_name)
  {
    $navbar['options'][$page_name]['class'] = "active";
  }

  $navbar['options'][$page_name]['url'] = generate_url(array('page' => $page_name));
  $navbar['options'][$page_name]['text'] = escape_html($page_desc);

  if (in_array($page_name, array('alert_checks', 'alert_maintenance', 'contacts')))
  {
    $navbar['options'][$page_name]['userlevel'] = 5; // Minimum user level to display item
  }
}
$navbar['options']['alert_maintenance']['community'] = FALSE; // Not exist in Community Edition

$navbar['options']['update']['url']  = generate_url(array('page' => 'alert_regenerate', 'action'=>'update'));
$navbar['options']['update']['text'] = 'Rebuild';
$navbar['options']['update']['icon'] = 'oicon-arrow-circle';
$navbar['options']['update']['right'] = TRUE;
$navbar['options']['update']['userlevel'] = 10; // Minimum user level to display item
// We don't really need to highlight Regenerate, as it's not a display option, but an action.
// if ($vars['action'] == 'update') { $navbar['options']['update']['class'] = 'active'; }

$navbar['options']['add']['url']  = generate_url(array('page' => 'add_alert_check'));
$navbar['options']['add']['text'] = 'Add Checker';
$navbar['options']['add']['icon'] = 'oicon-plus-octagon';
$navbar['options']['add']['right'] = TRUE;
$navbar['options']['add']['userlevel'] = 10; // Minimum user level to display item

// Print out the navbar defined above
print_navbar($navbar);
unset($navbar);

// Run Actions

if ($_SESSION['userlevel'] == 10)
{
  if ($vars['submit'] == "delete_alert_checker" && $vars['confirm'] == "confirm")
  {
    // Maybe expand this to output more info.

    dbDelete('alert_tests', '`alert_test_id` = ?', array($vars['alert_test_id']));
    dbDelete('alert_table', '`alert_test_id` = ?', array($vars['alert_test_id']));
    //dbDelete('alert_table-state', '`alert_test_id` = ?', array($vars['alert_test_id']));
    dbDelete('alert_assoc', '`alert_test_id` = ?', array($vars['alert_test_id']));
    dbDelete('alert_contacts_assoc', '`alert_checker_id` = ?', array($vars['alert_test_id']));

    print_message("Deleted all traces of alert checker ".$vars['alert_test_id']);
  }
}

// EOF
