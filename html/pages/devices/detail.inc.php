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

// Display devices as a list in detailed format
?>
<table class="table table-hover table-striped table-bordered table-condensed table-rounded" style="margin-top: 10px;">
  <thead>
    <tr>
      <th></th>
      <th></th>
      <th>Device/Location</th>
      <th></th>
      <th>Platform</th>
      <th>Operating System</th>
      <th>Uptime/sysName</th>
    </tr>
  </thead>

<?php
foreach ($devices as $device)
{
  if (device_permitted($device['device_id']))
  {
    if (!$location_filter || $device['location'] == $location_filter)
    {
      include("includes/hostbox.inc.php");
    }
  }
}

echo("</table>");

// EOF
