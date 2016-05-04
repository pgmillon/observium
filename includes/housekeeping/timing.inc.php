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

// Minimum allowed age for delete perfomance times is 24h
$cutoff = age_to_unixtime($config['housekeeping']['timing']['age'], age_to_seconds('24h'));

if ($cutoff)
{
  $where = "`start` < $cutoff";
  $count_run = dbFetchCell("SELECT COUNT(*) FROM `perf_times` WHERE $where;");
  $count_dev = dbFetchCell("SELECT COUNT(*) FROM `devices_perftimes` WHERE $where;");

  if ($count_run || $count_dev)
  {
    if ($prompt)
    {
      $answer = print_prompt("Perfomance entries - $count_run (per-run) and $count_dev (per-device) older than " . format_unixtime($cutoff) . " will be deleted");
    }

    if ($answer)
    {
      $rows = dbDelete('devices_perftimes', $where);
      if ($rows === FALSE)
      {
        // Use LIMIT with big tables
        print_debug("Performance table (per-device) is too big, using LIMIT for delete entries");
        $rows = 0;
        $i    = 1000;
        while ($i && $rows < $count_dev)
        {
          $iter = dbDelete('devices_perftimes', $where.' LIMIT 1000000');
          if ($iter === FALSE) { break; }
          $rows += $iter;
          $i--;
        }
      }
      print_debug("Timing housekeeping: deleted $rows entries (per-device)");
      logfile("housekeeping.log", "Timing: deleted $rows entries older than " . format_unixtime($cutoff) . " (per-device)");

      $rows = dbDelete('perf_times', $where);
      if ($rows === FALSE)
      {
        // Use LIMIT with big tables
        print_debug("Performance table (per-run) is too big, using LIMIT for delete entries");
        $rows = 0;
        $i    = 1000;
        while ($i && $rows < $count_run)
        {
          $iter = dbDelete('perf_times', $where.' LIMIT 1000000');
          if ($iter === FALSE) { break; }
          $rows += $iter;
          $i--;
        }
      }
      print_debug("Timing housekeeping: deleted $rows entries (per-run)");
      logfile("housekeeping.log", "Timing: deleted $rows entries older than " . format_unixtime($cutoff) . " (per-run)");
    }
  }
  else if ($prompt)
  {
    print_message("No perfomance entries found older than " . format_unixtime($cutoff));
  }
} else {
  print_message("Timing housekeeping disabled in configuration or less than 24h.");
}

// EOF