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

    print_events(array('device' => $device['device_id'], 'short' => TRUE, 'pagesize' => '20',
                        'header' => array('title' => 'Eventlog',
                                          'icon' => 'oicon-clipboard-audit',
                                          'url' => generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'logs', 'section' => 'eventlog'))
                                    )
    ));


// EOF
