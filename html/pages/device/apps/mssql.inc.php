<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage applications
 * @author     Solomon Seal <slm4996+observium@gmail.com> 2014-04
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$sql = "SELECT * FROM `applications-state` WHERE `application_id` = ?";
$app_state = dbFetchRow($sql, array($app['app_id']));
$app_data = unserialize($app_state['app_state']);

$app_sections['system'] = "System";
if (!empty($app_data['stats']))
  $app_sections['stats'] = "Stats";
if (!empty($app_data['buffer']))
  $app_sections['buffers'] = "Buffers";

$app_graphs['system']['mssql_cpu_usage'] = 'Processor';
if (!empty($app_data['memory']))
  $app_graphs['system']['mssql_memory_usage'] = 'Memory';

$app_graphs['stats'] = array(
                'mssql_stats' => 'Users'
                );

$app_graphs['buffers'] = array(
                'mssql_buffer_page' => 'Page Lookups',
                'mssql_buffer_pglife' => 'Page Life Expectancy',
                'mssql_buffer_stalls' => 'Free List Stalls'
                );

// EOF
