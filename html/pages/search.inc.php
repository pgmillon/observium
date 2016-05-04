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

$page_title[] = "Search";

$sections = array('ipv4' => 'IPv4 Address', 'ipv6' => 'IPv6 Address', 'mac' => 'MAC Address', 'arp' => 'ARP/NDP Tables', 'fdb' => 'FDB Tables');

if (dbFetchCell("SELECT COUNT(wifi_session_id) FROM wifi_sessions") > '0')
  $sections['dot1x'] = '.1x Sessions'; //Can be extended to include all dot1x sessions

$navbar['brand'] = "Search";
$navbar['class'] = "navbar-narrow";

foreach ($sections as $section => $text)
{
  $type = strtolower($section);
  if (!isset($vars['search'])) { $vars['search'] = $section; }

  if ($vars['search'] == $section) { $navbar['options'][$section]['class'] = "active"; }
  $navbar['options'][$section]['url'] = generate_url(array('page' => 'search', 'search' => $section));
  $navbar['options'][$section]['text'] = $text;
}

print_navbar($navbar);

/// Little switch to provide some sanity checking.
switch ($vars['search'])
{
  case 'ipv4':
  case 'ipv6':
  case 'mac':
  case 'arp':
  case 'fdb':
  case 'dot1x':
    include($config['html_dir'].'/pages/search/'.$vars['search'].'.inc.php');
    break;
  default:
      print_error("<h4>Error</h4>
               This should not happen. Please ensure you are on the latest release and then report this to the Observium developers if it continues.");
    break;
}

// EOF
