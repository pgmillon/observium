<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

function discover_service($device, $service)
{
  if (! dbFetchCell("SELECT COUNT(service_id) FROM `services` WHERE `service_type`= ? AND `device_id` = ?", array($service, $device['device_id'])))
  {
    add_service($device, $service, "(Auto discovered) $service");
    log_event("Autodiscovered service: type $service", $device, 'service');
    echo("+");
  }

  echo("$service ");
}

// EOF
