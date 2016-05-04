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
 * Display syslog messages.
 *
 * Display pages with device syslog messages.
 * Examples:
 * print_syslogs() - display last 10 syslog messages from all devices
 * print_syslogs(array('pagesize' => 99)) - display last 99 syslog messages from all device
 * print_syslogs(array('pagesize' => 10, 'pageno' => 3, 'pagination' => TRUE)) - display 10 syslog messages from page 3 with pagination header
 * print_syslogs(array('pagesize' => 10, 'device' = 4)) - display last 10 syslog messages for device_id 4
 * print_syslogs(array('short' => TRUE)) - show small block with last syslog messages
 *
 * @param array $vars
 * @return none
 *
 */
function print_syslogs($vars)
{
  // Short events? (no pagination, small out)
  $short = (isset($vars['short']) && $vars['short']);
  // With pagination? (display page numbers in header)
  $pagination = (isset($vars['pagination']) && $vars['pagination']);
  pagination($vars, 0, TRUE); // Get default pagesize/pageno
  $pageno   = $vars['pageno'];
  $pagesize = $vars['pagesize'];
  $start = $pagesize * $pageno - $pagesize;

  $priorities = $GLOBALS['config']['syslog']['priorities'];

  $param = array();
  $where = ' WHERE 1 ';
  foreach ($vars as $var => $value)
  {
    if ($value != '')
    {
      $cond = array();
      switch ($var)
      {
        case 'device':
        case 'device_id':
          $where .= generate_query_values($value, 'device_id');
          break;
        case 'priority':
          if (!is_array($value)) { $value = explode(',', $value); }
          foreach ($value as $k => $v)
          {
            // Rewrite priority strings to numbers
            $value[$k] = priority_string_to_numeric($v);
          }
          // Do not break here, it's true!
        case 'program':
          $where .= generate_query_values($value, $var);
          break;
        case 'message':
          $where .= generate_query_values($value, 'msg', '%LIKE%');
          break;
        case 'timestamp_from':
          $where .= ' AND `timestamp` > ?';
          $param[] = $value;
          break;
        case 'timestamp_to':
          $where .= ' AND `timestamp` < ?';
          $param[] = $value;
          break;
      }
    }
  }

  // Show events only for permitted devices
  $query_permitted = generate_query_permitted();

  $query = 'FROM `syslog` ';
  $query .= $where . $query_permitted;
  $query_count = 'SELECT COUNT(*) ' . $query;

  $query = 'SELECT * ' . $query;
  $query .= ' ORDER BY `seq` DESC ';
  $query .= "LIMIT $start,$pagesize";

  // Query syslog messages
  $entries = dbFetchRows($query, $param);
  // Query syslog count
  if ($pagination && !$short) { $count = dbFetchCell($query_count, $param); }
  else { $count = count($entries); }

  if (!$count)
  {
    // There have been no entries returned. Print the warning.

    print_warning('<h4>No syslog entries found!</h4>
Check that the syslog daemon and Observium configuration options are set correctly, that your devices are configured to send syslog to Observium and that there are no firewalls blocking the messages.

See <a href="'.OBSERVIUM_URL.'/wiki/Category:Documentation" target="_blank">documentation</a> and <a href="'.OBSERVIUM_URL.'/wiki/Configuration_Options#Syslog_Settings" target="_blank">configuration options</a> for more information.');

  } else {
    // Entries have been returned. Print the table.

    $list = array('device' => FALSE, 'priority' => TRUE); // For now (temporarily) priority always displayed
    if (!isset($vars['device']) || empty($vars['device']) || $vars['page'] == 'syslog') { $list['device'] = TRUE; }
    if ($short || !isset($vars['priority']) || empty($vars['priority'])) { $list['priority'] = TRUE; }


    $string = generate_box_open($vars['header']);

    $string .= '<table class="'.OBS_CLASS_TABLE_STRIPED_MORE.'">' . PHP_EOL;
    if (!$short)
    {
      $string .= '  <thead>' . PHP_EOL;
      $string .= '    <tr>' . PHP_EOL;
      $string .= '      <th class="state-marker"></th>' . PHP_EOL;
  #    $string .= '      <th></th>' . PHP_EOL;
      $string .= '      <th>Date</th>' . PHP_EOL;
      if ($list['device']) { $string .= '      <th>Device</th>' . PHP_EOL; }
      if ($list['priority']) { $string .= '      <th>Priority</th>' . PHP_EOL; }
      $string .= '      <th>Message</th>' . PHP_EOL;
      $string .= '    </tr>' . PHP_EOL;
      $string .= '  </thead>' . PHP_EOL;
    }
    $string .= '  <tbody>' . PHP_EOL;

    foreach ($entries as $entry)
    {
      switch ($entry['priority'])
      {
        case "0": // Emergency
        case "1": // Alert
        case "2": // Critical
        case "3": // Error
            $entry['html_row_class'] = "error";
            break;
        case "4": // Warning
            $entry['html_row_class'] = "warning";
            break;
        case "5": // Notification
            $entry['html_row_class'] = "recovery";
            break;
        case "6": // Informational
            $entry['html_row_class'] = "up";
            break;
        case "7": // Debugging
            $entry['html_row_class'] = "suppressed";
            break;
        default:
      }

      $string .= '  <tr class="'.$entry['html_row_class'].'">' . PHP_EOL;
      $string .= '<td class="state-marker"></td>' . PHP_EOL;

      if ($short)
      {
        $string .= '    <td class="syslog" style="white-space: nowrap">';
        $timediff = $GLOBALS['config']['time']['now'] - strtotime($entry['timestamp']);
        $string .= generate_tooltip_link('', formatUptime($timediff, "short-3"), format_timestamp($entry['timestamp']), NULL) . '</td>' . PHP_EOL;
      } else {
        $string .= '    <td width="160">';
        $string .= format_timestamp($entry['timestamp']) . '</td>' . PHP_EOL;
      }

      if ($list['device'])
      {
        $dev = device_by_id_cache($entry['device_id']);
        $device_vars = array('page'    => 'device',
                             'device'  => $entry['device_id'],
                             'tab'     => 'logs',
                             'section' => 'syslog');
        $string .= '    <td class="entity">' . generate_device_link($dev, short_hostname($dev['hostname']), $device_vars) . '</td>' . PHP_EOL;
      }
      if ($list['priority'])
      {
        if (!$short) { $string .= '    <td style="color: ' . $priorities[$entry['priority']]['color'] . '; white-space: nowrap;">' . nicecase($priorities[$entry['priority']]['name']) . ' (' . $entry['priority'] . ')</td>' . PHP_EOL; }
      }
      $entry['program'] = (empty($entry['program'])) ? '[[EMPTY]]' : $entry['program'];
      if ($short)
      {
        $string .= '    <td class="syslog">';
        $string .= '<strong style="color: ' . $priorities[$entry['priority']]['color'] . ';">' . $entry['program'] . '</strong> : ';
      } else {
        $string .= '    <td>';
        $string .= '<strong>' . $entry['program'] . '</strong> : ';
      }
      $string .= escape_html($entry['msg']) . '</td>' . PHP_EOL;
      $string .= '  </tr>' . PHP_EOL;
    }

    $string .= '  </tbody>' . PHP_EOL;
    $string .= '</table>' . PHP_EOL;

    $string .= generate_box_close();

    // Print pagination header
    if ($pagination && !$short) { $string = pagination($vars, $count) . $string . pagination($vars, $count); }

    // Print syslog
    echo $string;
  }
}

// EOF
