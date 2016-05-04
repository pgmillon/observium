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

$alert_rules = cache_alert_rules();
$alert_assoc = cache_alert_assoc();
$alert_table = cache_device_alert_table($device['device_id']);

if (!isset($vars['status'])) { $vars['status'] = 'failed'; }
if (!$vars['entity_type']) { $vars['entity_type'] = 'all'; }

// Build Navbar

$navbar['class'] = "navbar-narrow";
$navbar['brand'] = "Alert Types";

if ($vars['entity_type'] == 'all') { $navbar['options']['all']['class'] = "active"; }
$navbar['options']['all']['url'] = generate_url(array('page' => 'device', 'device' => $device['device_id'],
                                                'tab' => 'alerts', 'entity_type' => 'all'));
$navbar['options']['all']['text'] = "All";

foreach ($alert_table as $entity_type => $thing)
{

  if (!$vars['entity_type']) { $vars['entity_type'] = $entity_type; }
  if ($vars['entity_type'] == $entity_type) { $navbar['options'][$entity_type]['class'] = "active"; }

  $navbar['options'][$entity_type]['url'] = generate_url(array('page' => 'device', 'device' => $device['device_id'],
                                                  'tab' => 'alerts', 'entity_type' => $entity_type));
  $navbar['options'][$entity_type]['icon'] = $config['entities'][$entity_type]['icon'];
  $navbar['options'][$entity_type]['text'] = escape_html(nicecase($entity_type));
}

$navbar['options_right']['update']['url']  = generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'alerts', 'action'=>'update'));
$navbar['options_right']['update']['text'] = 'Rebuild';
$navbar['options_right']['update']['icon'] = 'oicon-arrow-circle';
if ($vars['action'] == 'update') { $navbar['options_right']['update']['class'] = 'active'; }

$navbar['options_right']['filters']['url']       = '#';
$navbar['options_right']['filters']['text']      = 'Filter';
$navbar['options_right']['filters']['icon']      = 'oicon-filter';
$navbar['options_right']['filters']['link_opts'] = 'data-hover="dropdown" data-toggle="dropdown"';

$filters = array('all'     => array('url'   => generate_url($vars, array('status' => 'all')),
                                       'url_o' => generate_url($vars, array('status' => 'all')),
                                       'icon'  => 'oicon-information',
                                       'text'  => 'All'),

                 'failed_delayed' => array('url'   => generate_url($vars, array('status' => 'failed_delayed')),
                                       'url_o' => generate_url($vars, array('page' => 'alerts', 'status' => 'all')),
                                       'icon'  => 'oicon-exclamation',
                                       'text'  => 'Failed & Delayed'),

                 'failed'     => array('url'   => generate_url($vars, array('status' => 'failed')),
                                       'url_o' => generate_url($vars, array('status' => 'all')),
                                       'icon'  => 'oicon-exclamation-red',
                                       'text'  => 'Failed'),

                 'suppressed' => array('url'   => generate_url($vars, array('status' => 'suppressed')),
                                       'url_o' => generate_url($vars, array('status' => 'all')),
                                       'icon'  => 'oicon-exclamation-white',
                                       'text'  => 'Suppressed')
);

foreach ($filters as $option => $option_array)
{

  $navbar['options_right']['filters']['suboptions'][$option]['text'] = $option_array['text'];
  $navbar['options_right']['filters']['suboptions'][$option]['icon'] = $option_array['icon'];

  if ($vars['status'] == $option)
  {
    $navbar['options_right']['filters']['suboptions'][$option]['class'] = "active";
    if ($vars['status'] != "all") {
      $navbar['options_right']['filters']['class'] = "active";
    }
    $navbar['options_right']['filters']['suboptions'][$option]['url'] = $option_array['url_o'];
    $navbar['options_right']['filters']['text'] .= " (".$option_array['text'].")";
    $navbar['options_right']['filters']['icon'] = $option_array['icon'];

  } else {
    $navbar['options_right']['filters']['suboptions'][$option]['url'] = $option_array['url'];
  }
}

print_navbar($navbar);
unset($navbar);

// Run actions

if($vars['action'] == 'update')
{
  echo '<div class="box box-solid">';
  update_device_alert_table($device);
  $alert_table = cache_device_alert_table($device['device_id']);
  echo '</div>';
}

$vars['pagination'] = TRUE;

print_alert_table($vars);

// EOF
