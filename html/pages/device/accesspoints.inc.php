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

$i = "1";

$aps = dbFetchRows("SELECT * FROM `accesspoints`  LEFT JOIN `accesspoints-state` ON  `accesspoints`.`accesspoint_id` = `accesspoints-state`.`accesspoint_id` WHERE `device_id` = ? AND `deleted` = '0'  ORDER BY `name`,`radio_number` ASC", array($device['device_id']));

if(count($aps))
{

  echo('<table class="table table-bordered table-rounded table-striped table-hover">');

  foreach ($aps as $ap)
  {
    include('includes/print-accesspoint.inc.php');

    $i++;
  }
  echo('</table>');

} else {

  print_message('No access points found.', 'warning');

}

// EOF
