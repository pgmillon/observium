<?php

// Import from CVS
require(dirname(__FILE__) . '/CsvFileIterator.php');

// Base observium includes
include(dirname(__FILE__) . '/../includes/defaults.inc.php');
include(dirname(__FILE__) . '/../config.php');
include(dirname(__FILE__) . '/../includes/definitions.inc.php');
include(dirname(__FILE__) . '/../includes/functions.inc.php');

// for generate provider data, uncomment this and run:
// php tests/IncludesRewritesTest.php

/*
// Generate provider data
foreach (array('iosrx', 'iosxe', 'ios') as $os)
{
  foreach (array('entPhysicalDescr', 'entPhysicalName') as $file)
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
  * @dataProvider providerSimpleTemplate
  * @group template
  */
  public function testSimpleTemplate($template, $keys, $result)
  {
    $this->assertSame($result, simple_template($template, $keys));
  }

  public function providerSimpleTemplate()
  {
    $return = array(
      // One line php-style comments
      array(
        '<h1>{{title}}</h1>   // just something interesting... #or ^not...',
        array('title' => 'A Comedy of Errors'),
        '<h1>A Comedy of Errors</h1>'
      ),
      // Multiline php-style comments
      array(
        '/**
          * just something interesting... #or ^not...
          */
        <h1>{{title}}</h1>
        /**
          * just something interesting... #or ^not...
          */',
        array('title' => 'A Comedy of Errors'),
        '        <h1>A Comedy of Errors</h1>'.PHP_EOL
      ),
      // Var not exist
      array(
        '<h1>{{title}}</h1>',
        array('non_exist' => 'A Comedy of Errors'),
        '<h1></h1>'
      ),
    );

    $templates_dir = dirname(__FILE__) . '/templates';
    foreach (scandir($templates_dir) as $dir)
    {
      $json = $templates_dir.'/'.$dir.'/'.$dir.'.json';
      if ($dir != '.' && $dir != '..' && is_dir($templates_dir.'/'.$dir) && is_file($json))
      {
        $template = $templates_dir.'/'.$dir.'/'.$dir.'.mustache';
        $result   = $templates_dir.'/'.$dir.'/'.$dir.'.txt';

        $return[] = array(
                      file_get_contents($template),
                      json_decode(file_get_contents($json), TRUE),
                      file_get_contents($result)
                    );
      }
    }

    return $return;
  }
}

// EOF
