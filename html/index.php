<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webinterface
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

include("../includes/sql-config.inc.php");

include_once($config['html_dir'] . "/includes/functions.inc.php");

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

if (version_compare(PHP_VERSION, '5.3.27', '<'))
{
  $notifications[] = array('text' => '<h4>Your PHP version is too old.</h4> Your currently installed PHP version <b>' . PHP_VERSION . '</b> is older than the required minimum of <b>5.4</b>.
                                        Please upgrade your version of PHP to prevent possible incompatibilities and security problems.', 'severity' => 'danger');
}

if (isset($config['alerts']['suppress']) && $config['alerts']['suppress'])
{
  $notifications[] = array('text' => '<h4>All Alert Notifications Suppressed</h4>'.
                                     'All alert notifications have been suppressed in the configuration.',
                                     'severity' => 'warning');
}

// verify if PHP supports session, die if it does not
check_extension_exists('session', '', TRUE);

ob_start('html_callback');
//ob_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <base href="<?php echo($config['base_url']); ?>" />
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <link href="css/bootstrap.css" rel="stylesheet" type="text/css" />
  <link href="css/bootstrap-select.css" rel="stylesheet" type="text/css" />
  <link href="css/bootstrap-switch.css" rel="stylesheet" type="text/css" />
  <link href="css/bootstrap-hacks.css" rel="stylesheet" type="text/css" />
  <link href="css/jquery.qtip.min.css" rel="stylesheet" type="text/css" />
  <link href="css/sprite.css" rel="stylesheet" type="text/css" />
  <link href="css/flags.css" rel="stylesheet" type="text/css" />
  <!-- ##CSS_CACHE## -->
  <script type="text/javascript" src="js/jquery.min.js"></script>
  <!--<script type="text/javascript" src="js/jquery-ui.min.js"></script>--> <?php // FIXME. We not use JQueryUI or I wrong? (mike) ?>
  <script src="js/bootstrap.min.js"></script>
  <!-- ##JS_CACHE## -->
<?php /* html5.js below from https://github.com/aFarkas/html5shiv */ ?>
  <!--[if lt IE 9]><script src="js/html5shiv.min.js"></script><![endif]-->
<?php

$runtime_start = utime();

ini_set('allow_url_fopen', 0);
ini_set('display_errors', 0);

$_SERVER['PATH_INFO'] = (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $_SERVER['ORIG_PATH_INFO']);

$vars = get_vars(); // Parse vars from GET/POST/URI
// print_vars($vars);

if ($vars['export'] == 'yes') // This is for display XML on export pages
{
  // Code prettify (but it's still horrible)
  $GLOBALS['cache_html']['js'][]  = 'js/google-code-prettify.js';
  $GLOBALS['cache_html']['css'][] = 'css/google-code-prettify.css';
}

include($config['html_dir'] . "/includes/authenticate.inc.php");

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
  <title><?php if (isset($config['page_title']))
               {
                  echo($config['page_title']);
               } else {
                  $title = array(nicecase($vars['page']));
                  // if suffix is set, put it in the back
                  if ($config['page_title_suffix']) { $title[] = $config['page_title_suffix']; }
                  echo(rtrim($config['page_title_prefix'] . implode(" - ", $title), ' -:'));
                  unset($title);

               } ?></title>
  <link rel="shortcut icon" href="<?php echo($config['favicon']);  ?>" />
<?php

if ($_SESSION['authenticated'])
{
  $feeds = array('eventlog');
  //if ($config['enable_syslog']) { $feeds[] = 'syslog'; }
  foreach ($feeds as $feed)
  {
    $feed_href = generate_feed_url(array('feed' => $feed));
    if ($feed_href) echo($feed_href.PHP_EOL);
  }
}

if ($vars['widescreen'] == "yes") { $_SESSION['widescreen'] = 1; unset($vars['widescreen']); }
if ($vars['widescreen'] == "no")  { unset($_SESSION['widescreen']); unset($vars['widescreen']); }

if ($vars['big_graphs'] == "yes") { $_SESSION['big_graphs'] = 1; unset($vars['big_graphs']); }
if ($vars['big_graphs'] == "no")  { unset($_SESSION['big_graphs']); unset($vars['big_graphs']); }

if ($_SESSION['widescreen'])
{
  // Widescreen style additions
  $GLOBALS['cache_html']['css'][] = 'css/styles-wide.css';
}

echo '</head>';

if($vars['bare'] == 'yes')
{
  echo '<body style="padding-top: 10px;">';
} else {
  echo '<body>';
}

// Determine type of web browser.
$browser_type = detect_browser_type();
if ($browser_type == 'mobile' || $browser_type == 'tablet') { $_SESSION['touch'] = 'yes'; }
if ($vars['touch'] == "yes") { $_SESSION['touch'] = 'yes'; }
if ($vars['touch'] == "no") { unset($_SESSION['touch'], $vars['touch']); }

if ($_SESSION['authenticated'])
{
  $allow_mobile = (in_array(detect_browser_type(), array('mobile', 'tablet')) ? $config['web_mouseover_mobile'] : TRUE);
  if ($config['web_mouseover'] && $allow_mobile)
  {
    // Enable qTip tooltips
    $GLOBALS['cache_html']['js'][]  = 'js/jquery.qtip.min.js';
  }
  // Do various queries which we use in multiple places
  include($config['html_dir'] . "/includes/cache-data.inc.php");

  // Include navbar
  if ($vars['bare'] != "yes") { include($config['html_dir'] . "/includes/navbar.inc.php"); }

}
?>

  <div class="container">

<?php

if ($_SESSION['authenticated'])
{
  if ($_SESSION['userlevel'] > 7)
  {
    $latest['version']  = get_obs_attrib('latest_ver');
    $latest['revision'] = get_obs_attrib('latest_rev');
    $latest['date']     = get_obs_attrib('latest_rev_date');

    if ($latest['revision'] > OBSERVIUM_REV + $config['version_check_revs'])
    {
      $notifications[] = array('text' => '<h4>There is a newer revision of Observium available!</h4> Version '. $latest['version'] .' ('.format_unixtime(datetime_to_unixtime($latest['date']), 'jS F Y').') is ' .($latest['revision']-OBSERVIUM_REV) .' revisions ahead.', 'severity' => 'warning');
      $alerts[]        = array('text' => '<h4>There is a newer revision of Observium available!</h4> Version '. $latest['version'] .' ('.format_unixtime(datetime_to_unixtime($latest['date']), 'jS F Y').') is ' .($latest['revision']-OBSERVIUM_REV) .' revisions ahead.', 'severity' => 'warning');
    }

    // Warn about lack of mcrypt unless told not to.
    if ($config['login_remember_me'] || isset($_SESSION['mcrypt_required']))
    {
      check_extension_exists('mcrypt', 'This extension required for use by the "remember me" feature. Please install the php5-mcrypt package on Ubuntu/Debian or the php-mcrypt package on RHEL/CentOS. Alternatively, you can disable this feature by setting $config[\'login_remember_me\'] = FALSE; in your config.');
    }

    // Warning about web_url config, only for ssl
    if (is_ssl() && preg_match('/^http:/', $config['web_url']))
    {
      $notifications[] = array('text' => 'Setting \'web_url\' for "External Web URL" not set or incorrect, please update on ' . generate_link('Global Settings Edit', array('page' => 'settings', 'section' => 'wui')) . ' page.', 'severity' => 'warning');
    }

    // Warning about need DB schema update
    $db_version = get_db_version();
    $db_version = sprintf("%03d", $db_version+1);
    if (is_file($config['install_dir'] . "/update/$db_version.sql") || is_file($config['install_dir'] . "/update/$db_version.php"))
    {
      $notifications[] = array('text' => 'Your database schema is old and needs updating. Run from server console:
                  <pre style="padding: 3px" class="small">' . $config['install_dir'] . '/discovery.php -u</pre>', 'severity' => 'alert');
    }
    unset($db_version);

    // Check mysqli extension
    if (OBS_DB_EXTENSION != 'mysqli' && check_extension_exists('mysqli', ''))
    {
      $notifications[] = array('title'    => 'Deprecated MySQL Extension', 
                               'text'     => 'The deprecated mysql extension is still in use, we recommend using mysqli.<br />To switch, add the following to your config.php: <pre>$config[\'db_extension\']  = \'mysqli\';</pre>', 
                               'severity' => 'warning');
    }
    //$notifications[] = array('text' => dbHostInfo(), 'severity' => 'debug');

    // Warning about obsolete config on some pages
    if (OBS_DEBUG ||
        in_array($vars['tab'], array('data', 'perf', 'edit', 'showtech')) ||
        in_array($vars['page'], array('pollerlog', 'settings', 'preferences')))
    {
      // FIXME move to notification center?
      print_obsolete_config();
    }
  }

  if (isset($cache['maint']['count']) && $cache['maint']['count'] > 0)
  {
    $notifications[] = array('text' => '<h4>Scheduled Maintenance in Progress</h4>'.
                                     'Some or all alert notifications have been suppressed due to a scheduled maintenance.',
                                     'severity' => 'warning');


    $alerts[] = array('text' => '<h4>Scheduled Maintenance in Progress</h4>'.
                                     'Some or all alert notifications have been suppressed due to a scheduled maintenance.',
                                     'severity' => 'warning');

  }

  foreach ($alerts as $alert)
  {
    // FIXME handle severity parameter with colour or icon?
    echo('<div width="100%" class="alert alert-'.$alert['severity'].'">');
    if(isset($alert['title'])) { echo('<h4>'.$alert['title'].'</h4>'); }
    echo($alert['text'] . '</div>');
  }

  // Authenticated. Print a page.
  if (isset($vars['page']) && !strstr("..", $vars['page']) && is_file($config['html_dir']."/pages/" . $vars['page'] . ".inc.php"))
  {
    $page_file = $config['html_dir']."/pages/" . $vars['page'] . ".inc.php";
  } else {
    if (isset($config['front_page']) && is_file($config['html_dir']."/".$config['front_page']))
    {
      $page_file = $config['front_page'];
      $vars['page'] = 'front';
    } else {
      $page_file = $config['html_dir']."/pages/front/default.php";
      $vars['page'] = 'front';
    }
  }

  if($config['pages'][$vars['page']]['custom_panel'])
  {
    include($page_file);
  } else {


    if(is_file($config['html_dir']."/includes/panels/".$vars['page'].".inc.php"))
    {
      $panel_file = $config['html_dir']."/includes/panels/".$vars['page'].".inc.php";
    } else {
      $panel_file = $config['html_dir']."/includes/panels/default.inc.php";
    }
  ?>

<div class="row">
<div class="col-xl-4 visible-xl">

<?php include($panel_file); ?>

</div>

<div class="col-xl-8 col-lg-12">

<?php include($page_file); ?>

</div>

  <?php
  }

} else if ($config['auth_mechanism'] == 'cas') {
  // Not Authenticated. CAS logon.
  echo('Not authorized.');

  exit;
} else {
  // Not Authenticated. Print login.
  include($config['html_dir']."/pages/logon.inc.php");

  exit;
}

$runtime_end = utime(); $runtime = $runtime_end - $runtime_start;
$gentime = substr($runtime, 0, 5);
$fullsize = memory_get_usage();
unset($cache);
$cachesize = $fullsize - memory_get_usage();
if ($cachesize < 0) { $cachesize = 0; } // Silly PHP!


?>
</div>

<?php
if($vars['bare'] != 'yes')
{

?>

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
          <li class="dropdown"><a href="<?php echo OBSERVIUM_URL; ?>" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><?php echo OBSERVIUM_PRODUCT . ' ' . OBSERVIUM_VERSION_LONG; ?></a>
            <div class="dropdown-menu" style="padding: 10px;">
              <div style="max-width: 145px;"><img src="images/login-hamster-large.png" alt="" /></div>

            </div>
          </li>
        </ul>

        <ul class="nav pull-right">
          <!--<li><a id="poller_status"></a></li>-->
          <li class="dropdown">
            <?php
            $notification_count = count($notifications);
            if (count($notifications)) // FIXME level 10 only, maybe? (answer: just do not add notifications for this users. --mike)
            {
              $div_class = 'dropdown-menu';
              if ($notification_count > 5)
              {
                $div_class .= ' pre-scrollable';
              }
            ?>
            <a href="<?php echo(generate_url(array('page'=>'overview'))); ?>" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
              <i class="oicon-exclamation-red"></i> <b class="caret"></b></a>
            <div class="<?php echo($div_class); ?>" style="width: 700px; max-height: 500px; z-index: 2000;">

              <h3>Notifications</h3>
<?php
foreach ($notifications as $notification)
{
  // FIXME handle severity parameter with colour or icon?
  echo('<div width="100%" class="callout callout-'.$notification['severity'].'">');
  if(isset($notification['title'])) { echo('<h4>'.$notification['title'].'</h4>'); }
  echo($notification['text'] . '</div>');
}
?>
            </div>
            <?php
            } else {
              // Dim the icon to 20% opacity, makes the red pretty much blend in to the navbar
              ?>
            <a href="<?php echo(generate_url(array('page'=>'overview'))); ?>" data-alt="Notification center" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
              <i style="opacity: 0.2; filter: alpha(opacity=20);" class="oicon-tick-circle"></i></a>
              <?php
            }
            ?>
          </li>

          <li class="dropdown">
            <a href="<?php echo(generate_url(array('page'=>'overview'))); ?>" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
              <i class="oicon-time"></i> <?php echo($gentime); ?>s <b class="caret"></b></a>
            <div class="dropdown-menu" style="padding: 10px 10px 0px 10px;">
              <table class="table  table-condensed-more  table-striped">
                <tr>
                  <th>Page</th><td><?php echo($gentime); ?>s</td>
                </tr>
                <tr>
                  <th>Cache</th><td><?php echo($cache_time); ?>s</td>
                </tr>

              </table>
              <table class="table  table-condensed-more  table-striped">
                <tr>
                  <th colspan=2>MySQL</th>
                </tr>
                <tr>
                  <th>Cell</th><td><?php echo(($db_stats['fetchcell']+0).'/'.round($db_stats['fetchcell_sec']+0,4).'s'); ?></td>
                </tr>
                <tr>
                  <th>Row</th><td><?php echo(($db_stats['fetchrow']+0).'/'.round($db_stats['fetchrow_sec'],4).'s'); ?></td>
                </tr>
                <tr>
                  <th>Rows</th><td><?php echo(($db_stats['fetchrows']+0).'/'.round($db_stats['fetchrows_sec']+0,4).'s'); ?></td>
                </tr>
                <tr>
                  <th>Column</th><td><?php echo(($db_stats['fetchcol']+0).'/'.round($db_stats['fetchcol_sec']+0,4).'s'); ?></td>
                </tr>
              </table>
              <table class="table  table-condensed-more  table-striped">
                <tr>
                  <th colspan=2>Memory</th>
                </tr>
                <tr>
                  <th>Cached</th><td><?php echo formatStorage($cachesize); ?></td>
                </tr>
                <tr>
                  <th>Page</th><td><?php echo formatStorage($fullsize); ?></td>
                </tr>
                <tr>
                  <th>Peak</th><td><?php echo formatStorage(memory_get_peak_usage()); ?></td>
                </tr>
              </table>
            </div>
          </li>

<?php if ($config['profile_sql'] == TRUE && $SESSION['userlevel'] = '10') // FIXME level 10 only?
{
?>
          <li class="dropdown">
            <a href="<?php echo(generate_url(array('page'=>'overview'))); ?>" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
              <i class="oicon-databases"></i> <b class="caret"></b></a>
            <div class="dropdown-menu" style="padding: 10px 10px 0px 10px; width: 1150px; height: 700px; overflow: scroll;">

              <table class="table  table-condensed-more  table-striped">

  <?php

  $sql_profile = array_sort($sql_profile, 'time', 'SORT_DESC');
  $sql_profile = array_slice($sql_profile, 0, 15);
  foreach ($sql_profile AS $sql_query)
  {
    echo '<tr><td>', $sql_query['time'], '</td><td>';
    print_sql($sql_query['sql']);
    echo '</td></tr>';
  }

  ?>
              </table>
            </div>
          </li>
<?php
} // End profile_sql
?>

       </ul>
      </div>
    </div>
  </div>
</div>

<?php
} // end if bare

if (is_array($page_title))
{
  // if suffix is set, put it in the back
  if ($config['page_title_suffix']) { $page_title[] = $config['page_title_suffix']; }

  $title = implode(" - ", $page_title);

  // if prefix is set, put it in front
  if ($config['page_title_prefix']) { $title = $config['page_title_prefix'] . $title; }

  // set the title
  echo('<script type="text/javascript">document.title = "'.$title.'";</script>');
}

//  <script type="text/javascript">
//  $(document).ready(function()
//  {
//    $('#poller_status').load('ajax_poller_status.php');
//  });
//
//  var auto_refresh = setInterval(
//    function ()
//    {
//      $('#poller_status').load('ajax_poller_status.php');
//    }, 10000); // refresh every 10000 milliseconds
//  </script>


  // No dropdowns on touch gadgets
  if ($_SESSION['touch'] != 'yes')
  {
    echo '<script type="text/javascript" src="js/twitter-bootstrap-hover-dropdown.min.js"></script>';
  }

?>

  <script type="text/javascript" src="js/bootstrap-select.min.js"></script>
  <script type="text/javascript">$('.selectpicker').selectpicker();</script>

  <script type="text/javascript" src="js/bootstrap-switch.min.js"></script>
  <script type="text/javascript" src="js/observium.js"></script>
  <!-- ##SCRIPT_CACHE## -->

  </body>
</html>
<?php

// EOF
