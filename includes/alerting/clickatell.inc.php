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

// Clickatell Config
$url = 'https://api.clickatell.com/http/sendmsg';

// POST Data
$postdata = http_build_query(
  array(
    "to" => $endpoint['recipient'],
    "from" => $endpoint['originator'],
    "text" => $message,
    "user" => $endpoint['user'],
    "password" => $endpoint['password'],
    "api_id" => $endpoint['apiid'])
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
