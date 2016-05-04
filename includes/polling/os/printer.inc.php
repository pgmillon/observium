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

// Printers hardware
$printer = trim(snmp_get($device, 'HOST-RESOURCES-MIB::hrDeviceDescr.1', '-OQv'),'" ');

if ($printer)
{
 list($hardware) = explode(';', $printer);
} else {
  if ($device['os'] == "jetdirect")
  {
    // ...7.0 = STRING: "MFG:Hewlett-Packard;CMD:PJL,MLC,BIDI-ECP,PCL,POSTSCRIPT,PCLXL;MDL:hp LaserJet 1320 series;CLS:PRINTER;DES:Hewlett-Packard LaserJet 1320 series;MEM:9MB;COMMENT:RES=1200x1;"
    //                  "MFG:HP;MDL:Officejet Pro K5400;CMD:MLC,PCL,PML,DW-PCL,DESKJET,DYN;1284.4DL:4d,4e,1;CLS:PRINTER;DES:C8185A;SN:MY82E680JG;S:038000ec840010210068eb800008fb8000041c8003844c8004445c8004d46c8003b;Z:0102,05000009000009029cc1016a81017a21025e41,0600,070000000000000"
    $jdinfo = trim(snmp_get($device, '1.3.6.1.4.1.11.2.3.9.1.1.7.0', '-OQv'),'" ');
    preg_match('/(?:MDL|MODEL|DESCRIPTION):([^;]+);/', $jdinfo, $matches);
    $hardware = $matches[1];
  }
  elseif ($device['os'] == "samsung")
  {
    // STRING: Samsung ML-2850 Series OS 1.03.00.16 01-22-2008;Engine 1.01.06;NIC V4.01.02(ML-285x) 09-13-2007;S/N 4F66BKEQ410592R
    list(,$hardware) = explode(' ', $poll_device['sysDescr']);
  }
}
// Strip off useless brand fields
$hardware = str_ireplace(array('HP ', 'Hewlett-Packard ', ' Series', 'Samsung ', 'Epson ', 'Brother ', 'OKI '), '', $hardware);
$hardware = ucfirst($hardware);

// Features
/// FIXME. Need rewrite function. -- Mike 03/2013
//
// PrtMarkerMarkTech ::= DESCRIPTION "The type of marking technology used for this marking sub-unit"
//other(1),
//unknown(2),
//electrophotographicLED(3),
//electrophotographicLaser(4),
//electrophotographicOther(5),
//impactMovingHeadDotMatrix9pin(6),
//impactMovingHeadDotMatrix24pin(7),
//impactMovingHeadDotMatrixOther(8),
//impactMovingHeadFullyFormed(9),
//impactBand(10),
//impactOther(11),
//inkjetAqueous(12),
//inkjetSolid(13),
//inkjetOther(14),
//pen(15),
//thermalTransfer(16),
//thermalSensitive(17),
//thermalDiffusion(18),
//thermalOther(19),
//electroerosion(20),
//electrostatic(21),
//photographicMicrofiche(22),
//photographicImagesetter(23),
//photographicOther(24),
//ionDeposition(25),
//eBeam(26),
//typesetter(27)
$features = trim(snmp_get($device, 'Printer-MIB::prtMarkerMarkTech.1.1', '-OQv'),'" ');

// Serial number
$serial = trim(snmp_get($device, 'Printer-MIB::prtGeneralSerialNumber.1', '-OQv'),'" ');

// OS version
if ($device['os'] == "samsung")
{
  // STRING: Samsung ML-2850 Series OS 1.03.00.16 01-22-2008;Engine 1.01.06;NIC V4.01.02(ML-285x) 09-13-2007;S/N 4F66BKEQ410592R
  list($version) = explode (';', $poll_device['sysDescr']);
  preg_match('/([\d]+\.[\d\.]+)/', $version, $matches);
  $version = $matches[1];
}
elseif ($device['os'] == "okilan")
{
  // STRING: OKI OkiLAN 8100e Rev.02.73 10/100BASE Ethernet PrintServer: Attached to C3200n Rev.N2.14 : (C)2004 Oki Data Corporation
  list(,$version) = explode (':', $poll_device['sysDescr']);
  preg_match('/Rev\.N*([\d]+\.[\d\.]+)/', $version, $matches);
  $version = $matches[1];
}

// EOF
