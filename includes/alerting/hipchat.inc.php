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

$color = ($message_tags['ALERT_STATE'] == "RECOVER" ? "green" : "red");

// Default URL if not set
if ($endpoint['url'] == "") { $endpoint['url'] = 'https://api.hipchat.com'; }

$message = '<a href="'.$message_tags['ALERT_URL'].'">'.$title.'</a>';
$message .= '<br />';
$message .= str_replace("             ", "", str_replace("\n", " ", $message_tags['METRICS']));

// JSON data
$data_string = json_encode(array(
  "from" => $endpoint['from'],
  "color" => $color,
  "message_format" => 'html',
  "message" => $message));

// JSON data + HTTP headers
$context_data = array(
  'method' => 'POST',
  'header' =>
    "Connection: close\r\n".
    "Content-Type: application/json\r\n".
    "Content-Length: ".strlen($data_string)."\r\n".
    "Authorization: Bearer " . $endpoint['token']."\r\n",
  'content'=> $data_string);

// API URL to POST to
$url = $endpoint['url'] . '/v2/room/' . urlencode($endpoint['room_id']) . '/notification';

// Send out API call
get_http_request($url, $context_data);

// Return of this API call is "204 No Content" - there is no way to know if it went through or not, so we return TRUE always and hope for the best.
$notify_status['success'] = TRUE;

unset($message, $context_data);

// EOF
