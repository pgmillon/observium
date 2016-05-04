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

  print_alert_log(array('device' => $device['device_id'],
                        'short' => TRUE, 'pagesize' => 7,
                        'header' => array('title' => 'Alert Log',
                                          'icon' => 'oicon-bell--exclamation',
                                          'url' => generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'logs', 'section' => 'alertlog'))
                                    )
                 ));


// EOF
