<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage applications
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$navbar['brand'] = nicecase($app['app_type']);
$navbar['class'] = "navbar-narrow";
foreach ($app_sections as $app_section => $app_section_text)
{
  if (!$vars['app_section']) { $vars['app_section'] = $app_section; }
  $navbar['brand'] = nicecase($app['app_type']);
  $navbar['options'][$app_section]['text'] = $app_section_text;
  if ($vars['app_section'] == $app_section) { $navbar['options'][$app_section]['class'] = "active"; }
  $navbar['options'][$app_section]['url'] = generate_url($vars,array('app_section'=>$app_section));
}

print_navbar($navbar);

// EOF