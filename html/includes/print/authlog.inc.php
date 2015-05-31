<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage web
 * @copyright  (C) 2006-2014 Adam Armstrong
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
    $string = "<table class=\"table table-bordered table-striped table-hover table-condensed table-rounded\">
  <thead>
    <tr>
      <th style=\"width: 200px;\">Date</th>
      <th style=\"width: 200px;\">User</th>
      <th style=\"width: 200px;\">From</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>";

    foreach ($authlog['entries'] as $entry)
    {
      if (strstr(strtolower($entry['result']), 'fail', true)) { $class = " class=\"error\""; } else { $class = ""; }
      $string .= '
      <tr'.$class.'>
        <td>'.$entry['datetime'].'</td>
        <td>'.$entry['user'].'</td>
        <td>'.$entry['address'].'</td>
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
