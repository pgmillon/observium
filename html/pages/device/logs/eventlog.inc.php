<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2014, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

unset($search, $types);

//Message field
$search[] = array('type'    => 'text',
                  'name'    => 'Message',
                  'id'      => 'message',
                  'value'   => $vars['message']);
//Type field
//$types[''] = 'All Types';
$types['system'] = 'System';
foreach (dbFetchRows('SELECT `type` FROM `eventlog` WHERE `device_id` = ? GROUP BY `type` ORDER BY `type`', array($vars['device'])) as $data)
{
  $type = $data['type'];
  $types[$type] = ucfirst($type);
}
$search[] = array('type'    => 'multiselect',
                  'name'    => 'Types',
                  'id'      => 'type',
                  //'width'   => '130px',
                  'value'   => $vars['type'],
                  'values'  => $types);
$search[] = array('type'    => 'newline',
                  'hr'      => TRUE);
$search[] = array('type'    => 'datetime',
                  'id'      => 'timestamp',
                  'presets' => TRUE,
                  'min'     => dbFetchCell('SELECT MIN(`timestamp`) FROM `eventlog` WHERE `device_id` = ?', array($vars['device'])),
                  'max'     => dbFetchCell('SELECT MAX(`timestamp`) FROM `eventlog` WHERE `device_id` = ?', array($vars['device'])),
                  'from'    => $vars['timestamp_from'],
                  'to'      => $vars['timestamp_to']);

print_search($search, 'Eventlog');

/// Pagination
$vars['pagination'] = TRUE;

print_events($vars);

$pagetitle[] = "Events";

// EOF
