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

echo("Zhone DLSAM".PHP_EOL);

//Zhone DSLAM; Model: 8820-A2-xxx

if (strpos($poll_device['sysDescr'], 'Model') !== FALSE )
{
  $data = explode(";", $poll_device['sysDescr']);
  foreach ($data AS $datum)
  {
    list($field, $value) = explode(":", $datum);

    $field = trim($field); $value = trim($value);
    if (strpos($field, 'Zhone')    !== FALSE) { $hardware = $field; }
    if (strpos($field, 'Paradyne') !== FALSE) { $hardware = $field; }
    $hardware = str_replace("Unit", "", $hardware);

    if ($field == "Model")             { $hardware .= $value; }
    if ($field == "CCA")               { } // Currently unused
    if ($field == "S/W Release")       { $version = $value; }
    if ($field == "Hardware Revision") { $features = $value; } // Currently unused
    if ($field == "Serial number")     { $serial = $value; }

  }
}

// EOF
