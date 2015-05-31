<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2014, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

echo('<table class="table table-hover table-bordered table-striped table-condensed table-rounded">');
echo('<thead><tr>
        <th>Server Name</th>
        <th>Status</th>
        <th>Operating System</th>
        <th>Memory</th>
        <th>CPU</th>
      </tr></thead>');

foreach (dbFetchRows("SELECT * FROM vminfo WHERE device_id = ? ORDER BY vmwVmDisplayName", array($device['device_id'])) as $vm)
{
  print_vm_row($vm, $device);
}

echo("</table>");

$pagetitle[] = "Virtual Machines";

// EOF
