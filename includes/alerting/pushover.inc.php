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

$message = $title . PHP_EOL;
$message .= str_replace("             ", "", $message_tags['METRICS']);

// POST data
$data_string = "token=" . urlencode($endpoint['token']) . "&user=" . urlencode($endpoint['user']) . "&message=" . urlencode($message);

// POST data and HTTP headers
$context_data = array (
  'method' => 'POST',
  'header' =>
    "Connection: close\r\n".
    "Content-Length: ".strlen($data_string)."\r\n",
  'content'=> $data_string );

// API URL to POST to
$url = 'https://api.pushover.net/1/messages.json';

// Send out API call and parse response into an associative array
$result = json_decode(get_http_request($url, $context_data), TRUE);

// Check if call succeeded
if ($result['status'] == 1)
{
  $notify_status['success'] = TRUE;
} else {
  $notify_status['success'] = FALSE;
}

unset($message, $result, $context_data);

// EOF
