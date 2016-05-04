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

$i = "1";

$aps = dbFetchRows("SELECT * FROM `accesspoints`  LEFT JOIN `accesspoints-state` ON  `accesspoints`.`accesspoint_id` = `accesspoints-state`.`accesspoint_id` WHERE `device_id` = ? AND `deleted` = '0'  ORDER BY `name`,`radio_number` ASC", array($device['device_id']));

if(count($aps))
{

  echo('<table class="table   table-striped table-hover">');

  foreach ($aps as $ap)
  {
    include($config['html_dir'].'/includes/print-accesspoint.inc.php');

    $i++;
  }
  echo('</table>');

} else {

  print_message('No access points found.', 'warning');

}

// EOF
