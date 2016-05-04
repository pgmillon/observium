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

?>
  <div class="box box-solid">
    <div class="box-header ">
      <a href="<?php echo(generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'logs', 'section' => 'syslog'))); ?>">
        <i class="oicon-bell"></i><h3 class="box-title">Alerts</h3>
      </a>
    </div>
    <div class="box-body no-padding">
      <?php print_alert_table(array('device' => $device['device_id'], 'short' => TRUE, 'pagesize' => 10, 'status' => 'failed', 'no_header' => TRUE)); ?>
    </div>
  </div>

<?php

// EOF
