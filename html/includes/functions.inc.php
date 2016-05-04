<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage functions
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// Notifications and alerts in bottom navbar
$notifications = array();
$alerts        = array();

include_once($config['html_dir'].'/includes/graphs/functions.inc.php');

$print_functions = array('addresses', 'events', 'mac_addresses', 'rows',
                         'status', 'arptable', 'fdbtable', 'navbar',
                         'search', 'syslogs', 'inventory', 'alert',
                         'authlog', 'dot1xtable', 'alert_log',
                         'common', 'routing', 'neighbours');

foreach ($print_functions as $item)
{
  $print_path = $config['html_dir'].'/includes/print/'.$item.'.inc.php';
  if (is_file($print_path)) { include($print_path); }
}

// Load generic entity include

include($config['html_dir'].'/includes/entities/generic.inc.php');

// Load all per-entity includes
foreach ($config['entities'] as $entity_type => $item)
{
  $path = $config['html_dir'].'/includes/entities/'.$entity_type.'.inc.php';
  if (is_file($path)) { include($path); }
}

/**
 * Used for replace some strings at end of run all html scripts
 *
 * @param string $buffer HTML buffer from ob_start()
 * @return string Changed buffer
 */
function html_callback($buffer)
{
  $types = array(
    'css'    => '  <link href="STRING" rel="stylesheet" type="text/css" />' . PHP_EOL,
    'js'     => '  <script type="text/javascript" src="STRING"></script>' . PHP_EOL,
    'script' => '  <script type="text/javascript">' . PHP_EOL .
                '  <!-- Begin' . PHP_EOL . 'STRING' . PHP_EOL .
                '  // End -->' . PHP_EOL . '  </script>' . PHP_EOL,
  );

  foreach ($types as $type => $string)
  {
    if (isset($GLOBALS['cache_html'][$type]))
    {
      $uptype = strtoupper($type);
      $$type = '<!-- ' . $uptype . ' BEGIN -->' . PHP_EOL;
      foreach (array_unique($GLOBALS['cache_html'][$type]) as $link) // Do not use global $cache variable, because it reset before flush ob_cache
      {
        $$type .= str_replace('STRING', $link, $string);
      }
      $$type .= '  <!-- ' . $uptype . ' END -->' . PHP_EOL;

      $buffer = str_replace('<!-- ##' . $uptype . '_CACHE## -->', $$type, $buffer);
    }
  }
  return $buffer;
}

// Parce $_GET, $_POST and URI into $vars
// TESTME needs unit testing
// DOCME needs phpdoc block
function get_vars($vars_order = array())
{
  if (is_string($vars_order))
  {
    $vars_order = explode(' ', $vars_order);
  }
  else if (empty($vars_order) || !is_array($vars_order))
  {
    $vars_order = array('OLDGET', 'POST', 'URI', 'GET'); // Default order
  }

  $vars = array();
  foreach ($vars_order as $order)
  {
    $order = strtoupper($order);
    switch ($order)
    {
      case 'OLDGET':
        // Parse GET variables into $vars for backwards compatibility
        // Can probably remove this soon
        foreach ($_GET as $key=>$get_var)
        {
          if (strstr($key, "opt"))
          {
            list($name, $value) = explode("|", $get_var);
            if (!isset($value)) { $value = "yes"; }
            if (!isset($vars[$name])) { $vars[$name] = $value; }
          }
        }
        break;
      case 'POST':
        // Parse POST variables into $vars
        foreach ($_POST as $name => $value)
        {
          if (!isset($vars[$name]))
          {
            $vars[$name] = var_decode($value);
          }
        }
        break;
      case 'URI':
      case 'URL':
        // Parse URI into $vars
        $segments = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        foreach ($segments as $pos => $segment)
        {
          //$segment = urldecode($segment);
          if ($pos == "0" && strpos($segment, '=') === FALSE)
          {
            $segment = urldecode($segment);
            $vars['page'] = $segment;
          } else {
            list($name, $value) = explode('=', $segment, 2);
            if (!isset($vars[$name]))
            {
              if (!isset($value) || $value === '')
              {
                $vars[$name] = 'yes';
              } else {
                $value = str_replace('%7F', '/', urldecode($value)); // %7F (DEL, delete) - not defined in HTML 4 standard
                if (strpos($value, ','))
                {
                  // Here commas list (convert to array)
                  $vars[$name] = explode(',', $value);
                } else {
                  // Here can be string as encoded array
                  $vars[$name] = var_decode($value);
                  if (strpos($vars[$name], '%1F') !== FALSE)
                  {
                    $vars[$name] = str_replace('%1F', ',', $vars[$name]); // %1F (US, unit separator) - not defined in HTML 4 standard
                  }
                }
              }
            }
          }
        }
        break;
      case 'GET':
        // Parse GET variable into $vars
        foreach ($_GET as $name => $value)
        {
          if (!isset($vars[$name]))
          {
            $value = str_replace('%7F', '/', urldecode($value)); // %7F (DEL, delete) - not defined in HTML 4 standard
            if (strpos($value, ','))
            {
              // Here commas list (convert to array)
              $vars[$name] = explode(',', $value);
            } else {
              // Here can be string as encoded array
              $vars[$name] = var_decode($value);
              if (strpos($vars[$name], '%1F') !== FALSE)
              {
                $vars[$name] = str_replace('%1F', ',', $vars[$name]); // %1F (US, unit separator) - not defined in HTML 4 standard
              }
            }
          }
        }
        break;
    }
  }

  // Always convert location to array
  if (isset($vars['location']))
  {
    if ($vars['location'] === '')
    {
      // Unset location if is empty string
      unset($vars['location']);
    }
    else if (is_array($vars['location']))
    {
      // Additionaly decode locations if array entries encoded
      foreach ($vars['location'] as $k => $location)
      {
        $vars['location'][$k] = var_decode($location);
      }
    } else {
       // All other location strings covert to array
      $vars['location'] = array($vars['location']);
    }
  }

  //r($vars);
  return($vars);
}

// Detect if current URI is link to graph
// TESTME needs unit testing
// DOCME needs phpdoc block
function is_graph()
{
  //if (OBS_DEBUG)
  //{
  //  print_vars(realpath($_SERVER['SCRIPT_FILENAME']));
  //  print_vars(realpath($GLOBALS['config']['html_dir'].'/graph.php'));
  //  print_vars(realpath($_SERVER['SCRIPT_FILENAME']) === realpath($GLOBALS['config']['html_dir'].'/graph.php'));
  //}
  return (realpath($_SERVER['SCRIPT_FILENAME']) === realpath($GLOBALS['config']['html_dir'].'/graph.php'));
}

// TESTME needs unit testing
/**
 * Generates base64 data uri with alert graph
 *
 * @return string
 */
function generate_alert_graph($graph_array)
{
  global $config;

  $vars = $graph_array;
  $auth = (is_cli() ? TRUE : $GLOBALS['auth']); // Always set $auth to true for cli
  $vars['image_data_uri'] = TRUE;
  $vars['height'] = '150';
  $vars['width']  = '400';
  $vars['legend'] = 'no';
  $vars['from']   = $config['time']['twoday'];
  $vars['to']     = $config['time']['now'];

  include($config['html_dir'].'/includes/graphs/graph.inc.php');

  return $image_data_uri;
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function datetime_preset($preset)
{
  $begin_fmt = 'Y-m-d 00:00:00';
  $end_fmt   = 'Y-m-d 23:59:59';

  switch($preset)
  {
    case 'sixhours':
      $from = date('Y-m-d H:i:00', strtotime('-6 hours'));
      $to   = date('Y-m-d H:i:59');
      break;
    case 'today':
      $from = date($begin_fmt);
      $to   = date($end_fmt);
      break;
    case 'yesterday':
      $from = date($begin_fmt, strtotime('-1 day'));
      $to   = date($end_fmt,   strtotime('-1 day'));
      break;
    case 'tweek':
      $from = (date('l') == 'Monday') ? date($begin_fmt) : date($begin_fmt, strtotime('last Monday'));
      $to   = (date('l') == 'Sunday') ? date($end_fmt)   : date($end_fmt,   strtotime('next Sunday'));
      break;
    case 'lweek':
      $from = date($begin_fmt, strtotime('-6 days'));
      $to   = date($end_fmt);
      break;
    case 'tmonth':
      $tmonth = date('Y-m');
      $from = $tmonth.'-01 00:00:00';
      $to   = date($end_fmt, strtotime($tmonth.' next month - 1 hour'));
      break;
    case 'lmonth':
      $from = date($begin_fmt, strtotime('previous month + 1 day'));
      $to   = date($end_fmt);
      break;
    case 'tquarter':
    case 'lquarter':
      $quarter = ceil(date('m') / 3); // Current quarter
      if ($preset == 'lquarter')
      {
        $quarter = $quarter - 1; // Previous quarter
      }
      $year = date('Y');
      if ($quarter < 1)
      {
        $year   -= 1;
        $quarter = 4;
      }
      $tmonth = $quarter * 3;
      $fmonth = $tmonth - 2;

      $from = $year.'-'.zeropad($fmonth).'-01 00:00:00';
      $to   = date('Y-m-t 23:59:59', strtotime($year.'-'.$tmonth.'-01'));
      break;
    case 'tyear':
      $from = date('Y-01-01 00:00:00');
      $to   = date('Y-12-31 23:59:59');
      break;
    case 'lyear':
      $from = date($begin_fmt, strtotime('previous year + 1 day'));
      $to   = date($end_fmt);
      break;
  }

  return array('from' => $from, 'to' => $to);
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function bug()
{
  echo('<div class="alert alert-error">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <strong>Bug!</strong> Please report this to the Observium development team.
</div>');
}

/**
 * This function determines type of web browser for current User-Agent (mobile/tablet/generic).
 * For more detailed browser info and custom User-Agent use detect_browser()
 *
 * @return string Return type of browser (generic/mobile/tablet)
 */
function detect_browser_type()
{
  $ua_info = detect_browser();

  return $ua_info['type'];
}

/**
 * This function determines detailed info of web browser by User-Agent agent string.
 * If User-Agent not passed, used current from $_SERVER['HTTP_USER_AGENT'] 
 *
 * @param string $user_agent Custom User-Agent string, by default, the value of HTTP User-Agent header is used
 *
 * @return array Return detected browser info: user_agent, type, icon, platform, browser, version,
 *                                             browser_full - full browser name (ie: Chrome 43.0)
 *                                             svg          - supported or not svg images (TRUE|FALSE),
 *                                             screen_ratio - for HiDPI screens it more that 1,
 *                                             screen_resolution - full resolution of client screen (if exist),
 *                                             screen_size  - initial size of browser window (if exist)
 */
// TESTME! needs unit testing
function detect_browser($user_agent = NULL)
{
  $ua_custom = !is_null($user_agent); // Used custom user agent?

  if (!$ua_custom && isset($GLOBALS['cache']['detect_browser']))
  {
    //if (isset($_COOKIE['observium_screen_ratio']) && !isset($GLOBALS['cache']['detect_browser']['screen_resolution']))
    //{
    //  r($_COOKIE);
    //}
    // Return cached info
    return $GLOBALS['cache']['detect_browser'];
  }

  $detect = new Mobile_Detect;

  if ($ua_custom)
  {
    // Set custom User-Agent
    $detect->setUserAgent($user_agent);
  } else {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
  }

  // Default type and icon
  $type = 'generic';
  $icon = 'icon-laptop';
  if ($detect->isMobile())
  {
    // Any phone device (exclude tablets).
    $type = 'mobile';
    $icon = 'glyphicon glyphicon-phone';
    if ($detect->isTablet())
    {
      // Any tablet device.
      $type = 'tablet';
      $icon = 'icon-tablet';
    }
  }

  // Load additional function
  if (!function_exists('parse_user_agent'))
  {
    include_once($GLOBALS['config']['install_dir'].'/libs/UserAgentParser.php');
  }

  // Detect Browser name, version and platform
  $ua_info = parse_user_agent($user_agent);

  $detect_browser = array('user_agent' => $user_agent,
                          'type'       => $type,
                          'icon'       => $icon,
                          'browser_full' => $ua_info['browser'] . ' ' . preg_replace('/^([^\.]+(?:\.[^\.]+)?).*$/', '\1', $ua_info['version']),
                          'browser'    => $ua_info['browser'],
                          'version'    => $ua_info['version'],
                          'platform'   => $ua_info['platform']);

   // For custom UA, do not cache and return only base User-Agent info
  if ($ua_custom)
  {
    return $detect_browser;
  }

  // Load screen and DPI detector. This set cookies with:
  //  $_COOKIE['observium_screen_ratio'] - if ratio >= 2, than HiDPI screen is used
  //  $_COOKIE['observium_screen_resolution'] - screen resolution 'width x height', ie: 1920x1080
  //  $_COOKIE['observium_screen_size'] - current window size (less than resolution) 'width x height', ie: 1097x456
  $GLOBALS['cache_html']['js'][]  = 'js/observium-screen.js';

  // Additional browser info (screen_ratio, screen_size, svg)
  if ($ua_info['browser'] == 'Firefox')
  {
    // Do not use srcset in FF, while issue open:
    // https://bugzilla.mozilla.org/show_bug.cgi?id=1149357
    $zoom = 1;
  }
  else if (isset($_COOKIE['observium_screen_ratio']))
  {
    // Note, Opera uses ratio 1.5
    $zoom = round($_COOKIE['observium_screen_ratio']); // Use int zoom
  } else {
    // If JS not supported or cookie not set, use default zoom 2 (for allow srcset)
    $zoom = 2;
  }
  $detect_browser['screen_ratio'] = $zoom;
  $detect_browser['svg']          = ($ua_info['browser'] == 'Firefox'); // SVG supported or allowed
  if (isset($_COOKIE['observium_screen_resolution']))
  {
    $detect_browser['screen_resolution'] = $_COOKIE['observium_screen_resolution'];
    //$detect_browser['screen_size']       = $_COOKIE['observium_screen_size'];
  }

  $GLOBALS['cache']['detect_browser'] = $detect_browser; // Store to cache

  //r($GLOBALS['cache']['detect_browser']);
  return $GLOBALS['cache']['detect_browser'];
}

// DOCME needs phpdoc block
function safe_base64_encode($string)
{
  $data = base64_encode($string);
  $data = str_replace(array('+','/','='), array('-','_',''), $data);
  return $data;
}

// DOCME needs phpdoc block
function safe_base64_decode($string)
{
  $data = str_replace(array('-','_'), array('+','/'), $string);
  $mod4 = strlen($data) % 4;
  if ($mod4)
  {
    $data .= substr('====', $mod4);
  }
  return base64_decode($data);
}

// DOCME needs phpdoc block
// MOVEME includes/common.inc.php
function encrypt($string, $key)
{
  return safe_base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $string, MCRYPT_MODE_ECB));
}

// DOCME needs phpdoc block
// MOVEME includes/common.inc.php
function decrypt($encrypted, $key)
{
  return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, safe_base64_decode($encrypted), MCRYPT_MODE_ECB), "\t\n\r\0\x0B");
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function data_uri($file, $mime)
{
  $contents = file_get_contents($file);
  $base64   = base64_encode($contents);

  return ('data:' . $mime . ';base64,' . $base64);
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function toner_map($descr, $colour)
{
  foreach ($GLOBALS['config']['toner'][$colour] as $str)
  {
    if (stripos($descr, $str) !== FALSE) { return TRUE; }
  }

  return FALSE;
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function toner_to_colour($descr, $percent)
{
  $colour = get_percentage_colours(100-$percent);

  if (substr($descr, -1) == 'C' || toner_map($descr, "cyan"   )) { $colour['left'] = "B6F6F6"; $colour['right'] = "33B4B1"; }
  if (substr($descr, -1) == 'M' || toner_map($descr, "magenta")) { $colour['left'] = "FBA8E6"; $colour['right'] = "D028A6"; }
  if (substr($descr, -1) == 'Y' || toner_map($descr, "yellow" )) { $colour['left'] = "FFF764"; $colour['right'] = "DDD000"; }
  if (substr($descr, -1) == 'K' || toner_map($descr, "black"  )) { $colour['left'] = "888787"; $colour['right'] = "555555"; }

  return $colour;
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_link($text, $vars, $new_vars = array(), $escape = TRUE)
{
  if ($escape) { $text = escape_html($text); }
  return '<a href="'.generate_url($vars, $new_vars).'">'.$text.'</a>';
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function pagination(&$vars, $total, $return_vars = FALSE)
{
  $pagesizes = array(10,20,50,100,500,1000,10000,50000); // Permitted pagesizes
  if (is_numeric($vars['pagesize']))
  {
    $per_page = (int)$vars['pagesize'];
  }
  else if (isset($_SESSION['pagesize']))
  {
    $per_page = $_SESSION['pagesize'];
  } else {
    $per_page = $GLOBALS['config']['web_pagesize'];
  }
  if (!$vars['short'])
  {
    // Permit fixed pagesizes only (except $vars['short'] == TRUE)
    foreach ($pagesizes as $pagesize)
    {
      if ($per_page <= $pagesize) { $per_page = $pagesize; break; }
    }
    if (isset($vars['pagesize']) && $vars['pagesize'] != $_SESSION['pagesize'])
    {
      if ($vars['pagesize'] != $GLOBALS['config']['web_pagesize'])
      {
        $_SESSION['pagesize'] = $per_page; // Store pagesize in session only if changed default
      }
      else if (isset($_SESSION['pagesize']))
      {
        unset($_SESSION['pagesize']);      // Reset pagesize from session
      }
    }
  }
  $vars['pagesize'] = $per_page;       // Return back current pagesize

  $page     = (int)$vars['pageno'];
  $lastpage = ceil($total/$per_page);
  if ($page < 1) { $page = 1; }
  else if (!$return_vars && $lastpage < $page) { $page = (int)$lastpage; }
  $vars['pageno'] = $page; // Return back current pageno

  if ($return_vars) { return ''; } // Silent exit (needed for detect default pagesize/pageno)

  $start = ($page - 1) * $per_page;
  $prev  = $page - 1;
  $next  = $page + 1;
  $lpm1  = $lastpage - 1;

  $adjacents = 5;
  $pagination = '';

  if ($total > 99 || $total > $per_page)
  {
    $pagination .= '<div class="row">';
    $pagination .= '<div class="col-sm-1"><span class="btn disabled" style="line-height: 20px;">'.$total.'&nbsp;Items</span></div>';
    $pagination .= '<div class="col-sm-10">';
    $pagination .= '<div class="pagination pagination-centered"><ul>';

    if ($prev)
    {
      #$pagination .= '<li><a href="'.generate_url($vars, array('pageno' => 1)).'">First</a></li>';
      $pagination .= '<li><a href="'.generate_url($vars, array('pageno' => $prev)).'">Prev</a></li>';
    }

    if ($lastpage < 7 + ($adjacents * 2))
    {
      for ($counter = 1; $counter <= $lastpage; $counter++)
      {
        if ($counter == $page)
        {
          $pagination.= "<li class='active'><a>$counter</a></li>";
        } else {
          $pagination.= "<li><a href='".generate_url($vars, array('pageno' => $counter))."'>$counter</a></li>";
        }
      }
    }
    elseif ($lastpage > 5 + ($adjacents * 2))
    {
      if ($page < 1 + ($adjacents * 2))
      {
        for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
        {
          if ($counter == $page)
          {
            $pagination.= "<li class='active'><a>$counter</a></li>";
          } else {
            $pagination.= "<li><a href='".generate_url($vars, array('pageno' => $counter))."'>$counter</a></li>";
          }
        }

        $pagination.= "<li><a href='".generate_url($vars, array('pageno' => $lpm1))."'>$lpm1</a></li>";
        $pagination.= "<li><a href='".generate_url($vars, array('pageno' => $lastpage))."'>$lastpage</a></li>";
      }
      elseif ($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
      {
        $pagination.= "<li><a href='".generate_url($vars, array('pageno' => '1'))."'>1</a></li>";
        $pagination.= "<li><a href='".generate_url($vars, array('pageno' => '2'))."'>2</a></li>";

        for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
        {
          if ($counter == $page)
          {
            $pagination.= "<li class='active'><a>$counter</a></li>";
          } else {
            $pagination.= "<li><a href='".generate_url($vars, array('pageno' => $counter))."'>$counter</a></li>";
          }
        }

        $pagination.= "<li><a href='".generate_url($vars, array('pageno' => $lpm1))."'>$lpm1</a></li>";
        $pagination.= "<li><a href='".generate_url($vars, array('pageno' => $lastpage))."'>$lastpage</a></li>";
      } else {
        $pagination.= "<li><a href='".generate_url($vars, array('pageno' => '1'))."'>1</a></li>";
        $pagination.= "<li><a href='".generate_url($vars, array('pageno' => '2'))."'>2</a></li>";
        for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
        {
          if ($counter == $page)
          {
            $pagination.= "<li class='active'><a>$counter</a></li>";
          } else {
            $pagination.= "<li><a href='".generate_url($vars, array('pageno' => $counter))."'>$counter</a></li>";
          }
        }
      }
    }

    if ($page < $counter - 1)
    {
      $pagination.= "<li><a href='".generate_url($vars, array('pageno' => $next))."'>Next</a></li>";
      # No need for "Last" as we don't have "First", 1, 2 and the 2 last pages are always in the list.
      #$pagination.= "<li><a href='".generate_url($vars, array('pageno' => $lastpage))."'>Last</a></li>";
    }
    else if ($lastpage > 1)
    {
      $pagination.= "<li class='active'><a>Next</a></li>";
      #$pagination.= "<li class='active'><a>Last</a></li>";
    }

    $pagination.= "</ul></div></div>";

    $pagination.= '
       <div class="col-sm-1">
       <form class="pull-right" action="#">
       <select class="selectpicker" data-width="90px" name="type" id="type" onchange="window.open(this.options[this.selectedIndex].value,\'_top\')">';

    foreach ($pagesizes as $pagesize)
    {
      $pagination .= '<option class="text-center" title="# '.$pagesize.'"';
      $pagination .= ' value="'.generate_url($vars, array('pagesize' => $pagesize, 'pageno' => floor($start / $pagesize))).'"';

      if ($pagesize == $per_page) { $pagination .= (' selected'); }
      if ($pagesize == $GLOBALS['config']['web_pagesize']) { $pagesize = "[ $pagesize ]"; }
      $pagination .= '>'.$pagesize.'</option>';
    }

    $pagination .= '</select></form></div></div>';
  }

  return $pagination;
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_url($vars, $new_vars = array())
{
  $vars = ($vars) ? array_merge($vars, $new_vars) : $new_vars;

  $url = $vars['page'];
  if ($url[strlen($url)-1] !== '/') { $url .= '/'; }
  unset($vars['page']);

  foreach ($vars as $var => $value)
  {
    if ($var == "username" || $var == "password")
    {
      // Ignore these vars. They shouldn't end up in URLs.
    }
    else if (is_array($value))
    {
      $url .= urlencode($var) . '=' . var_encode($value) . '/';
    }
    else if ($value == "0" || $value != "" && strstr($var, "opt") === FALSE && is_numeric($var) === FALSE)
    {
      $url .= urlencode($var) . '=' . urlencode($value).'/';
    }
  }

  // If we're being generated outside of the web interface, prefix the generated URL to make it work properly.
  if (is_cli())
  {
    if ($GLOBALS['config']['web_url'] == 'http://localhost:80/')
    {
      // override default web_url by http://localhost/
      $url = 'http://'.get_localhost().'/'.$url;
    } else {
      $url = $GLOBALS['config']['web_url'] . $url;
    }
  }

  return($url);
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_feed_url($vars)
{
  $url = FALSE;
  if (!class_exists('SimpleXMLElement')) { return $url; } // Break if class SimpleXMLElement is not available.

  if (is_numeric($_SESSION['user_id']) && is_numeric($_SESSION['userlevel']))
  {
    $key = get_user_pref($_SESSION['user_id'], 'atom_key');
  }
  if ($key)
  {
    $param   = array(rtrim($GLOBALS['config']['base_url'], '/').'/feed.php?id='.$_SESSION['user_id']);
    $param[] = 'hash='.encrypt($_SESSION['user_id'].'|'.$_SESSION['userlevel'].'|'.$_SESSION['auth_mechanism'], $key);

    $feed_type = 'atom';
    foreach ($vars as $var => $value)
    {
      if ($value != '')
      {
        switch ($var)
        {
          case 'v':
            if ($value == 'rss')
            {
              $param[] = "$var=rss";
              $feed_type = 'rss';
            }
            break;
          case 'feed':
            $title = "Observium :: ".ucfirst($value)." Feed";
            $param[] = 'size='.$GLOBALS['config']['frontpage']['eventlog']['items'];
            // no break here
          case 'size':
            $param[] = "$var=$value";
            break;
        }
      }
    }

    $baseurl = implode('&amp;', $param);

    $url = '<link href="'.$baseurl.'" rel="alternate" title="'.$title.'" type="application/'.$feed_type.'+xml" />';
  }

  return $url;
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_location_url($location, $vars = array())
{
  if ($location === '') { $location = OBS_VAR_UNSET; }
  $value = var_encode($location);
  return generate_url(array('page' => 'devices', 'location' => $value), $vars);
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_overlib_content($graph_array, $text = NULL, $escape = TRUE)
{
  global $config;

  $graph_array['height'] = "100";
  $graph_array['width']  = "210";
  
  if ($escape) { $text = escape_html($text); }

  $content = '<div style="width: 590px;"><span style="font-weight: bold; font-size: 16px;">'.$text.'</span><br />';
  /*
  $box_args = array('body-style' => 'width: 590px;');
  if (strlen($text))
  {
    $box_args['title'] = $text;
  }
  $content = generate_box_open($box_args);
  */
  foreach (array('day', 'week', 'month', 'year') as $period)
  {
    $graph_array['from'] = $config['time'][$period];
    $content .= generate_graph_tag($graph_array);
  }
  $content .= "</div>";
  //$content .= generate_box_close();

  return $content;
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function get_percentage_colours($percentage)
{

  if     ($percentage > '90') { $background['left']='cb181d'; $background['right']='fb6a4a'; $background['class'] = 'error'; }
  elseif ($percentage > '80') { $background['left']='cc4c02'; $background['right']='fe9929'; $background['class'] = 'warning'; }
  elseif ($percentage > '60') { $background['left']='6a51a3'; $background['right']='9e9ac8'; $background['class'] = 'information'; }
  elseif ($percentage > '30') { $background['left']='045a8d'; $background['right']='74a9cf'; $background['class'] = 'information'; }
  else                        { $background['left']='4d9221'; $background['right']='7fbc41'; $background['class'] = 'information'; }

  return($background);
}

/**
 * Generate common popup links which uses ajax/entitypopup.php
 *
 * @param string $type Popup type, see possible types in html/ajax/entitypopup.php
 * @param string $text Text used as link name and ajax data
 * @param array $vars Array for generate url
 * @param string Additional css classes for link
 * @param boolean $escape Escape or not text in url
 * @return string Returns string with link, when hover on this link show popup message based on type
 */
function generate_popup_link($type, $text = NULL, $vars = array(), $class = NULL, $escape = TRUE)
{
  if (!is_string($type) || !is_string($text)) { return ''; }

  if ($type == 'ip')
  {
    list($ip, $mask) = explode('/', $text, 2);
    $ip_version = get_ip_version($ip);
    if ($ip_version === 6)
    {
      // Autocompress IPv6 addresses
      $text = Net_IPv6::compress($ip);
      if (strlen($mask))
      {
        $text .= '/' . $mask;
      }
    }
  }
  $url  = (count($vars) ? generate_url($vars) : 'javascript:void(0)'); // If vars empty, set link not clickable
  $data = $text;
  if ($escape) { $text = escape_html($text); }

  return '<a href="'.$url.'" class="entity-popup'.($class ? " $class" : '').'" data-eid="'.$data.'" data-etype="'.$type.'">'.$text.'</a>';
}

/**
 * Generate mouseover links with static tooltip from URL, link text, contents and a class.
 *
 * Tooltips with static position and linked to current object.
 * Note, mostly same as overlib_link(), except tooltip position.
 * Always display tooltip if content not empty
 *
 * @param string $url URL string
 * @param string $text Text displayed as link
 * @param string $contents Text content displayed in mouseover tooltip (only for non-mobile devices)
 * @param string $class Css class name used for link
 * @param boolean $escape Escape or not link text
 */
// TESTME needs unit testing
function generate_tooltip_link($url, $text, $contents = '', $class = NULL, $escape = FALSE)
{
  global $config, $link_iter;

  $link_iter++;

  $href = (strlen($url) ? 'href="' . $url . '"' : '');
  if ($escape) { $text = escape_html($text); }

  // Allow the Grinch to disable popups and destroy Christmas.
  $allow_mobile = (in_array(detect_browser_type(), array('mobile', 'tablet')) ? $config['web_mouseover_mobile'] : TRUE);
  if ($config['web_mouseover'] && strlen($contents) && $allow_mobile)
  {
    $output  = '<a '.$href.' class="'.$class.'" style="cursor: pointer;" data-rel="tooltip" data-tooltip="'.escape_html($contents).'">'.$text.'</a>';
    //$output  = '<a '.$href.' class="'.$class.'" data-toggle="tooltip" title="'.escape_html($contents).'">'.$text.'</a>';
  } else {
    $output  = '<a '.$href.' class="'.$class.'">'.$text.'</a>';
  }

  return $output;
}

/**
 * Generate mouseover links from URL, link text, contents and a class.
 *
 * Tooltips followed by mouse cursor.
 * Note, by default text NOT escaped for compatability with many old magic code usage.
 *
 * @param string $url URL string
 * @param string $text Text displayed as link
 * @param string $contents Text content displayed in mouseover tooltip (only for non-mobile devices)
 * @param string $class Css class name used for link
 * @param boolean $escape Escape or not link text
 */
// TESTME needs unit testing
// RENAMEME to generate_mouseover_link() or something similar
function overlib_link($url, $text, $contents, $class = NULL, $escape = FALSE)
{
  global $config, $link_iter;

  $link_iter++;

  $href = (strlen($url) ? 'href="' . $url . '"' : '');
  if ($escape) { $text = escape_html($text); }

  // Allow the Grinch to disable popups and destroy Christmas.
  $allow_mobile = (in_array(detect_browser_type(), array('mobile', 'tablet')) ? $config['web_mouseover_mobile'] : TRUE);
  if ($config['web_mouseover'] && strlen($contents) && $allow_mobile)
  {
    $output  = '<a '.$href.' class="tooltip-from-data '.$class.'" style="cursor: pointer;" data-tooltip="'.escape_html($contents).'">'.$text.'</a>';
  } else {
    $output  = '<a '.$href.' class="'.$class.'">'.$text.'</a>';
  }

  return $output;
}

/**
 * Generate menu links with item counts from URL, link text, contents and a class.
 *
 * Tooltips with static position and linked to current object.
 * Note, mostly same as overlib_link(), except tooltip position.
 * Always display tooltip if content not empty
 *
 * @param string $url URL string
 * @param string $text Text displayed as link
 * @param string $count Counts displayed at right
 * @param string $class Css class name used for count (default is 'label')
 * @param boolean $escape Escape or not link text
 */
// TESTME needs unit testing
function generate_menu_link($url, $text, $count = NULL, $class = 'label', $escape = FALSE)
{
  $href = (strlen($url) ? 'href="' . $url . '"' : '');
  if ($escape) { $text = escape_html($text); }

  $output = '<a role="menuitem" ' . $href . '><span>' . $text . '</span>';
  if (is_numeric($count))
  {
    $output .= '<span class="' . $class . '">' . $count . '</span>';
  }
  $output .= '</a>';

  return $output;
}

// Generate a typical 4-graph popup using $graph_array
// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_graph_popup($graph_array)
{
  global $config;

  // Todo - this should have entity headers where appropriate, too.

  // Take $graph_array and print day,week,month,year graps in overlib, hovered over graph

  $original_from = $graph_array['from'];

  $graph = generate_graph_tag($graph_array);
  /*
  $box_args = array('body-style' => 'width: 850px;');
  if (strlen($graph_array['popup_title']))
  {
    $box_args['title'] = $graph_array['popup_title'];
  }
  $content = generate_box_open($box_args);
  */
  $content = "<div class=entity-title><h4>".$graph_array['popup_title']."</h4></div>";
  $content .= '<div style="width: 850px">';
  $graph_array['legend']   = "yes";
  $graph_array['height']   = "100";
  $graph_array['width']    = "340";
  $graph_array['from']     = $config['time']['day'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['time']['week'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['time']['month'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['time']['year'];
  $content .= generate_graph_tag($graph_array);
  $content .= "</div>";
  //$content .= generate_box_close();

  $graph_array['from'] = $original_from;

  $graph_array['link'] = generate_url($graph_array, array('page' => 'graphs', 'height' => NULL, 'width' => NULL, 'bg' => NULL));

  return overlib_link($graph_array['link'], $graph, $content, NULL);
}

// output the popup generated in generate_graph_popup();
// TESTME needs unit testing
// DOCME needs phpdoc block
function print_graph_popup($graph_array)
{
  echo(generate_graph_popup($graph_array));
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function permissions_cache($user_id)
{
  $permissions = array();

  foreach (dbFetchRows("SELECT * FROM `entity_permissions` WHERE `user_id` = ?", array($user_id)) as $entity)
  {
    switch($entity['entity_type'])
    {
      case "group": // this is a group, so expand it's members into an array
        $group = get_group_by_id($entity['entity_id']);
        foreach(get_group_entities($entity['entity_id']) as $group_entity)
        {
          $permissions[$group['entity_type']][$group_entity] = TRUE;
        }
        break;
      default:
        $permissions[$entity['entity_type']][$entity['entity_id']] = TRUE;
        break;
    }
  }

  // Alerts
  $alert = array();
  foreach (dbFetchRows('SELECT `alert_table_id`, `device_id`, `entity_id`, `entity_type` FROM `alert_table`') as $alert_table_entry)
  {
    //r($alert_table_entry);
    if (is_entity_permitted($alert_table_entry['entity_id'], $alert_table_entry['entity_type'], $alert_table_entry['device_id'], $permissions))
    {
      $alert[$alert_table_entry['alert_table_id']] = TRUE;
    }
  }
  if (count($alert))
  {
    $permissions['alert'] = $alert;
  }

  return $permissions;
}

/**
 * Store cached device/port/etc permitted IDs into $_SESSION['cache']
 *
 * IDs collected in html/includes/cache-data.inc.php
 * This function used mostly in print_search() or print_form(), see html/includes/print/search.inc.php
 * Cached IDs from $_SESSION used in ajax forms by generate_query_permitted()
 *
 * @return null
 */
function permissions_cache_session()
{
  // Store device IDs in SESSION var for use to check permissions with ajax queries
  foreach (array('permitted', 'disabled', 'ignored') as $key)
  {
    $_SESSION['cache']['devices'][$key] = $GLOBALS['cache']['devices'][$key];
  }

  // Store port IDs in SESSION var for use to check permissions with ajax queries
  foreach (array('permitted', 'deleted', 'errored', 'ignored', 'poll_disabled', 'device_disabled', 'device_ignored') as $key)
  {
    $_SESSION['cache']['ports'][$key] = $GLOBALS['cache']['ports'][$key];
  }
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function bill_permitted($bill_id)
{
  global $permissions;

  if ($_SESSION['userlevel'] >= "5")
  {
    $allowed = TRUE;
  } elseif ($permissions['bill'][$bill_id]) {
    $allowed = TRUE;
  } else {
    $allowed = FALSE;
  }

  return $allowed;
}



// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
/**
 * Returns a device_id when given an entity_id and an entity_type. Returns FALSE if the device isn't found.
 *
 * @param $entity_id
 * @param $entity_type
 *
 * @return bool|integer
 */
function get_device_id_by_entity_id($entity_id, $entity_type)
{

  // $entity = get_entity_by_id_cache($entity_type, $entity_id);
  $translate = entity_type_translate_array($entity_type);

  if (is_numeric($entity_id) && $entity_type)
  {
    $device_id = dbFetchCell('SELECT `device_id` FROM `' .   $translate['table']. '` WHERE `' . $translate['id_field'] . '` = ?', array($entity_id));
  }
  if (is_numeric($device_id))
  {
    return $device_id;
  } else {
    return FALSE;
  }
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function port_permitted($port_id, $device_id = NULL)
{
  return is_entity_permitted($port_id, 'port', $device_id);
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function port_permitted_array(&$ports)
{
  // Strip out the ports the user isn't allowed to see, if they don't have global rights
  if ($_SESSION['userlevel'] < '7')
  {
    foreach ($ports as $key => $port)
    {
      if (!port_permitted($port['port_id'], $port['device_id']))
      {
        unset($ports[$key]);
      }
    }
  }
}

function entity_permitted_array(&$entities, $entity_type)
{

  $entity_type_data = entity_type_translate_array($entity_type);

  // Strip out the entities the user isn't allowed to see, if they don't have global view rights
  if ($_SESSION['userlevel'] < '7')
  {
    foreach ($entities as $key => $entity)
    {
      if (!is_entity_permitted($entity[$entity_type_data['id_field']], $entity_type, $entity['device_id']))
      {
        unset($entities[$key]);
      }
    }
  }
}


// TESTME needs unit testing
// DOCME needs phpdoc block
function application_permitted($app_id, $device_id = NULL)
{
  global $permissions;

  if (is_numeric($app_id))
  {
    if (!$device_id) { $device_id = get_device_id_by_app_id ($app_id); }
    if ($_SESSION['userlevel'] >= "5") {
      $allowed = TRUE;
    } elseif (device_permitted($device_id)) {
      $allowed = TRUE;
    } elseif ($permissions['application'][$app_id]) {
      $allowed = TRUE;
    } else {
      $allowed = FALSE;
    }
  } else {
    $allowed = FALSE;
  }

  return $allowed;
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function device_permitted($device_id)
{
  global $permissions;

  if ($_SESSION['userlevel'] >= "5")
  {
    $allowed = true;
  } elseif ($permissions['device'][$device_id]) {
    $allowed = true;
  } else {
    $allowed = false;
  }

  return $allowed;
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function print_graph_tag($args)
{
  echo(generate_graph_tag($args));
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_graph_tag($args)
{
  if (empty($args)) { return ''; } // Quick return if passed empty array

  if (is_array($args['style']))
  {
    $style = implode("; ", $args['style']) . ';';
    unset($args['style']);
  }

  // Detect allowed screen ratio for current browser
  $ua_info = detect_browser();
  $zoom = $ua_info['screen_ratio'];

  if ($zoom >= 2)
  {
    // Add img srcset for HiDPI screens
    $args_x = $args;
    $args_x['zoom'] = $zoom;
    $srcset = ' srcset="'.generate_graph_url($args_x).' '.$args_x['zoom'].'x"';
  } else{
    $srcset = '';
  }

  return '<img src="'.generate_graph_url($args).'"'.$srcset.' style="max-width: 100%; width: auto; '.$style.'" alt="" />';
}

function generate_graph_url($args)
{

  foreach ($args as $key => $arg)
  {
    if (is_array($arg)) { $arg = var_encode($arg); } // Encode arrays
    $urlargs[] = $key."=".$arg;
  }

  $url = 'graph.php?' . implode('&amp;',$urlargs);

  if (is_cli())
  {
    if ($GLOBALS['config']['web_url'] == 'http://localhost:80/')
    {
      // override default web_url by http://localhost/
      $url = 'http://'.get_localhost().'/'.$url;
    } else {
      $url = $GLOBALS['config']['web_url'] . $url;
    }
  }

  return $url;

}

// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_graph_js_state($args)
{
  // we are going to assume we know roughly what the graph url looks like here.
  // TODO: Add sensible defaults
  $from   = (is_numeric($args['from'])   ? $args['from']   : 0);
  $to     = (is_numeric($args['to'])     ? $args['to']     : 0);
  $width  = (is_numeric($args['width'])  ? $args['width']  : 0);
  $height = (is_numeric($args['height']) ? $args['height'] : 0);
  $legend = str_replace("'", "", $args['legend']);

  $state = <<<STATE
<script type="text/javascript">
document.graphFrom = $from;
document.graphTo = $to;
document.graphWidth = $width;
document.graphHeight = $height;
document.graphLegend = '$legend';
</script>
STATE;

  return $state;
}

/**
 * Generate Percentage Bar
 *
 * This function generates an Observium percentage bar from a supplied array of arguments.
 * It is possible to draw a bar that does not work at all,
 * So care should be taken to make sure values are valid.
 *
 * @param array $args
 * @return string
 */

// TESTME needs unit testing
function percentage_bar($args)
{
  if (strlen($args['bg']))     { $style .= 'background-color:'.$args['bg'].';'; }
  if (strlen($args['border'])) { $style .= 'border-color:'.$args['border'].';'; }
  if (strlen($args['width']))  { $style .= 'width:'.$args['width'].';'; }
  if (strlen($args['text_c'])) { $style_b .= 'color:'.$args['text_c'].';'; }

  $total = '0';
  $output = '<div class="percbar" style="'.$style.'">';
  foreach ($args['bars'] as $bar)
  {
    $output .= '<div class="bar" style="width:'.$bar['percent'].'%; background-color:'.$bar['colour'].';"></div>';
    $total += $bar['percent'];
  }
  $left = '100' - $total;
  if ($left > 0) { $output .= '<div class="bar" style="width:'.$left.'%;"></div>'; }

  if ($left >= 0) { $output .= '<div class="bar-text" style="margin-left: -100px; margin-top: 0px; float: right; text-align: right; '.$style_b.'">'.$args['text'].'</div>'; }

  foreach ($args['bars'] as $bar)
  {
    $output .= '<div class="bar-text" style="width:'.$bar['percent'].'%; max-width:'.$bar['percent'].'%; padding-left: 4px;">'.$bar['text'].'</div>';
  }
#  if ($left > '0') { $output .= '<div class="bar-text" style="margin-left: -100px; margin-top: -16px; float: right; text-align: right; '.$style_b.'">'.$args['text'].'</div>'; }

  $output .= '</div>';

  return $output;
}

// Legacy function
// DO NOT USE THIS. Please replace instances of it with percentage_bar from above.
// TESTME needs unit testing
// DOCME needs phpdoc block
function print_percentage_bar($width, $height, $percent, $left_text, $left_colour, $left_background, $right_text, $right_colour, $right_background)
{

  if ($percent > "100") { $size_percent = "100"; } else { $size_percent = $percent; }

  $percentage_bar['border']  = "#".$left_background;
  $percentage_bar['bg']      = "#".$right_background;
  $percentage_bar['width']   = $width;
  $percentage_bar['text']    = $right_text;
  $percentage_bar['bars'][0] = array('percent' => $size_percent, 'colour' => '#'.$left_background, 'text' => $left_text);

  $output = percentage_bar($percentage_bar);

  return $output;
}

// DOCME needs phpdoc block
function print_optionbar_start($height = 0, $width = 0, $marginbottom = 5)
{
   echo(PHP_EOL . '<div class="box box-solid well-shaded">' . PHP_EOL);
}

// DOCME needs phpdoc block
function print_optionbar_end()
{
  echo(PHP_EOL . '  </div>' . PHP_EOL);
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function geteventicon($message)
{
  if ($message == "Device status changed to Down") { $icon = "server_connect.png"; }
  if ($message == "Device status changed to Up") { $icon = "server_go.png"; }
  if ($message == "Interface went down" || $message == "Interface changed state to Down") { $icon = "if-disconnect.png"; }
  if ($message == "Interface went up" || $message == "Interface changed state to Up") { $icon = "if-connect.png"; }
  if ($message == "Interface disabled") { $icon = "if-disable.png"; }
  if ($message == "Interface enabled") { $icon = "if-enable.png"; }
  if (isset($icon)) { return $icon; } else { return false; }
}

function get_entity_icon($entity)
{

  $config['entities'][$entry['type']]['icon'];

}

// TESTME needs unit testing
// DOCME needs phpdoc block
function overlibprint($text)
{
  return "onmouseover=\"return overlib('" . $text . "');\" onmouseout=\"return nd();\"";
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function device_link_class($device)
{
  if (isset($device['status']) && $device['status'] == '0') { $class = "red"; } else { $class = ""; }
  if (isset($device['ignore']) && $device['ignore'] == '1')
  {
     $class = "grey";
     if (isset($device['status']) && $device['status'] == '1') { $class = "green"; }
  }
  if (isset($device['disabled']) && $device['disabled'] == '1') { $class = "grey"; }

  return $class;
}

/**
 * Return cached locations list
 *
 * If filter used, return locations avialable only for specified params.
 * Without filter return all avialable locations (cached)
 *
 * @param array $filter
 * @return array
 */
// TESTME needs unit testing
function get_locations($filter = array())
{
  foreach ($filter as $var => $value)
  {
    switch ($var)
    {
      case 'location_lat':
      case 'location_lon':
      case 'location_country':
      case 'location_state':
      case 'location_county':
      case 'location_city':
        // Check geo params only when GEO enabled globally
        if (!$GLOBALS['config']['geocoding']['enable']) { break; }
      case 'location':
        $where_array[$var] = generate_query_values($value, $var);
        break;
    }
  }

  if (count($where_array))
  {
    // Return only founded locations
    $where = implode('', $where_array) . $GLOBALS['cache']['where']['devices_permitted'];
    $locations = dbFetchColumn("SELECT DISTINCT `location` FROM `devices_locations` WHERE 1 $where;");
  } else {
    $locations = array();
    foreach ($GLOBALS['cache']['device_locations'] as $location => $count)
    {
      $locations[] = $location;
    }
  }
  sort($locations);

  return $locations;
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function foldersize($path)
{
  $total_size = 0;
  $files = scandir($path);
  $total_files = 0;

  foreach ($files as $t)
  {
    if (is_dir(rtrim($path, '/') . '/' . $t))
    {
      if ($t<>"." && $t<>"..")
      {
        $size = foldersize(rtrim($path, '/') . '/' . $t);
        $total_size += $size;
      }
    } else {
      $size = filesize(rtrim($path, '/') . '/' . $t);
      $total_size += $size;
      $total_files++;
    }
  }

  return array($total_size, $total_files);
}

// return the filename of the device RANCID config file
// TESTME needs unit testing
// DOCME needs phpdoc block
function get_rancid_filename($hostname, $rdebug = FALSE)
{
  global $config;

  $hostnames = array($hostname);

  if ($rdebug) { echo("Hostname: $hostname<br />"); }

  // Also check non-FQDN hostname.
  list($shortname) = explode('.', $hostname);

  if ($rdebug) { echo("Short hostname: $shortname<br />"); }

  if ($shortname != $hostname)
  {
    $hostnames[] = $shortname;
    if ($rdebug) { echo("Hostname different from short hostname, looking for both<br />"); }
  }

  // Addition of a domain suffix for non-FQDN device names.
  if (isset($config['rancid_suffix']) && $config['rancid_suffix'] !== '')
  {
    $hostnames[] = $hostname . '.' . trim($config['rancid_suffix'], ' .');
    if ($rdebug) { echo("RANCID suffix configured, also looking for " . $hostname . '.' . trim($config['rancid_suffix'], ' .') . "<br />"); }
  }

  foreach ($config['rancid_configs'] as $config_path)
  {
    if ($config_path[strlen($config_path)-1] != '/') { $config_path .= '/'; }
    if ($rdebug) { echo("Looking in configured directory: <b>$config_path</b><br />"); }

    foreach ($hostnames as $host)
    {
      if (is_file($config_path . $host))
      {
        if ($rdebug) { echo("File <b>" . $config_path . $host . "</b> found.<br />"); }
        return $config_path . $host;
      } else {
        if ($rdebug) { echo("File <b>" . $config_path . $host . "</b> not found.<br />"); }
      }
    }
  }

  return FALSE;
}

// return the filename of the device NFSEN rrd file
// TESTME needs unit testing
// DOCME needs phpdoc block
function get_nfsen_filename($hostname)
{
  global $config;

  $nfsen_rrds = (is_array($config['nfsen_rrds']) ? $config['nfsen_rrds'] : array($config['nfsen_rrds']));
  foreach ($nfsen_rrds as $nfsen_rrd)
  {
    if ($nfsen_rrd[strlen($nfsen_rrd)-1] != '/') { $nfsen_rrd .= '/'; }
    $basefilename_underscored = preg_replace('/\./', $config['nfsen_split_char'], $hostname);
    if ($config['nfsen_suffix'])
    {
      $nfsen_filename = (strstr($basefilename_underscored, $config['nfsen_suffix'], TRUE));
    } else {
      $nfsen_filename = $basefilename_underscored;
    }
    $nfsen_rrd_file = $nfsen_rrd . $nfsen_filename . '.rrd';
    if (is_file($nfsen_rrd_file))
    {
      return $nfsen_rrd_file;
    }
  }

  return FALSE;
}

// Note, by default text NOT escaped.
// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_ap_link($args, $text = NULL, $type = NULL, $escape = FALSE)
{
  global $config;

  humanize_port($args);
  if (!$text) { $text = rewrite_ifname($args['port_label'], !$escape); } // Negative escape flag for exclude double escape
  if ($type) { $args['graph_type'] = $type; }
  if (!isset($args['graph_type'])) { $args['graph_type'] = 'port_bits'; }

  if (!isset($args['hostname'])) { $args = array_merge($args, device_by_id_cache($args['device_id'])); }

  $content = "<div class=entity-title>".$args['text']." - " . rewrite_ifname($args['port_label'], !$escape) . "</div>";
  if ($args['ifAlias']) { $content .= $args['ifAlias']."<br />"; }
  $content .= "<div style=\'width: 850px\'>";
  $graph_array['type']     = $args['graph_type'];
  $graph_array['legend']   = "yes";
  $graph_array['height']   = "100";
  $graph_array['width']    = "340";
  $graph_array['to']           = $config['time']['now'];
  $graph_array['from']     = $config['time']['day'];
  $graph_array['id']       = $args['accesspoint_id'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['time']['week'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['time']['month'];
  $content .= generate_graph_tag($graph_array);
  $graph_array['from']     = $config['time']['year'];
  $content .= generate_graph_tag($graph_array);
  $content .= "</div>";

  $url = generate_ap_url($args);
  if (port_permitted($args['interface_id'], $args['device_id']))
  {
    return overlib_link($url, $text, $content, $class, $escape);
  } else {
    return rewrite_ifname($text);
  }
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_ap_url($ap, $vars=array())
{
  return generate_url(array('page' => 'device', 'device' => $ap['device_id'], 'tab' => 'accesspoint', 'ap' => $ap['accesspoint_id']), $vars);
}

/**
 * Generate SQL WHERE string with check permissions and ignores for device_id, port_id and other
 *
 * Note, this function uses comparison operator IN. Max number of values in the IN list
 * is limited by the 'max_allowed_packet' option (default: 1048576)
 *
 * Usage examples:
 *  generate_query_permitted()
 *   ' AND `device_id` IN (1,4,8,33) AND `device_id` NOT IN (66) AND (`device_id` != '' AND `device_id` IS NOT NULL) '
 *  generate_query_permitted(array('device'), array('device_table' => 'D'))
 *   ' AND `D`.`device_id` IN (1,4,8,33) AND `D`.`device_id` NOT IN (66) AND (`D`.`device_id` != '' AND `D`.`device_id` IS NOT NULL) '
 *  generate_query_permitted(array('device', 'port'), array('port_table' => 'I')) ==
 *   ' AND `device_id` IN (1,4,8,33) AND `device_id` NOT IN (66) AND (`device_id` != '' AND `device_id` IS NOT NULL)
 *     AND `I`.`port_id` IN (1,4,8,33) AND `I`.`port_id` NOT IN (66) AND (`I`.`port_id` != '' AND `I`.`port_id` IS NOT NULL) '
 *  generate_query_permitted(array('device', 'port'), array('port_table' => 'I', 'hide_ignored' => TRUE))
 *    This additionaly exclude all ignored devices and ports
 *
 * @uses html/includes/cache-data.inc.php
 * @global integer $_SESSION['userlevel']
 * @global boolean $GLOBALS['config']['web_show_disabled']
 * @global array $GLOBALS['permissions']
 * @global array $GLOBALS['cache']['devices']
 * @global array $GLOBALS['cache']['ports']
 * @global string $GLOBALS['vars']['page']
 * @param array|string $type_array Array with permission types, currently allowed 'devices', 'ports'
 * @param array $options Options for each permission type: device_table, port_table, hide_ignored, hide_disabled
 * @return string
 */
// TESTME needs unit testing
function generate_query_permitted($type_array = array('device'), $options = array())
{
  if (!is_array($type_array)) { $type_array = array($type_array); }
  $user_limited = ($_SESSION['userlevel'] < 5 ? TRUE : FALSE);
  $page = $GLOBALS['vars']['page'];

  // If device IDs stored in SESSION use it (used in ajax)
  //if (!isset($GLOBALS['cache']['devices']) && isset($_SESSION['cache']['devices']))
  //{
  //  $GLOBALS['cache']['devices'] = $_SESSION['cache']['devices'];
  //}

  if (!isset($GLOBALS['permissions']))
  {
    // Note, this function must used after load permissions list!
    print_error("Function ".__FUNCTION__."() on page '$page' called before include cache-data.inc.php or something wrong with caching permissions. Please report this to developers!");
  }
  // Use option hide_disabled if passed or use config
  $options['hide_disabled'] = (isset($options['hide_disabled']) ? (bool)$options['hide_disabled'] : !$GLOBALS['config']['web_show_disabled']);

  //$query_permitted = '';

  foreach ($type_array as $type)
  {
    switch ($type)
    {
      // Devices permission query
      case 'device':
      case 'devices':
        $column = '`device_id`';
        $query_permitted = array();
        if (isset($options['device_table'])) { $column = '`'.$options['device_table'].'`.'.$column; }

        // Show only permitted devices
        if ($user_limited)
        {
          if (count($GLOBALS['permissions']['device']))
          {
            $query_permitted[] = " $column IN (".
                                 implode(',', array_keys($GLOBALS['permissions']['device'])).
                                 ')';

          } else {
            // Exclude all entries, because there is no permitted devices
            $query_permitted[] = ' 0';
          }
        }

        // Also don't show ignored and disabled devices (except on 'device' and 'devices' pages)
        $devices_excluded = array();
        if (strpos($page, 'device') !== 0)
        {
          if ($options['hide_ignored'] && count($GLOBALS['cache']['devices']['ignored']))
          {
            $devices_excluded = array_merge($devices_excluded, $GLOBALS['cache']['devices']['ignored']);
          }
          if ($options['hide_disabled'] && count($GLOBALS['cache']['devices']['disabled']))
          {
            $devices_excluded = array_merge($devices_excluded, $GLOBALS['cache']['devices']['disabled']);
          }
        }
        if (count($devices_excluded))
        {
          // Set query with excluded devices
          $query_permitted[] = " $column NOT IN (".
                               implode(',', array_unique($devices_excluded)).
                               ')';
        }

        // At the end excluded entries with empty/null device_id (wrong entries)
        $query_permitted[] = " ($column != '' AND $column IS NOT NULL)";
        $query_part[] = implode(" AND ", $query_permitted);
        unset($query_permitted);
        break;
      // Ports permission query
      case 'port':
      case 'ports':
        $column = '`port_id`';
        if (isset($options['port_table'])) { $column = '`'.$options['port_table'].'`.'.$column; }

        // If port IDs stored in SESSION use it (used in ajax)
        //if (!isset($GLOBALS['cache']['ports']) && isset($_SESSION['cache']['ports']))
        //{
        //  $GLOBALS['cache']['ports'] = $_SESSION['cache']['ports'];
        //}

        // Show only permitted ports
        if ($user_limited)
        {
          if (count($GLOBALS['permissions']['port']))
          {
            $query_permitted[] = " $column IN (" .
                                 implode(',', array_keys($GLOBALS['permissions']['port'])) .
                                 ')';
          } else {
            // Exclude all entries, because there is no permitted ports
            $query_permitted[] = '0';
          }
        }

        $ports_excluded = array();
        // Don't show ports with disabled polling.
        if (count($GLOBALS['cache']['ports']['poll_disabled']))
        {
          $ports_excluded = array_merge($ports_excluded, $GLOBALS['cache']['ports']['poll_disabled']);
          //foreach ($GLOBALS['cache']['ports']['poll_disabled'] as $entry)
          //{
          //  $ports_excluded[] = $entry;
          //}
          //$ports_excluded = array_unique($ports_excluded);
        }
        // Don't show deleted ports (except on 'deleted-ports' page)
        if ($page != 'deleted-ports' && count($GLOBALS['cache']['ports']['deleted']))
        {
          $ports_excluded = array_merge($ports_excluded, $GLOBALS['cache']['ports']['deleted']);
          //foreach ($GLOBALS['cache']['ports']['deleted'] as $entry)
          //{
          //  $ports_excluded[] = $entry;
          //}
          //$ports_excluded = array_unique($ports_excluded);
        }
        if ($page != 'device' && !in_array('device', $type_array))
        {
          // Don't show ports for disabled devices (except on 'device' page or if 'device' permissions already queried)
          if ($options['hide_disabled'] && !$user_limited && count($GLOBALS['cache']['ports']['device_disabled']))
          {
            $ports_excluded = array_merge($ports_excluded, $GLOBALS['cache']['ports']['device_disabled']);
            //foreach ($GLOBALS['cache']['ports']['device_disabled'] as $entry)
            //{
            //  $ports_excluded[] = $entry;
            //}
            //$ports_excluded = array_unique($ports_excluded);
          }
          // Don't show ports for ignored devices (except on 'device' page)
          if ($options['hide_ignored'] && count($GLOBALS['cache']['ports']['device_ignored']))
          {
            $ports_excluded = array_merge($ports_excluded, $GLOBALS['cache']['ports']['device_ignored']);
            //foreach ($GLOBALS['cache']['ports']['device_ignored'] as $entry)
            //{
            //  $ports_excluded[] = $entry;
            //}
            //$ports_excluded = array_unique($ports_excluded);
          }
        }
        // Don't show ignored ports (only on some pages!)
        if (($page == 'overview' || $options['hide_ignored']) && count($GLOBALS['cache']['ports']['ignored']))
        {
          $ports_excluded = array_merge($ports_excluded, $GLOBALS['cache']['ports']['ignored']);
          //foreach ($GLOBALS['cache']['ports']['ignored'] as $entry)
          //{
          //  $ports_excluded[] = $entry;
          //}
          //$ports_excluded = array_unique($ports_excluded);
        }
        unset($entry);
        if (count($ports_excluded))
        {
          // Set query with excluded ports
          $query_permitted[] = $column . " NOT IN (".
                             implode(',', array_unique($ports_excluded)).
                             ')';

        }

        // At the end excluded entries with empty/null port_id (wrong entries)
        $query_permitted[] = "($column != '' AND $column IS NOT NULL)";

        $query_part[] = implode(" AND ", $query_permitted);
        unset($query_permitted);

        break;
      case 'sensor':
      case 'sensors':
        // For sensors
        // FIXME -- this is easily generifyable, just use translate_table_array()

        $column = '`sensor_id`';

        if (isset($options['sensor_table'])) { $column = '`'.$options['sensor_table'].'`.'.$column; }

        // If IDs stored in SESSION use it (used in ajax)
        //if (!isset($GLOBALS['cache']['sensors']) && isset($_SESSION['cache']['sensors']))
        //{
        //  $GLOBALS['cache']['sensors'] = $_SESSION['cache']['sensors'];
        //}

        // Show only permitted entities
        if ($user_limited)
        {
          if (count($GLOBALS['permissions']['sensor']))
          {
            $query_permitted .= " $column IN (";
            $query_permitted .= implode(',', array_keys($GLOBALS['permissions']['sensor']));
            $query_permitted .= ')';
          } else {
            // Exclude all entries, because there are no permitted entities
            $query_permitted .= '0';
          }
          $query_part[] = $query_permitted;
          unset($query_permitted);
        }

        break;

      case 'alert':
      case 'alerts':
        // For generic alert

        $column = '`alert_table_id`';

        // Show only permitted entities
        if ($user_limited)
        {
          if (count($GLOBALS['permissions']['alert']))
          {
            $query_permitted .= " $column IN (";
            $query_permitted .= implode(',', array_keys($GLOBALS['permissions']['alert']));
            $query_permitted .= ')';
          } else {
            // Exclude all entries, because there are no permitted entities
            $query_permitted .= '0';
          }
          $query_part[] = $query_permitted;
          unset($query_permitted);
        }

        break;
      case 'bill':
      case 'bills':
        // For bills
        break;
    }
  }
  if (count($query_part))
  { //r($query_part);
    $query_permitted = " AND ((".implode(") OR (", $query_part)."))";
  }

  $query_permitted .= ' ';

  //r($query_permitted);

  return $query_permitted;
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function get_user_prefs($user_id)
{
  $prefs = array();
  foreach (dbFetchRows("SELECT * FROM `users_prefs` WHERE `user_id` = ?", array($user_id)) as $entry)
  {
    $prefs[$entry['pref']] = $entry;
  }
  return $prefs;
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function get_user_pref($user_id, $pref)
{
  if ($entry = dbFetchRow("SELECT `value` FROM `users_prefs` WHERE `user_id` = ? AND `pref` = ?", array($user_id, $pref)))
  {
    return $entry['value'];
  }
  else
  {
    return NULL;
  }
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function set_user_pref($user_id, $pref, $value)
{
  if (dbFetchCell("SELECT COUNT(*) FROM `users_prefs` WHERE `user_id` = ? AND `pref` = ?", array($user_id, $pref)))
  {
    $id = dbUpdate(array('value' => $value), 'users_prefs', '`user_id` = ? AND `pref` = ?', array($user_id, $pref));
  }
  else
  {
    $id = dbInsert(array('user_id' => $user_id, 'pref' => $pref, 'value' => $value), 'users_prefs');
  }
  return $id;
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function del_user_pref($user_id, $pref)
{
  return dbDelete('users_prefs', "`user_id` = ? AND `pref` = ?", array($user_id, $pref));
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function get_smokeping_files($rdebug = 0)
{
  global $config;

  $smokeping_files = array();

  if ($rdebug) { echo('- Recursing through ' . $config['smokeping']['dir'] . '<br />'); }

  if (is_dir($config['smokeping']['dir']))
  {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($config['smokeping']['dir'])) as $file)
    {
      if (basename($file) != "." && basename($file) != ".." && strstr($file, ".rrd"))
      {
        if ($rdebug) { echo('- Found file ending in ".rrd": ' . basename($file) . '<br />'); }

        if (strstr($file, "~"))
        {
          list($target,$slave) = explode("~", basename($file,".rrd"));
          if ($rdebug) { echo('- Determined to be a slave file for target <b>' . $target . '</b><br />'); }
          $target = str_replace($config['smokeping']['split_char'], ".", $target);
          if ($config['smokeping']['suffix']) { $target = $target.$config['smokeping']['suffix']; if ($rdebug) { echo('- Suffix is configured, target is now <b>' . $target . '</b><br />'); } }
          $smokeping_files['incoming'][$target][$slave] = $file;
          $smokeping_files['outgoing'][$slave][$target] = $file;
        } else {
          $target = basename($file,".rrd");
          if ($rdebug) { echo('- Determined to be a local file, for target <b>' . $target . '</b><br />'); }
          $target = str_replace($config['smokeping']['split_char'], ".", $target);
          if ($rdebug) { echo('- After replacing configured split_char ' . $config['smokeping']['split_char'] . ' by . target is <b>' . $target . '</b><br />'); }
          if ($config['smokeping']['suffix']) { $target = $target.$config['smokeping']['suffix']; if ($rdebug) { echo('- Suffix is configured, target is now <b>' . $target . '</b><br />'); } }
          $smokeping_files['incoming'][$target][$config['own_hostname']] = $file;
          $smokeping_files['outgoing'][$config['own_hostname']][$target] = $file;
        }
      }
    }
  } else {
    if ($rdebug) { echo("- Smokeping RRD directory not found: " . $config['smokeping']['dir']); }
  }

  return $smokeping_files;
}

/**
 * Darkens or lightens a colour
 * Found via http://codepad.org/MTGLWVd0
 *
 * First argument is the colour in hex, second argument is how dark it should be 1=same, 2=50%
 *
 * @return string
 * @param string $rgb
 * @param int $darker
 */

function darken_color($rgb, $darker=2)
{
  if (strpos($rgb, '#') !== FALSE)
  {
    $hash = '#';
    $rgb  = str_replace('#', '', $rgb);
  } else {
    $hash = '';
  }
  $len  = strlen($rgb);
  if ($len == 6) {} // Passed RGB
  else if ($len == 8)
  {
    // Passed RGBA, remove alpha channel
    $rgb = substr($rgb, 0, 6);
  } else {
    $rgb = FALSE;
  }

  if ($rgb === FALSE) { return $hash.'000000'; }

  $darker = ($darker > 1) ? $darker : 1;

  list($R16, $G16, $B16) = str_split($rgb, 2);

  $R = sprintf("%02X", floor(hexdec($R16) / $darker));
  $G = sprintf("%02X", floor(hexdec($G16) / $darker));
  $B = sprintf("%02X", floor(hexdec($B16) / $darker));

  return $hash.$R.$G.$B;
}

// Originally from http://stackoverflow.com/questions/6054033/pretty-printing-json-with-php/21162086#21162086

// FIXME : This is only required for PHP < 5.4, remove this when our requirements are >= 5.4

if (!defined('JSON_UNESCAPED_SLASHES')) { define('JSON_UNESCAPED_SLASHES', 64); }
if (!defined('JSON_PRETTY_PRINT'))      { define('JSON_PRETTY_PRINT', 128); }
if (!defined('JSON_UNESCAPED_UNICODE')) { define('JSON_UNESCAPED_UNICODE', 256); }

function json_output($status, $message)
{
  header("Content-type: application/json; charset=utf-8");
  echo json_encode(array("status" => $status, "message" => $message));

  exit();
}

function _json_encode($data, $options = 448)
{
  if (version_compare(PHP_VERSION, '5.4', '>=')) {
    return json_encode($data, $options);
  }
  else {
    return _json_format(json_encode($data), $options);
  }
}

function _json_format($json, $options = 448)
{
  $prettyPrint = (bool) ($options & JSON_PRETTY_PRINT);
  $unescapeUnicode = (bool) ($options & JSON_UNESCAPED_UNICODE);
  $unescapeSlashes = (bool) ($options & JSON_UNESCAPED_SLASHES);
  if (!$prettyPrint && !$unescapeUnicode && !$unescapeSlashes)
  {
    return $json;
  }
  $result = '';
  $pos = 0;
  $strLen = strlen($json);
  $indentStr = ' ';
  $newLine = "\n";
  $outOfQuotes = true;
  $buffer = '';
  $noescape = true;
  for ($i = 0; $i < $strLen; $i++)
  {
    // Grab the next character in the string
    $char = substr($json, $i, 1);
    // Are we inside a quoted string?
    if ('"' === $char && $noescape)
    {
      $outOfQuotes = !$outOfQuotes;
    }
    if (!$outOfQuotes)
    {
      $buffer .= $char;
      $noescape = '\\' === $char ? !$noescape : true;
      continue;
    }
    elseif ('' !== $buffer)
    {
      if ($unescapeSlashes)
      {
        $buffer = str_replace('\\/', '/', $buffer);
      }
      if ($unescapeUnicode && function_exists('mb_convert_encoding'))
      {
        // http://stackoverflow.com/questions/2934563/how-to-decode-unicode-escape-sequences-like-u00ed-to-proper-utf-8-encoded-cha
        $buffer = preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
          function ($match)
          {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
          }, $buffer);
      }
      $result .= $buffer . $char;
      $buffer = '';
      continue;
    }
    elseif(false !== strpos(" \t\r\n", $char))
    {
      continue;
    }
    if (':' === $char)
    {
      // Add a space after the : character
      $char .= ' ';
    }
    elseif (('}' === $char || ']' === $char))
    {
      $pos--;
      $prevChar = substr($json, $i - 1, 1);
      if ('{' !== $prevChar && '[' !== $prevChar)
      {
        // If this character is the end of an element,
        // output a new line and indent the next line
        $result .= $newLine;
        for ($j = 0; $j < $pos; $j++)
        {
          $result .= $indentStr;
        }
      }
      else
      {
        // Collapse empty {} and []
        $result = rtrim($result) . "\n\n" . $indentStr;
      }
    }
    $result .= $char;
    // If the last character was the beginning of an element,
    // output a new line and indent the next line
    if (',' === $char || '{' === $char || '[' === $char)
    {
      $result .= $newLine;
      if ('{' === $char || '[' === $char)
      {
        $pos++;
      }
      for ($j = 0; $j < $pos; $j++)
      {
        $result .= $indentStr;
      }
    }
  }
  // If buffer not empty after formating we have an unclosed quote
  if (strlen($buffer) > 0)
  {
    //json is incorrectly formatted
    $result = false;
  }
  return $result;
}


// EOF
