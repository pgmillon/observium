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

// FIXME. What is hell here, needed simple way for detect features

$mfeatures = array
(
  'M-401-B0030A0' => '4ch+UPG, D931-D934, Rx=-28dBm/ch, ER:0-40km, ZR:0-80km',
  'M-401-B003AA0' => '4ch+UPG, D931-D934, Rx=-28dBm/ch, ER:40-80km, ZR:40-120km',
  'M-401-B003BA0' => '4ch+UPG, D931-D934, Rx=-28dBm/ch, ER:80-120km, ZR:80-160km',
  'M-401-B1B30A0' => '4ch+UPG, D931-D934, Tx=+8dBm/ch, Rx=-28dBm/ch, ER:80-120km, ZR:80-160km',
  'M-401-B1B3AA0' => '4ch+UPG, D931-D934, Tx=+8dBm/ch, Rx=-28dBm/ch, ER:120-160km, ZR:120-200km',
  'M-4000-02A2A00' => '40ch, D921-D960, in-line, 30dB/+24dBm, 40km DCM',
  'M-4000-02B2B00' => '40ch, D921-D960, in-line, 30dB/+24dBm, 80km DCM',
  'M-1600-D0000C0' => '16ch, D943-D958',
  'M-1601-C0000C0' => '16ch+UPG, D921-D936, IL Link=5.0dB, pass through',
  'M-1601-D1000C0' => '16ch+UPG, D921-D936, Tx=+8dBm/ch, ER:0-40km, ZR:0-80km, pass through',
  'M-1601-D1U00C0' => '16ch+UPG, D921-D936, Tx=+8dBm/ch, ER:0-120km, ZR:0-160km',
  'M-1601-D0030C0' => '16ch+UPG, D921-D936, Rx=-28dBm/ch, ER:0-40km, ZR:0-80km',
  'M-1601-D003AC0' => '16ch+UPG, D921-D936, Rx=-28dBm/ch, ER:40-80km, ZR:40-120km',
  'M-1601-D1A00C0' => '16ch+Upg, D921-D936, Tx=+8dBm/ch, ER:40-80km, ZR:40-120km, pass through',
  'M-1601-D1A30C0' => '16ch+UPG, D921-D936, Tx=+8dBm/ch, Rx=-28dBm/ch, ER:40-80km, ZR:40-120km',
  'M-1601-D1B30C0' => '16ch+UPG, D921-D936, Tx=+8dBm/ch, Rx=-28dBm/ch, ER:80-120km, ZR:80-160km',
  'M-1601-D1B3AC0' => '16ch+UPG, D921-D936, Tx=+8dBm/ch, Rx=-28dBm/ch, ER:120-160km, ZR:120-200km',
  'M-1601-D1A3TC0' => '16ch+UPG, D921-D936, Tx=+8dBm/ch, Rx=-28dBm/ch, ER:0-120km, ZR:0-160km',
  'M-1601-D1B3TC0' => '16ch+UPG, D921-D936, Tx=+8dBm/ch, Rx=-28dBm/ch, ER:40-160km, ZR:40-200km',
  'M-1601-F1A00E0' => '16ch+Upg, D921-D936, Tx=+8dBm/ch, ER:40-80km, ZR:40-120km',
);

# SmartOptics, M-Series M-1601-D1A30C1 R1B, SmartOS v2.3.9 (Compiled on Tue Oct 14 09:40:33 CEST 2014)
if (preg_match('/^SmartOptics, \S+Series (?<hardware>.+), SmartOS v(?<version>\S+) /', $poll_device['sysDescr'], $matches))
{
  $hardware = $matches['hardware']; // -> M-1601-D1A30C1 R1B
  $version  = $matches['version'];  // -> 2.3.9

  // Features
  $fa = array();
  if (preg_match('/^(M-1601-[A-Z0-9]+)(C0|C1) /', $hardware, $matches))
  {
    # An M-1601 ending in "C1" has an optical supervisory channel feature.
    if ($matches[2] == 'C1')
    {
      $fa[] = 'OSC';
    }
    if (isset($mfeatures[$matches[1] . 'C0']))
    {
      $fa[] = $mfeatures[$matches[1] . 'C0'];
    }
  }
  else if (preg_match('/^(M-[0-9]+-[A-Z0-9]+) /', $hardware, $matches))
  {
    # all other M-series
    if (isset($mfeatures[$matches[1]]))
    {
      $fa[] = $mfeatures[$matches[1]];
    }
  }
  if (count($fa))
  {
    $features = implode(', ', $fa);
  }
}

// EOF
