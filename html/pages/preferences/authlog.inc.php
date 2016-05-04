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

print_authlog(array('page'      => $vars['page'],
                    'username'  => $_SESSION['username'],
                    'short'     => TRUE,
                    'header'    => array('header-border' => TRUE, 'title' => 'The last 10 login attempts')));

// EOF
