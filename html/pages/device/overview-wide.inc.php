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

$overview = 1;

$ports['total']    = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ?", array($device['device_id']));
$ports['up']       = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ? AND `ifAdminStatus` = 'up' AND (`ifOperStatus` = 'up' OR `ifOperStatus` = 'monitoring')", array($device['device_id']));
$ports['down']     = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ? AND `ifAdminStatus` = 'up' AND (`ifOperStatus` = 'lowerLayerDown' OR `ifOperStatus` = 'down')", array($device['device_id']));
$ports['disabled'] = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ? AND `ifAdminStatus` = 'down'", array($device['device_id']));

$services['total']    = dbFetchCell("SELECT COUNT(service_id) FROM `services` WHERE `device_id` = ?", array($device['device_id']));
$services['up']       = dbFetchCell("SELECT COUNT(service_id) FROM `services` WHERE `device_id` = ? AND `service_status` = '1' AND `service_ignore` ='0'", array($device['device_id']));
$services['down']     = dbFetchCell("SELECT COUNT(service_id) FROM `services` WHERE `device_id` = ? AND `service_status` = '0' AND `service_ignore` = '0'", array($device['device_id']));
$services['disabled'] = dbFetchCell("SELECT COUNT(service_id) FROM `services` WHERE `device_id` = ? AND `service_ignore` = '1'", array($device['device_id']));

if ($services['down']) { $services_colour = $warn_colour_a; } else { $services_colour = $list_colour_a; }
if ($ports['down']) { $ports_colour = $warn_colour_a; } else { $ports_colour = $list_colour_a; }
?>

<div class="row">
<div class="col-md-4">

<?php
/* Begin Left Pane */

include("overview/information.inc.php");

  include("overview/alerts.inc.php");

  include("overview/alertlog.inc.php");

  include("overview/events.inc.php");

if ($config['enable_syslog'])
{
  if (dbFetchCell("SELECT COUNT(*) from `syslog` WHERE `device_id` = ?", array($device['device_id'])))
  {
    include("overview/syslog.inc.php");
  }
}

echo("</div>");
/* End Left Pane */

/* Begin Center Pane */
echo('<div class="col-md-4">');

include("overview/ports.inc.php");

include("overview/services.inc.php");

if (is_array($entity_state['group']['c6kxbar']))
{
  include("overview/c6kxbar.inc.php");
}

include("overview/toner.inc.php");
include("overview/sensors.inc.php");

echo("</div>");
/* End Left Pane */

/* Begin Center Pane */
echo('<div class="col-md-4">');

if ($device['os_group'] == "unix")
{
  include("overview/processors-unix.inc.php");
} else {
  include("overview/processors.inc.php");
}

if (is_array($device_state['ucd_mem']))
{
  include("overview/ucd_mem.inc.php");
} else {
  include("overview/mempools.inc.php");
}

include("overview/storage.inc.php");

include("overview/status.inc.php");

echo('</div>');

/* End Center Pane */

/* Begin Right Pane */

?>

</div>

<?php

// EOF
