<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webinterface
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

include_once("../includes/defaults.inc.php");
include_once("../config.php");
include_once("../includes/definitions.inc.php");
include($config['install_dir'] . "/includes/functions.inc.php");
include($config['html_dir'] . "/includes/functions.inc.php");

// Preflight checks

if (!is_dir($config['rrd_dir']))
{
  print_error("RRD Directory is missing ({$config['rrd_dir']}).  Graphing may fail.");
}

if (!is_dir($config['log_dir']))
{
  print_error("Log Directory is missing ({$config['log_dir']}).  Logging may fail.");
}

if (!is_dir($config['temp_dir']))
{
  print_error("Temp Directory is missing ({$config['temp_dir']}).  Graphing may fail.");
}

if (!is_writable($config['temp_dir']))
{
  print_error("Temp Directory is not writable ({$config['tmp_dir']}).  Graphing may fail.");
}

if (ini_get('register_globals'))
{
  $notifications[] = array('text' => 'register_globals enabled in php.ini. Disable it!', 'severity' => 'alert');
}
// verify if PHP supports session, die if it does not
check_extension_exists('session', '', TRUE);

ob_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <base href="<?php echo($config['base_url']); ?>" />
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <link href="css/bootstrap.css" rel="stylesheet" type="text/css" />
  <link href="css/bootstrap-select.css" rel="stylesheet" type="text/css" />
  <link href="css/bootstrap-hacks.css" rel="stylesheet" type="text/css" />
  <link href="css/jquery.qtip.min.css" rel="stylesheet" type="text/css" />
  <link href="css/sprite.css" rel="stylesheet" type="text/css" />
  <link href="css/flags.css" rel="stylesheet" type="text/css" />

  <script type="text/javascript" src="js/jquery.min.js"></script>

<?php /* html5.js below from https://github.com/aFarkas/html5shiv */ ?>
  <!--[if lt IE 9]><script src="js/html5shiv.min.js"></script><![endif]-->
<?php
// If the php-ref scripts are installed, load up the bits needed
if ($ref_loaded)
{
?>
  <script type="text/javascript" src="js/ref.js"></script>
  <link   href="css/ref.css" rel="stylesheet" type="text/css" />
<?php
}

$runtime_start = utime();

ini_set('allow_url_fopen', 0);
ini_set('display_errors', 0);

$_SERVER['PATH_INFO'] = (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $_SERVER['ORIG_PATH_INFO']);

$vars = get_vars(); // Parse vars from GET/POST/URI
// print_vars($vars);

if ($vars['page'] == 'device')
{
  // Code prettify (but it's still horrible)
  if ($vars['tab'] == 'showconfig')
  {
    echo('
    <script type="text/javascript" src="js/google-code-prettify.js"></script>
    <link   href="css/google-code-prettify.css" rel="stylesheet" type="text/css" />');
  }
  // DHTML expandable tree
  if ($vars['tab'] == 'entphysical')
  {
    echo('
    <script type="text/javascript" src="js/mktree.js"></script>
    <link   href="css/mktree.css" rel="stylesheet" type="text/css" />');
  }
}

include($config['html_dir'] . "/includes/authenticate.inc.php");

if ($vars['widescreen'] == "yes") { $_SESSION['widescreen'] = 1; unset($vars['widescreen']); }
if ($vars['widescreen'] == "no")  { unset($_SESSION['widescreen']); unset($vars['widescreen']); }

if ($vars['big_graphs'] == "yes") { $_SESSION['big_graphs'] = 1; unset($vars['big_graphs']); }
if ($vars['big_graphs'] == "no")  { unset($_SESSION['big_graphs']); unset($vars['big_graphs']); }

// Load the settings for Multi-Tenancy. - FIXME i don't think we still support this, nor that it really works well. could/should be done in config.php by who needs this.
if (isset($config['branding']) && is_array($config['branding']))
{
  if ($config['branding'][$_SERVER['SERVER_NAME']])
  {
    foreach ($config['branding'][$_SERVER['SERVER_NAME']] as $confitem => $confval)
    {
      eval("\$config['" . $confitem . "'] = \$confval;");
    }
  } else {
    foreach ($config['branding']['default'] as $confitem => $confval)
    {
      eval("\$config['" . $confitem . "'] = \$confval;");
    }
  }
}

// page_title_prefix is displayed, unless page_title is set
if ($config['page_title']) { $config['page_title_prefix'] = $config['page_title']; }

$page_refresh = print_refresh($vars); // $page_refresh used in navbar for refresh menu

?>
  <title><?php echo($config['page_title_prefix'] . ($config['page_title_prefix'] != '' && $config['page_title_suffix'] != '' ? ' - ' : '') . $config['page_title_suffix']); ?></title>
  <link rel="shortcut icon" href="<?php echo($config['favicon']);  ?>" />
<?php

$feeds = array('eventlog');
//if ($config['enable_syslog']) { $feeds[] = 'syslog'; }
foreach ($feeds as $feed)
{
  $feed_href = generate_feed_url(array('feed' => $feed));
  if ($feed_href) echo($feed_href.PHP_EOL);
}

if ($_SESSION['widescreen']) { echo('<link rel="stylesheet" href="css/styles-wide.css" type="text/css" />'); }

echo '</head>';
?>

<body>
<header class="navbar navbar-fixed-top">
    <div class="navbar-inner">
      <div class="container">
        <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target="#main-nav">
          <span class="oicon-bar"></span>
          <span class="oicon-bar"></span>
          <span class="oicon-bar"></span>
        </button>
        <a class="brand brand-observium" href="">&nbsp;</a>
        <div class="nav-collapse" id="main-nav">
          <ul class="nav">
            <li class="divider-vertical" style="margin: 0;"></li>
            <li class="dropdown">
              <a href="overview/" class="hidden-sm dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
                <i class="oicon-globe-model"></i>  <b class="caret"></b></a>
              <a href="overview/" class="visible-sm dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
                <i class="oicon-globe-model"></i> <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="overview/"><i class="menu-icon oicon-globe-model"></i> Overview</a></li>
                <li class="divider"></li>
                <li><a href="eventlog/"><i class="menu-icon oicon-clipboard-audit"></i> Event Log</a></li>
                <li><a href="syslog/"><i class="menu-icon oicon-clipboard-eye"></i> Syslog</a></li>
                <li><a href="pollerlog/"><i class="menu-icon oicon-clipboard-report-bar"></i> Polling Information</a></li>
                <li class="divider"></li>
                <li><a href="alerts/"><i class="menu-icon oicon-bell"></i> Alerts</a></li>
                <li><a href="alert_checks/"><i class="menu-icon oicon-eye"></i> Alert Checks</a></li>
                <li><a href="alert_log/"><i class="menu-icon oicon-bell--exclamation"></i> Alert Logs</a></li>
                <li class="divider"></li>
                <li><a href="groups/"><i class="menu-icon oicon-category"></i> Groups</a></li>
                <li class="divider"></li>
                <li><a href="inventory/"><i class="menu-icon oicon-wooden-box"></i> Inventory</a></li>
                <li class="divider"></li>
                <li class="dropdown-submenu"><a href="search/"><i class="menu-icon oicon-magnifier-zoom-actual"></i> Search</a>
                  <ul class="dropdown-menu">
                    <li><a href="search/search=ipv4/"><i class="menu-icon oicon-magnifier-zoom-actual"></i> IPv4 Address    </a></li>
                    <li><a href="search/search=ipv6/"><i class="menu-icon oicon-magnifier-zoom-actual"></i> IPv6 Address    </a></li>
                    <li><a href="search/search=mac/"><i class="menu-icon oicon-magnifier-zoom-actual"></i> MAC Address    </a></li>
                    <li><a href="search/search=arp/"><i class="menu-icon oicon-magnifier-zoom-actual"></i> ARP/NDP Tables    </a></li>
                    <li><a href="search/search=fdb/"><i class="menu-icon oicon-magnifier-zoom-actual"></i> FDB Tables    </a></li>
                    <li><a href="search/search=dot1x/"><i class="menu-icon oicon-magnifier-zoom-actual"></i> .1x Sessions    </a></li>
                  </ul>
                <li>
              </ul>
            </li>
            <li class="divider-vertical" style="margin: 0;"></li>
            <li class="dropdown">
              <a href="devices/" class="hidden-sm dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
                <i class="oicon-servers"></i> Devices <b class="caret"></b></a>
              <a href="devices/" class="visible-sm dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
                <i class="oicon-servers"></i> <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="devices/"><i class="menu-icon oicon-servers"></i> All Devices</a></li>
                <li class="divider"></li>
                <li class="dropdown-submenu">
                  <a href="locations/"><i class="menu-icon oicon-building-hedge"></i> Locations</a>
                </li>
                <li class="divider"></li>
                <li><a href="devices/type=server/"><i class="menu-icon oicon-server"></i> Servers&nbsp;<span class="right">(5)</span></a></li>
                <li><a href="devices/type=workstation/"><i class="menu-icon oicon-computer"></i> Workstations&nbsp;<span class="right">(1)</span></a></li>
                <li><a href="devices/type=network/"><i class="menu-icon oicon-network-hub"></i> Network&nbsp;<span class="right">(24)</span></a></li>
                <li><a href="devices/type=wireless/"><i class="menu-icon oicon-wi-fi-zone"></i> Wireless&nbsp;<span class="right">(7)</span></a></li>
                <li><a href="devices/type=firewall/"><i class="menu-icon oicon-wall-brick"></i> Firewalls&nbsp;<span class="right">(4)</span></a></li>
                <li><a href="devices/type=power/"><i class="menu-icon oicon-plug"></i> Power&nbsp;<span class="right">(1)</span></a></li>
                <li><a href="devices/type=environment/"><i class="menu-icon oicon-water"></i> Environment&nbsp;<span class="right">(1)</span></a></li>
                <li><a href="devices/type=loadbalancer/"><i class="menu-icon oicon-arrow-split"></i> Load Balancers&nbsp;<span class="right">(2)</span></a></li>
                <li><a href="devices/type=storage/"><i class="menu-icon oicon-database"></i> Storage&nbsp;<span class="right">(1)</span></a></li>
                <li class="divider"></li>
                <li><a href="devices/status=0/"><i class="menu-icon oicon-circle-red"></i> Down</a></li>
                <li><a href="devices/ignore=1/"><i class="menu-icon oicon-circle-yellow"></i> Ignored</a></li>
                <li class="divider"></li>
                <li><a href="addhost/"><i class="menu-icon oicon-server--plus"></i> Add Device</a></li>
                <li><a href="delhost/"><i class="menu-icon oicon-server--minus"></i> Delete Device</a></li>
              </ul>
            </li>
            <li class="divider-vertical" style="margin: 0;"></li>
            <li class="dropdown">
              <a href="ports/" class="hidden-sm dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
                <i class="oicon-network-ethernet"></i> Ports <b class="caret"></b></a>
              <a href="ports/" class="visible-sm dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
                <i class="oicon-network-ethernet"></i> <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="ports/"><i class="menu-icon oicon-network-ethernet"></i> All Ports&nbsp;<span class="right">(4355)</span></a></li>
                <li class="divider"></li>
                <li><a href="bills/"><i class="menu-icon oicon-money-coin"></i> Traffic Accounting</a></li>
                <li><a href="pseudowires/"><i class="menu-icon oicon-layer-shape-curve"></i> Pseudowires (8)</a></li>
                <li><a href="ports/cbqos=yes/"><i class="menu-icon oicon-category-group"></i> CBQoS (362)</a></li>
                <li class="divider"></li>
                <li><a href="customers/"><i class="menu-icon "></i> <img src="images/16/group_link.png" alt="" /> Customers</a></li>
                <li><a href="iftype/type=l2tp/"><i class="menu-icon "></i> <img src="images/16/user.png" alt="" /> L2TP</a></li>
                <li><a href="iftype/type=transit/"><i class="menu-icon "></i> <img src="images/16/lorry_link.png" alt="" /> Transit</a></li>
                <li><a href="iftype/type=peering/"><i class="menu-icon "></i> <img src="images/16/bug_link.png" alt="" /> Peering</a></li>
                <li><a href="iftype/type=peering%2Ctransit/"><i class="menu-icon "></i> <img src="images/16/world_link.png" alt="" /> Peering & Transit</a></li>
                <li><a href="iftype/type=core/"><i class="menu-icon "></i> <img src="images/16/brick_link.png" alt="" /> Core</a></li>
                <li class="divider"></li>
                <li><a href="ports/alerted=yes/"><i class="menu-icon oicon-chain--exclamation"></i> Alerts&nbsp;<span class="right">(446)</span></a></li>
                <li><a href="ports/errors=yes/"><i class="menu-icon oicon-exclamation-button"></i> Errored&nbsp;<span class="right">(11)</span></a></li>
                <li><a href="ports/state=down/"><i class="menu-icon oicon-network-status-busy"></i> Down</a></li>
                <li><a href="ports/ignore=1/"><i class="menu-icon oicon-network-status-away"></i> Ignored</a></li>
                <li><a href="ports/state=admindown/"><i class="menu-icon oicon-network-status-offline"></i> Disabled</a></li>
                <li><a href="deleted-ports/"><i class="menu-icon oicon-badge-square-minus"></i> Deleted&nbsp;<span class="right">(337)</span></a></li>
              </ul>
            </li>
            <li class="divider-vertical" style="margin: 0;"></li>
            <li class="dropdown">
              <a href="#" class="hidden-sm dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
                <i class="oicon-system-monitor"></i> Health <b class="caret"></b></a>
              <a href="#" class="visible-sm dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
                <i class="oicon-system-monitor"></i> <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="health/metric=processor/"><i class="menu-icon oicon-processor"></i> Processors</a></li>
                <li><a href="health/metric=mempool/"><i class="menu-icon oicon-memory"></i> Memory</a></li>
                <li><a href="health/metric=storage/"><i class="menu-icon oicon-drive"></i> Storage</a></li>
                <li class="divider"></li>
                <li><a href="health/metric=fanspeed/"><i class="menu-icon oicon-weather-wind"></i> Fanspeed </a></li>
                <li><a href="health/metric=humidity/"><i class="menu-icon oicon-water"></i> Humidity </a></li>
                <li><a href="health/metric=temperature/"><i class="menu-icon oicon-thermometer-high"></i> Temperature <i class="oicon-exclamation-red"></i></a></li>
                <li class="divider"></li>
                <li><a href="health/metric=current/"><i class="menu-icon oicon-current"></i> Current <i class="oicon-exclamation-red"></i></a></li>
                <li><a href="health/metric=voltage/"><i class="menu-icon oicon-voltage"></i> Voltage <i class="oicon-exclamation-red"></i></a></li>
                <li><a href="health/metric=power/"><i class="menu-icon oicon-power"></i> Power <i class="oicon-exclamation-red"></i></a></li>
                <li><a href="health/metric=frequency/"><i class="menu-icon oicon-frequency"></i> Frequency </a></li>
                <li class="divider"></li>
                <li><a href="health/"><i class="menu-icon "></i>  </a></li>
                <li><a href="health/metric=runtime/"><i class="menu-icon oicon-time"></i> Runtime </a></li>
                <li><a href="health/metric=state/"><i class="menu-icon oicon-exclamation-white"></i> State <i class="oicon-exclamation-red"></i></a></li>
                <li><a href="health/metric=capacity/"><i class="menu-icon oicon-ui-progress-bar"></i> Capacity </a></li>
                <li><a href="health/metric=dbm/"><i class="menu-icon oicon-arrow-incident-red"></i> dBm <i class="oicon-exclamation-red"></i></a></li>
                <li><a href="health/metric=volts/"><i class="menu-icon "></i> Volts </a></li>
                <li><a href="health/metric=amps/"><i class="menu-icon "></i> Amps </a></li>
                <li><a href="health/metric=ampere/"><i class="menu-icon "></i> Ampere </a></li>
              </ul>
            </li>
            <li class="divider-vertical" style="margin: 0;"></li>
            <li class="dropdown">
              <a href="#" class="hidden-sm dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
                <i class="oicon-application-icon-large"></i> Apps <b class="caret"></b></a>
              <a href="#" class="visible-sm dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
                <i class="oicon-application-icon-large"></i> <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="apps/app=apache/"><i class="menu-icon "></i> <img src="images/icons/apache.png" alt="" /> Apache</a></li>
                <li><a href="apps/app=bind/"><i class="menu-icon "></i> <img src="images/icons/bind.png" alt="" /> BIND</a></li>
                <li><a href="apps/app=memcached/"><i class="menu-icon "></i> <img src="images/icons/memcached.png" alt="" /> Memcached</a></li>
                <li><a href="apps/app=mysql/"><i class="menu-icon "></i> <img src="images/icons/mysql.png" alt="" /> MySQL</a></li>
              </ul>
            </li>
            <li class="divider-vertical" style="margin: 0;"></li>
            <li class="dropdown">
              <a href="#" class="hidden-sm dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
                <i class="oicon-arrow-branch-000-left"></i> Routing <b class="caret"></b></a>
              <a href="#" class="visible-sm dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
                <i class="oicon-arrow-branch-000-left"></i> <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="routing/protocol=vrf/"><i class="menu-icon oicon-arrow-branch-byr"></i> VRFs&nbsp;<span class="right">(10)</span></a></li>
                <li class="divider"></li>
                <li><a href="routing/protocol=ospf/"><i class="menu-icon "></i> <img src="images/16/text_letter_omega.png" alt="" /> OSPF&nbsp;<span class="right">(6)</span></a></li>
                <li class="divider"></li>
                <li><a href="routing/protocol=bgp/type=all/graph=NULL/"><i class="menu-icon "></i> <img src="images/16/link.png" alt="" /> BGP All Sessions&nbsp;<span class="right">(329)</span></a></li>
                <li><a href="routing/protocol=bgp/type=external/graph=NULL/"><i class="menu-icon "></i> <img src="images/16/world_link.png" alt="" /> BGP External</a></li>
                <li><a href="routing/protocol=bgp/type=internal/graph=NULL/"><i class="menu-icon "></i> <img src="images/16/brick_link.png" alt="" /> BGP Internal</a></li>
                <li class="divider"></li>
                <li><a href="routing/protocol=bgp/adminstatus=start/state=down/"><i class="menu-icon "></i> <img src="images/16/link_error.png" alt="" /> BGP Alerts&nbsp;<span class="right">(2)</span></a></li>
              </ul>
            </li>
          </ul>
          <ul class="nav pull-right">
          <li class="dropdown hidden-xs">
            <form id="searchform" class="navbar-search" action="#" style="margin-left: 5px; margin-right: 10px;  margin-top: 5px; margin-bottom: -5px;">
              <input style="width: 100px;" onkeyup="lookup(this.value);" onblur="$('#suggestions').fadeOut()" type="text" value="" class="dropdown-toggle" placeholder="Search" />
            </form>
            <div id="suggestions" class="typeahead dropdown-menu"></div>
          </li>
<li><a href="device/device=340/tab=edit/section=mibs/touch=yes/"> <i class="icon-laptop"></i></a></li>            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><i class="oicon-gear"></i> <b class="caret"></b></a>
              <ul class="dropdown-menu">
<li class="dropdown-submenu">  <a tabindex="-1" href="device/device=340/tab=edit/section=mibs/"><i class="oicon-arrow-circle"></i> Refresh <span id="countrefresh"></span></a>  <ul class="dropdown-menu">    <li class="disabled"><a href="device/device=340/tab=edit/section=mibs/refresh=0/"><i class="icon-ban-circle"></i> Manually</a></li>    <li class="disabled"><a href="device/device=340/tab=edit/section=mibs/refresh=60/"><i class="icon-refresh"></i> Every 1 minute</a></li>    <li class="disabled"><a href="device/device=340/tab=edit/section=mibs/refresh=120/"><i class="icon-refresh"></i> Every 2 minutes</a></li>    <li class="disabled"><a href="device/device=340/tab=edit/section=mibs/refresh=300/"><i class="icon-refresh"></i> Every 5 minutes</a></li>    <li class="disabled"><a href="device/device=340/tab=edit/section=mibs/refresh=900/"><i class="icon-refresh"></i> Every 15 minutes</a></li>    <li class="disabled"><a href="device/device=340/tab=edit/section=mibs/refresh=1800/"><i class="icon-refresh"></i> Every 30 minutes</a></li>  </ul></li><li class="divider"></li><li><a href="device/device=340/tab=edit/section=mibs/widescreen=yes/" title="Switch to wide screen layout"><i class="oicon-arrow-move" style="font-size: 16px; color: #555;"></i> Widescreen</a></li><li><a href="device/device=340/tab=edit/section=mibs/big_graphs=yes/" title="Switch to larger graphs"><i class="oicon-layout-4" style="font-size: 16px; color: #555;"></i> Large Graphs</a></li><li class="divider"></li><li class="dropdown-submenu">  <a tabindex="-1" href="simpleapi/"><i class="oicon-application-block"></i> Simple API</a>  <ul class="dropdown-menu">    <li><a href="simpleapi/"><i class="oicon-application-block"></i> API Manual</a></li>    <li><a href="simpleapi/api=errorcodes/"><i class="oicon-application--exclamation"></i> Error Codes</a></li>  </ul></li><li class="divider"></li><li class="dropdown-submenu">  <a tabindex="-1" href="adduser/"><i class="oicon-users"></i> Users</a>  <ul class="dropdown-menu">    <li><a href="adduser/"><i class="oicon-user--plus"></i> Add User</a></li>    <li><a href="edituser/"><i class="oicon-user--pencil"></i> Edit User</a></li>    <li><a href="edituser/"><i class="oicon-user--minus"></i> Remove User</a></li>    <li><a href="authlog/"><i class="oicon-user-detective"></i> Authentication Log</a></li>  </ul></li>                <li class="divider"></li>
                <li><a href="settings/" title="Global Settings"><i class="oicon-wrench"></i> Global Settings</a></li>
                <li><a href="preferences/" title="My Settings "><i class="oicon-wrench-screwdriver"></i> My Settings</a></li>
                <li class="divider"></li>
                <li><a href="logout/" title="Logout"><i class="oicon-door-open-out"></i> Logout</a></li>
                <li class="divider"></li>
                <li><a href="http://www.observium.org/wiki/Documentation" title="Help"><i class="oicon-question"></i> Help</a></li>
                <li><a href="about/"><i class="oicon-information"></i> About Observium</a></li>
              </ul>
            </li>
          </ul>
        </div><!-- /.nav-collapse -->
      </div>
    </div><!-- /navbar-inner -->
  </header>

<script type="text/javascript">

key_count_global = 0;
function lookup(inputString) {
  if (inputString.trim().length == 0) {
    $('#suggestions').fadeOut(); // Hide the suggestions box
  } else {
    key_count_global++;
    setTimeout("lookupwait("+key_count_global+",\""+inputString+"\")", 300); // Added timeout 0.3s before send query
  }
}

function lookupwait(key_count,inputString) {
  if(key_count == key_count_global) {
    $.post("ajax_search.php", {queryString: ""+inputString+""}, function(data) { // Do an AJAX call
      $('#suggestions').fadeIn(); // Show the suggestions box
      $('#suggestions').html(data); // Fill the suggestions box
    });
  }
}


</script>

  <div class="container">

<table class="table table-hover table-striped table-bordered table-condensed table-rounded" style="vertical-align: middle; margin-bottom: 10px;">
              <tbody><tr class="up" style="vertical-align: middle;">
               <td class="state-marker"></td>
               <td style="width: 70px; text-align: center; vertical-align: middle;"><img src="images/os/linux.png" alt=""></td>
               <td style="vertical-align: middle;"><span style="font-size: 20px;"><a href="device/device=1/" class="entity-popup " data-eid="1" data-etype="device" data-hasqtip="0">localhost</a></span>
               <br><a href="devices/location=czoyNzoiSGV0em5lciwgTnVyZW1iZXJnLCBHZXJtYW55Ijs%3D/">Hetzner, Nuremberg, Germany</a></td>
               <td><div class="pull-right" style="padding: 2px; margin: 0;"><a href="graphs/to=1423852680/device=1/type=device_processor/from=1423766280/legend=yes/" class="tooltip-from-data " data-tooltip="<div class=entity-title></div><div style=&quot;width: 850px&quot;><img src=&quot;graph.php?height=100&amp;amp;width=340&amp;amp;to=1423852680&amp;amp;device=1&amp;amp;type=device_processor&amp;amp;from=1423766280&amp;amp;legend=yes&amp;amp;popup_title=&amp;amp;bg=FFFFFF00&quot; style=&quot;max-width: 100%; width: auto; &quot; alt=&quot;&quot; /><img src=&quot;graph.php?height=100&amp;amp;width=340&amp;amp;to=1423852680&amp;amp;device=1&amp;amp;type=device_processor&amp;amp;from=1423247880&amp;amp;legend=yes&amp;amp;popup_title=&amp;amp;bg=FFFFFF00&quot; style=&quot;max-width: 100%; width: auto; &quot; alt=&quot;&quot; /><img src=&quot;graph.php?height=100&amp;amp;width=340&amp;amp;to=1423852680&amp;amp;device=1&amp;amp;type=device_processor&amp;amp;from=1421174280&amp;amp;legend=yes&amp;amp;popup_title=&amp;amp;bg=FFFFFF00&quot; style=&quot;max-width: 100%; width: auto; &quot; alt=&quot;&quot; /><img src=&quot;graph.php?height=100&amp;amp;width=340&amp;amp;to=1423852680&amp;amp;device=1&amp;amp;type=device_processor&amp;amp;from=1392316680&amp;amp;legend=yes&amp;amp;popup_title=&amp;amp;bg=FFFFFF00&quot; style=&quot;max-width: 100%; width: auto; &quot; alt=&quot;&quot; /></div>" data-hasqtip="7"><img src="graph.php?height=45&amp;width=150&amp;to=1423852680&amp;device=1&amp;type=device_processor&amp;from=1423766280&amp;legend=no&amp;popup_title=&amp;bg=FFFFFF00" style="max-width: 100%; width: auto; " alt=""></a><div style="padding: 0px; font-weight: bold; font-size: 7pt; text-align: center;">Processors</div></div><div class="pull-right" style="padding: 2px; margin: 0;"><a href="graphs/to=1423852680/device=1/type=device_ucd_memory/from=1423766280/legend=yes/" class="tooltip-from-data " data-tooltip="<div class=entity-title></div><div style=&quot;width: 850px&quot;><img src=&quot;graph.php?height=100&amp;amp;width=340&amp;amp;to=1423852680&amp;amp;device=1&amp;amp;type=device_ucd_memory&amp;amp;from=1423766280&amp;amp;legend=yes&amp;amp;popup_title=&amp;amp;bg=FFFFFF00&quot; style=&quot;max-width: 100%; width: auto; &quot; alt=&quot;&quot; /><img src=&quot;graph.php?height=100&amp;amp;width=340&amp;amp;to=1423852680&amp;amp;device=1&amp;amp;type=device_ucd_memory&amp;amp;from=1423247880&amp;amp;legend=yes&amp;amp;popup_title=&amp;amp;bg=FFFFFF00&quot; style=&quot;max-width: 100%; width: auto; &quot; alt=&quot;&quot; /><img src=&quot;graph.php?height=100&amp;amp;width=340&amp;amp;to=1423852680&amp;amp;device=1&amp;amp;type=device_ucd_memory&amp;amp;from=1421174280&amp;amp;legend=yes&amp;amp;popup_title=&amp;amp;bg=FFFFFF00&quot; style=&quot;max-width: 100%; width: auto; &quot; alt=&quot;&quot; /><img src=&quot;graph.php?height=100&amp;amp;width=340&amp;amp;to=1423852680&amp;amp;device=1&amp;amp;type=device_ucd_memory&amp;amp;from=1392316680&amp;amp;legend=yes&amp;amp;popup_title=&amp;amp;bg=FFFFFF00&quot; style=&quot;max-width: 100%; width: auto; &quot; alt=&quot;&quot; /></div>" data-hasqtip="8"><img src="graph.php?height=45&amp;width=150&amp;to=1423852680&amp;device=1&amp;type=device_ucd_memory&amp;from=1423766280&amp;legend=no&amp;popup_title=&amp;bg=FFFFFF00" style="max-width: 100%; width: auto; " alt=""></a><div style="padding: 0px; font-weight: bold; font-size: 7pt; text-align: center;">Detailed Memory</div></div><div class="pull-right" style="padding: 2px; margin: 0;"><a href="graphs/to=1423852680/device=1/type=device_storage/from=1423766280/legend=yes/" class="tooltip-from-data " data-tooltip="<div class=entity-title></div><div style=&quot;width: 850px&quot;><img src=&quot;graph.php?height=100&amp;amp;width=340&amp;amp;to=1423852680&amp;amp;device=1&amp;amp;type=device_storage&amp;amp;from=1423766280&amp;amp;legend=yes&amp;amp;popup_title=&amp;amp;bg=FFFFFF00&quot; style=&quot;max-width: 100%; width: auto; &quot; alt=&quot;&quot; /><img src=&quot;graph.php?height=100&amp;amp;width=340&amp;amp;to=1423852680&amp;amp;device=1&amp;amp;type=device_storage&amp;amp;from=1423247880&amp;amp;legend=yes&amp;amp;popup_title=&amp;amp;bg=FFFFFF00&quot; style=&quot;max-width: 100%; width: auto; &quot; alt=&quot;&quot; /><img src=&quot;graph.php?height=100&amp;amp;width=340&amp;amp;to=1423852680&amp;amp;device=1&amp;amp;type=device_storage&amp;amp;from=1421174280&amp;amp;legend=yes&amp;amp;popup_title=&amp;amp;bg=FFFFFF00&quot; style=&quot;max-width: 100%; width: auto; &quot; alt=&quot;&quot; /><img src=&quot;graph.php?height=100&amp;amp;width=340&amp;amp;to=1423852680&amp;amp;device=1&amp;amp;type=device_storage&amp;amp;from=1392316680&amp;amp;legend=yes&amp;amp;popup_title=&amp;amp;bg=FFFFFF00&quot; style=&quot;max-width: 100%; width: auto; &quot; alt=&quot;&quot; /></div>" data-hasqtip="9"><img src="graph.php?height=45&amp;width=150&amp;to=1423852680&amp;device=1&amp;type=device_storage&amp;from=1423766280&amp;legend=no&amp;popup_title=&amp;bg=FFFFFF00" style="max-width: 100%; width: auto; " alt=""></a><div style="padding: 0px; font-weight: bold; font-size: 7pt; text-align: center;">Filesystem Usage</div></div><div class="pull-right" style="padding: 2px; margin: 0;"><a href="graphs/to=1423852680/device=1/type=device_bits/from=1423766280/legend=yes/" class="tooltip-from-data " data-tooltip="<div class=entity-title></div><div style=&quot;width: 850px&quot;><img src=&quot;graph.php?height=100&amp;amp;width=340&amp;amp;to=1423852680&amp;amp;device=1&amp;amp;type=device_bits&amp;amp;from=1423766280&amp;amp;legend=yes&amp;amp;popup_title=&amp;amp;bg=FFFFFF00&quot; style=&quot;max-width: 100%; width: auto; &quot; alt=&quot;&quot; /><img src=&quot;graph.php?height=100&amp;amp;width=340&amp;amp;to=1423852680&amp;amp;device=1&amp;amp;type=device_bits&amp;amp;from=1423247880&amp;amp;legend=yes&amp;amp;popup_title=&amp;amp;bg=FFFFFF00&quot; style=&quot;max-width: 100%; width: auto; &quot; alt=&quot;&quot; /><img src=&quot;graph.php?height=100&amp;amp;width=340&amp;amp;to=1423852680&amp;amp;device=1&amp;amp;type=device_bits&amp;amp;from=1421174280&amp;amp;legend=yes&amp;amp;popup_title=&amp;amp;bg=FFFFFF00&quot; style=&quot;max-width: 100%; width: auto; &quot; alt=&quot;&quot; /><img src=&quot;graph.php?height=100&amp;amp;width=340&amp;amp;to=1423852680&amp;amp;device=1&amp;amp;type=device_bits&amp;amp;from=1392316680&amp;amp;legend=yes&amp;amp;popup_title=&amp;amp;bg=FFFFFF00&quot; style=&quot;max-width: 100%; width: auto; &quot; alt=&quot;&quot; /></div>" data-hasqtip="10"><img src="graph.php?height=45&amp;width=150&amp;to=1423852680&amp;device=1&amp;type=device_bits&amp;from=1423766280&amp;legend=no&amp;popup_title=&amp;bg=FFFFFF00" style="max-width: 100%; width: auto; " alt=""></a><div style="padding: 0px; font-weight: bold; font-size: 7pt; text-align: center;">Traffic</div></div>    </td>
   </tr>
 </tbody></table>

<script>
(function ($) {

        $(function() {

                // fix sub nav on scroll
                var $win = $(window),
                                $body = $('body'),
                                $nav = $('.subnav'),
                                navHeight = $('.navbar').first().height(),
                                subnavHeight = $('.subnav').first().height(),
                                subnavTop = $('.subnav').length && $('.subnav').offset().top - 14,
                                marginTop = parseInt($body.css('margin-top'), 10);
                                isFixed = 0;

//                                 subnavTop = $('.subnav').length && $('.subnav').offset().top - navHeight,

                processScroll();

                $win.on('scroll', processScroll);

                function processScroll() {
                        var i, scrollTop = $win.scrollTop();

                        if (scrollTop >= subnavTop && !isFixed) {
                                isFixed = 1;
                                $nav.addClass('subnav-fixed');
                                $body.css('margin-top', marginTop + subnavHeight + 'px');
                        } else if (scrollTop <= subnavTop && isFixed) {
                                isFixed = 0;
                                $nav.removeClass('subnav-fixed');
                                $body.css('margin-top', marginTop + 'px');
                        }
                }

        });

})(window.jQuery);
</script>


  <div class="navbar navbar-narrow subnav" style="">
    <div class="navbar-inner">
      <div class="container">
        <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target="#nav-KVhaCdTnajmOwXRH">
          <span class="oicon-bar"></span>
        </button>

  <div class="nav-collapse" id="nav-KVhaCdTnajmOwXRH"><ul class="nav"><li class=""><a href="device/device=340/tab=overview/"><i class="oicon-server"></i> <span>Overview</span></a></li><li class=""><a href="device/device=340/tab=graphs/"><i class="oicon-chart-up"></i> <span>Graphs</span></a></li><li class=""><a href="device/device=340/tab=health/"><i class="oicon-system-monitor"></i> <span>Health</span></a></li><li class=""><a href="device/device=340/tab=ports/"><i class="oicon-network-ethernet"></i> <span>Ports</span></a></li><li class=""><a href="device/device=340/tab=wifi/"><i class="oicon-wi-fi-zone"></i> <span>Wifi</span></a></li><li class=""><a href="device/device=340/tab=vlans/"><i class="oicon-arrow-branch-bgr"></i> <span>VLANs</span></a></li><li class=""><a href="device/device=340/tab=logs/"><i class="oicon-clipboard-audit"></i> <span>Logs</span></a></li><li class=""><a href="device/device=340/tab=alerts/"><i class="oicon-bell"></i> <span>Alerts</span></a></li></ul><ul class="nav pull-right"><li class=""><a href="device/device=340/tab=data/"><i class="oicon-application-list"></i> <span></span></a></li><li class=""><a href="device/device=340/tab=perf/"><i class="oicon-time"></i> <span></span></a></li><li class=" active"><a href="device/device=340/tab=edit/"><i class="oicon-gear"></i> <span></span></a></li></ul>        </div>
      </div>
    </div>
  </div>

     <div class="callout callout-info">
      <div><h4>Device not yet discovered</h4>
This device has not yet been successfully discovered. System information and statistics will not be populated and graphs will not draw.</div>
      </div>

    <div class="callout callout-suppressed">
      <div><h4>Device not yet discovered</h4>
This device has not yet been successfully discovered. System information and statistics will not be populated and graphs will not draw.</div>
      </div>

    <div class="callout callout-error">
      <div><h4>Device not yet discovered</h4>
This device has not yet been successfully discovered. System information and statistics will not be populated and graphs will not draw.</div>
      </div>


  <div class="navbar navbar-narrow" style="">
    <div class="navbar-inner">
      <div class="container">
        <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target="#nav-krGNzJ6pEOsNbFwc">
          <span class="oicon-bar"></span>
        </button>

   <a class="brand">Edit</a><div class="nav-collapse" id="nav-krGNzJ6pEOsNbFwc"><ul class="nav"><li class=""><a href="device/device=340/tab=edit/section=device/">Device Settings</a></li><li class=""><a href="device/device=340/tab=edit/section=snmp/">SNMP</a></li><li class=""><a href="device/device=340/tab=edit/section=geo/">Geolocation</a></li><li class="active"><a href="device/device=340/tab=edit/section=mibs/">MIBs</a></li><li class=""><a href="device/device=340/tab=edit/section=graphs/">Graphs</a></li><li class=""><a href="device/device=340/tab=edit/section=alerts/">Alerts</a></li><li class=""><a href="device/device=340/tab=edit/section=ports/">Ports</a></li><li class=""><a href="device/device=340/tab=edit/section=sensors/">Sensors</a></li><li class=""><a href="device/device=340/tab=edit/section=modules/">Modules</a></li></ul><ul class="nav pull-right"><li class=""><a href="device/device=340/tab=edit/section=delete/"><i class="oicon-server--minus"></i> <span>Delete</span></a></li></ul>        </div>
      </div>
    </div>
  </div>


   <div class="alert alert-danger">
      <b>Danger</b> This page allows you to disable certain MIBs to be polled for a device. This configuration disables all discovery modules using this MIB.
   </div>
   <div class="alert alert-warning">
      <b>Warning</b> This page allows you to disable certain MIBs to be polled for a device. This configuration disables all discovery modules using this MIB.
   </div>
   <div class="alert alert-delay">
      <b>Delay</b> This page allows you to disable certain MIBs to be polled for a device. This configuration disables all discovery modules using this MIB.
   </div>

   <div class="alert alert-recovery">
      <b>Recovery</b> This page allows you to disable certain MIBs to be polled for a device. This configuration disables all discovery modules using this MIB.
   </div>
   <div class="alert alert-success">
      <b>Success</b> This page allows you to disable certain MIBs to be polled for a device. This configuration disables all discovery modules using this MIB.
   </div>
   <div class="alert alert-info">
      <b>Information</b> This page allows you to disable certain MIBs to be polled for a device. This configuration disables all discovery modules using this MIB.
   </div>
   <div class="alert alert-suppressed">
      <b>Suppressed</b> This page allows you to disable certain MIBs to be polled for a device. This configuration disables all discovery modules using this MIB.
   </div>
   <div class="alert alert-up-ignore">
      <b>Up Ignore</b> This page allows you to disable certain MIBs to be polled for a device. This configuration disables all discovery modules using this MIB.
   </div>
   <div class="alert alert-down-ignore">
      <b>Down Ignore</b> This page allows you to disable certain MIBs to be polled for a device. This configuration disables all discovery modules using this MIB.
   </div>
   <div class="alert alert-disabled">
      <b>Disabled</b> This page allows you to disable certain MIBs to be polled for a device. This configuration disables all discovery modules using this MIB.
   </div>




<!-- START search form -->
<form method="POST" action="search/search=arp/" class="form-inline" id="search-8kJv">
<div class="navbar">
<div class="navbar-inner"><div class="container">  <a class="brand">ARP/NDP</a>
<div class="nav" style="margin: 5px 0 5px 0;">    <select multiple name="device_id[]" title="Device" id="device_id" class="selectpicker show-tick" data-selected-text-format="count>2" data-live-search="true" data-width="130px" data-size="15">
      <option value="59">aix.test</option> <option value="22">apc-8953.test</option> <option value="31">apc-ap8941.test</option> <option value="67">breeze1.test</option> <option value="68">breeze2.test</option> <option value="69">breeze3.test</option>  <option value="81">cisco-ons.test</option> <option value="19">darwin-i386.test</option> <option value="20">darwin-ppc.test</option>  <option value="53">extendair2.test</option> <option value="91">f5.test</option> <option value="55">freebsd10.test</option> <option value="78">ipso.test</option> <option value="57">timos.test</option> <option value="95">windows.test</option> 
    </select>
    <select name="searchby" id="searchby" class="selectpicker show-tick" data-selected-text-format="count>2" data-width="120px" data-size="15">
      <option value="">Search By</option> <option value="mac">MAC Address</option> <option value="ip">IP Address</option> 
    </select>
    <select name="ip_version" id="ip_version" class="selectpicker show-tick" data-selected-text-format="count>2" data-width="120px" data-size="15">
      <option value="">IPv4 &amp; IPv6</option> <option value="4">IPv4 only</option> <option value="6">IPv6 only</option> 
    </select>
  <div class="input-prepend">
    <span class="add-on">Address</span>
    <input type="text" style="width:120px" name="address" id="address" class="input" value=""/>
  </div>
</div>    <div class="nav pull-right">
      <button type="button" class="btn pull-right" style="line-height: 20px;" onclick="form_to_path('search-8kJv');"><i class="icon-search"></i> Search</button>
    </div>
</div></div></div></form>
<!-- END search form -->

<!-- START form-lNtuWtItDnzwTAh2 -->
<div class="well" style="padding: 5px;">
<form method="POST" id="form-lNtuWtItDnzwTAh2" action="devices/format=detail/disabled=0/" class="form-inline" style="margin-bottom:0;">
  <div class="row" > <!-- START row-0 -->
    <div class="col-lg-2">

    <input type="text" placeholder="Hostname" style="width:180px" name="hostname" id="hostname" class="input" value=""/>

    </div>
    <div class="col-lg-2">
    <select multiple name="location[]" title="Select Locations" id="location" class="selectpicker show-tick" data-selected-text-format="count>2" data-live-search="true" data-width="180px" data-size="15">
      <option value="czo3OiJbVU5TRVRdIjs=" selected>[UNSET]</option> <option value="czoxOToiNl82OlBMRDJCTEMwMTpCYXkgMSI7">6_6:PLD2BLC01:Bay 1</option> <option value="czo4OiJBVSBSb3cgMiI7">AU Row 2</option> <option value="czoxNToiQ1dpbmQtVGVoYWNoYXBpIjs=">CWind-Tehachapi</option> <option value="czoxMjoiQ2Fsd2luZC1Sb3cyIjs=">Calwind-Row2</option> <option value="czoxNToiQ3VzdG9tZXI6IExvdHRlIjs=">Customer: Lotte</option> <option value="czoyNzoiSFE6V1A6MXN0IEZsb29yOkRhdGEgQ2VudGVyIjs=">HQ:WP:1st Floor:Data Center</option> <option value="czo2OiJIamVtbWUiOw==">Hjemme</option> <option value="czoxMDoiSW50ZXJYaW9uMyI7" selected>InterXion3</option> <option value="czo5OiJNTVUuQi5CMDYiOw==">MMU.B.B06</option> <option value="czoxNjoiTmV0d29yayBDbG9zZXQgMSI7">Network Closet 1</option> <option value="czoxMToiTm8gTG9jYXRpb24iOw==">No Location</option> <option value="czo3OiJOeW1idXJrIjs=">Nymburk</option> <option value="czo5OiJQb3AgQ29tYXQiOw==">Pop Comat</option> <option value="czo3OiJSYWRpbyBCIjs=">Radio B</option> <option value="czoxMjoiU2FuIEpvc2UsIENBIjs=">San Jose, CA</option> <option value="czozMDoiU2l0dGluZyBvbiB0aGUgRG9jayBvZiB0aGUgQmF5Ijs=">Sitting on the Dock of the Bay</option> <option value="czoxODoiU2t5bGluZSBUYW5rNWItTmFmIjs=">Skyline Tank5b-Naf</option> <option value="czoxMToiVFYgUmVwZWF0ZXIiOw==">TV Repeater</option> <option value="czo3OiJVbmtub3duIjs=">Unknown</option> 
    </select>
    </div>
    <div class="col-lg-2">
    <select multiple name="os[]" title="Select OS" id="os" class="selectpicker show-tick" data-selected-text-format="count>2" data-width="180px" data-size="15">
      <option value="aix">AIX</option> <option value="audiocodes" selected>Audiocodes</option> <option value="cisco-ons">Cisco Cerent ONS</option> <option value="ciscosb">Cisco Small Business</option> <option value="freebsd">FreeBSD</option> <option value="hwg-ste" selected>HWg-STE</option> <option value="ios">Cisco IOS</option> <option value="olivetti" selected>Olivetti Printer</option> <option value="poseidon">Poseidon</option> <option value="procurve">HP ProCurve</option> <option value="timos">Alcatel-Lucent TimOS</option> 
    </select>
    </div>
    <div class="col-lg-2">
    <select multiple name="hardware[]" title="Select Hardware" id="hardware" class="selectpicker show-tick" data-selected-text-format="count>2" data-width="180px" data-size="15">
      <option value="Generic 28C-1">Generic 28C-1</option> <option value="HWg-STE Plus">HWg-STE Plus</option> <option value="MP-118 FXS">MP-118 FXS</option> 
    </select>
    </div>
    <div class="col-lg-2">
    <select multiple name="group[]" title="Select Groups" id="group" class="selectpicker show-tick" data-selected-text-format="count>2" data-width="180px" data-size="15">
      <option value="0" disabled>[there is no data]</option> 
    </select>
    </div>
    <div class="col-lg-2 pull-right">
    <select name="sort" id="sort" class="selectpicker show-tick pull-right" data-selected-text-format="count>2" data-width="150px" data-size="15">
      <option value="hostname" data-icon="oicon-sort-alphabet-column" selected>Hostname</option> <option value="location">Location</option> <option value="os">Operating System</option> <option value="uptime">Uptime</option> 
    </select>
    </div>
  </div> <!-- END row-0 -->
  <div class="row" style="margin-top: 5px;"> <!-- START row-1 -->
    <div class="col-lg-2">

    <input type="text" placeholder="sysName" style="width:180px" name="sysname" id="sysname" class="input" value=""/>

    </div>
    <div class="col-lg-2">

    <input type="text" placeholder="Location" style="width:180px" name="location_text" id="location_text" class="input" value=""/>

    </div>
    <div class="col-lg-2">
    <select multiple name="version[]" title="Select OS Version" id="version" class="selectpicker show-tick" data-selected-text-format="count>2" data-width="180px" data-size="15">
      <option value="4.80A.014.006">4.80A.014.006</option> <option value="5.00A.027.005">5.00A.027.005</option> 
    </select>
    </div>
    <div class="col-lg-2">
    <select multiple name="features[]" title="Select Featureset" id="features" class="selectpicker show-tick" data-selected-text-format="count>2" data-width="180px" data-size="15">
      <option value="electrophotographicLaser">ElectrophotographicLaser</option> 
    </select>
    </div>
    <div class="col-lg-2">
    <select multiple name="type[]" title="Select Device Type" id="type" class="selectpicker show-tick" data-selected-text-format="count>2" data-width="180px" data-size="15">
      <option value="environment">Environment</option> <option value="printer">Printer</option> <option value="voip">Voip</option> 
    </select>
    </div>
    <div class="col-lg-2 pull-right">
      <button type="button" class="btn pull-right" style="line-height: 20px;" onclick="form_to_path('form-lNtuWtItDnzwTAh2');"><i class="icon-search"></i> Search</button>
    </div>
  </div> <!-- END row-1 -->
</form>
</div>
<!-- END form-lNtuWtItDnzwTAh2 -->


<table class="table table-hover table-striped table-bordered table-condensed table-rounded" style="margin-top: 10px;">
  <thead>
    <tr>
      <th class="state-marker"></th>
      <th></th>
      <th>Device/Location</th>
      <th></th>
      <th>Platform</th>
      <th>Operating System</th>
      <th>Uptime/sysName</th>
    </tr>
  </thead>


  <tbody><tr class="up" onclick="location.href='device/device=1/'" style="cursor: pointer;">
    <td class="state-marker"></td>
    <td style="width: 64px; text-align: center; vertical-align: middle;"><img src="images/os/linux.png" alt=""></td>
    <td style="width: 300px;"><span class="entity-title"><a href="device/device=1/" class="entity-popup " data-eid="1" data-etype="device" data-hasqtip="0">localhost</a></span><br>Hetzner, Nuremberg, Germany</td>
    <td style="width: 55px;"><i class="oicon-network-ethernet"></i> 3<br></td>
    <td>Generic x86 [64bit]<br></td>
    <td>Linux<br>3.13.0-32-generic</td>
    <td>3h 20m 8s<br>ubuntu</td>
  </tr></tbody></table>

<div class="row">
<div class="col-md-12" style="margin-bottom: 10px;">
<div class="alert statusbox alert-danger"><span class="header"><a href="device/device=351/" class="entity-popup " data-eid="351" data-etype="device" data-hasqtip="6">asr9k</a></span>
  <p>BGP Peer Down<br><span class="entity">192.168.0.1</span><br><small>25d 10h 19m</small><br></p></div>
<div class="alert statusbox alert-warning"><span class="header"><a href="device/device=342/" class="entity-popup " data-eid="342" data-etype="device" data-hasqtip="9">pc6224</a></span>
  <p>Port Down<br><span class="entity"><a href="device/device=342/tab=port/port=151463/" class="entity-popup red" data-eid="151463" data-etype="port" data-hasqtip="10">lag- 16</a></span><br>
  <small>7h 32m 31s</small><br></p></div>
<div class="alert statusbox alert-info"><span class="header"><a href="device/device=342/" class="entity-popup " data-eid="342" data-etype="device" data-hasqtip="9">pc6224</a></span>
  <p>Port Down<br><span class="entity"><a href="device/device=342/tab=port/port=151463/" class="entity-popup red" data-eid="151463" data-etype="port" data-hasqtip="10">lag- 16</a></span><br>
  <small>7h 32m 31s</small><br></p></div>
<div class="alert statusbox alert-danger"><span class="header"><a href="device/device=351/" class="entity-popup " data-eid="351" data-etype="device" data-hasqtip="6">asr9k</a></span>
  <p>BGP Peer Down<br><span class="entity">192.168.0.1</span><br><small>25d 10h 19m</small><br></p></div>
<div class="alert statusbox alert-success"><span class="header"><a href="device/device=342/" class="entity-popup " data-eid="342" data-etype="device" data-hasqtip="9">pc6224</a></span>
  <p>Port Down<br><span class="entity"><a href="device/device=342/tab=port/port=151463/" class="entity-popup red" data-eid="151463" data-etype="port" data-hasqtip="10">lag- 16</a></span><br>
  <small>7h 32m 31s</small><br></p></div>
<div class="alert statusbox alert-delay"><span class="header"><a href="device/device=342/" class="entity-popup " data-eid="342" data-etype="device" data-hasqtip="9">pc6224</a></span>
  <p>Port Down<br><span class="entity"><a href="device/device=342/tab=port/port=151463/" class="entity-popup red" data-eid="151463" data-etype="port" data-hasqtip="10">lag- 16</a></span><br>
  <small>7h 32m 31s</small><br></p></div>
<div class="alert statusbox alert-suppressed"><span class="header"><a href="device/device=342/" class="entity-popup " data-eid="342" data-etype="device" data-hasqtip="9">pc6224</a></span>
  <p>Port Down<br><span class="entity"><a href="device/device=342/tab=port/port=151463/" class="entity-popup red" data-eid="151463" data-etype="port" data-hasqtip="10">lag- 16</a></span><br>
  <small>7h 32m 31s</small><br></p></div>
</div>
</div>



       <table class="table table-hover table-bordered table-condensed table-rounded table-striped">  
       <thead><tr><th class="state-marker"></th><th></th><th><a href="device/device=1/tab=ports/view=details/sort=port/">Port</a></th><th></th><th><a href="device/device=1/tab=ports/view=details/sort=traffic/">Traffic</a></th><th><a href="device/device=1/tab=ports/view=details/sort=speed/">Speed</a></th><th><a href="device/device=1/tab=ports/view=details/sort=media/">Media</a></th><th><a href="device/device=1/tab=ports/view=details/sort=mac/">MAC Address</a></th><th></th>      </tr>  </thead>
       <tbody>
        <tr class="warning" style="cursor: pointer;">
         <td class="state-marker"></td>
         <td style="width: 1px;"></td>
         <td style="width: 350px;">        <span class="entity-title">
              <a href="device/device=1/tab=port/port=1/" class="entity-popup " data-eid="1" data-etype="port" data-hasqtip="1">lo</a> 
           </span><br><span class="small"></span><a class="small" href="javascript:popUp('/netcmd.php?cmd=whois&query=127.0.0.1')">127.0.0.1/8</a><br><a class="small" href="javascript:popUp('/netcmd.php?cmd=whois&query=0000:0000:0000:0000:0000:0000:0000:0001')">::1/128</a></td><td style="width: 147px;"><a href="device/device=1/tab=port/port=1/" class="entity-popup " data-eid="1" data-etype="port" data-hasqtip="2"><img src="graph.php?type=port_bits&amp;id=1&amp;from=1423755349&amp;to=1423841749&amp;width=100&amp;height=20&amp;legend=no" alt=""></a><a href="device/device=1/tab=port/port=1/" class="entity-popup " data-eid="1" data-etype="port" data-hasqtip="3"><img src="graph.php?type=port_upkts&amp;id=1&amp;from=1423755349&amp;to=1423841749&amp;width=100&amp;height=20&amp;legend=no" alt=""></a><a href="device/device=1/tab=port/port=1/" class="entity-popup " data-eid="1" data-etype="port" data-hasqtip="4"><img src="graph.php?type=port_errors&amp;id=1&amp;from=1423755349&amp;to=1423841749&amp;width=100&amp;height=20&amp;legend=no" alt=""></a></td><td style="width: 120px; white-space: nowrap;"><i class="icon-circle-arrow-down" style=""></i> <span class="small" style="">0bps</span><br><i class="icon-circle-arrow-up" style=""></i> <span class="small" style="">0bps</span><br><i class="icon-circle-arrow-down" style=""></i> <span class="small" style="">0pps</span><br><i class="icon-circle-arrow-up" style=""></i> <span class="small" style="">0pps</span></td><td style="width: 75px;"><span class="small">10Mbps</span><br><span class="small"></span></td><td style="width: 150px;"><span class="small">Loopback</span><br>-</td><td style="width: 150px;">-<br><span class="small">MTU 65536</span></td><td style="width: 375px" class="small"></td></tr>

        <tr class="up" onclick="location.href='device/device=1/tab=port/port=2/'" style="cursor: pointer;">
         <td class="state-marker"></td>
         <td style="width: 1px;"></td>
         <td style="width: 350px;">        <span class="entity-title">
              <a href="device/device=1/tab=port/port=2/" class="entity-popup " data-eid="2" data-etype="port" data-hasqtip="5">eth0</a> 
           </span><br><span class="small"></span><a class="small" href="javascript:popUp('/netcmd.php?cmd=whois&query=192.168.125.129')">192.168.125.129/24</a></td><td style="width: 147px;"><a href="device/device=1/tab=port/port=2/" class="entity-popup " data-eid="2" data-etype="port" data-hasqtip="6"><img src="graph.php?type=port_bits&amp;id=2&amp;from=1423755349&amp;to=1423841749&amp;width=100&amp;height=20&amp;legend=no" alt=""></a><a href="device/device=1/tab=port/port=2/" class="entity-popup " data-eid="2" data-etype="port" data-hasqtip="7"><img src="graph.php?type=port_upkts&amp;id=2&amp;from=1423755349&amp;to=1423841749&amp;width=100&amp;height=20&amp;legend=no" alt=""></a><a href="device/device=1/tab=port/port=2/" class="entity-popup " data-eid="2" data-etype="port" data-hasqtip="8"><img src="graph.php?type=port_errors&amp;id=2&amp;from=1423755349&amp;to=1423841749&amp;width=100&amp;height=20&amp;legend=no" alt=""></a></td><td style="width: 120px; white-space: nowrap;"><i class="icon-circle-arrow-down" style=""></i> <span class="small" style="">0bps</span><br><i class="icon-circle-arrow-up" style=""></i> <span class="small" style="">0bps</span><br><i class="icon-circle-arrow-down" style=""></i> <span class="small" style="">0pps</span><br><i class="icon-circle-arrow-up" style=""></i> <span class="small" style="">0pps</span></td><td style="width: 75px;"><span class="small">1Gbps</span><br><span class="small"></span></td><td style="width: 150px;"><span class="small">Ethernet</span><br>-</td><td style="width: 150px;"><span class="small">00:0c:29:da:32:30</span><br><span class="small">MTU 1500</span></td><td style="width: 375px" class="small"></td></tr>

       <tr class="disabled" onclick="location.href='device/device=1/tab=port/port=3/'" style="cursor: pointer;">
         <td class="state-marker"></td>
         <td style="width: 1px;"></td>
         <td style="width: 350px;">        <span class="entity-title">
              <a href="device/device=1/tab=port/port=3/" class="entity-popup gray" data-eid="3" data-etype="port" data-hasqtip="9">derp</a> 
           </span><br><span class="small"></span></td><td style="width: 147px;"><a href="device/device=1/tab=port/port=3/" class="entity-popup gray" data-eid="3" data-etype="port" data-hasqtip="10"><img src="graph.php?type=port_bits&amp;id=3&amp;from=1423755622&amp;to=1423842022&amp;width=100&amp;height=20&amp;legend=no" alt=""></a><a href="device/device=1/tab=port/port=3/" class="entity-popup gray" data-eid="3" data-etype="port" data-hasqtip="11" aria-describedby="qtip-11"><img src="graph.php?type=port_upkts&amp;id=3&amp;from=1423755622&amp;to=1423842022&amp;width=100&amp;height=20&amp;legend=no" alt=""></a><a href="device/device=1/tab=port/port=3/" class="entity-popup gray" data-eid="3" data-etype="port" data-hasqtip="12"><img src="graph.php?type=port_errors&amp;id=3&amp;from=1423755622&amp;to=1423842022&amp;width=100&amp;height=20&amp;legend=no" alt=""></a></td><td style="width: 120px; white-space: nowrap;"></td><td style="width: 75px;"><br><span class="small"></span></td><td style="width: 150px;"><span class="small">Ethernet</span><br>-</td><td style="width: 150px;"><span class="small">b6:bd:5f:52:eb:80</span><br><span class="small">MTU 1500</span></td><td style="width: 375px" class="small"></td></tr>
         </tbody></table>


<div class="row"> <!-- begin row -->

  <div class="col-md-6"> <!-- begin poller options -->

<fieldset>
  <legend>Device MIBs</legend>
</fieldset>

<table class="table table-bordered table-striped table-condensed table-rounded">
  <thead>
    <tr>
      <th>Module</th>
      <th style="width: 80;">Status</th>
      <th style="width: 80;"></th>
    </tr>
  </thead>
  <tbody>

<tr><td><strong>TRAPEZE-NETWORKS-AP-CONFIG-MIB</strong></td><td><span class="text-success">enabled</span></td><td><form id="toggle_mib" name="toggle_mib" style="margin: 0px;" method="post" action="">
  <input type="hidden" name="toggle_mib" value="TRAPEZE-NETWORKS-AP-CONFIG-MIB">
  <button type="submit" class="btn btn-mini btn-danger" name="Submit" >Disable</button>
</form></td></tr><tr><td><strong>TRAPEZE-NETWORKS-AP-STATUS-MIB</strong></td><td><span class="text-success">enabled</span></td><td><form id="toggle_mib" name="toggle_mib" style="margin: 0px;" method="post" action="">
  <input type="hidden" name="toggle_mib" value="TRAPEZE-NETWORKS-AP-STATUS-MIB">
  <button type="submit" class="btn btn-mini btn-danger" name="Submit" >Disable</button>
</form></td></tr><tr><td><strong>TRAPEZE-NETWORKS-CLIENT-SESSION-MIB</strong></td><td><span class="text-success">enabled</span></td><td><form id="toggle_mib" name="toggle_mib" style="margin: 0px;" method="post" action="">
  <input type="hidden" name="toggle_mib" value="TRAPEZE-NETWORKS-CLIENT-SESSION-MIB">
  <button type="submit" class="btn btn-mini btn-danger" name="Submit" >Disable</button>
</form></td></tr><tr><td><strong>TRAPEZE-NETWORKS-SYSTEM-MIB</strong></td><td><span class="text-success">enabled</span></td><td><form id="toggle_mib" name="toggle_mib" style="margin: 0px;" method="post" action="">
  <input type="hidden" name="toggle_mib" value="TRAPEZE-NETWORKS-SYSTEM-MIB">
  <button type="submit" class="btn btn-mini btn-danger" name="Submit" >Disable</button>
</form></td></tr>  </tbody>
</table>

</div> <!-- end poller options -->

  </div> <!-- end row -->
</div> <!-- end container -->
</div>

<div class="navbar navbar-fixed-bottom">
  <div class="navbar-inner">
    <div class="container">
      <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
        <span class="oicon-bar"></span>
        <span class="oicon-bar"></span>
        <span class="oicon-bar"></span>
      </a>
      <div class="nav-collapse">
        <ul class="nav">
          <li class="divider-vertical" style="margin:0;"></li>

          <li class="dropdown"><a href="http://www.observium.org" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">Observium 0.15.2.6251 (rolling)</a>
            <div class="dropdown-menu" style="padding: 10px;">
              <div style="max-width: 145px;"><img src="images/login-hamster-large.png" alt="" /></div>

            </div>
          </li>
          <li class="divider-vertical" style="margin:0;"></li>
        </ul>

        <ul class="nav pull-right">
          <li><a id="poller_status"></a></li>

          <li class="divider-vertical" style="margin:0;"></li>
          <li class="dropdown">
                        <a href="overview/" alt="Notification center" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
              <i style="opacity: 0.2; filter: alpha(opacity=20);" class="oicon-tick-circle"></i></a>
                        </li>

          <li class="divider-vertical" style="margin:0;"></li>
          <li class="dropdown">
            <a href="overview/" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
              <i class="oicon-time"></i> 0.089s <b class="caret"></b></a>
            <div class="dropdown-menu" style="padding: 10px;">
              <table class="table table-bordered table-condensed-more table-rounded table-striped">
                <tr>
                  <th>Page</th><td>0.089s</td>
                </tr>
                <tr>
                  <th>Cache</th><td>0.075s</td>
                </tr>

              </table>
              <table class="table table-bordered table-condensed-more table-rounded table-striped">
                <tr>
                  <th colspan=2>MySQL</th>
                </tr>
                <tr>
                  <th>Cell</th><td>31/0.0015s</td>
                </tr>
                <tr>
                  <th>Row</th><td>2/0.0003s</td>
                </tr>
                <tr>
                  <th>Rows</th><td>9/0.0387s</td>
                </tr>
                <tr>
                  <th>Column</th><td>2/0.0004s</td>
                </tr>
              </table>
              <table class="table table-bordered table-condensed-more table-rounded table-striped">
                <tr>
                  <th colspan=2>Memory</th>
                </tr>
                <tr>
                  <th>Cached</th><td>1.69MB</td>
                </tr>
                <tr>
                  <th>Page</th><td>8.98MB</td>
                </tr>
                <tr>
                  <th>Peak</th><td>29.7MB</td>
                </tr>
              </table>
            </div>
          </li>

          <li class="dropdown">
            <a href="overview/" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
              <i class="oicon-databases"></i> <b class="caret"></b></a>
            <div class="dropdown-menu" style="padding: 10px; width: 1150px;">

              <table class="table table-bordered table-condensed-more table-rounded table-striped">

  <tr><td>0.00638795</td><td><p><code style="color: black; background-color: white;"><span style="font-weight:bold;">SELECT</span> <span style="color: purple;">`device_id`</span><span >,</span> <span style="color: purple;">`ports`</span><span >.</span><span style="color: purple;">`port_id`</span><span >,</span> <span style="color: purple;">`ifAdminStatus`</span><span >,</span> <span style="color: purple;">`ifOperStatus`</span><span >,</span> <span style="color: purple;">`deleted`</span><span >,</span> <span style="color: purple;">`ignore`</span><span >,</span> <span style="color: purple;">`ifOutErrors_delta`</span><span >,</span> <span style="color: purple;">`ifInErrors_delta`</span> <span style="font-weight:bold;">FROM</span> <span style="color: purple;">`ports`</span>
                            <span style="font-weight:bold;">LEFT JOIN</span> <span style="color: purple;">`ports-state`</span> <span style="font-weight:bold;">ON</span> <span style="color: purple;">`ports`</span><span >.</span><span style="color: purple;">`port_id`</span> <span >=</span> <span style="color: purple;">`ports-state`</span><span >.</span><span style="color: purple;">`port_id`</span></code></p></td></tr><tr><td>0.00111890</td><td><p><code style="color: black; background-color: white;"><span style="font-weight:bold;">SELECT</span> <span >*</span> <span style="font-weight:bold;">FROM</span> <span style="color: purple;">`sensors`</span> <span style="font-weight:bold;">LEFT JOIN</span> <span style="color: purple;">`sensors-state`</span> <span style="font-weight:bold;">ON</span> <span style="color: purple;">`sensors`</span><span >.</span><span style="color: purple;">`sensor_id`</span> <span >=</span> <span style="color: purple;">`sensors-state`</span><span >.</span><span style="color: purple;">`sensor_id`</span></code></p></td></tr><tr><td>0.00039387</td><td><p><code style="color: black; background-color: white;"><span style="font-weight:bold;">SELECT</span> <span >*</span> <span style="font-weight:bold;">FROM</span> <span style="color: purple;">`device_graphs`</span> <span style="font-weight:bold;">ORDER BY</span> <span style="color: purple;">`graph`</span></code></p></td></tr><tr><td>0.00030398</td><td><p><code style="color: black; background-color: white;"><span style="font-weight:bold;">SELECT</span> <span style="color: purple;">`device_id`</span><span >,</span> <span style="color: purple;">`ospfAdminStat`</span> <span style="font-weight:bold;">FROM</span> <span style="color: purple;">`ospf_instances`</span></code></p></td></tr><tr><td>0.00027514</td><td><p><code style="color: black; background-color: white;"><span style="font-weight:bold;">SELECT</span> <span >*</span> <span style="font-weight:bold;">FROM</span> <span style="color: purple;">`entity_permissions`</span> <span style="font-weight:bold;">WHERE</span> <span style="color: purple;">`user_id`</span> <span >=</span> <span style="color: blue;">'2'</span></code></p></td></tr><tr><td>0.00016594</td><td><p><code style="color: black; background-color: white;"><span style="font-weight:bold;">SELECT</span> <span style="color: purple;">`device_id`</span><span >,</span><span style="color: purple;">`bgpPeerState`</span><span >,</span><span style="color: purple;">`bgpPeerAdminStatus`</span><span >,</span><span style="color: purple;">`bgpPeerRemoteAs`</span> <span style="font-weight:bold;">FROM</span> <span style="color: #333;">bgpPeers</span></code></p></td></tr><tr><td>0.00013614</td><td><p><code style="color: black; background-color: white;"><span style="font-weight:bold;">SELECT</span> <span style="font-weight:bold;">COUNT</span>(<span >*</span>) <span style="font-weight:bold;">FROM</span> <span style="color: purple;">`ospf_instances`</span> <span style="font-weight:bold;">WHERE</span> <span style="color: purple;">`ospfAdminStat`</span> <span >=</span> <span style="color: blue;">'enabled'</span> <span style="font-weight:bold;">AND</span> <span style="color: purple;">`device_id`</span> <span >=</span> <span style="color: blue;">'340'</span></code></p></td></tr><tr><td>0.00011206</td><td><p><code style="color: black; background-color: white;"><span style="font-weight:bold;">SELECT</span> <span style="font-weight:bold;">COUNT</span>(<span >*</span>) <span style="font-weight:bold;">FROM</span> <span style="color: #333;">cempMemPool</span> <span style="font-weight:bold;">WHERE</span> <span style="color: #333;">device_id</span> <span >=</span> <span style="color: blue;">'340'</span></code></p></td></tr><tr><td>0.00011110</td><td><p><code style="color: black; background-color: white;"><span style="font-weight:bold;">SELECT</span> <span style="font-weight:bold;">COUNT</span>(<span >*</span>) <span style="font-weight:bold;">FROM</span> <span style="color: purple;">`eigrp_ports`</span> <span style="font-weight:bold;">WHERE</span> <span style="color: purple;">`device_id`</span> <span >=</span> <span style="color: blue;">'340'</span></code></p></td></tr><tr><td>0.00010085</td><td><p><code style="color: black; background-color: white;"><span style="font-weight:bold;">SELECT</span> <span style="color: purple;">`value`</span> <span style="font-weight:bold;">FROM</span> <span style="color: purple;">`users_prefs`</span> <span style="font-weight:bold;">WHERE</span> <span style="color: purple;">`user_id`</span> <span >=</span> <span style="color: blue;">'2'</span> <span style="font-weight:bold;">AND</span> <span style="color: purple;">`pref`</span> <span >=</span> <span style="color: blue;">'atom_key'</span></code></p></td></tr><tr><td>0.00008106</td><td><p><code style="color: black; background-color: white;"><span style="font-weight:bold;">SELECT</span> <span >*</span> <span style="font-weight:bold;">FROM</span> <span style="color: purple;">`devices`</span> <span style="font-weight:bold;">LEFT JOIN</span> <span style="color: purple;">`devices_locations`</span> <span style="font-weight:bold;">USING</span> (<span style="color: purple;">`device_id`</span>) <span style="font-weight:bold;">ORDER BY</span> <span style="color: purple;">`hostname`</span><span >;</span></code></p></td></tr><tr><td>0.00008011</td><td><p><code style="color: black; background-color: white;"><span style="font-weight:bold;">SELECT</span> <span style="color: purple;">`value`</span> <span style="font-weight:bold;">FROM</span> <span style="color: purple;">`users_prefs`</span> <span style="font-weight:bold;">WHERE</span> <span style="color: purple;">`user_id`</span> <span >=</span> <span style="color: blue;">'2'</span> <span style="font-weight:bold;">AND</span> <span style="color: purple;">`pref`</span> <span >=</span> <span style="color: blue;">'atom_key'</span></code></p></td></tr><tr><td>0.00007796</td><td><p><code style="color: black; background-color: white;"><span style="font-weight:bold;">SELECT</span> <span style="font-weight:bold;">COUNT</span>(<span >*</span>) <span style="font-weight:bold;">FROM</span> <span style="color: #333;">cpmCPU</span> <span style="font-weight:bold;">WHERE</span> <span style="color: #333;">device_id</span> <span >=</span> <span style="color: blue;">'340'</span></code></p></td></tr><tr><td>0.00007701</td><td><p><code style="color: black; background-color: white;"><span style="font-weight:bold;">SELECT</span> <span style="font-weight:bold;">DISTINCT</span> <span style="color: purple;">`port_id`</span> <span style="font-weight:bold;">FROM</span> <span style="color: purple;">`ports_cbqos`</span> <span style="font-weight:bold;">WHERE</span> <span style="color: green;">1</span>  <span style="font-weight:bold;">AND</span> <span style="color: purple;">`port_id`</span> <span style="font-weight:bold;">NOT</span> <span style="font-weight:bold;">IN</span> (<span style="color: green;">149198</span><span >,</span><span style="color: green;">149200</span><span >,</span><span style="color: green;">149202</span><span >,</span><span style="color: green;">149204</span><span >,</span><span style="color: green;">149206</span><span >,</span><span style="color: green;">149208</span><span >,</span><span style="color: green;">149210</span><span >,</span><span style="color: green;">149212</span><span >,</span><span style="color: green;">149269</span><span >,</span><span style="color: green;">152295</span><span >,</span><span style="color: green;">152296</span><span >,</span><span style="color: green;">162687</span><span >,</span><span style="color: green;">162688</span><span >,</span><span style="color: green;">162689</span><span >,</span><span style="color: green;">162841</span><span >,</span><span style="color: green;">162842</span><span >,</span><span style="color: green;">169911</span><span >,</span><span style="color: green;">176733</span><span >,</span><span style="color: green;">177110</span><span >,</span><span style="color: green;">178343</span><span >,</span><span style="color: green;">178344</span><span >,</span><span style="color: green;">178492</span><span >,</span><span style="color: green;">178567</span><span >,</span><span style="color: green;">181895</span><span >,</span><span style="color: green;">182628</span><span >,</span><span style="color: green;">182629</span><span >,</span><span style="color: green;">182630</span><span >,</span><span style="color: green;">182642</span><span >,</span><span style="color: green;">182643</span><span >,</span><span style="color: green;">182645</span><span >,</span><span style="color: green;">182647</span><span >,</span><span style="color: green;">182648</span><span >,</span><span style="color: green;">182658</span><span >,</span><span style="color: green;">183051</span><span >,</span><span style="color: green;">183052</span><span >,</span><span style="color: green;">183062</span><span >,</span><span style="color: green;">183079</span><span >,</span><span style="color: green;">183085</span><span >,</span><span style="color: green;">183401</span><span >,</span><span style="color: green;">183653</span><span >,</span><span style="color: green;">183661</span><span >,</span><span style="color: green;">183675</span><span >,</span><span style="color: green;">183676</span><span >,</span><span style="color: green;">183689</span><span >,</span><span style="color: green;">183690</span><span >,</span><span style="color: green;">183691</span><span >,</span><span style="color: green;">183693</span><span >,</span><span style="color: green;">183694</span><span >,</span><span style="color: green;">183695</span><span >,</span><span style="color: green;">183697</span><span >,</span><span style="color: green;">183699</span><span >,</span><span style="color: green;">183896</span><span >,</span><span style="color: green;">183897</span><span >,</span><span style="color: green;">183898</span><span >,</span><span style="color: green;">183899</span><span >,</span><span style="color: green;">183957</span><span >,</span><span style="color: green;">183971</span><span >,</span><span style="color: green;">184132</span><span >,</span><span style="color: green;">184133</span><span >,</span><span style="color: green;">184179</span><span >,</span><span style="color: green;">184180</span><span >,</span><span style="color: green;">184181</span><span >,</span><span style="color: green;">184182</span><span >,</span><span style="color: green;">184183</span><span >,</span><span style="color: green;">184184</span><span >,</span><span style="color: green;">184210</span><span >,</span><span style="color: green;">184246</span><span >,</span><span style="color: green;">184247</span><span >,</span><span style="color: green;">184248</span><span >,</span><span style="color: green;">184450</span><span >,</span><span style="color: green;">184459</span><span >,</span><span style="color: green;">184460</span><span >,</span><span style="color: green;">184578</span><span >,</span><span style="color: green;">184590</span><span >,</span><span style="color: green;">184591</span><span >,</span><span style="color: green;">184592</span><span >,</span><span style="color: green;">184593</span><span >,</span><span style="color: green;">184594</span><span >,</span><span style="color: green;">184595</span><span >,</span><span style="color: green;">184596</span><span >,</span><span style="color: green;">184597</span><span >,</span><span style="color: green;">184598</span><span >,</span><span style="color: green;">184599</span><span >,</span><span style="color: green;">184600</span><span >,</span><span style="color: green;">184601</span><span >,</span><span style="color: green;">184602</span><span >,</span><span style="color: green;">184603</span><span >,</span><span style="color: green;">184604</span><span >,</span><span style="color: green;">184605</span><span >,</span><span style="color: green;">184606</span><span >,</span><span style="color: green;">184607</span><span >,</span><span style="color: green;">184608</span><span >,</span><span style="color: green;">184609</span><span >,</span><span style="color: green;">184610</span><span >,</span><span style="color: green;">184611</span><span >,</span><span style="color: green;">184612</span><span >,</span><span style="color: green;">184613</span><span >,</span><span style="color: green;">184614</span><span >,</span><span style="color: green;">184615</span><span >,</span><span style="color: green;">184616</span><span >,</span><span style="color: green;">184617</span><span >,</span><span style="color: green;">184618</span><span >,</span><span style="color: green;">184619</span><span >,</span><span style="color: green;">184620</span><span >,</span><span style="color: green;">184621</span><span >,</span><span style="color: green;">184622</span><span >,</span><span style="color: green;">184623</span><span >,</span><span style="color: green;">184624</span><span >,</span><span style="color: green;">184625</span><span >,</span><span style="color: green;">184626</span><span >,</span><span style="color: green;">184627</span><span >,</span><span style="color: green;">184628</span><span >,</span><span style="color: green;">184629</span><span >,</span><span style="color: green;">184630</span><span >,</span><span style="color: green;">184631</span><span >,</span><span style="color: green;">184632</span><span >,</span><span style="color: green;">184633</span><span >,</span><span style="color: green;">184634</span><span >,</span><span style="color: green;">184635</span><span >,</span><span style="color: green;">184636</span><span >,</span><span style="color: green;">184637</span><span >,</span><span style="color: green;">184638</span><span >,</span><span style="color: green;">184639</span><span >,</span><span style="color: green;">184640</span><span >,</span><span style="color: green;">184641</span><span >,</span><span style="color: green;">184642</span><span >,</span><span style="color: green;">184643</span><span >,</span><span style="color: green;">184644</span><span >,</span><span style="color: green;">184645</span><span >,</span><span style="color: green;">184646</span><span >,</span><span style="color: green;">184647</span><span >,</span><span style="color: green;">184648</span><span >,</span><span style="color: green;">184649</span><span >,</span><span style="color: green;">184650</span><span >,</span><span style="color: green;">184651</span><span >,</span><span style="color: green;">184652</span><span >,</span><span style="color: green;">184653</span><span >,</span><span style="color: green;">184654</span><span >,</span><span style="color: green;">184655</span><span >,</span><span style="color: green;">184656</span><span >,</span><span style="color: green;">184657</span><span >,</span><span style="color: green;">184658</span><span >,</span><span style="color: green;">184659</span><span >,</span><span style="color: green;">184967</span><span >,</span><span style="color: green;">185417</span><span >,</span><span style="color: green;">185442</span><span >,</span><span style="color: green;">185784</span><span >,</span><span style="color: green;">185785</span><span >,</span><span style="color: green;">185786</span><span >,</span><span style="color: green;">185787</span><span >,</span><span style="color: green;">185788</span><span >,</span><span style="color: green;">185789</span><span >,</span><span style="color: green;">185790</span><span >,</span><span style="color: green;">185791</span><span >,</span><span style="color: green;">185792</span><span >,</span><span style="color: green;">185793</span><span >,</span><span style="color: green;">185794</span><span >,</span><span style="color: green;">185795</span><span >,</span><span style="color: green;">185796</span><span >,</span><span style="color: green;">185797</span><span >,</span><span style="color: green;">185798</span><span >,</span><span style="color: green;">185799</span><span >,</span><span style="color: green;">185800</span><span >,</span><span style="color: green;">185801</span><span >,</span><span style="color: green;">185802</span><span >,</span><span style="color: green;">185803</span><span >,</span><span style="color: green;">185804</span><span >,</span><span style="color: green;">185805</span><span >,</span><span style="color: green;">185806</span><span >,</span><span style="color: green;">185807</span><span >,</span><span style="color: green;">186958</span><span >,</span><span style="color: green;">186959</span><span >,</span><span style="color: green;">186960</span><span >,</span><span style="color: green;">187504</span><span >,</span><span style="color: green;">187513</span><span >,</span><span style="color: green;">188759</span><span >,</span><span style="color: green;">188760</span><span >,</span><span style="color: green;">188761</span><span >,</span><span style="color: green;">188762</span><span >,</span><span style="color: green;">188763</span><span >,</span><span style="color: green;">188764</span><span >,</span><span style="color: green;">194468</span><span >,</span><span style="color: green;">194469</span><span >,</span><span style="color: green;">194470</span><span >,</span><span style="color: green;">194471</span><span >,</span><span style="color: green;">194472</span><span >,</span><span style="color: green;">194473</span><span >,</span><span style="color: green;">194474</span><span >,</span><span style="color: green;">194475</span><span >,</span><span style="color: green;">194476</span><span >,</span><span style="color: green;">194477</span><span >,</span><span style="color: green;">194478</span><span >,</span><span style="color: green;">194479</span><span >,</span><span style="color: green;">194480</span><span >,</span><span style="color: green;">194481</span><span >,</span><span style="color: green;">194482</span><span >,</span><span style="color: green;">194483</span><span >,</span><span style="color: green;">194484</span><span >,</span><span style="color: green;">194485</span><span >,</span><span style="color: green;">194486</span><span >,</span><span style="color: green;">194487</span><span >,</span><span style="color: green;">194488</span><span >,</span><span style="color: green;">194489</span><span >,</span><span style="color: green;">194490</span><span >,</span><span style="color: green;">194491</span><span >,</span><span style="color: green;">194492</span><span >,</span><span style="color: green;">194493</span><span >,</span><span style="color: green;">194494</span><span >,</span><span style="color: green;">194495</span><span >,</span><span style="color: green;">194496</span><span >,</span><span style="color: green;">194497</span><span >,</span><span style="color: green;">194498</span><span >,</span><span style="color: green;">194499</span><span >,</span><span style="color: green;">194500</span><span >,</span><span style="color: green;">194501</span><span >,</span><span style="color: green;">194502</span><span >,</span><span style="color: green;">194503</span><span >,</span><span style="color: green;">194504</span><span >,</span><span style="color: green;">194505</span><span >,</span><span style="color: green;">194506</span><span >,</span><span style="color: green;">194507</span><span >,</span><span style="color: green;">194508</span><span >,</span><span style="color: green;">194509</span><span >,</span><span style="color: green;">194510</span><span >,</span><span style="color: green;">194511</span><span >,</span><span style="color: green;">194512</span><span >,</span><span style="color: green;">194513</span><span >,</span><span style="color: green;">194514</span><span >,</span><span style="color: green;">194515</span><span >,</span><span style="color: green;">194516</span><span >,</span><span style="color: green;">194517</span><span >,</span><span style="color: green;">194518</span><span >,</span><span style="color: green;">194519</span><span >,</span><span style="color: green;">194520</span><span >,</span><span style="color: green;">194521</span><span >,</span><span style="color: green;">194522</span><span >,</span><span style="color: green;">194523</span><span >,</span><span style="color: green;">194524</span><span >,</span><span style="color: green;">194525</span><span >,</span><span style="color: green;">194526</span><span >,</span><span style="color: green;">194527</span><span >,</span><span style="color: green;">194528</span><span >,</span><span style="color: green;">194529</span><span >,</span><span style="color: green;">194530</span><span >,</span><span style="color: green;">194531</span><span >,</span><span style="color: green;">194532</span><span >,</span><span style="color: green;">194533</span><span >,</span><span style="color: green;">194534</span><span >,</span><span style="color: green;">194535</span><span >,</span><span style="color: green;">194536</span><span >,</span><span style="color: green;">194537</span><span >,</span><span style="color: green;">194538</span><span >,</span><span style="color: green;">194539</span><span >,</span><span style="color: green;">194540</span><span >,</span><span style="color: green;">194541</span><span >,</span><span style="color: green;">194542</span><span >,</span><span style="color: green;">194543</span><span >,</span><span style="color: green;">194544</span><span >,</span><span style="color: green;">194545</span><span >,</span><span style="color: green;">194546</span><span >,</span><span style="color: green;">194547</span><span >,</span><span style="color: green;">194548</span><span >,</span><span style="color: green;">194549</span><span >,</span><span style="color: green;">194550</span><span >,</span><span style="color: green;">194551</span><span >,</span><span style="color: green;">194552</span><span >,</span><span style="color: green;">194553</span><span >,</span><span style="color: green;">194554</span><span >,</span><span style="color: green;">194555</span><span >,</span><span style="color: green;">194556</span><span >,</span><span style="color: green;">194557</span><span >,</span><span style="color: green;">194558</span><span >,</span><span style="color: green;">194559</span><span >,</span><span style="color: green;">194560</span><span >,</span><span style="color: green;">194561</span><span >,</span><span style="color: green;">194562</span><span >,</span><span style="color: green;">194563</span><span >,</span><span style="color: green;">194564</span><span >,</span><span style="color: green;">194565</span><span >,</span><span style="color: green;">194566</span><span >,</span><span style="color: green;">194567</span><span >,</span><span style="color: green;">194568</span><span >,</span><span style="color: green;">194569</span><span >,</span><span style="color: green;">194570</span><span >,</span><span style="color: green;">194571</span><span >,</span><span style="color: green;">194572</span><span >,</span><span style="color: green;">194573</span><span >,</span><span style="color: green;">194574</span><span >,</span><span style="color: green;">194575</span><span >,</span><span style="color: green;">194576</span><span >,</span><span style="color: green;">194577</span><span >,</span><span style="color: green;">194578</span><span >,</span><span style="color: green;">194579</span><span >,</span><span style="color: green;">194580</span><span >,</span><span style="color: green;">194581</span><span >,</span><span style="color: green;">194582</span><span >,</span><span style="color: green;">194583</span><span >,</span><span style="color: green;">194584</span><span >,</span><span style="color: green;">194585</span><span >,</span><span style="color: green;">194586</span><span >,</span><span style="color: green;">194587</span><span >,</span><span style="color: green;">194588</span><span >,</span><span style="color: green;">194589</span><span >,</span><span style="color: green;">194590</span><span >,</span><span style="color: green;">194591</span><span >,</span><span style="color: green;">194592</span><span >,</span><span style="color: green;">194593</span><span >,</span><span style="color: green;">194594</span><span >,</span><span style="color: green;">194595</span><span >,</span><span style="color: green;">194596</span><span >,</span><span style="color: green;">194597</span><span >,</span><span style="color: green;">194598</span><span >,</span><span style="color: green;">194599</span><span >,</span><span style="color: green;">194600</span><span >,</span><span style="color: green;">194601</span><span >,</span><span style="color: green;">194602</span><span >,</span><span style="color: green;">194603</span><span >,</span><span style="color: green;">194604</span><span >,</span><span style="color: green;">194605</span><span >,</span><span style="color: green;">194606</span><span >,</span><span style="color: green;">194607</span><span >,</span><span style="color: green;">194608</span><span >,</span><span style="color: green;">194609</span><span >,</span><span style="color: green;">194610</span><span >,</span><span style="color: green;">194611</span><span >,</span><span style="color: green;">194612</span><span >,</span><span style="color: green;">194613</span><span >,</span><span style="color: green;">194614</span><span >,</span><span style="color: green;">194615</span><span >,</span><span style="color: green;">194616</span><span >,</span><span style="color: green;">194617</span><span >,</span><span style="color: green;">194618</span><span >,</span><span style="color: green;">194619</span><span >,</span><span style="color: green;">194620</span><span >,</span><span style="color: green;">194624</span><span >,</span><span style="color: green;">194665</span><span >,</span><span style="color: green;">194724</span>) <span style="font-weight:bold;">AND</span> (<span style="color: purple;">`port_id`</span> <span >!</span><span >=</span> <span style="color: blue;">''</span> <span style="font-weight:bold;">AND</span> <span style="color: purple;">`port_id`</span> <span style="font-weight:bold;">IS</span> <span style="font-weight:bold;">NOT</span> <span style="font-weight:bold;">NULL</span>)</code></p></td></tr><tr><td>0.00006294</td><td><p><code style="color: black; background-color: white;"><span style="font-weight:bold;">SELECT</span> <span style="font-weight:bold;">COUNT</span>(<span style="color: purple;">`cef_switching_id`</span>) <span style="font-weight:bold;">FROM</span> <span style="color: purple;">`cef_switching`</span> <span style="font-weight:bold;">WHERE</span> <span style="color: green;">1</span>  <span style="font-weight:bold;">AND</span> (<span style="color: purple;">`device_id`</span> <span >!</span><span >=</span> <span style="color: blue;">''</span> <span style="font-weight:bold;">AND</span> <span style="color: purple;">`device_id`</span> <span style="font-weight:bold;">IS</span> <span style="font-weight:bold;">NOT</span> <span style="font-weight:bold;">NULL</span>)</code></p></td></tr>              </table>
            </div>
          </li>

       </ul>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">document.title = "Observium Dev - wifi.ctr - Settings";</script>  <script type="text/javascript">
  <!-- Begin
  function popUp(URL)
  {
    day = new Date();
    id = day.getTime();
    eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=1,location=0,statusbar=0,        menubar=0,resizable=1,width=550,height=600');");
  }
  // End -->
  </script>

  <script src="js/bootstrap.min.js"></script>

<script type="text/javascript" src="js/twitter-bootstrap-hover-dropdown.min.js"></script><script type="text/javascript" src="js/jquery.qtip.min.js"></script>
  <script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>
  <script type="text/javascript" src="js/bootstrap-select.min.js"></script>
  <script type="text/javascript">$('.selectpicker').selectpicker();</script>

  <script type="text/javascript" src="js/bootstrap-switch.min.js"></script>
  <script type="text/javascript" src="js/observium.js"></script>

  </body>
</html>

