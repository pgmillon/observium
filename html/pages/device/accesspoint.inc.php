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

$ap = dbFetchRow("SELECT * FROM `accesspoints` LEFT JOIN `accesspoints-state` ON  `accesspoints`.`accesspoint_id` = `accesspoints-state`.`accesspoint_id` WHERE `device_id` = ? AND accesspoints.`accesspoint_id` = ? AND `deleted` = '0' ORDER BY `name`,`radio_number` ASC", array($device['device_id'],$vars['ap']));

echo('<table class="table table-bordered table-rounded table-striped">');

include("includes/print-accesspoint.inc.php");

echo('</table>');

// EOF
