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

$message = $title . ' [' . $message_tags['ALERT_URL'] . ']' . PHP_EOL;
$message .= str_replace("             ", "", $message_tags['METRICS']);

use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Client;
use Fabiang\Xmpp\Protocol\Message;

if (isset($endpoint['server']))
{
  // Set server hostname if specified by user
  $hostname = $endpoint['server'];
} else {
  // Find server by SRV record, if we have an @ in our login username
  if (strstr($endpoint['username'], '@') !== FALSE)
  {
    list(,$xmppdomain) = explode('@', $endpoint['username'], 2);

    $resolver = new Net_DNS2_Resolver();
    
    $maxprio = -1;

    // Find and use highest priority server only. Could be improved to cycle if there are multiple?
    try
    {
      $response = $resolver->query("_xmpp-client._tcp.$xmppdomain", 'SRV', 'IN');
      if ($response)
      {
        foreach ($response->answer as $answer)
        {
          if ($answer->priority > $maxprio)
          {
            $hostname = $answer->target;
          }
        }
      }
    } catch (\Exception $e) { print_debug("Error while resolving: " . $e->getMessage()); } // Continue when error resolving
  }
}

if ($hostname != '')
{
  // Default to port to 5222 unless specified by endpoint data
  $port = ($endpoint['port'] ? $endpoint['port'] : 5222);
  
  list($username,$xmppdomain) = explode('@',$endpoint['username']); // Username is only the part before @
  $password = $endpoint['password'];
  
  $options = new Options("tcp://$hostname:$port");
  $options->setUsername($username);
  $options->setPassword($password);

  list($rusername,$rxmppdomain ) = explode('@',$endpoint['recipient']);
  if ($rxmppdomain != '') { $options->setTo($rxmppdomain); } // Set destination domain to the recipient's part after the @
  
  $client = new Client($options);
  
  try
  {
    $client->connect();
  
    $xmessage = new Message;
    $xmessage->setMessage($message);
    $xmessage->setTo($endpoint['recipient']);
    $client->send($xmessage);
  
    $client->disconnect();
  
    $notify_status['success'] = TRUE;
  } catch (\Exception $e) {
    // reason:  $e->getMessage()
    $notify_status['success'] = FALSE;
  }
} else {
  // reason: Could not determine server hostname!
  $notify_status['success'] = FALSE;
}
  
unset($message);
  
// EOF
 