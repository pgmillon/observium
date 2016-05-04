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

if (!is_array($alert_rules)) { $alert_rules = cache_alert_rules(); }

$navbar['class'] = 'navbar-narrow';
$navbar['brand'] = 'Contacts';

$pages = array('contacts' => 'Contact List');

foreach ($pages as $page_name => $page_desc)
{
    if ($vars['page'] == $page_name)
    {
        $navbar['options'][$page_name]['class'] = "active";
    }

    $navbar['options'][$page_name]['url'] = generate_url(array('page' => $page_name));
    $navbar['options'][$page_name]['text'] = escape_html($page_desc);
}

$navbar['options_right']['add']['url']       = '#add_contact_modal';
$navbar['options_right']['add']['link_opts'] = 'data-toggle="modal"';
$navbar['options_right']['add']['text']      = 'Add Contact';
$navbar['options_right']['add']['icon']      = 'oicon-mail--plus';
$navbar['options_right']['add']['userlevel'] = 10;

// Print out the navbar defined above
print_navbar($navbar);
unset($navbar);

// EOF
