<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage ajax
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */


if(!is_numeric($vars['device_id']))
{
  json_output('error', 'Non-numeric device_id');
} else {

  $update = dbUpdate(array('force_discovery' => '1'), 'devices', '`device_id` = ?', array($vars['device_id']));

  if(!empty($update))
  {
    json_output('ok', 'Device will be rediscovered within 5 minutes');
  } else {
    json_output('error', 'Error setting device rediscovery bit!');
  }
}

// EOF
