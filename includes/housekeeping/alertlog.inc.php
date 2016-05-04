<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage housekeeping
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// Minimum allowed age for delete alert_log entries times is 24h
$cutoff = age_to_unixtime($config['housekeeping']['alertlog']['age'], age_to_seconds('24h'));

if ($cutoff)
{
  $where = "UNIX_TIMESTAMP(`timestamp`) < $cutoff";
  $count = dbFetchCell("SELECT COUNT(*) FROM `alert_log` WHERE $where");
  if ($count)
  {
    if ($prompt)
    {
      $answer = print_prompt("$count alert log entries older than " . format_unixtime($cutoff) . " will be deleted");
    }

    if ($answer)
    {
      $rows = dbDelete('alert_log', "$where");
      if ($rows === FALSE)
      {
        // Use LIMIT with big tables
        print_debug("Alert log table is too big, using LIMIT for delete entries");
        $rows = 0;
        $i    = 1000;
        while ($i && $rows < $count)
        {
          $iter = dbDelete('alert_log', $where.' LIMIT 1000000');
          if ($iter === FALSE) { break; }
          $rows += $iter;
          $i--;
        }
      }
      print_debug("Alert log housekeeping: deleted $rows entries");
      logfile("housekeeping.log", "Alert log: deleted $rows entries older than " . format_unixtime($cutoff));
    }
  }
  else if ($prompt)
  {
    print_message("No alert log entries found older than " . format_unixtime($cutoff));
  }
} else {
  print_message("Alert log housekeeping disabled in configuration or wrong.");
}

// EOF