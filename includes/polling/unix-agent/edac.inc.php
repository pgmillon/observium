<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// We use and parse -v because -rfull does not supply info about uncorrected errrors.

// mc0: 0 Uncorrected Errors with no DIMM info
// mc0: 0 Corrected Errors with no DIMM info
// mc0: csrow0: 0 Uncorrected Errors
// mc0: csrow0: CPU#0Channel#1_DIMM#0: 0 Corrected Errors

// mc0: 0 Uncorrected Errors with no DIMM info
// mc0: 11 Corrected Errors with no DIMM info
// mc0: csrow0: 1 Uncorrected Errors
// mc0: csrow0: ch0: 4 Corrected Errors
// mc0: csrow0: ch1: 0 Corrected Errors

if ($agent_data['edac'] != '')
{
  echo('EDAC ');

  foreach (explode("\n",$agent_data['edac']) as $line)
  {
    list($mc,$data) = explode(': ',$line,2);
    // mc0: 0 Uncorrected Errors with no DIMM info
    if (preg_match("/^(.*) Uncorrected Errors with no DIMM info$/", $data, $matches))
    {
      $edac[$mc]['row']['unknown']['all']['ue'] = $matches[1];
    }
    // mc0: 0 Corrected Errors with no DIMM info
    elseif (preg_match("/^(.*) Corrected Errors with no DIMM info$/", $data, $matches))
    {
      $edac[$mc]['row']['unknown']['all']['ce'] = $matches[1];
    }
    // mc0: csrow0: 0 Uncorrected Errors
    elseif (preg_match("/^csrow(.*): (.*) Uncorrected Errors$/", $data, $matches))
    {
      $edac[$mc]['row'][$matches[1]]['all']['ue'] = $matches[2];
    }
    // mc0: csrow0: CPU#0Channel#1_DIMM#0: 0 Corrected Errors
    elseif (preg_match("/^csrow(.*): (.*): (.*) Corrected Errors$/", $data, $matches))
    {
      $edac[$mc]['row'][$matches[1]][$matches[2]]['ce'] = $matches[3];
      $edac_name[$mc]['rowname'][$matches[1]][$matches[2]] = $matches[2];
    }
  }

  set_dev_attrib($device, 'edac_rownames', serialize($edac_name));

  $graphs['edac_errors'] = TRUE;

  foreach ($edac as $mc => $data)
  {
    foreach ($data['row'] as $row => $channels)
    {
      foreach ($channels as $channel => $errors)
      {
        foreach (array('ce','ue') as $errortype)
        {
          if (isset($errors[$errortype]))
          {
            $row_id = ($row === 'unknown' ? 'unknown' : "csrow$row"); // Yes, ===, otherwise PHP thinks unknown means 0.
            $rrd_filename = "edac-errors-$mc-$row_id-$channel-$errortype.rrd";

            rrdtool_create($device, $rrd_filename, " \
                DS:errors:GAUGE:600:0:125000000000 ");

            rrdtool_update($device, $rrd_filename, "N:" . $errors[$errortype]);
          }
        }
      }
    }
  }
} else {
  // No more EDAC app; remove row names from DB
  del_dev_attrib($device, 'edac_rownames');
}

unset($edac, $edac_name);

// EOF
