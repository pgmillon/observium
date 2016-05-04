<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

// Pagination
$vars['pagination'] = TRUE;

$vars['entity'] = $port['port_id'];
$vars['entity_type'] = "port";

print_events($vars);

$page_title[] = "Events";

// EOF
