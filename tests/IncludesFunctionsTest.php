<?php

include(dirname(__FILE__) . '/../includes/defaults.inc.php');
include(dirname(__FILE__) . '/../config.php');
include(dirname(__FILE__) . '/../includes/definitions.inc.php');
include(dirname(__FILE__) . '/../includes/functions.inc.php');

class IncludesFunctionsTest extends PHPUnit_Framework_TestCase
{
  /**
  * @dataProvider providerEmail
  */
  public function testParseEmail($string, $result)
  {
    $this->assertSame($result, parse_email($string));
  }

  public function providerEmail()
  {
    return array(
        array('test@example.com',     array('test@example.com' => NULL)),
        array(' test@example.com ',   array('test@example.com' => NULL)),
        array('<test@example.com>',   array('test@example.com' => NULL)),
        array('<test@example.com> ',  array('test@example.com' => NULL)),
        array(' <test@example.com> ', array('test@example.com' => NULL)),

        array('Test Title <test@example.com>',      array('test@example.com' => 'Test Title')),
        array('Test Title<test@example.com>',       array('test@example.com' => 'Test Title')),
        array('"Test Title" <test@example.com>',    array('test@example.com' => 'Test Title')),
        array('"Test Title <test@example.com>',     array('test@example.com' => 'Test Title')),
        array('Test Title" <test@example.com>',     array('test@example.com' => 'Test Title')),
        array('" Test Title " <test@example.com>',  array('test@example.com' => 'Test Title')),
        array('\'Test Title\' <test@example.com>',  array('test@example.com' => 'Test Title')),

        array('"Test Title" <test@example.com>,"Test Title 2" <test2@example.com>',
              array('test@example.com' => 'Test Title', 'test2@example.com' => 'Test Title 2')),
        array('\'Test Title\' <test@example.com>, "Test Title 2" <test2@sub.example.com>',
              array('test@example.com' => 'Test Title', 'test2@sub.example.com' => 'Test Title 2')),

        array('example.com',                 FALSE),
        array('<example.com>',               FALSE),
        array('Test Title test@example.com', FALSE),
        array('Test Title <example.com>',    FALSE),
    );
  }

  /**
  * @dataProvider providerSiToScale
  */
  public function testSiToScale($units, $precision, $result)
  {
    $this->assertSame($result, si_to_scale($units, $precision));
  }

  public function providerSiToScale()
  {
    $results = array(
      array('yocto',  5, 1.0E-29),
      array('zepto', -6, 1.0E-21),
      array('atto',   9, 1.0E-27),
      array('femto',  8, 1.0E-23),
      array('pico',   0, 1.0E-12),
      array('nano',  -7, 1.0E-9),
      array('micro',  4, 1.0E-10),
      array('milli',  7, 1.0E-10),
      array('units',  3, 0.001),
      array('kilo',   2, 10),
      array('mega',  -2, 1000000),
      array('giga',  -1, 1000000000),
      array('tera',  -4, 1000000000000),
      array('exa',    4, 100000000000),
      array('peta',  -3, 1000000000000000000),
      array('zetta',  1, 1.0E+20),
      array('yotta',  7, 100000000000000000),
      array('',      -6, 1),
      array('test',   6, 1.0E-6),
      array('0',     -3, 1),
      array('5',      2, 1000),
      array('-1',     1, 0.01),
    );
    return $results;
  }

  /**
  * @dataProvider providerSiToScaleValue
  */
  public function testSiToScaleValue($value, $scale, $result)
  {
    $this->assertSame($result, $value * si_to_scale($scale));
  }

  public function providerSiToScaleValue()
  {
    return array(
      array('330',  '-2', 3.3),
      array('1194', '-2', 11.94),
      array('928',  NULL, 928),
      array('9',     '1', 90),
      array('22',    '0', 22),
      array('1194', 'milli', 1.194),
    );
  }

  /**
  * @dataProvider providerFloatCompare
  */
  public function testFloatCompare($a, $b, $epsilon, $result)
  {
    $this->assertSame($result, float_cmp($a, $b, $epsilon));
  }

  public function providerFloatCompare()
  {
    return array(
      array('330', '-2', NULL, 1),
      array('1',    '2', 0.1,  -1),
      array('test',       'milli', 1.194,  1),
      array(0.001,    0.000999999,  NULL,  0),
      array('0.000001',  0.000002,  NULL, -1),
      array(array('NULL'),    '0',  0.01,  1),
      array(array('NULL'), array('NULL'), NULL, 0),
    );
  }

  /**
  * @dataProvider providerIsHexString
  */
  public function testIsHexString($string, $result)
  {
    $this->assertSame($result, IsHexString($string));
  }

  public function providerIsHexString()
  {
    $results = array(
      array('49 6E 70 75 74 20 31 00 ', TRUE),
      array('49 6E 70 75 74 20 31 00',  TRUE),
      array('49 6E 70 75 74 20 31 0',   FALSE),
      array('Simple String',            FALSE),
      array('49 6E 70 75 74 20 31 0R ', FALSE)
    );
    return $results;
  }

  /**
  * @dataProvider providerSNMPHexString
  */
  public function testSNMPHexString($string, $result)
  {
    $this->assertSame($result, snmp_hexstring($string));
  }

  public function providerSNMPHexString()
  {
    $results = array(
      array('49 6E 70 75 74 20 31 00 ', 'Input 1'),
      array('49 6E 70 75 74 20 31 00',  'Input 1'),
      array('49 6E 70 75 74 20 31 0',   '49 6E 70 75 74 20 31 0'),
      array('Simple String',            'Simple String'),
      array('49 6E 70 75 74 20 31 0R ', '49 6E 70 75 74 20 31 0R ')
    );
    return $results;
  }

  /**
  * @dataProvider providerStateStringToNumeric
  */
  public function testStateStringToNumeric($type, $value, $result)
  {
    $this->assertSame($result, state_string_to_numeric($type, $value));
  }

  public function providerStateStringToNumeric()
  {
    $results = array(
      array('mge-status-state',           'No', 2),
      array('mge-status-state',           'no', 2),
      array('mge-status-state',           'Banana', -1),
      array('inexistent-status-state',    'Vanilla', -1),
      array('radlan-hwenvironment-state', 'notFunctioning', 6),
      array('radlan-hwenvironment-state', 'notFunctioning ', 6),
      array('cisco-envmon-state',         'warning', 2),
      array('cisco-envmon-state',         'war ning', -1),
      array('powernet-sync-state',        'inSync', 1),
    );
    return $results;
  }

  /**
  * @dataProvider providerArrayMergeIndexed
  */
  public function testArrayMergeIndexed($result, $array1, $array2, $array3 = NULL)
  {

    if ($array3 == NULL)
    {
      $this->assertSame($result, array_merge_indexed($array1, $array2));
    } else {
      $this->assertSame($result, array_merge_indexed($array1, $array2, $array3));
    }
  }

  public function providerArrayMergeIndexed()
  {
    $results = array(
      array( // Simple 2 array test
        array( // Result
          1 => array('Test1' => 'Moo', 'Test2' => 'Foo', 'Test3' => 'Bar'),
          2 => array('Test1' => 'Baz', 'Test4' => 'Bam', 'Test2' => 'Qux'),
        ),
        array( // Array 1
          1 => array('Test1' => 'Moo'),
          2 => array('Test1' => 'Baz', 'Test4' => 'Bam'),
          ),
        array( // Array 2
          1 => array('Test2' => 'Foo', 'Test3' => 'Bar'),
          2 => array('Test2' => 'Qux'),
        ),
      ),
      array( // Simple 3 array test
        array( // Result
          1 => array('Test1' => 'Moo', 'Test2' => 'Foo', 'Test3' => 'Bar'),
          2 => array('Test1' => 'Baz', 'Test4' => 'Bam', 'Test2' => 'Qux'),
        ),
        array( // Array 1
          1 => array('Test1' => 'Moo'),
          2 => array('Test1' => 'Baz', 'Test4' => 'Bam'),
          ),
        array( // Array 2
          1 => array('Test2' => 'Foo'),
          2 => array('Test2' => 'Qux'),
        ),
        array( // Array 3
          1 => array('Test3' => 'Bar'),
          2 => array('Test2' => 'Qux'),
        ),
      array( // Partial overwrite by array 2
        array( // Result
          1 => array('Test1' => 'Moo', 'Test2' => 'Foo', 'Test3' => 'Bar'),
          2 => array('Test1' => 'Baz', 'Test4' => 'Bam', 'Test2' => 'Qux'),
        ),
        array( // Array 1
          1 => array('Test1' => 'Moo', 'Test2' => '000', 'Test3' => '666'),
          2 => array('Test1' => 'Baz', 'Test4' => 'Bam'),
          ),
        array( // Array 2
          1 => array('Test2' => 'Foo', 'Test3' => 'Bar'),
          2 => array('Test2' => 'Qux'),
        ),
      ),
      ),
    );

    return $results;
  }

  /**
  * @dataProvider providerMatchNetwork
  */
  public function testMatchNetwork($result, $ip, $nets, $first = FALSE)
  {
    $this->assertSame($result, match_network($ip, $nets, $first));
  }

  public function providerMatchNetwork()
  {
    $nets1 = array('127.0.0.0/8', '192.168.0.0/16', '10.0.0.0/8', '172.16.0.0/12', '!172.16.6.7/32');
    $nets2 = array('fe80::/16', '!fe80:ffff:0:ffff:1:144:52:0/112', '192.168.0.0/16', '172.16.0.0/12', '!172.16.6.7/32');
    $nets3 = array('fe80::/16', 'fe80:ffff:0:ffff:1:144:52:0/112', '!fe80:ffff:0:ffff:1:144:52:0/112');
    $nets4 = array('172.16.0.0/12', '!172.16.6.7');
    $nets5 = array('fe80::/16', '!FE80:FFFF:0:FFFF:1:144:52:38');
    $nets6 = "I'm a stupid";

    $results = array(
      // Only IPv4 nets
      array(TRUE,  '127.0.0.1',  $nets1),
      array(FALSE, '1.1.1.1',    $nets1),       // not in ranges
      array(TRUE,  '172.16.6.6', $nets1),
      array(FALSE, '172.16.6.7', $nets1),       // excluded net
      array(TRUE,  '172.16.6.7', $nets1, TRUE), // excluded, but first match
      array(FALSE, '256.16.6.1', $nets1),       // wrong IP
      // Both IPv4 and IPv6
      array(FALSE, '1.1.1.1',    $nets2),
      array(TRUE,  '172.16.6.6', $nets2),
      array(TRUE,  'FE80:FFFF:0:FFFF:129:144:52:38', $nets2),
      array(FALSE, 'FE81:FFFF:0:FFFF:129:144:52:38', $nets2), // not in ranges
      array(FALSE, 'FE80:FFFF:0:FFFF:1:144:52:38',   $nets2), // excluded net
      // Only IPv6 nets
      array(FALSE, '1.1.1.1',    $nets3),
      array(FALSE, '172.16.6.6', $nets3),
      array(TRUE,  'FE80:FFFF:0:FFFF:129:144:52:38', $nets3),
      array(FALSE, 'FE81:FFFF:0:FFFF:129:144:52:38', $nets3),
      array(FALSE, 'FE80:FFFF:0:FFFF:1:144:52:38',   $nets3),
      array(TRUE,  'FE80:FFFF:0:FFFF:1:144:52:38',   $nets3, TRUE), // excluded, but first match
      // IPv4 net without mask
      array(TRUE,  '172.16.6.6', $nets4),
      array(FALSE, '172.16.6.7', $nets4),       // excluded net
      // IPv6 net without mask
      array(TRUE,  'FE80:FFFF:0:FFFF:129:144:52:38', $nets5),
      array(FALSE, 'FE81:FFFF:0:FFFF:129:144:52:38', $nets5),
      array(FALSE, 'FE80:FFFF:0:FFFF:1:144:52:38',   $nets5),
      array(TRUE,  'FE80:FFFF:0:FFFF:1:144:52:38',   $nets5, TRUE), // excluded, but first match
      // Are you stupid? YES :)
      array(FALSE, '172.16.6.6', $nets6),
      array(FALSE, 'FE80:FFFF:0:FFFF:129:144:52:38', $nets6),
    );
    return $results;
  }
}

// EOF
