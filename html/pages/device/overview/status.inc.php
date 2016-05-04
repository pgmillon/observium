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

  $sql  = "SELECT * FROM `status`";
  $sql .= " LEFT JOIN `status-state` USING(`status_id`)";
  $sql .= " WHERE `device_id` = ? ORDER BY `entPhysicalClass` DESC, `status_descr`;";

  $status = dbFetchRows($sql, array($device['device_id']));

  if (count($status))
  {
?>

  <div class="box box-solid">
    <div class="box-header ">
      <a href="<?php echo(generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'health', 'metric' => 'status'))); ?>">
        <i class="<?php echo($config['entities']['status']['icon']); ?>"></i><h3 class="box-title">Status Indicators</h3>
      </a>
    </div>
    <div class="box-body no-padding">

<?php

    echo('<table class="table table-condensed table-striped">');
    foreach ($status as $status)
    {
      $status['status_descr'] = truncate($status['status_descr'], 48, '');

      print_status_row($status, $vars);
    }

    echo("</table>");
    echo("</div></div>");
  }

// EOF
