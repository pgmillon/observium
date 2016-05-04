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

$navbar['class'] = 'navbar-narrow';
$navbar['brand'] = 'Apps';

$app_types = array();
foreach ($app_list as $app)
{
  if ($vars['app'] == $app['app_type'])
  {
    $navbar['options'][$app['app_type']]['class'] = 'active';
  }
  $navbar['options'][$app['app_type']]['url']  = generate_url(array('page' => 'apps', 'app' => $app['app_type']));
  $navbar['options'][$app['app_type']]['text'] = nicecase($app['app_type']);

  $image = $config['html_dir'].'/images/icons/'.$app['app_type'].'.png';
  $icon = (is_file($image) ? $app['app_type'] : 'apps');
  $navbar['options'][$app['app_type']]['image'] = 'images/icons/'.$icon.'.png';

  $app_types[$app['app_type']] = array();
}

print_navbar($navbar);
unset($navbar);

if ($vars['app'])
{
  $include = $config['html_dir'].'/pages/apps/'.$vars['app'].'.inc.php';
  if (is_file($include))
  {
    include($include);
  } else {
    include($config['html_dir'].'/pages/apps/default.inc.php');
  }
} else {
  include($config['html_dir'].'/pages/apps/overview.inc.php');
}

$page_title[] = "Apps";

// EOF
