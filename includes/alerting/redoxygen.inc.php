<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage alerting
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2015 Observium Limited
 *
 */

/* Red Oxygen SMS delivery integration */
/* Refer to: http://redoxygen.com/developers/http/ */
/* Refer to: http://www.redoxygen.com/support/wiki/doku.php?id=red_api:http */

/* Endpoint variables:
 * from: Optional descriptor for originating system, defaults to 'Observium'
 * originator: Sender of SMS
 * acctid: API access key to send SMS
 * email: Email address associated with account
 * password: Password for email address/account ID combination
 * recipient: Recipient phone number/s
 */

$message = $title . PHP_EOL;
$message .= str_replace("             ", "", $message_tags['METRICS']);

// Default URL if not set
if ($endpoint['url'] == "") { $endpoint['url'] = 'https://redoxygen.net/sms.dll?Action=SendSMS'; }

// Default from value if not set
if ($endpoint['from'] == "") { $endpoint['from'] = 'Observium'; }

// Pre-pend from value to message
$message = $endpoint['from'] . " " . $message;

$url = $endpoint['url'];

// Remove common delimiters used in phone numbers, e.g. dot, dash, space, from recipient number
$recipient = str_replace('.', '', $endpoint['recipient']);
$recipient = str_replace('-', '', $recipient);
$recipient = str_replace(' ', '', $recipient);
// URL encode POST values
$recipient = urlencode($recipient);
$email = urlencode($endpoint['email']);
$password = urlencode($endpoint['password']);
$message = urlencode($message);
$acctid = urlencode($endpoint['acctid']);

// POST Data - ENSURE AccountId is the first parameter, their API seems to fail if not.
$postdata = "AccountId=" . $acctid . "&Email=" . $email . "&Password=" . $password . "&Recipient=" . $recipient . "&Message=" . $message;

$context_data = array(
  'method'  => 'POST',
  'content' => $postdata
);

// Send out API call and parse response into an associative array
$response = get_http_request($url, $context_data);

if ($response == '0000')
{
  $notify_status['success'] = TRUE;
} else {
  $notify_status['success'] = FALSE;
}

unset($url, $send, $message, $response, $postdata, $context_data);

// EOF
