<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2014, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage applications
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

$app_sections = array('system' => "System",
                      'backend' => "Backend",
                      'jvm' => "Java VM",
                     );

$app_graphs['system'] = array(
                'zimbra_fdcount'  => 'Open file descriptors',
                );

$app_graphs['backend'] = array(
                'zimbra_mtaqueue'     => 'MTA queue size',
                'zimbra_connections'  => 'Open connections',
                'zimbra_threads'      => 'Threads',
                );

$app_graphs['jvm'] = array(
                'zimbra_jvmthreads'      => 'JVM Threads',
                );

// EOF
