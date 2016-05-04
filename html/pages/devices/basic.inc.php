<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

echo('<table class="table table-hover table-striped table-bordered table-condensed table-rounded" style="margin-top: 10px;">');

foreach ($devices as $device)
{
  if (device_permitted($device['device_id']))
  {
    if (!$location_filter || $device['location'] == $location_filter)
    {
      print_device_hostbox($device, 'basic');
      //include("includes/hostbox-basic.inc.php");
    }
  }
}

echo("</table>");

// EOF
