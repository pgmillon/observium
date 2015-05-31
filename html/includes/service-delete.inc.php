<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

$updated = '1';

$affected = dbDelete('services', '`service_id` = ?', array($_POST['service']));

if ($affected)
{
  $message .= $message_break . $rows .  " service deleted!";
  $message_break .= "<br />";
}

?>
