<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$app_rows = dbFetchRows("SELECT * FROM `applications` WHERE `device_id`  = ?", array($device['device_id']));

if (count($app_rows))
{
  echo('Applications: ');
  foreach ($app_rows as $app)
  {
    if (!isset($agent_data['app'][$app['app_type']]))
    {
      $app_include = $config['install_dir'].'/includes/polling/applications/'.$app['app_type'].'.inc.php';
      if (is_file($app_include))
      {
        include($app_include);
      }
      else
      {
        echo($app['app_type'].' include missing! ');
      }
    }
  }
  echo(PHP_EOL);
}

// EOF
