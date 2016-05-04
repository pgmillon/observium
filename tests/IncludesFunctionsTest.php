<?php

include(dirname(__FILE__) . '/../includes/defaults.inc.php');
//include(dirname(__FILE__) . '/../config.php'); // Do not include user editable config here
include(dirname(__FILE__) . '/data/test_definitions.inc.php'); // Fake definitions for testing
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
  * @group numbers
  */
  public function testFloatCompare($a, $b, $epsilon, $result)
  {
    $this->assertSame($result, float_cmp($a, $b, $epsilon));
  }

  public function providerFloatCompare()
  {
    return array(
      // numeric tests
      array('330', '-2', NULL,  1), // $a > $b
      array('1',    '2', 0.1,  -1), // $a < $b
      array(-1,      -2, 0.1,   1), // $a > $b
      array(-1.1,  -1.4, 0.5,   0), // $a == $b
      array(-1.1,  -1.4, -0.5,  0), // $a == $b
      array((double)0, (double)70, 0.1, -1), // $a < $b and $a == 0
      array((double)70, (double)0, 0.1,  1), // $a > $b and $b == 0
      array((int)0.001, (double)0, NULL, 0), // $a == $b
      array(0.001,    0.000999999,  0.00001,  0), // $a == $b
      array(-0.001,  -0.000999999,  0.00001,  0), // $a == $b
      array(-0.001,  -0.000899999,  0.00001, -1), // $a < $b
      //array('-0.00000001', 0.00000002, NULL,  0), // $a == $b, FIXME, FALSE
      //array(0.00000002, '-0.00000001', NULL,  0), // $a == $b, FIXME, FALSE
      array(0.2, '-0.000000000001', NULL,  1), // $a == $b
      array(0.99999999, 1.00000002, NULL,  0), // $a == $b
      array(0.001,   -0.000999999,  NULL,  1), // $a > $b
      array(-0.000999999,   0.001,  NULL, -1), // $a < $b
      array(3672,   3888,           0.05,  0), // big numbers, greater epsilon
      array(3888,   3672,           0.05,  0), // big numbers, greater epsilon
      array(4000,   4810,            0.1,  0), // big numbers, greater epsilon
      array(4000,   4000.01,        NULL,  0), // big numbers

      /* Regular large numbers */
      array(1000000,      1000001,  NULL,  0),
      array(1000001,      1000000,  NULL,  0),
      array(10000,          10001,  NULL, -1),
      array(10001,          10000,  NULL,  1),
      /* Negative large numbers */
      array(-1000000,    -1000001,  NULL,  0),
      array(-1000001,    -1000000,  NULL,  0),
      array(-10000,        -10001,  NULL,  1),
      array(-10001,        -10000,  NULL, -1),
      /* Numbers around 1 */
      array(1.0000001,  1.0000002,  NULL,  0),
      array(1.0000002,  1.0000001,  NULL,  0),
      array(1.0002,        1.0001,  NULL,  1),
      array(1.0001,        1.0002,  NULL, -1),
      /* Numbers around -1 */
      array(-1.0000001,-1.0000002,  NULL,  0),
      array(-1.0000002,-1.0000001,  NULL,  0),
      array(-1.0002,      -1.0001,  NULL, -1),
      array(-1.0001,      -1.0002,  NULL,  1),
      /* Numbers between 1 and 0 */
      array(0.000000001000001,   0.000000001000002,  NULL,  0),
      array(0.000000001000002,   0.000000001000001,  NULL,  0),
      array(0.000000000001002,   0.000000000001001,  NULL,  1),
      array(0.000000000001001,   0.000000000001002,  NULL, -1),
      /* Numbers between -1 and 0 */
      array(-0.000000001000001, -0.000000001000002,  NULL,  0),
      array(-0.000000001000002, -0.000000001000001,  NULL,  0),
      array(-0.000000000001002, -0.000000000001001,  NULL, -1),
      array(-0.000000000001001, -0.000000000001002,  NULL,  1),
      /* Comparisons involving zero */
      array(0.0,              0.0,  NULL,  0),
      array(0.0,             -0.0,  NULL,  0),
      array(-0.0,            -0.0,  NULL,  0),
      array(0.00000001,       0.0,  NULL,  1),
      array(0.0,       0.00000001,  NULL, -1),
      array(-0.00000001,      0.0,  NULL, -1),
      array(0.0,      -0.00000001,  NULL,  1),

      array(0.0,     1.0E-10,        0.1,  0),
      array(1.0E-10,     0.0,        0.1,  0),
      array(1.0E-10,     0.0, 0.00000001,  1),
      array(0.0,     1.0E-10, 0.00000001, -1),

      array(0.0,    -1.0E-10,        0.1,  0),
      array(-1.0E-10,    0.0,        0.1,  0),
      array(-1.0E-10,    0.0, 0.00000001, -1),
      array(0.0,    -1.0E-10, 0.00000001,  1),
      /* Comparisons of numbers on opposite sides of 0 */
      array(1.000000001, -1.0,  NULL,  1),
      array(-1.0,   1.0000001,  NULL, -1),
      array(-1.000000001, 1.0,  NULL, -1),
      array(1.0, -1.000000001,  NULL,  1),
      /* Comparisons involving extreme values (overflow potential) */
      array(PHP_INT_MAX,  PHP_INT_MAX,  NULL,  0),
      array(PHP_INT_MAX, -PHP_INT_MAX,  NULL,  1),
      array(-PHP_INT_MAX, PHP_INT_MAX,  NULL, -1),
      array(PHP_INT_MAX,  PHP_INT_MAX / 2, NULL,  1),
      array(PHP_INT_MAX, -PHP_INT_MAX / 2, NULL,  1),
      array(-PHP_INT_MAX, PHP_INT_MAX / 2, NULL, -1),

      // other tests
      array('test',       'milli', 1.194,  1),
      array(array('NULL'),    '0',  0.01,  1),
      array(array('NULL'), array('NULL'), NULL, 0),
    );
  }

  /**
  * @dataProvider providerIsHexString
  * @group hex
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
  * @group hex
  */
  public function testSNMPHexString($string, $result)
  {
    $this->assertSame($result, snmp_hexstring($string));
  }

  public function providerSNMPHexString()
  {
    $results = array(
      array('49 6E 70 75 74 20 31 00 ', 'Input 1'),
      array('49 6E 70 75 74 20 31',     'Input 1'),
      array('4A 7D 34 3D',              'J}4='),
      array('49 6E 70 75 74 20 31 0',   '49 6E 70 75 74 20 31 0'),
      array('Simple String',            'Simple String'),
      array('49 6E 70 75 74 20 31 0R ', '49 6E 70 75 74 20 31 0R ')
    );
    return $results;
  }

  /**
  * @dataProvider providerStr2Hex
  * @group hex
  */
  public function testStr2Hex($string, $result)
  {
    $this->assertSame($result, str2hex($string));
  }

  public function providerStr2Hex()
  {
    $results = array(
      array(' ',              '20'),
      array('Input 1',        '496e7075742031'),
      array('J}4=',           '4a7d343d'),
      array('Simple String',  '53696d706c6520537472696e67'),
    );
    return $results;
  }

  /**
  * @dataProvider providerHex2IP
  * @group ip
  */
  public function testHex2IP($string, $result)
  {
    $this->assertSame($result, hex2ip($string));
  }

  public function providerHex2IP()
  {
    $results = array(
      // IPv4
      array('C1 9C 5A 26',  '193.156.90.38'),
      array('4a7d343d',     '74.125.52.61'),
      array('207d343d',     '32.125.52.61'),
      // IPv4 (converted to snmp string)
      array('J}4=',         '74.125.52.61'),
      array('J}4:',         '74.125.52.58'),
      array('    ',         '32.32.32.32'),
      // IPv6
      array('20 01 07 F8 00 12 00 01 00 00 00 00 00 05 02 72',  '2001:07f8:0012:0001:0000:0000:0005:0272'),
      array('20:01:07:F8:00:12:00:01:00:00:00:00:00:05:02:72',  '2001:07f8:0012:0001:0000:0000:0005:0272'),
      array('200107f8001200010000000000050272',                 '2001:07f8:0012:0001:0000:0000:0005:0272'),
      // Wrong data
      array('4a7d343dd',                        '4a7d343dd'),
      array('200107f800120001000000000005027',  '200107f800120001000000000005027'),
      array('193.156.90.38',                    '193.156.90.38'),
      array('Simple String',                    'Simple String'),
      array('',  ''),
      array(FALSE,  FALSE),
    );
    return $results;
  }

  /**
  * @dataProvider providerIp2Hex
  * @group ip
  */
  public function testIp2Hex($string, $separator, $result)
  {
    $this->assertSame($result, ip2hex($string, $separator));
  }

  public function providerIp2Hex()
  {
    $results = array(
      // IPv4
      array('193.156.90.38', ' ', 'c1 9c 5a 26'),
      array('74.125.52.61',  ' ', '4a 7d 34 3d'),
      array('74.125.52.61',   '', '4a7d343d'),
      // IPv6
      array('2001:07f8:0012:0001:0000:0000:0005:0272', ' ', '20 01 07 f8 00 12 00 01 00 00 00 00 00 05 02 72'),
      array('2001:7f8:12:1::5:0272',                   ' ', '20 01 07 f8 00 12 00 01 00 00 00 00 00 05 02 72'),
      array('2001:7f8:12:1::5:0272',                    '', '200107f8001200010000000000050272'),
      // Wrong data
      array('4a7d343dd',                       NULL, '4a7d343dd'),
      array('200107f800120001000000000005027', NULL, '200107f800120001000000000005027'),
      array('300.156.90.38',                   NULL, '300.156.90.38'),
      array('Simple String',                   NULL, 'Simple String'),
      array('',    NULL, ''),
      array(FALSE, NULL, FALSE),
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
      array('mge-status-state',           'Banana', FALSE),
      array('inexistent-status-state',    'Vanilla', FALSE),
      array('radlan-hwenvironment-state', 'notFunctioning', 6),
      array('radlan-hwenvironment-state', 'notFunctioning ', 6),
      array('cisco-envmon-state',         'warning', 2),
      array('cisco-envmon-state',         'war ning', FALSE),
      array('powernet-sync-state',        'inSync', 1),
      // Numeric value
      array('cisco-envmon-state',         '2', 2),
      array('cisco-envmon-state',          2, 2),
      array('cisco-envmon-state',         '2.34', FALSE),
      array('cisco-envmon-state',          10, FALSE),
    );
    return $results;
  }

  /**
  * @dataProvider providerPriorityStringToNumeric
  */
  public function testPriorityStringToNumeric($value, $result)
  {
    $this->assertSame($result, priority_string_to_numeric($value));
  }

  public function providerPriorityStringToNumeric()
  {
    $results = array(
      // Named value
      array('emerg',    0),
      array('alert',    1),
      array('crit',     2),
      array('err',      3),
      array('warning',  4),
      array('notice',   5),
      array('info',     6),
      array('debug',    7),
      array('DeBuG',    7),
      // Numeric value
      array('0',        0),
      array('7',        7),
      array(8,          8),
      // Wrong value
      array('some',    15),
      array(array(),   15),
      array(0.1,       15),
      array('100',     15),
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
  * @group ip
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

  /**
  * @dataProvider providerIsPingable
  * @group network
  */
  public function testIsPingable($result, $hostname, $try_a = TRUE)
  {
    $flags = OBS_DNS_ALL;
    if (!$try_a) { $flags = $flags ^ OBS_DNS_A; }
    $ping = isPingable($hostname, $flags);
    $ping = is_numeric($ping) && $ping > 0; // Function return random float number
    $this->assertSame($result, $ping);
  }

  public function providerIsPingable()
  {
    $array = array(
      array(TRUE,  'localhost'),
      array(TRUE,  '127.0.0.1'),
      array(FALSE, 'yohoho.i.butylka.roma'),
      array(FALSE, '127.0.0.1', FALSE), // Try ping IPv4 with IPv6 disabled
    );
    $cmd = $GLOBALS['config']['fping6'] . " -c 1 -q ::1 2>&1";
    exec($cmd, $output, $return); // Check if we have IPv6 support in current system
    if ($return === 0)
    {
      // IPv6 only
      $array[] = array(TRUE,  'localhost', FALSE);
      $array[] = array(TRUE,  '::1',       FALSE);
      $array[] = array(FALSE, '::ffff',    FALSE);
    }
    return $array;
  }

  /**
  * @dataProvider providerGetDeviceOS
  * @group snmp
  */
  public function testGetDeviceOS($result, $old_os, $sysObjectID, $sysDescr)
  {
    $device = array('device_id'      => 0,
                    'disabled'       => 0,
                    'ignore'         => 0,
                    'status'         => 1,
                    'snmp_version'   => 'v2c',
                    'snmp_community' => 'test',
                    'snmp_port'      => 161,
                    'snmp_timeout'   => 1,
                    'snmp_retries'   => 0,
                    'hostname'       => 'example.test',
                    'os'             => $old_os,
                    );
    $fake_data  = 'sysDescr.0 = '.$sysDescr.PHP_EOL;
    $fake_data .= 'sysObjectID.0 = '.$sysObjectID;
    $GLOBALS['config']['snmpget'] = dirname(__FILE__) . '/data/snmpfake.sh fakedata '.escapeshellarg($fake_data); //.' -d';
    $os = get_device_os($device);
    $this->assertSame($result, $os);
  }

  public function providerGetDeviceOS()
  {
    $array = array(
      array('procurve', '', '.1.3.6.1.4.1.11.2.3.7.11.104',       'HP ProCurve 1810G - 24 GE, P.2.2, eCos-2.0, CFE-2.1'),
      array('hpvc',     '', '.1.3.6.1.4.1.11.2.3.7.11.33.4.1.1',  'GbE2c L2/L3 Ethernet Blade Switch for HP c-Class BladeSystem'),
      array('hpvc',     '', '.1.3.6.1.4.1.11.5.7.5.1',            'HP VC Flex-10 Enet Module Virtual Connect 3.18 '),
    );
    return $array;
  }
}

// EOF
