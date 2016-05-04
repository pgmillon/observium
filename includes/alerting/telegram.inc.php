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

$message = $message_tags['ALERT_STATE'] . " : " . $message_tags['ALERT_MESSAGE'] . "\n\nDevice name : " . $message_tags['ENTITY_NAME'] . "\nDevice Uptime : " . $message_tags['DEVICE_UPTIME'] . "\n\nMore informations : " . $message_tags['ALERT_URL'];

$url = 'https://api.telegram.org/bot' . $endpoint['bot_hash'] . '/sendMessage';

// POST Data
$postdata = http_build_query(
  array(
    "chat_id" => $endpoint['recipient'],
    "text" => $message)
);

$context_data = array(
  'method'  => 'POST',
  'content' => $postdata
);

// Send out API call and parse response into an associative array
$response = get_http_request($url, $context_data);

$send = explode(":", $response);
if ($send[0] == "ID")
{
  $notify_status['success'] = TRUE;
} else {
  $notify_status['success'] = FALSE;
}

unset($url, $send, $message, $response, $postdata, $context_data);

// EOF

