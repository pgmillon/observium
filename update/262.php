<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage update
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$contacts = dbFetchRows("SELECT * FROM `alert_contacts`");

foreach ($contacts as $contact)
{
  $endpoint = array();

  if (!json_decode($contact['contact_endpoint']))
  {
    foreach (explode("||", $contact['contact_endpoint']) as $datum)
    {
      list($field, $value) = explode("::", $datum);
      $endpoint[$field] = $value;
    }

    dbUpdate(array('contact_endpoint' => json_encode($endpoint)), 'alert_contacts', '`contact_id` = ?', array($contact['contact_id']));
  }
}

echo(PHP_EOL);

// EOF
