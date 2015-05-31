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

$navbar['class'] = 'navbar-narrow';
$navbar['brand'] = 'Apps';

foreach ($app_list as $app)
{
  if ($vars['app'] == $app['app_type'])
  {
    $navbar['options'][$app['app_type']]['class'] = 'active';
  }
  $navbar['options'][$app['app_type']]['url']  = generate_url(array('page' => 'apps', 'app' => $app['app_type']));
  $navbar['options'][$app['app_type']]['text'] = nicecase($app['app_type']);
}

print_navbar($navbar);
unset($navbar);

if ($vars['app'])
{
  if (is_file("pages/apps/".$vars['app'].".inc.php"))
  {
    include("pages/apps/".$vars['app'].".inc.php");
  } else {
    include("pages/apps/default.inc.php");
  }
} else {
  include("pages/apps/overview.inc.php");
}

$pagetitle[] = "Apps";

// EOF
