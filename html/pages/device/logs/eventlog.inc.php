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

unset($search, $types);

//Message field
$search[] = array('type'        => 'text',
                  'id'          => 'message',
                  'placeholder' => 'Message',
                  'name'        => 'Message',
                  'value'       => $vars['message']);

//Severity field
foreach (dbFetchColumn('SELECT DISTINCT `severity` FROM `eventlog` WHERE `device_id` = ?;', array($vars['device'])) as $severity)
{
  $severities[$severity] = ucfirst($config['syslog']['priorities'][$severity]['name']);
}
krsort($severities);
$search[] = array('type'    => 'multiselect',
                  'name'    => 'Severities',
                  'id'      => 'severity',
                  'width'   => '125px',
                  'subtext' => TRUE,
                  'value'   => $vars['severity'],
                  'values'  => $severities);

//Type field
$types['device'] = 'Device';
foreach (dbFetchColumn('SELECT DISTINCT `entity_type` FROM `eventlog` IGNORE INDEX (`type`) WHERE `device_id` = ?;', array($vars['device'])) as $type)
{
  $types[$type] = ucfirst($type);
}
$search[] = array('type'    => 'multiselect',
                  'name'    => 'Types',
                  'id'      => 'type',
                  'width'   => '125px',
                  'value'   => $vars['type'],
                  'values'  => $types);
//$search[] = array('type'    => 'newline',
//                  'hr'      => TRUE);
$search[] = array('type'    => 'datetime',
                  'id'      => 'timestamp',
                  'presets' => TRUE,
                  'min'     => dbFetchCell('SELECT `timestamp` FROM `eventlog` WHERE `device_id` = ? ORDER BY `timestamp` LIMIT 0,1;', array($vars['device'])),
                  'max'     => dbFetchCell('SELECT `timestamp` FROM `eventlog` WHERE `device_id` = ? ORDER BY `timestamp` DESC LIMIT 0,1;', array($vars['device'])),
                  'from'    => $vars['timestamp_from'],
                  'to'      => $vars['timestamp_to']);

print_search($search, 'Eventlog');

/// Pagination
$vars['pagination'] = TRUE;

print_events($vars);

$page_title[] = "Events";

// EOF
