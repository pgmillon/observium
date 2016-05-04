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

/*
 * For full docs see https://developer.pagerduty.com/documentation/integration/events
 */

// Unless this is a recovery, it is a new incident by default
$pagerduty_event_type = ($message_tags['ALERT_STATE'] == "RECOVER" ? "resolve" : "trigger");

// JSON data
$data_string = json_encode(array(
  "service_key"  => $endpoint['service_key'],
  "event_type"   => $pagerduty_event_type,
  "description"  => 'Conditions: '.$message_tags['CONDITIONS'],
  "incident_key" => $message_tags['ALERT_ID'],
  "client"       => $message_tags['DEVICE_HOSTNAME'],
  "client_url"   => $message_tags['ALERT_URL']));

// JSON data + HTTP headers
$context_data = array(
  'method' => 'POST',
  'header' =>
    "Connection: close\r\n".
    "Content-Type: application/json\r\n".
    "Content-Length: ".strlen($data_string)."\r\n",
  'content'=> $data_string
);

// API URL to POST to
$url = 'https://events.pagerduty.com/generic/2010-04-15/create_event.json';

// Send out API call and parse response into an associative array
$result = json_decode(get_http_request($url, $context_data), TRUE);

// Check if call succeeded
if ($result['status'] == 'success')
{
  $notify_status['success'] = TRUE;
} else {
  $notify_status['success'] = FALSE;
}

unset($result);

// EOF
