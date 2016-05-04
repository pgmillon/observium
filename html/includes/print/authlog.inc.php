<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage web
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

/**
 * Display authentication log.
 *
 * @param array $vars
 * @return none
 *
 */
function print_authlog($vars)
{
  $authlog = get_authlog_array($vars);

  if (!$authlog['count'])
  {
    // There have been no entries returned. Print the warning. Shouldn't happen, how did you get here without auth?!
    print_warning('<h4>No authentication entries found!</h4>');
  } else {
    $string = generate_box_open($vars['header']);
    // Entries have been returned. Print the table.
    $string .= '<table class="'.OBS_CLASS_TABLE_STRIPED_MORE.'">' . PHP_EOL;
    $cols = array(
      //'NONE'     => NULL,
      'date'     => array('Date', 'style="width: 150px;"'),
      'user'     => 'User',
      'from'     => 'From',
      'ua'       => array('User-Agent', 'style="width: 200px;"'),
      'NONE'     => 'Action',
    );
    if ($vars['page'] == 'preferences') { unset($cols['user']); }
    $string .= get_table_header($cols); //, $vars); // Currently sorting is not available
    $string .= '<tbody>' . PHP_EOL;

    foreach ($authlog['entries'] as $entry)
    {
      if (strlen($entry['user_agent']) > 1)
      {
        $entry['detect_browser'] = detect_browser($entry['user_agent']);
        //r($entry['detect_browser']);

        $entry['user_agent'] = '<i class="' . $entry['detect_browser']['icon'] . '"></i>&nbsp;' . $entry['detect_browser']['browser_full'];
        if ($entry['detect_browser']['platform'])
        {
          $entry['user_agent'] .= ' ('.$entry['detect_browser']['platform'].')';
        }
      }
      if (strstr(strtolower($entry['result']), 'fail', true)) { $class = " class=\"error\""; } else { $class = ""; }
      $string .= '
      <tr'.$class.'>
        <td>'.$entry['datetime'].'</td>';
      if (isset($cols['user']))
      {
        $string .= '
        <td>'.escape_html($entry['user']).'</td>';
      }
      $string .= '
        <td>'. ($_SESSION['userlevel'] > 5 ? generate_popup_link('ip', $entry['address']) : preg_replace('/^\d+/', '*', $entry['address'])) . '</td>
        <td>'.$entry['user_agent'].'</td>
        <td>'.$entry['result'].'</td>
      </tr>' . PHP_EOL;
    }

    $string .= '  </tbody>' . PHP_EOL;
    $string .= '</table>';

    $string .= generate_box_close();

    // Add pagination header
    if ($authlog['pagination_html']) { $string = $authlog['pagination_html'] . $string . $authlog['pagination_html']; }

    // Print authlog
    echo $string;
  }
}

// DOCME needs phpdoc block
function get_authlog_array($vars)
{
  $array = array();

  // Short authlog? (no pagination, small out)
  $array['short'] = (isset($vars['short']) && $vars['short']);
  if ($array['short'])
  {
    // For short, always limit to last 10 entries
    $start    = 0;
    $pagesize = 10;
  } else {
    // With pagination? (display page numbers in header)
    $array['pagination'] = (isset($vars['pagination']) && $vars['pagination']);
    pagination($vars, 0, TRUE); // Get default pagesize/pageno
    $array['pageno']   = $vars['pageno'];
    $array['pagesize'] = $vars['pagesize'];
    $start    = $array['pagesize'] * $array['pageno'] - $array['pagesize'];
    $pagesize = $array['pagesize'];
  }

  $query = " FROM `authlog`" . generate_authlog_where($vars);

  $query_count = 'SELECT COUNT(`id`) '.$query;
  $query_updated = 'SELECT MAX(`datetime`) '.$query;

  $where = 
  $query = 'SELECT * '.$query;
  $query .= ' ORDER BY `datetime` DESC ';
  $query .= "LIMIT $start,$pagesize";

  // Query authlog
  $array['entries'] = dbFetchRows($query, $param);

  // Query authlog count
  if ($array['pagination'] && !$array['short'])
  {
    $array['count'] = dbFetchCell($query_count, $param);
    $array['pagination_html'] = pagination($vars, $array['count']);
  } else {
    $array['count'] = count($array['entries']);
  }

  // Query for last timestamp
  $array['updated'] = dbFetchCell($query_updated, $param);

  return $array;
}

function generate_authlog_where($vars)
{
  $sql = '';

  // Build query
  foreach ($vars as $var => $value)
  {
    switch ($var)
    {
      case "user_id":
        if ($value == '') { continue; }
        $value = auth_username_by_id($value);
        //break;
      case "user":
      case "username":
        if ($value == '') { continue; }
        $sql .= generate_query_values($value, 'user');
        break;
      case "address":
      case "ip":
        if ($value == '') { continue; }
        $sql .= generate_query_values($value, 'address', '%LIKE%');
        break;
      case "useragent":
      case "user_agent":
        $sql .= generate_query_values($value, 'user_agent', '%LIKE%');
        break;
      case "result":
      //case "action":
        $sql .= generate_query_values($value, 'result', 'LIKE%');
        break;
    }
  }
  if (strlen($sql))
  {
    $sql = ' WHERE 1' . $sql;
  }
  else if ($_SESSION['userlevel'] != 10)
  {
    // Complete hide for non-priveleged users
    $sql = ' WHERE 0';
  }

  return $sql;
}

// EOF
