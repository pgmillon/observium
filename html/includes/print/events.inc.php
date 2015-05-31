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
 * Display events.
 *
 * Display pages with device/port/system events on some formats.
 * Examples:
 * print_events() - display last 10 events from all devices
 * print_events(array('pagesize' => 99)) - display last 99 events from all device
 * print_events(array('pagesize' => 10, 'pageno' => 3, 'pagination' => TRUE)) - display 10 events from page 3 with pagination header
 * print_events(array('pagesize' => 10, 'device' = 4)) - display last 10 events for device_id 4
 * print_events(array('short' => TRUE)) - show small block with last events
 *
 * @param array $vars
 * @return none
 *
 */
function print_events($vars)
{
  // Get events array
  $events = get_events_array($vars);

  if (!$events['count'])
  {
    // There have been no entries returned. Print the warning.
    print_warning('<h4>No eventlog entries found!</h4>');
  } else {
    // Entries have been returned. Print the table.
    $list = array('device' => FALSE, 'port' => FALSE);
    if (!isset($vars['device']) || empty($vars['device']) || $vars['page'] == 'eventlog') { $list['device'] = TRUE; }
    if ($events['short'] || !isset($vars['port']) || empty($vars['port'])) { $list['port'] = TRUE; }

    $string = '<table class="table table-bordered table-striped table-hover table-condensed-more">' . PHP_EOL;
    if (!$events['short'])
    {
      $string .= '  <thead>' . PHP_EOL;
      $string .= '    <tr>' . PHP_EOL;
      $string .= '      <th>Date</th>' . PHP_EOL;
      if ($list['device']) { $string .= '      <th>Device</th>' . PHP_EOL; }
      if ($list['port'])   { $string .= '      <th>Entity</th>' . PHP_EOL; }
      $string .= '      <th>Message</th>' . PHP_EOL;
      $string .= '    </tr>' . PHP_EOL;
      $string .= '  </thead>' . PHP_EOL;
    }
    $string   .= '  <tbody>' . PHP_EOL;

    foreach ($events['entries'] as $entry)
    {

      $icon = geteventicon($entry['message']);
      if ($icon) { $icon = '<img src="images/16/' . $icon . '" />'; }

      $string .= '  <tr>' . PHP_EOL;
      if ($events['short'])
      {
        $string .= '    <td class="syslog" style="white-space: nowrap">';
        $timediff = $GLOBALS['config']['time']['now'] - strtotime($entry['timestamp']);
        $string .= overlib_link('', formatUptime($timediff, "short-3"), format_timestamp($entry['timestamp']), NULL) . '</td>' . PHP_EOL;
      } else {
        $string .= '    <td style="width: 160px">';
        $string .= format_timestamp($entry['timestamp']) . '</td>' . PHP_EOL;
      }

      if ($list['device'])
      {
        $dev = device_by_id_cache($entry['device_id']);
        $device_vars = array('page'    => 'device',
                             'device'  => $entry['device_id'],
                             'tab'     => 'logs',
                             'section' => 'eventlog');
        $string .= '    <td class="entity">' . generate_device_link($dev, short_hostname($dev['hostname']), $device_vars) . '</td>' . PHP_EOL;
      }
      if ($list['port'])
      {
        if ($entry['type'] == 'port')
        {
          $this_if = get_port_by_id_cache($entry['reference']);
          $entry['link'] = '<span class="entity">' . generate_port_link($this_if, short_ifname($this_if['label'])) . '</span>';
        } else {
          $entry['link'] = ucfirst($entry['type']);
        }
        if (!$events['short']) { $string .= '    <td>' . $entry['link'] . '</td>' . PHP_EOL; }
      }
      if ($events['short'])
      {
        $string .= '    <td class="syslog">' . $entry['link'] . ' ';
      } else {
        $string .= '    <td>';
      }
      $string .= htmlspecialchars($entry['message']) . '</td>' . PHP_EOL;
      $string .= '  </tr>' . PHP_EOL;
    }

    $string .= '  </tbody>' . PHP_EOL;
    $string .= '</table>';

    // Print pagination header
    if ($events['pagination_html']) { $string = $events['pagination_html'] . $string . $events['pagination_html']; }

    // Print events
    echo $string;
  }
}

/**
 * Display short events.
 *
 * This is use function:
 * print_events(array('short' => TRUE))
 *
 * @param array $vars
 * @return none
 *
 */
function print_events_short($var)
{
  $var['short'] = TRUE;
  print_events($var);
}

/**
 * Params:
 * short
 * pagination, pageno, pagesize
 * device_id, port, type, message, timestamp_from, timestamp_to
 */
function get_events_array($vars)
{
  $array = array();

  // Short events? (no pagination, small out)
  $array['short'] = (isset($vars['short']) && $vars['short']);
  // With pagination? (display page numbers in header)
  $array['pagination'] = (isset($vars['pagination']) && $vars['pagination']);
  pagination($vars, 0, TRUE); // Get default pagesize/pageno
  $array['pageno']   = $vars['pageno'];
  $array['pagesize'] = $vars['pagesize'];
  $start    = $array['pagesize'] * $array['pageno'] - $array['pagesize'];
  $pagesize = $array['pagesize'];

  // Begin query generate
  $param = array();
  $where = ' WHERE 1 ';
  foreach ($vars as $var => $value)
  {
    if ($value != '')
    {
      switch ($var)
      {
        case 'device':
        case 'device_id':
          $where .= generate_query_values($value, 'device_id');
          break;
        case 'port':
          $where .= generate_query_values($value, 'reference');
          break;
        case 'type':
          $where .= generate_query_values($value, 'type');
          break;
        case 'message':
          $where .= generate_query_values($value, 'message', '%LIKE%');
          break;
        case 'timestamp_from':
          $where .= ' AND `timestamp` >= ?';
          $param[] = $value;
          break;
        case 'timestamp_to':
          $where .= ' AND `timestamp` <= ?';
          $param[] = $value;
          break;
      }
    }
  }

  // Show events only for permitted devices
  $query_permitted = generate_query_permitted();

  $query = 'FROM `eventlog` ';
  $query .= $where . $query_permitted;
  $query_count = 'SELECT COUNT(`event_id`) '.$query;
  $query_updated = 'SELECT MAX(`timestamp`) '.$query;

  $query = 'SELECT * '.$query;
  $query .= ' ORDER BY `event_id` DESC ';
  $query .= "LIMIT $start,$pagesize";

  // Query events
  $array['entries'] = dbFetchRows($query, $param);

  // Query events count
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
