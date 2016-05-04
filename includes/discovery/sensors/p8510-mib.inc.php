<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

# FIXME could do with a rewrite?

echo(" P8510-MIB ");

$regexp = '/
      \.1\.3\.6\.1\.4\.1\.22626\.1\.5\.2\.
      (?P<id>\d+)
      \.
      (?:
              1\.0 (?P<name>.*)|
              3\.0 (?P<temp_intval>.*)|
              5\.0 (?P<limit_high>.*)|
              6\.0 (?P<limit_low>.*)|
      )
/x';

$oids = snmp_walk($device, ".1.3.6.1.4.1.22626.1.5.2", "-OsqnU", "");

if ($oids)
{
  $out = array();
  foreach (explode("\n", $oids) as $line)
  {
    preg_match($regexp, $line, $match);
    if ($match['name'])
    {
      $out[$match['id']]['name'] = $match['name'];
    }

    if ($match['temp_intval'])
    {
      $out[$match['id']]['temp_intval'] = $match['temp_intval'];
    }

    if ($match['limit_high'])
    {
      $out[$match['id']]['limit_high'] = $match['limit_high'];
    }

    if ($match['limit_low'])
    {
      $out[$match['id']]['limit_low'] = $match['limit_low'];
    }
  }

  $scale = 0.1;
  foreach ($out as $sensor_id=>$sensor)
  {
    if ($sensor['temp_intval'] != 9999)
    {
      $temperature_oid = '.1.3.6.1.4.1.22626.1.5.2.' . $sensor_id . '.3.0';
      $temperature_id = $sensor_id;
      $descr = trim($sensor['name'], ' "');
      $temperature = $sensor['temp_intval'];
      $limits = array('limit_high' => trim($sensor['limit_high'], ' "'),
                      'limit_low'  => trim($sensor['limit_low'], ' "'));

      discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $temperature_id, 'cometsystem-p85xx', $descr, $scale, $temperature, $limits);
    }
  }
}

// EOF
