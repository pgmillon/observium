<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// Display devices as a list in detailed format
?>
<div class="box box-solid">
<table class="table table-hover table-striped  table-condensed " style="margin-top: 10px;">
  <thead>
    <tr>
      <th></th>
      <th></th>
      <th>Device/Location</th>
      <th></th>
    </tr>
  </thead>

<?php
foreach ($devices as $device)
{
  if (device_permitted($device['device_id']))
  {
    if (!$location_filter || $device['location'] == $location_filter)
    {
      print_device_row($device, 'status');
    }
  }
}

echo("</table></div>");

// EOF
