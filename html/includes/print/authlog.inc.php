<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage web
 * @copyright  (C) 2006-2015 Adam Armstrong
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
    // Entries have been returned. Print the table.
    $string = '<table class="table table-bordered table-striped table-hover table-condensed table-rounded">' . PHP_EOL;
    $cols = array(
      //'NONE'     => NULL,
      'date'     => array('Date', 'style="width: 200px;"'),
      'user'     => 'User',
      'from'     => 'From',
      'ua'       => array('User-Agent', 'style="width: 200px;"'),
      'NONE'     => 'Action',
    );
    $string .= get_table_header($cols); //, $vars); // Currently sorting is not available
    $string .= '<tbody>' . PHP_EOL;

    foreach ($authlog['entries'] as $entry)
    {
      if (strlen($entry['user_agent']) > 1)
      {
        $entry['detect_browser'] = detect_browser($entry['user_agent'], TRUE);
        $entry['user_agent'] = $entry['detect_browser']['browser'];
        if ($entry['detect_browser']['platform'])
        {
          $entry['user_agent'] .= ' ('.$entry['detect_browser']['platform'].')';
        }
      }
      if (strstr(strtolower($entry['result']), 'fail', true)) { $class = " class=\"error\""; } else { $class = ""; }
      $string .= '
      <tr'.$class.'>
        <td>'.$entry['datetime'].'</td>
        <td>'.escape_html($entry['user']).'</td>
        <td>'.$entry['address'].'</td>
        <td>'.$entry['user_agent'].'</td>
        <td>'.$entry['result'].'</td>
      </tr>' . PHP_EOL;
    }

    $string .= '  </tbody>' . PHP_EOL;
    $string .= '</table>';

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
  // With pagination? (display page numbers in header)
  $array['pagination'] = (isset($vars['pagination']) && $vars['pagination']);
  pagination($vars, 0, TRUE); // Get default pagesize/pageno
  $array['pageno']   = $vars['pageno'];
  $array['pagesize'] = $vars['pagesize'];
  $start    = $array['pagesize'] * $array['pageno'] - $array['pagesize'];
  $pagesize = $array['pagesize'];

  $query = " FROM `authlog`";

  $query_count = 'SELECT COUNT(`id`) '.$query;
  $query_updated = 'SELECT MAX(`datetime`) '.$query;

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

// EOF
