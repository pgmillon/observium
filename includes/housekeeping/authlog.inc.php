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

// Minimum allowed age for delete authlog entries times is 24h
$cutoff = age_to_unixtime($config['housekeeping']['authlog']['age'], age_to_seconds('24h'));

if ($cutoff)
{
  $where = "UNIX_TIMESTAMP(`datetime`) < $cutoff";
  $count = dbFetchCell("SELECT COUNT(*) FROM `authlog` WHERE $where");
  if ($count)
  {
    if ($prompt)
    {
      $answer = print_prompt("$count authlog entries older than " . format_unixtime($cutoff) . " will be deleted");
    }

    if ($answer)
    {
      $rows = dbDelete('authlog', "$where");
      if ($rows === FALSE)
      {
        // Use LIMIT with big tables
        print_debug("Authlog table is too big, using LIMIT for delete entries");
        $rows = 0;
        $i    = 1000;
        while ($i && $rows < $count)
        {
          $iter = dbDelete('authlog', $where.' LIMIT 1000000');
          if ($iter === FALSE) { break; }
          $rows += $iter;
          $i--;
        }
      }
      print_debug("Authlog housekeeping: deleted $rows entries");
      logfile("housekeeping.log", "Authlog: deleted $rows entries older than " . format_unixtime($cutoff));
    }
  }
  else if ($prompt)
  {
    print_message("No authlog entries found older than " . format_unixtime($cutoff));
  }
} else {
  print_message("Authlog housekeeping disabled in configuration or wrong.");
}

// EOF
