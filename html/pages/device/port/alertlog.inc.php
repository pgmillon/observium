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

// Pagination
$vars['pagination'] = TRUE;

$vars['entity_type'] = 'port';
$vars['entity_id']   = $vars['port'];

// Print Alert Log
print_alert_log($vars);

$page_title[] = 'Alert Log';

// EOF
