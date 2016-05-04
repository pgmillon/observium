<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage alerting
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// FIXME: This is fairly messy and crude. Feel free to improve it!

$color = ($message_tags['ALERT_STATE'] == "RECOVER" ? "good" : "danger");

// JSON data
$data = array("username" => $endpoint['username'], "channel" => $endpoint['channel'],
  //"text" => $title
  );

$data['attachments'][] = array('fallback' => $title,
                               //'pretext' => "Observium Alert Notification",
                               'title' => $title,
                               'title_link' => $message_tags['ALERT_URL'],
                               //'text' => simple_template('slack_text.tpl', $message_tags, array('is_file' => TRUE)),
                               'fields' => array(array('title' => 'Device', 'value' => $message_tags['DEVICE_HOSTNAME'] ." (".$message_tags['DEVICE_OS'].")", 'short' => TRUE),
                                                 array('title' => 'Entity', 'value' => $message_tags['ENTITY_TYPE'] ." / ". $message_tags['ENTITY_NAME'] . (isset($message_tags['ENTITY_DESCRIPTION']) ? '
'.$message_tags['ENTITY_DESCRIPTION'] : ''), 'short' => TRUE),
                                                 array('title' => 'Alert Message/Duration',  'value'  => $message_tags['ALERT_MESSAGE'].PHP_EOL.$message_tags['DURATION'], 'short' => TRUE),
                                                 array('title' => 'Metrics',  'value'  => str_replace("             ", "", $message_tags['METRICS']), 'short' => TRUE)),
                               'color' => $color);

/*
foreach ($graphs as $graph)
{
    $data['attachments'][] = array('fallback' => "Graph Image",
      'title' => $graph['label'],
      'image_url' => $graph['url'],
      'color' => 'danger');

}
*/

$data_string = json_encode($data);

// JSON + HTTP headers
$context_data = array(
  'method' => 'POST',
  'header' => "Connection: close\r\n".
    "Content-Length: ".strlen($data_string)."\r\n",
  'content'=> $data_string);

// Send out API call
$result = get_http_request($endpoint['url'], $context_data);

// Check if call succeeded
if ($result == "ok")
{
  $notify_status['success'] = TRUE;
} else {
  $notify_status['success'] = FALSE;
}

unset($data, $result, $context_data);

// EOF
