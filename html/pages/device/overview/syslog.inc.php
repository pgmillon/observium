<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

?>
  <div class="widget widget-table">
    <div class="widget-header">
      <a href="<?php echo(generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'logs', 'section' => 'syslog'))); ?>">
        <i class="oicon-clipboard-eye"></i><h3>Syslog</h3>
      </a>
    </div>
    <div class="widget-content">
      <?php print_syslogs(array('device' => $device['device_id'], 'short' => TRUE)); ?>
    </div>
  </div>

<?php

// EOF
