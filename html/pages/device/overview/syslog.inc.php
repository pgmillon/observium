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

if ($config['enable_syslog'])
{
  if (dbFetchCell("SELECT COUNT(*) from `syslog` WHERE `device_id` = ?", array($device['device_id'])))
  {

    print_syslogs(array('device' => $device['device_id'], 'short' => TRUE, 'pagesize' => '20',
                        'header' => array('title' => 'Syslog',
                                          'icon' => 'oicon-clipboard-eye',
                                          'url' => generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'logs', 'section' => 'syslog'))
                                    )
    ));

  }
}

// EOF
