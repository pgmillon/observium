<?php

// Import from CVS
require(dirname(__FILE__) . '/data/CsvFileIterator.php');

// Base observium includes
include(dirname(__FILE__) . '/../includes/defaults.inc.php');
//include(dirname(__FILE__) . '/../config.php'); // Do not include user editable config here
include(dirname(__FILE__) . '/data/test_definitions.inc.php'); // Fake definitions for testing
include(dirname(__FILE__) . '/../includes/definitions.inc.php');
include(dirname(__FILE__) . '/../includes/functions.inc.php');

// for generate provider data, uncomment this and run:
// php tests/IncludesRewritesTest.php

/*
// Generate provider data
foreach (array('iosrx', 'iosxe', 'ios', 'procurve', 'vrp') as $os)
{
  foreach (array('entPhysicalDescr', 'entPhysicalName', 'hwEntityBomEnDesc') as $file)
  {
    if (!is_file(dirname(__FILE__) . "/data/$os.$file.txt")) { continue; }
    $s = fopen(dirname(__FILE__) . "/data/$os.$file.txt", 'r');
    while ($line = fgets($s))
    {
      list(,$string) = explode(' = ', $line, 2);
      $string = trim($string);
      if (!isset($valid[$string]))
      {
        $rewrite = rewrite_entity_name($string);
        $valid[$string] = $rewrite;
      }
    }
    fclose($s);
  }
}
$csv = fopen(dirname(__FILE__) . "/data/providerRewriteEntityName.csv", 'w');
foreach ($valid as $string => $rewrite)
{
  fputcsv($csv, array($string, $rewrite));
}
fclose($csv);
exit;
*/

class IncludesRewritesTest extends PHPUnit_Framework_TestCase
{
  /**
  * @dataProvider providerRewriteEntityNameCsv
  * @group rename_entity
  */
  public function testRewriteEntityNameCsv($string, $result)
  {
    $this->assertSame($result, rewrite_entity_name($string));
  }

  public function providerRewriteEntityNameCsv()
  {
    return new CsvFileIterator(dirname(__FILE__) . '/data/providerRewriteEntityName.csv');
  }

  /**
  * @dataProvider providerRewriteEntityName
  * @group rename_entity
  */
  public function testRewriteEntityName($string, $result)
  {
    $this->assertSame($result, rewrite_entity_name($string));
  }

  public function providerRewriteEntityName()
  {
    return array(
      array('GenuineIntel Intel Celeron M processor .50GHz, 1496 MHz', 'Intel Celeron M processor .50GHz, 1496 MHz'),
      array('CPU Intel Celeron M (TM) (R)', 'Intel Celeron M'),
    );
  }

  /**
   * @dataProvider providerTrimQuotes
   * @group string
   */
  public function testTrimQuotes($string, $result)
  {
    $this->assertEquals($result, trim_quotes($string));
  }

  public function providerTrimQuotes()
  {
    return array(
      array('\"sdfslfkm s\'fdsf" a;lm aamjn ',          '"sdfslfkm s\'fdsf" a;lm aamjn'),
      array('sdfslfkm s\'fdsf" a;lm aamjn \"',          'sdfslfkm s\'fdsf" a;lm aamjn "'),
      array('sdfslfkm s\'fdsf" a;lm aamjn ',            'sdfslfkm s\'fdsf" a;lm aamjn'),
      array('\"sdfslfkm s\'fdsf" a;lm aamjn \"',        'sdfslfkm s\'fdsf" a;lm aamjn '),
      array('"sdfslfkm s\'fdsf" a;lm aamjn "',          'sdfslfkm s\'fdsf" a;lm aamjn '),
      array('"\"sdfslfkm s\'fdsf" a;lm aamjn \""',      'sdfslfkm s\'fdsf" a;lm aamjn '),
      array('\'\"sdfslfkm s\'fdsf" a;lm aamjn \"\'',    'sdfslfkm s\'fdsf" a;lm aamjn '),
      array('  \'\"sdfslfkm s\'fdsf" a;lm aamjn \"\' ', 'sdfslfkm s\'fdsf" a;lm aamjn '),
      array('"""sdfslfkm s\'fdsf" a;lm aamjn """',      'sdfslfkm s\'fdsf" a;lm aamjn '),
      array('"""sdfslfkm s\'fdsf" a;lm aamjn """"""""', 'sdfslfkm s\'fdsf" a;lm aamjn """""'),
      array('"""""""sdfslfkm s\'fdsf" a;lm aamjn """',  '""""sdfslfkm s\'fdsf" a;lm aamjn '),
      // escaped quotes
      array('\"Mike Stupalov\" <mike@observium.org>',      '"Mike Stupalov" <mike@observium.org>'),
      // utf-8
      array('Avenue Léon, België ',                     'Avenue Léon, België'),
      array('\"Avenue Léon, België \"',                 'Avenue Léon, België '),
      array('"Винни пух и все-все-все "',               'Винни пух и все-все-все '),
      // multilined
      array('  \'\"\"sdfslfkm s\'fdsf"
            a;lm aamjn \"\"\' ', 'sdfslfkm s\'fdsf"
            a;lm aamjn '),
    );
  }

  /**
   * @dataProvider providerRewriteDefinitionHardware
   * @group hardware
   */
  public function testRewriteDefinitionHardware($os, $id, $result)
  {
    $device = array('os' => $os, 'sysObjectID' => $id);
    $this->assertEquals($result, rewrite_definition_hardware($device));
  }

  public function providerRewriteDefinitionHardware()
  {
    return array(
      array('calix', '.1.3.6.1.4.1.6321.1.2.2.5.3', 'E7-2'),
      array('calix', '.1.3.6.1.4.1.6321.1.2.1',     'C7'),
      array('calix', '.1.3.6.1.4.1.6321',           'C7'),
      array('calix', '.1.3.6.1.4.1.6321.1.2.3',     'E5-100'),
    );
  }
}

// EOF
