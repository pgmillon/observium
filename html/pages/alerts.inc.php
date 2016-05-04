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

include($config['html_dir']."/includes/alerting-navbar.inc.php");

if (!isset($vars['status'])) { $vars['status'] = 'failed'; }
if (!$vars['entity_type']) { $vars['entity_type'] = "all"; }

$navbar['class'] = "navbar-narrow";
$navbar['brand'] = "Alert Types";

$types = dbFetchRows("SELECT DISTINCT `entity_type` FROM `alert_table` WHERE 1" . generate_query_permitted(array('alert')));

$navbar['options']['all']['url'] = generate_url($vars, array('page' => 'alerts', 'entity_type' => 'all'));
$navbar['options']['all']['text'] = escape_html(nicecase('all'));
if ($vars['entity_type'] == 'all')
{
  $navbar['options']['all']['class'] = "active";
  $navbar['options']['all']['url'] = generate_url($vars, array('page' => 'alerts', 'entity_type' => NULL));
}

foreach ($types as $thing)
{
  if ($vars['entity_type'] == $thing['entity_type'])
  {
    $navbar['options'][$thing['entity_type']]['class'] = "active";
    $navbar['options'][$thing['entity_type']]['url'] = generate_url($vars, array('page' => 'alerts', 'entity_type' => NULL));
  } else {
    $navbar['options'][$thing['entity_type']]['url'] = generate_url($vars, array('page' => 'alerts', 'entity_type' => $thing['entity_type']));
  }
  $navbar['options'][$thing['entity_type']]['icon'] = $config['entities'][$thing['entity_type']]['icon'];
  $navbar['options'][$thing['entity_type']]['text'] = escape_html(nicecase($thing['entity_type']));
}

$navbar['options_right']['filters']['url']       = '#';
$navbar['options_right']['filters']['text']      = 'Filter';
$navbar['options_right']['filters']['icon']      = 'oicon-filter';
$navbar['options_right']['filters']['link_opts'] = 'data-hover="dropdown" data-toggle="dropdown"';

$filters = array('all'     => array('url'   => generate_url($vars, array('page' => 'alerts', 'status' => 'all')),
                                       'url_o' => generate_url($vars, array('page' => 'alerts', 'status' => 'all')),
                                       'icon'  => 'oicon-information',
                                       'text'  => 'All'),

                 'failed_delayed' => array('url'   => generate_url($vars, array('page' => 'alerts', 'status' => 'failed_delayed')),
                                       'url_o' => generate_url($vars, array('page' => 'alerts', 'status' => 'all')),
                                       'icon'  => 'oicon-exclamation',
                                       'text'  => 'Failed & Delayed'),

                 'failed'     => array('url'   => generate_url($vars, array('page' => 'alerts', 'status' => 'failed')),
                                       'url_o' => generate_url($vars, array('page' => 'alerts', 'status' => 'all')),
                                       'icon'  => 'oicon-exclamation-red',
                                       'text'  => 'Failed'),

                 'suppressed' => array('url'   => generate_url($vars, array('page' => 'alerts', 'status' => 'suppressed')),
                                       'url_o' => generate_url($vars, array('page' => 'alerts', 'status' => 'all')),
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

// Print out the navbar defined above
print_navbar($navbar);

// Cache the alert_tests table for use later
$alert_rules = cache_alert_rules($vars);

// Print out a table of alerts matching $vars
if ($vars['status'] != 'failed')
{
  $vars['pagination'] = TRUE;
}

print_alert_table($vars);

// EOF
