<?php

/**
 * Observium Network Management and Monitoring System
 *
 * @package    observium
 * @subpackage config
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

/// WEB UI //////////////////////////////////////////////////////////

$section = 'wui';
$config_sections[$section]['text'] = "Web UI";

$setting = 'web_url';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "General";
$config_variable[$setting]['name']       = "External Web URL";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "URL used in links generated for emails, notifications and other external media.";

$setting = 'page_title_prefix';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "General";
$config_variable[$setting]['name']       = "Page Title Prefix";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Prefix used in the HTML page title.";

$setting = 'page_refresh';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "General";
$config_variable[$setting]['name']       = "Refresh pages";
$config_variable[$setting]['type']       = "enum"; // Normally this setting is just int, but I limit it with list
$config_variable[$setting]['params']     = array(0    => array('name' =>   'Manually', 'icon' => 'icon-ban-circle'),
                                                 60   => array('name' =>   '1 minute', 'icon' => 'icon-refresh'),
                                                 120  => array('name' =>  '2 minutes', 'icon' => 'icon-refresh'),
                                                 300  => array('name' =>  '5 minutes', 'icon' => 'icon-refresh'),
                                                 900  => array('name' => '15 minutes', 'icon' => 'icon-refresh'),
                                                 1800 => array('name' => '30 minutes', 'icon' => 'icon-refresh'));
$config_variable[$setting]['shortdesc']  = "Defines an autorefresh for pages in the web interface. If it's unset pages won't auto refresh.";

$setting = 'web_mouseover';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "General";
$config_variable[$setting]['name']       = "Mouseover popups";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Define the mouseover popups with extra information and graphs.";

$setting = 'web_mouseover_mobile';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "General";
$config_variable[$setting]['name']       = "Mouseover popups on Mobile phones/tablets";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Allow display mouseover popups on Mobile devices. By default on mobile devices popups always disabled.";

$setting = 'web_show_disabled';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "General";
$config_variable[$setting]['name']       = "Show disabled devices";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Show or not disabled devices on major pages. (To hide disabled devices and their ports/alerts/etc, set to False).";

$setting = 'login_message';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Login";
$config_variable[$setting]['name']       = "Login message";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Define the login message shown on the login page.";

$setting = 'login_remember_me';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Login";
$config_variable[$setting]['name']       = "Remember me";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Enable or disable the remember me feature.";

$setting = 'front_page';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Frontpage";
$config_variable[$setting]['name']       = "Front page to display";
$config_variable[$setting]['type']       = "enum";
$config_variable[$setting]['params_call'] = 'config_get_front_page_files'; // Call to this function for possible options
$config_variable[$setting]['shortdesc']  = "PHP file to use as Observium front page";

$setting = 'frontpage|eventlog|severity';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Frontpage";
$config_variable[$setting]['name']       = "Eventlog severities";
$config_variable[$setting]['type']       = "enum-array";
$config_variable[$setting]['params']     = array_slice($config['syslog']['priorities'], 0, 8);
$config_variable[$setting]['value_call'] = 'priority_string_to_numeric'; // Call to this function for current values
$config_variable[$setting]['shortdesc']  = "Show eventlog entries only with this severities";

$setting = 'frontpage|syslog|items';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Frontpage";
$config_variable[$setting]['name']       = "Syslog items";
$config_variable[$setting]['type']       = "enum|5|10|15|25|50"; // Normally this setting is just int, but I limit it with list
$config_variable[$setting]['shortdesc']  = "Only show the last XX items of the syslog view";

$setting = 'frontpage|syslog|priority';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Frontpage";
$config_variable[$setting]['name']       = "Syslog priorities";
$config_variable[$setting]['type']       = "enum-array";
$config_variable[$setting]['params']     = array_slice($config['syslog']['priorities'], 0, 8);
$config_variable[$setting]['value_call'] = 'priority_string_to_numeric'; // Call to this function for current values
$config_variable[$setting]['shortdesc']  = "Show syslog entries only with this priorities";

$setting = 'frontpage|map|api';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Frontpage Map";
$config_variable[$setting]['name']       = "Map API";
$config_variable[$setting]['type']       = "enum";
$config_variable[$setting]['params']['google-mc'] = array('name' => "Google");
$config_variable[$setting]['params']['google']    = array('name' => "Google (old)");
$config_variable[$setting]['shortdesc']  = "";

$setting = 'rrdgraph_real_95th';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Graphs";
$config_variable[$setting]['name']       = "Display 95% percentile";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Enable or disable display the 95% based on the highest value for ports (aka real 95%).";

$setting = 'graphs|style';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Graphs";
$config_variable[$setting]['name']       = "Graphs style";
$config_variable[$setting]['type']       = "enum";
$config_variable[$setting]['params']['default'] = array('name' => "Default");
$config_variable[$setting]['params']['mrtg']    = array('name' => "MRTG");
$config_variable[$setting]['shortdesc']  = "Use alternative graphs style. NOTE, mrtg style for now work only for port bits graphs.";

$setting = 'graphs|ports_scale_default';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Graphs";
$config_variable[$setting]['name']       = "Ports graph default scale";
$config_variable[$setting]['type']       = "enum";
$config_variable[$setting]['params']['auto']  = array('name' => "Autoscale");
$config_variable[$setting]['params']['speed'] = array('name' => "Interface Speed");
foreach ($config['graphs']['ports_scale_list'] as $entry)
{
  $speed = intval(unit_string_to_numeric($entry, 1000));
  $config_variable[$setting]['params'][$entry] = formatRates($speed, 4, 4);
}
$config_variable[$setting]['shortdesc']  = "Use this value as default scale for port graphs.";

$setting = 'graphs|ports_scale_force';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Graphs";
$config_variable[$setting]['name']       = "Force graph scale";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Force scale also if real data more than selected scale.";

/// ALERTING /////////////////////////////////////////////////////////

$config_sections['alerting']['text'] = "Alerting";

$setting = 'alerts|disable|all';
$config_variable[$setting]['section']    = "alerting";
$config_variable[$setting]['name']       = "Disable All Notifications";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Disables alert notification generation for all notification transport types.";

$setting = 'alerts|suppress';
$config_variable[$setting]['section']    = "alerting";
$config_variable[$setting]['name']       = "Suppress All Alerts";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Causes all failed alerts to be placed in the suppressed state.";

$setting = 'email|default';
$config_variable[$setting]['section']    = "alerting";
$config_variable[$setting]['name']       = "Default Notification Email";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Email address to send notifications to as default. Only used when no contact matches the alert.";

$setting = 'email|from';
$config_variable[$setting]['section']    = "alerting";
$config_variable[$setting]['name']       = "Email from: address";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Email address used in the from: field for Observium-generated emails.";

$setting = 'email|default_only';
$config_variable[$setting]['section']    = "alerting";
$config_variable[$setting]['name']       = "Default Email Only";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "When no contact matches, use only the default notification email. Don't use the device's sysContact.";

/// AUTHENTICATION ///////////////////////////////////////////////////

$section = 'authentication';
$config_sections[$section]['text'] = "Authentication";

$setting = 'auth_mechanism';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "General";
$config_variable[$setting]['name']       = "Authentication module to use";
$config_variable[$setting]['type']       = "enum|mysql|ldap|radius|http-auth";
$config_variable[$setting]['shortdesc']  = "Specific settings for the individual authentication modules can be found below.";

$setting = 'auth|remote_user';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "General";
$config_variable[$setting]['name']       = "Trust Apache REMOTE_USER";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Disables built-in authentication and delegates this to Apache, for auth modules that support this. Make sure to read the documentation and handle with care!";

$setting = 'web_session_lifetime';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Sessions";
$config_variable[$setting]['name']       = "Session lifetime";
$config_variable[$setting]['type']       = "enum";
$config_variable[$setting]['params']     = array(0     => array('name' => 'Until browser restart'),
                                                 //600   => array('name' => '10 minutes'),
                                                 1800  => array('name' => '30 minutes'),
                                                 3600  => array('name' => '1 hour'),
                                                 10800 => array('name' => '3 hours'),
                                                 84600 => array('name' => '1 day'));
$config_variable[$setting]['shortdesc']  = "Default user sessions lifetime in seconds (0 - until browser restart).";

$setting = 'web_session_ip';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Sessions";
$config_variable[$setting]['name']       = "Session bind to IP";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Bind user sessions to his IP address.";

/*
$setting = 'web_session_cidr';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Sessions";
$config_variable[$setting]['name']       = "Allow user authorisation from specific IP ranges"
$config_variable[$setting]['type']       = "array|cidr";
$config_variable[$setting]['shortdesc']  = "Allow users to log in from specific IP ranges only. Leave empty for access from any IP address.";
*/

$setting = 'allow_unauth_graphs';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Graphs";
$config_variable[$setting]['name']       = "Allow graphs to be viewed by anyone";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Disables authentication for all graphs. This should be used with caution and should be left disabled when using the CIDR option!";

/*
$setting = 'allow_unauth_graphs_cidr';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Graphs";
$config_variable[$setting]['name']       = "Allow graphs to be viewed by anyone from specific IP ranges"
$config_variable[$setting]['type']       = "array|cidr";
$config_variable[$setting]['shortdesc']  = "Allow unauthenticated users to view graphs from specific IP ranges only.";
*/

/*
$setting = 'auth_ldap_server';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "LDAP";
$config_variable[$setting]['name']       = "LDAP servers";
$config_variable[$setting]['type']       = "array";
$config_variable[$setting]['shortdesc']  = "List of LDAP servers to authenticate against, in order.";
*/

$setting = 'auth_ldap_port';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "LDAP";
$config_variable[$setting]['name']       = "LDAP server port";
$config_variable[$setting]['type']       = "int";
$config_variable[$setting]['shortdesc']  = "Port to be used to connect to the LDAP servers.";

$setting = 'auth_ldap_version';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "LDAP";
$config_variable[$setting]['name']       = "LDAP version used";
$config_variable[$setting]['type']       = "enum|2|3";
$config_variable[$setting]['shortdesc']  = "LDAP version used to connect to the LDAP server.";

$setting = 'auth_ldap_starttls';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "LDAP";
$config_variable[$setting]['name']       = "Use STARTTLS";
$config_variable[$setting]['type']       = "enum|no|optional|require";
$config_variable[$setting]['shortdesc']  = "Use STARTTLS for LDAP security: No, Optional (Try but to not require), Require (Abort connection when failing).";

$setting = 'auth_ldap_referrals';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "LDAP";
$config_variable[$setting]['name']       = "Follow LDAP referrals";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Follow referrals received from LDAP server.";

$setting = 'auth_ldap_recursive';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "LDAP";
$config_variable[$setting]['name']       = "Recursive group lookup";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Do recursive group lookup for group memberships.";

$setting = 'auth_ldap_recursive_maxdepth';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "LDAP";
$config_variable[$setting]['name']       = "Recursive group lookup maximum depth";
$config_variable[$setting]['type']       = "int";
$config_variable[$setting]['shortdesc']  = "Maximum depth of group membership lookups.";

/*
$setting = 'auth_radius_server';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "RADIUS";
$config_variable[$setting]['name']       = "RADIUS servers";
$config_variable[$setting]['type']       = "array";
$config_variable[$setting]['shortdesc']  = "List of RADIUS servers to authenticate against, in order.";
*/

$setting = 'auth_radius_port';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "RADIUS";
$config_variable[$setting]['name']       = "RADIUS server port";
$config_variable[$setting]['type']       = "int";
$config_variable[$setting]['shortdesc']  = "Port to be used to connect to the RADIUS servers.";

$setting = 'auth_radius_secret';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "RADIUS";
$config_variable[$setting]['name']       = "RADIUS authentication secret";
$config_variable[$setting]['type']       = "password";
$config_variable[$setting]['shortdesc']  = "Authentication secret to be used to connect to the RADIUS server.";

$setting = 'auth_radius_timeout';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "RADIUS";
$config_variable[$setting]['name']       = "RADIUS connection timeout";
$config_variable[$setting]['type']       = "int";
$config_variable[$setting]['shortdesc']  = "Connection timeout in seconds.";

$setting = 'auth_radius_retries';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "RADIUS";
$config_variable[$setting]['name']       = "RADIUS connection retries";
$config_variable[$setting]['type']       = "int";
$config_variable[$setting]['shortdesc']  = "Number of times to try to connect to the RADIUS server.";

/// AUTODISCOVERY ////////////////////////////////////////////////////

$config_sections['autodiscovery']['text'] = "Auto Discovery";

/*
$setting = 'autodiscovery|ip_nets';
$config_variable[$setting]['section']    = "autodiscovery";
$config_variable[$setting]['subsection'] = "General";
$config_variable[$setting]['name']       = "Networks to permit autodiscovery";
$config_variable[$setting]['type']       = "array|cidr";
$config_variable[$setting]['shortdesc']  = "When discovering new devices, Observium will check if their IP address falls within these ranges before trying to add them. Currently only IPv4 is supported.";
$config_variable[$setting]['longdesc']   = ""; // FIXME please note this is not snmp scanning range
*/

$setting = 'autodiscovery|xdp';
$config_variable[$setting]['section']    = "autodiscovery";
$config_variable[$setting]['subsection'] = "General";
$config_variable[$setting]['name']       = "Enable autodiscovery via discovery protocols";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "This enables autodiscovery of neighbouring devices via discovery protocols, such as CDP, LLDP or FDP. Note that this doesn't enable or disable the discovery protocol tracking features, but controls whether Observium should try to auto-add devices it sees via those protocols.";

$setting = 'autodiscovery|bgp';
$config_variable[$setting]['section']    = "autodiscovery";
$config_variable[$setting]['subsection'] = "General";
$config_variable[$setting]['name']       = "Enable autodiscovery via iBGP neighbours";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "This enables autodiscovery of neighbouring devices via neighbours seen through the BGP protocol (internal BGP only). Note that this doesn't enable or disable the BGP protocol tracking features, but controls whether Observium should try to auto-add devices it sees via those protocols.";

$setting = 'autodiscovery|ospf';
$config_variable[$setting]['section']    = "autodiscovery";
$config_variable[$setting]['subsection'] = "General";
$config_variable[$setting]['name']       = "Enable autodiscovery via OSPF neighbours";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "This enables autodiscovery of neighbouring devices via neighbours seen through the OSPF protocol. Note that this doesn't enable or disable the OSPF protocol tracking features, but controls whether Observium should try to auto-add devices it sees via those protocols.";

$setting = 'autodiscovery|libvirt';
$config_variable[$setting]['section']    = "autodiscovery";
$config_variable[$setting]['subsection'] = "General";
$config_variable[$setting]['name']       = "Enable autodiscovery via Libvirt";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "This enables autodiscovery of virtual machines discovered through libvirt integration. Note that this doesn't enable or disable the libvirt virtual machine tracking features, but controls whether Observium should try to auto-add devices it sees via libvirt.";

$setting = 'autodiscovery|proxmox';
$config_variable[$setting]['section']    = "autodiscovery";
$config_variable[$setting]['subsection'] = "General";
$config_variable[$setting]['name']       = "Enable autodiscovery via Proxmox";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "This enables autodiscovery of virtual machines discovered through the Proxmox unix agent. Note that this doesn't enable or disable the Proxmox virtual machine tracking features, but controls whether Observium should try to auto-add devices it sees via Proxmox.";

$setting = 'autodiscovery|vmware';
$config_variable[$setting]['section']    = "autodiscovery";
$config_variable[$setting]['subsection'] = "General";
$config_variable[$setting]['name']       = "Enable autodiscovery via VMware";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "This enables autodiscovery of virtual machines discovered through VMware integration. Note that this doesn't enable or disable the VMware virtual machine tracking features, but controls whether Observium should try to auto-add devices it sees via VMware.";

/*

$config['autodiscovery']['snmpscan']       = TRUE; // Autodiscover hosts via SNMP scanning
^ NOT IMPLEMENTED
$config['discover_services']               = FALSE; // Autodiscover services via SNMP on devices of type "server"
^ DEPRECATED

$config['autodiscovery']['ping_skip']      = FALSE; // Skip icmp echo checks during autodiscovery (beware timeouts during discovery!)

#$config['bad_xdp'][] = "foo";
#$config['bad_xdp_regexp'][] = "/^SIP/"

*/

/// sysLocation ////////////////////////////////////////////////////////////

$section = 'syslocation';
$config_sections[$section]['text'] = "Locations";

//$setting = 'location_map';
//$config_variable[$setting]['section']    = $section;
//$config_variable[$setting]['subsection'] = "General";
//$config_variable[$setting]['name']       = "Location Mapping";
//$config_variable[$setting]['type']       = "key-value";
//$config_variable[$setting]['shortdesc']  = "Use this feature to map ugly locations to pretty locations (ie: 'Under the Sink' -> 'Under The Sink, The Office, London, UK')";

$setting = 'geocoding|enable';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "GEO Coding";
$config_variable[$setting]['name']       = "Enable GEO Coding";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Enable or disable geocoding of addresses. If disabled, best to disable the map on the front page as well.";

$setting = 'geocoding|api';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "GEO Coding";
$config_variable[$setting]['name']       = "GEO API to use";
$config_variable[$setting]['type']       = "enum";
$config_variable[$setting]['params']['openstreetmap'] = array('name' => "OpenStreetMap", 'subtext' => 'Rate limit 150 req/day', 'desc' => 'See the usage limits <a href="http://wiki.openstreetmap.org/wiki/Nominatim_usage_policy" target="_blank">here</a>');
$config_variable[$setting]['params']['yahoo']         = array('name' => "Yahoo",    'subtext' => 'Rate limit 2000 req/day');
$config_variable[$setting]['params']['google']        = array('name' => "Google",   'allowed'  => 'geocoding|api_key', 'subtext' => 'Allowed to use GEO API KEY', 'desc' => 'Request a KEY <a href="https://developers.google.com/maps/documentation/geocoding/get-api-key" target="_blank">here</a>');
$config_variable[$setting]['params']['yandex']        = array('name' => "Yandex",   'allowed'  => 'geocoding|api_key', 'subtext' => 'Allowed to use GEO API KEY', 'desc' => 'Request a KEY <a href="https://tech.yandex.ru/maps/keys" target="_blank">here</a>.<br />Note, If the key parameter is not passed,
                                                                                                                                     then the search is only available for the following countries: Russia, Ukraine, Belarus, Kazakhstan, Georgia, Abkhazia, South Ossetia, Armenia, Azerbaijan,
                                                                                                                                     Moldova, Turkmenistan, Tajikistan, Uzbekistan, Kyrgyzstan and Turkey.');
$config_variable[$setting]['params']['mapquest']      = array('name' => "MapQuest", 'required' => 'geocoding|api_key', 'subtext' => '<strong>REQUIRED to use GEO API KEY</strong>', 'desc' => 'Request a KEY <a href="https://developer.mapquest.com/user/register" target="_blank">here</a>');
$config_variable[$setting]['shortdesc']  = "Which API to use to resolve your addresses into coordinates. We currently support MapQuest, Google, Yandex and Openstreetmap. If locations turn up unknown, try switching to Google.";

$setting = 'geocoding|api_key';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "GEO Coding";
$config_variable[$setting]['name']       = "KEY for currently used GEO API";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Keys allowed in Google, Yandex and required for MapQuest";

$setting = 'geocoding|dns';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "GEO Coding";
$config_variable[$setting]['name']       = "Use DNS LOC records for geolocation";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Try to use DNS LOC records for detect device coordinates. See http://en.wikipedia.org/wiki/LOC_record and http://dnsloc.net/";

$setting = 'geocoding|default|lat';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "GEO Coding";
$config_variable[$setting]['name']       = "Default latitude";
$config_variable[$setting]['type']       = "float";
$config_variable[$setting]['shortdesc']  = "These latitude used by default if request to an geo api return nothing.";

$setting = 'geocoding|default|lon';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "GEO Coding";
$config_variable[$setting]['name']       = "Default longitude";
$config_variable[$setting]['type']       = "float";
$config_variable[$setting]['shortdesc']  = "These longitude used by default if request to an geo api return nothing.";

$setting = 'show_locations';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Web UI";
$config_variable[$setting]['name']       = "Enable Locations on menu";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "";

$setting = 'show_locations_dropdown';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Web UI";
$config_variable[$setting]['name']       = "Enable Locations dropdown on menu";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "";

$setting = 'location_menu_geocoded';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Web UI";
$config_variable[$setting]['name']       = "Build location menu with geocoded data";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Set to FALSE if you want to use the regular location menu, simply listing your configured locations. Useful if you don't have mappable addresses in your devices. Automatically disabled if you completely disable geocoding.";

/// INTEGRATION //////////////////////////////////////////////////////

$config_sections['integration']['text'] = "Integration";

$section = 'integration';

/*
$setting = 'rancid_configs';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "RANCID";
$config_variable[$setting]['name']       = "RANCID configuration directories";
$config_variable[$setting]['type']       = "array|string";
$config_variable[$setting]['shortdesc']  = "Defines rancid configuration directories. This is an array, multiple rancid groups are supported. For performance, put your largest/most likely group in front. Configurations should have the same hostname as used in Observium. Leave empty to disable.";
*/

$setting = 'rancid_ignorecomments';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "RANCID";
$config_variable[$setting]['name']       = "Ignore comments";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Toggles whether or not to filter out RANCID comments (lines starting with #) in configuration files.";

$setting = 'rancid_version';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "RANCID";
$config_variable[$setting]['name']       = "RANCID version used";
$config_variable[$setting]['type']       = "enum|2|3";
$config_variable[$setting]['shortdesc']  = "Depending on the RANCID version, a different delimiter is used in the RANCID configuration files (: vs ;).";

$setting = 'rancid_suffix';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "RANCID";
$config_variable[$setting]['name']       = "Hostname suffix";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "This string is added to the hostname in RANCID, for non-FQDN device names.";

$setting = 'smokeping|dir';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Smokeping";
$config_variable[$setting]['name']       = "Smokeping directory";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Defines smokeping directory containing the RRD files. Names in Smokeping should use the split character (as defined below) instead of dots.";

$setting = 'smokeping|split_char';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Smokeping";
$config_variable[$setting]['name']       = "Split character";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Defines what character to use in Smokeping host titles, to replace the dot as this is not allowed in Smokeping configuration.";

$setting = 'smokeping|suffix';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Smokeping";
$config_variable[$setting]['name']       = "Hostname suffix";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "This string is added to the hostname in Smokeping, for non-FQDN device names.";

/*
$setting = 'smokeping|slaves';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Smokeping";
$config_variable[$setting]['name']       = "Smokeping slaves";
$config_variable[$setting]['type']       = "array|string";
$config_variable[$setting]['shortdesc']  = "Defines slaves to be used in Smokeping configuration. Only used by the Smokeping configuration generator script.";
*/

$setting = 'collectd_dir';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "CollectD";
$config_variable[$setting]['name']       = "CollectD directory";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Defines collectd directory. Hosts should be set to use the same hostname in collectd.conf as is used in Observium.";

$setting = 'nfsen_enable';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "NfSen";
$config_variable[$setting]['name']       = "Enable NfSen integration";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Allows you to read RRD files created by NfSen.";

$setting = 'nfsen_rrds';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "NfSen";
$config_variable[$setting]['name']       = "NfSen RRD path";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Local file system path to NfSen RRD files";

$setting = 'nfsen_split_char';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "NfSen";
$config_variable[$setting]['name']       = "NfSen split character";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "This character will be converted to dots (router1_foo -> router1.foo) and nfsen_suffix will be appended (router1_foo_yourdomain_com - > router1.foo.yourdomain.com).";

$setting = 'nfsen_suffix';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "NfSen";
$config_variable[$setting]['name']       = "NfSen suffix";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "This string is added to the hostname in NfSen. According to nfsen.conf, ident strings must be 1 to 19 characters long only, containing characters [a-zA-Z0-9_].";

/// ROUTING //////////////////////////////////////////////////////////

$config_sections['routing']['text'] = "Routing";

$setting = 'enable_bgp';
$config_variable[$setting]['section']    = "routing";
$config_variable[$setting]['subsection'] = "Protocols";
$config_variable[$setting]['name']       = "BGP collection";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Enable collection and display of BGP data.";

//$setting = 'enable_rip';
//$config_variable[$setting]['section']    = "routing";
//$config_variable[$setting]['subsection'] = "Protocols";
//$config_variable[$setting]['name']       = "RIP sessions";
//$config_variable[$setting]['type']       = "bool";
//$config_variable[$setting]['shortdesc']  = "Enable collection and display of RIP data.";

$setting = 'enable_ospf';
$config_variable[$setting]['section']    = "routing";
$config_variable[$setting]['subsection'] = "Protocols";
$config_variable[$setting]['name']       = "OSPF collection";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Enable collection and display of OSPF data.";

//$setting = 'enable_isis';
//$config_variable[$setting]['section']    = "routing";
//$config_variable[$setting]['subsection'] = "Protocols";
//$config_variable[$setting]['name']       = "ISIS sessions";
//$config_variable[$setting]['type']       = "bool";
//$config_variable[$setting]['shortdesc']  = "Enable collection and display of ISIS data.";

$setting = 'enable_eigrp';
$config_variable[$setting]['section']    = "routing";
$config_variable[$setting]['subsection'] = "Protocols";
$config_variable[$setting]['name']       = "EIGRP collection";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Enable collection and display of EIGRP data.";

/// BILLING //////////////////////////////////////////////////////////

$section = 'billing';
$config_sections[$section]['text']       = "Billing";
$config_sections[$section]['edition']    = 'pro';

$setting = 'enable_billing';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['edition']    = 'pro';
$config_variable[$setting]['subsection'] = "General";
$config_variable[$setting]['name']       = "Billing module";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Enable Billing module";

$setting = 'billing|customer_autoadd';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['edition']    = 'pro';
$config_variable[$setting]['subsection'] = "General";
$config_variable[$setting]['name']       = "Customer auto-add";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Enable Auto-add bill per customer";

$setting = 'billing|circuit_autoadd';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['edition']    = 'pro';
$config_variable[$setting]['subsection'] = "General";
$config_variable[$setting]['name']       = "Circuit ID auto-add";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Enable Auto-add bill per circuit_id";

$setting = 'billing|bill_autoadd';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['edition']    = 'pro';
$config_variable[$setting]['subsection'] = "General";
$config_variable[$setting]['name']       = "Bill ID auto-add";
$config_variable[$setting]['type']       = "bool";
$config_variable[$setting]['shortdesc']  = "Enable Auto-add bill per bill_id";

$setting = 'billing|base';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['edition']    = 'pro';
$config_variable[$setting]['subsection'] = "General";
$config_variable[$setting]['name']       = "Billing base";
$config_variable[$setting]['type']       = "enum";
$config_variable[$setting]['params']     = array(1000 => array('name' => 1000, 'subtext' => '1kB = 1000B'), 1024 => array('subtext' => '1kB = 1024B'));
$config_variable[$setting]['shortdesc']  = "Set the base to divider bytes to kB, MB, GB, ... 1000 or 1024";

/// PATHS ////////////////////////////////////////////////////////////

$section = 'paths';
$config_sections[$section]['text'] = "System Paths";

// Observium system paths

$setting = 'temp_dir';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "System paths";
$config_variable[$setting]['name']       = "Temporary directory";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Path to write temporary files to (e.g. RRDtool graphs). Must be writable by the webserver user.";

// Essential binaries

$setting = 'rrdtool';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Essential binaries";
$config_variable[$setting]['name']       = "Path to 'rrdtool' binary";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Path to the 'rrdtool' binary. Required.";

$setting = 'fping';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Essential binaries";
$config_variable[$setting]['name']       = "Path to 'fping' binary";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Path to the 'fping' binary. Required for IPv4 support.";

$setting = 'fping6';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Essential binaries";
$config_variable[$setting]['name']       = "Path to 'fping6' binary";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Path to the 'fping6' binary. Required for IPv6 support.";

$setting = 'svn';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Essential binaries";
$config_variable[$setting]['name']       = "Path to 'svn' binary";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Path to the 'svn' binary. Required for versioning (in Pro) or for RANCID svn-based repository support.";

// SNMP

$setting = 'snmpget';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "SNMP";
$config_variable[$setting]['name']       = "Path to 'snmpget' binary";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Path to the 'snmpget' binary. Required.";

$setting = 'snmpwalk';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "SNMP";
$config_variable[$setting]['name']       = "Path to 'snmpwalk' binary";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Path to the 'snmpwalk' binary. Required.";

$setting = 'snmpbulkget';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "SNMP";
$config_variable[$setting]['name']       = "Path to 'snmpbulkget' binary";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Path to the 'snmpbulkget' binary. Required.";

$setting = 'snmpbulkwalk';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "SNMP";
$config_variable[$setting]['name']       = "Path to 'snmpbulkwalk' binary";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Path to the 'snmpbulkwalk' binary. Required.";

$setting = 'snmptranslate';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "SNMP";
$config_variable[$setting]['name']       = "Path to 'snmptranslate' binary";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Path to the 'snmptranslate' binary. Required.";

// Integration

$setting = 'ipmitool';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Integration";
$config_variable[$setting]['name']       = "Path to 'ipmitool' binary";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Path to the 'ipmitool' binary. Required for IPMI polling support.";

$setting = 'virsh';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Integration";
$config_variable[$setting]['name']       = "Path to 'virsh' binary";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Path to the 'virsh' binary. Required for libvirt-based Virtual Machine polling support.";

$setting = 'wmic';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Integration";
$config_variable[$setting]['name']       = "Path to 'wmic' binary";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Path to the 'wmic' binary. Required for WMI (Windows Management Instrumentation) polling support.";

// Web UI toolkit

$setting = 'whois';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Network tools";
$config_variable[$setting]['name']       = "Path to 'whois' binary";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Path to the 'whois' binary. Required for whois support in the web interface.";

$setting = 'mtr';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Network tools";
$config_variable[$setting]['name']       = "Path to 'mtr' binary";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Path to the 'mtr' binary. Required for mtr support in the web interface.";

$setting = 'nmap';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Network tools";
$config_variable[$setting]['name']       = "Path to 'nmap' binary";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Path to the 'nmap' binary. Required for nmap support in the web interface.";

// Housekeeping

$setting = 'file';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Housekeeping";
$config_variable[$setting]['name']       = "Path to 'file' binary";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Path to the 'file' binary. Required for housekeeping of broken RRD files.";

// RANCID

$setting = 'git';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Integration";
$config_variable[$setting]['name']       = "Path to 'git' binary";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Path to the 'git' binary. Required for RANCID git-based repository support.";

// Mapping

$setting = 'dot';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Neighbour maps";
$config_variable[$setting]['name']       = "Path to 'dot' binary";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Path to the 'dot' graphviz binary. Required for display of neighbour maps";

/* All of this binaries not used for now in neighbour maps
$setting = 'unflatten';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Neighbour maps";
$config_variable[$setting]['name']       = "Path to 'unflatten' binary";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Path to the 'unflatten' graphviz binary. Required for display of neighbour maps";

$setting = 'neato';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Neighbour maps";
$config_variable[$setting]['name']       = "Path to 'neato' binary";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Path to the 'neato' graphviz binary. Required for display of neighbour maps";

$setting = 'sfdp';
$config_variable[$setting]['section']    = $section;
$config_variable[$setting]['subsection'] = "Neighbour maps";
$config_variable[$setting]['name']       = "Path to 'sfdp' binary";
$config_variable[$setting]['type']       = "string";
$config_variable[$setting]['shortdesc']  = "Path to the 'sfdp' graphviz binary. Required for display of neighbour maps";
*/

/* Not included right now: $config['nagios_plugins'] - deprecated */

// EOF
