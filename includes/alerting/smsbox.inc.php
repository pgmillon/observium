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

// Kannel SMSBox documentation: http://kannel.org/download/1.5.0/userguide-1.5.0/userguide.html#AEN4623

$message = $message_tags['ALERT_STATE'] . " " . $message_tags['DEVICE_HOSTNAME'] .": " . $message_tags['ENTITY_NAME'] . "\n" . $message_tags['ALERT_MESSAGE'];

$context_data = array (
  'method' => 'GET',
  'header' => "Connection: close\r\n"
  );

$url = sprintf('%s://%s:%d/cgi-bin/sendsms?user=%s&password=%s&text=%s&from=%s&to=%s',
  $config['smsbox']['scheme'], $config['smsbox']['host'], $config['smsbox']['port'],
  $config['smsbox']['user'], $config['smsbox']['password'],
  urlencode($message),
  urlencode($config['smsbox']['from']), urlencode($endpoint['phone']));

$response = get_http_request($url, $context_data);

if (strpos($response, "Accepted") || strpos($response, "Queued"))
{
  $notify_status['success'] = TRUE;
} else {
  $notify_status['success'] = FALSE;
}

unset($message);

// EOF
