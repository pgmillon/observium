<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

echo('<table class="table table-hover  table-striped table-condensed ">');
echo('<thead><tr>
        <th>Server Name</th>
        <th>Status</th>
        <th>Operating System</th>
        <th>Memory</th>
        <th>CPU</th>
      </tr></thead>');

foreach (dbFetchRows("SELECT * FROM vminfo WHERE device_id = ? ORDER BY vm_name", array($device['device_id'])) as $vm)
{
  print_vm_row($vm, $device);
}

echo("</table>");

$page_title[] = "Virtual Machines";

// EOF
