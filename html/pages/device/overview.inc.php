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

?>

<div class="row">
<div class="col-md-6">

<?php
/* Begin Left Pane */

include("overview/information.inc.php");

include("overview/ports.inc.php");

include("overview/services.inc.php");

include("overview/alertlog.inc.php");

include("overview/syslog.inc.php");

include("overview/events.inc.php");

echo("</div>");
/* End Left Pane */

/* Begin Right Pane */
echo('<div class="col-md-6">');

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

if (is_array($entity_state['group']['c6kxbar']))
{
  include("overview/c6kxbar.inc.php");
}

include("overview/toner.inc.php");
include("overview/status.inc.php");
include("overview/sensors.inc.php");

/* End Right Pane */

?>

  </div>
</div>

<?php

// EOF
