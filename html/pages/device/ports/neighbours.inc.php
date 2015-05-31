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

?>
<div class="row">
<div class="col-md-12">
<?php

echo('<table class="table table-bordered table-striped table-hover table-condensed">');

$i = "1";

echo('<thead><tr>
          <th>Local Port</th>
          <th>Remote Port</th>
          <th>Remote Device</th>
          <th>Protocol</th>
      </tr></thead>');

echo('<tbody>');

foreach (dbFetchRows("SELECT * FROM links AS L, ports AS I WHERE I.device_id = ? AND I.port_id = L.local_port_id", array($device['device_id'])) as $neighbour)
{
  echo('<tr>');
  echo('<td><span style="font-weight: bold;">'.generate_port_link($neighbour).'</span><br />'.$neighbour['ifAlias'].'</td>');

  if (is_numeric($neighbour['remote_port_id']) && $neighbour['remote_port_id'])
  {
    $remote_port   = get_port_by_id($neighbour['remote_port_id']);
    $remote_device = device_by_id_cache($remote_port['device_id']);
    echo("<td>".generate_port_link($remote_port)."<br />".$remote_port['ifAlias']."</td>");
    echo("<td>".generate_device_link($remote_device)."<br />".$remote_device['hardware']."</td>");
  } else {
    echo("<td>".$neighbour['remote_port']."</td>");
    echo("<td>".$neighbour['remote_hostname']."
          <br />".$neighbour['remote_platform']."</td>");
  }
  echo("<td>".strtoupper($neighbour['protocol'])."</td>");
  echo("</tr>");
  $i++;
}

echo("</tbody></table>");

?>
  </div>
</div>
<?php

// EOF