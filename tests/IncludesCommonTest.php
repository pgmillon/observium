<?php

include(dirname(__FILE__) . '/../includes/defaults.inc.php');
include(dirname(__FILE__) . '/../config.php');
include(dirname(__FILE__) . '/../includes/definitions.inc.php');
include(dirname(__FILE__) . '/../includes/functions.inc.php');

class IncludesCommonTest extends PHPUnit_Framework_TestCase
{
  /**
   * @dataProvider providerAgeToSeconds
   */
  public function testAgeToSeconds($value, $result)
  {
    $this->assertSame($result, age_to_seconds($value));
  }

  public function providerAgeToSeconds()
  {
    return array(
      array('3y 4M 6w 5d 3h 1m 3s',  109191663),
      array('3y4M6w5d3h1m3s',        109191663),
      array('1.5w',                     907200),
      array(-886732,                         0),
      array('Star Wars',                     0),
    );
  }

  /**
   * @dataProvider providerAgeToUnixtime
   */
  public function testAgeToUnixtime($value, $min_age, $result)
  {
    // We fudge this a little since it's difficult to mock time().
    // We simply make sure that we are not off by more than 2 secs.
    $this->assertLessThanOrEqual(2, age_to_unixtime($value, $min_age) - $result);
  }

  public function providerAgeToUnixtime()
  {
    return array(
      array('3y 4M 6w 5d 3h 1m 3s', 1, time() - 109191663),
      array('3y4M6w5d3h1m3s',       1, time() - 109191663),
      array('1.5w',                 1,    time() - 907200),
      array('30m',               7200,                  0),
      array(-886732,                1,                  0),
      array('Star Wars',            1,                  0),
    );
  }

  /**
   * @dataProvider providerExternalExec
   * @group exec
   */
  public function testExternalExec($cmd, $timeout, $result)
  {
    $test = external_exec($cmd, $timeout);
    unset($GLOBALS['exec_status']['runtime']);
    $this->assertSame($result, $GLOBALS['exec_status']);
  }

  public function providerExternalExec()
  {
    return array(
      // normal stdout
      array('/bin/which true',
            NULL,
            array('command'  => '/bin/which true',
                  'exitcode' => 0,
                  'stderr'   => '',
                  'stdout'   => '/bin/true')
            ),
      // here generate stderr
      array('/bin/which true >&2',
            NULL,
            array('command'  => '/bin/which true >&2',
                  'exitcode' => 0,
                  'stderr'   => '/bin/true',
                  'stdout'   => '')
            ),
      // normal stdout, but exitcode 1
      array('/bin/false',
            NULL,
            array('command'  => '/bin/false',
                  'exitcode' => 1,
                  'stderr'   => '',
                  'stdout'   => '')
            ),
      // real stdout, exit code 127
      array('/bin/jasdhksdhka',
            NULL,
            array('command'  => '/bin/jasdhksdhka',
                  'exitcode' => 127,
                  'stderr'   => 'sh: 1: /bin/jasdhksdhka: not found',
                  'stdout'   => '')
            ),
      // normal stdout with special chars (tabs, eol in eof)
      array('/bin/cat '.dirname(__FILE__).'/data/text.txt',
            NULL,
            array('command'  => '/bin/cat '.dirname(__FILE__).'/data/text.txt',
                  'exitcode' => 0,
                  'stderr'   => '',
                  'stdout'   =>
"Observium is an autodiscovering network monitoring platform
\tsupporting a wide range of hardware platforms and operating systems
\tincluding Cisco, Windows, Linux, HP, Juniper, Dell, FreeBSD, Brocade,
\tNetscaler, NetApp and many more.

 Observium seeks to provide a powerful yet simple and intuitive interface
 to the health and status of your network.

~!@#$%^&*()_+`1234567890-=[]\{}|;':\",./<>?

")
            ),
      // timeout 5sec, ok
      array('/bin/sleep 1',
            5,
            array('command'  => '/bin/sleep 1',
                  'exitcode' => 0,
                  'stderr'   => '',
                  'stdout'   => '')
            ),
      // timeout 2sec, expired, exitcode -1
      array('/bin/sleep 10',
            1,
            array('command'  => '/bin/sleep 10',
                  'exitcode' => -1,
                  'stderr'   => '',
                  'stdout'   => '')
            ),
    );
  }

  /**
   * @dataProvider providerPercentClass
   */
  public function testPercentClass($value, $result)
  {
    $this->assertSame($result, percent_class($value));
  }

  public function providerPercentClass()
  {
    return array(
      array(  24,      'info'), // if < 25
      array(  25,          ''), // if < 50
      array(  49,          ''),
      array(  50,   'success'), // if < 75
      array(  74,   'success'),
      array(  75,   'warning'), // if < 90
      array(  89,   'warning'),
      array(  90,    'danger'), // else

      array('24',      'info'), // string input
      array(24.0,      'info'), // float input
    );
  }

  /**
   * @dataProvider providerPercentColour
   */
  public function testPercentColour($value, $brightness, $result)
  {
    if ($brightness === NULL) {
      $this->assertSame($result, percent_colour($value));
    } else {
      $this->assertSame($result, percent_colour($value, $brightness));
    }
  }

  public function providerPercentColour()
  {
    return array(
      array(0,    NULL, '#008000'), // default brightness
      array(20,   NULL, '#338000'),
      array(40,   NULL, '#668000'),
      array(60,   NULL, '#806600'),
      array(80,   NULL, '#803300'),
      array(100,  NULL, '#800000'),
      array(0,      64, '#004000'), // brightness = 64
      array(20,     64, '#194000'),
      array(40,     64, '#334000'),
      array(60,     64, '#403300'),
      array(80,     64, '#401900'),
      array(100,    64, '#400000'),
      array(0,     192, '#00c000'), // brightness = 192
      array(20,    192, '#4cc000'),
      array(40,    192, '#99c000'),
      array(60,    192, '#c09900'),
      array(80,    192, '#c04c00'),
      array(100,   192, '#c00000'),
      array(0,     255, '#00ff00'), // brightness = 256
      array(20,    255, '#66ff00'),
      array(40,    255, '#ccff00'),
      array(60,    255, '#ffcc00'),
      array(80,    255, '#ff6500'),
      array(100,   255, '#ff0000'),
    );
  }

  /**
   * @dataProvider providerTimeticksToSec
   */
  public function testTimeticksToSec($value, $float, $result)
  {
    $this->assertSame($result, timeticks_to_sec($value, $float));
  }

  public function providerTimeticksToSec()
  {
    return array(
      array('1:2:34:56.78', FALSE,    95696), // ints
      array('(95696)',      FALSE,    95696),
      array('1:2:34:56.78', TRUE,  95696.78), // floats
      array('(95696)',      TRUE,   95696.0),
    );
  }

  /**
   * @dataProvider providerDeviceUptime
   */
  public function testDeviceUptime($value, $result)
  {
    $this->assertSame($result, deviceUptime($value));
  }

  public function providerDeviceUptime()
  {
    return array(
      array(array('status' => 0, 'last_polled' =>        0),  'Never polled'),
      array(array('status' => 0, 'last_polled' => '-1 day'),  'Down 1 day'),
      array(array('status' => 1, 'uptime'      =>   '3600'),  '1h'),
    );
  }

  /**
   * @dataProvider providerFormatUptime
   */
  public function testFormatUptime($value, $format, $result)
  {
    $this->assertSame($result, formatUptime($value, $format));
  }

  public function providerFormatUptime()
  {
    return array(
      array(       0, 'long',     '0s'),                          // zero

      // format = long
      array(       1, 'long',     '1s'),                          // singulars
      array(      60, 'long',     '1m'),
      array(    3600, 'long',     '1h'),
      array(   86400, 'long',     '1 day'),
      array(31536000, 'long',     '1 year'),
      array(   90061, 'long',     '1 day, 1h 1m 1s'),
      array(31626061, 'long',     '1 year, 1 day, 1h 1m 1s'),

      array(    3661, 'long',     '1h 1m 1s'),                    // h/m/s mixins
      array(      61, 'long',     '1m 1s'),
      array(    3601, 'long',     '1h 1s'),
      array(    3660, 'long',     '1h 1m'),

      array(  176461, 'long',     '2 days, 1h 1m 1s'),            // plurals
      array(63162061, 'long',     '2 years, 1 day, 1h 1m 1s'),

      // format = longest
      array(       1, 'longest',  '1 second'),                    // singulars
      array(      60, 'longest',  '1 minute'),
      array(    3600, 'longest',  '1 hour'),

      array(    3661, 'longest',  '1 hour 1 minute 1 second'),    // h/m/s mixins
      array(      61, 'longest',  '1 minute 1 second'),
      array(    3601, 'longest',  '1 hour 1 second'),
      array(    3660, 'longest',  '1 hour 1 minute'),

      array(    7322, 'longest',  '2 hours 2 minutes 2 seconds'), // plural

      // format = short-3
      array(       1, 'short-3',  '1s'),                          // singulars
      array(      60, 'short-3',  '1m'),
      array(    3600, 'short-3',  '1h'),
      array(   86400, 'short-3',  '1d'),
      array(31536000, 'short-3',  '1y'),

      array(      61, 'short-3',  '1m 1s'),                       // minute mixins

      array(    3601, 'short-3',  '1h 1s'),                       // hour mixins
      array(    3660, 'short-3',  '1h 1m'),
      array(    3661, 'short-3',  '1h 1m 1s'),

      array(   86401, 'short-3',  '1d 1s'),                       // day mixins
      array(   86460, 'short-3',  '1d 1m'),
      array(   86461, 'short-3',  '1d 1m 1s'),
      array(   90000, 'short-3',  '1d 1h'),
      array(   90001, 'short-3',  '1d 1h 1s'),
      array(   90060, 'short-3',  '1d 1h 1m'),

      array(31536001, 'short-3',  '1y 1s'),                       // year mixins
      array(31536060, 'short-3',  '1y 1m'),
      array(31536061, 'short-3',  '1y 1m 1s'),
      array(31622400, 'short-3',  '1y 1d'),
      array(31622401, 'short-3',  '1y 1d 1s'),
      array(31622460, 'short-3',  '1y 1d 1m'),
      array(31626000, 'short-3',  '1y 1d 1h'),

      // format = short-2
      array(       1, 'short-2',  '1s'),                          // singulars
      array(      60, 'short-2',  '1m'),
      array(    3600, 'short-2',  '1h'),
      array(   86400, 'short-2',  '1d'),
      array(31536000, 'short-2',  '1y'),

      array(      61, 'short-2',  '1m 1s'),                       // minute mixins

      array(    3601, 'short-2',  '1h 1s'),                       // hour mixins
      array(    3660, 'short-2',  '1h 1m'),

      array(   86401, 'short-2',  '1d 1s'),                       // day mixins
      array(   86460, 'short-2',  '1d 1m'),
      array(   90000, 'short-2',  '1d 1h'),

      array(31536001, 'short-2',  '1y 1s'),                       // year mixins
      array(31536060, 'short-2',  '1y 1m'),
      array(31622400, 'short-2',  '1y 1d'),

      // format = shorter (should get same results as short-2)
      array(       1, 'shorter',  '1s'),                          // singulars
      array(      60, 'shorter',  '1m'),
      array(    3600, 'shorter',  '1h'),
      array(   86400, 'shorter',  '1d'),
      array(31536000, 'shorter',  '1y'),

      array(      61, 'shorter',  '1m 1s'),                       // minute mixins

      array(    3601, 'shorter',  '1h 1s'),                       // hour mixins
      array(    3660, 'shorter',  '1h 1m'),

      array(   86401, 'shorter',  '1d 1s'),                       // day mixins
      array(   86460, 'shorter',  '1d 1m'),
      array(   90000, 'shorter',  '1d 1h'),

      array(31536001, 'shorter',  '1y 1s'),                       // year mixins
      array(31536060, 'shorter',  '1y 1m'),
      array(31622400, 'shorter',  '1y 1d'),

    );
  }

  /**
   * @dataProvider providerHumanspeed
   */
  public function testHumanspeed($value, $result)
  {
    $this->assertSame($result, humanspeed($value));
  }

  public function providerHumanspeed()
  {
    return array(
      array(     '',         '-'),
      array(1024000,  '1.02Mbps'),
    );
  }

  /**
   * @dataProvider providerFormatMac
   */
  public function testFormatMac($value, $result)
  {
    $this->assertSame($result, format_mac($value));
  }

  public function providerFormatMac()
  {
    return array(
      array(     '123456789ABC', '12:34:56:78:9a:bc'),
      array(   '1234.5678.9abc', '12:34:56:78:9a:bc'),
      array('12:34:56:78:9a:bc', '12:34:56:78:9a:bc'),

      // Fake MAC to IPv4 (for 6to4 tunnels)
      array('ff:fe:56:78:9a:bc',    '86.120.154.188'),
      array('ff:fe:00:00:9a:bc',       '154.188.X.X'),
    );
  }

  /**
   * @dataProvider providerFormatNumberShort
   */
  public function testFormatNumberShort($value, $sf, $result)
  {
    $this->assertSame($result, format_number_short($value, $sf));
  }

  public function providerFormatNumberShort()
  {
    return array(
      array( '12345', 3,  '12345'),
      array('1234.5', 3,   '1234'),
      array('123.45', 3,    '123'),
      array('12.345', 3,   '12.3'),
      array('1.2345', 3,   '1.23'),
      array('.12345', 3,   '.123'),
      array('0.1234', 3,   '0.12'),

      array('-1.234', 3,   '-1.2'),

      array('1.234b', 3,      '1'), // alpha in decimals
    );
  }

  /**
   * @dataProvider providerSgn
   */
  public function testSgn($value, $result)
  {
    $this->assertSame($result, sgn($value));
  }

  public function providerSgn()
  {
    return array(
      array( 10,  1),
      array(  0,  0),
      array(-10, -1),
    );
  }

  /**
   * @dataProvider providerGetSensorRrd
   */
  public function testGetSensorRrd($device, $sensor, $config, $result)
  {
    $GLOBAL['config'] = $config;
    $this->assertSame($result, get_sensor_rrd($device, $sensor));
  }

  public function providerGetSensorRrd()
  {
    return array(
      array(array('os' => 'ios'), // device
            array('poller_type' => 'snmp', 'sensor_class' => 'class', 'sensor_type' => 'type', 'sensor_descr' => 'descr', 'sensor_index' => 4), // sensor
            array('os' => array('ios' => array('sensor_descr' => 'temperature'))), // config
            'sensor-class-type-4.rrd', // result
          ),
      array(array('os' => 'oh-es'), // device
            array('poller_type' => 'ipmi', 'sensor_class' => 'class', 'sensor_type' => 'type', 'sensor_descr' => 'descr', 'sensor_index' => 4), // sensor
            array('os' => array('oh-es' => array('sensor_descr' => 'temperature'))), // config
            'sensor-class-type-descr.rrd', // result
          ),
    );
  }

  /**
   * @dataProvider providerIfclass
   */
  public function testIfclass($ifOperStatus, $ifAdminStatus, $result)
  {
    $this->assertSame($result, ifclass($ifOperStatus, $ifAdminStatus));
  }

  public function providerIfclass()
  {
    return array(
      array(             '-',   'up', 'interface-upup'),
      array(            'up', 'down',           'gray'),
      array(          'down',   'up',            'red'),
      array('lowerLayerDown',   'up',         'orange'),
      array(    'monitoring',   'up',          'green'),
      array(            'up',   'up',               ''),
    );
  }

  /**
   * @dataProvider providerTruncate
   */
  public function testTruncate($value, $max, $rep = '...', $result)
  {
    $this->assertSame($result, truncate($value, $max, $rep));
  }

  public function providerTruncate()
  {
    return array(
      array('Observium is an autodiscovering network monitoring platform', 19, '...',
            'Observium is an ...'),
      array('Observium is an autodiscovering network monitoring platform', 19, '???',
            'Observium is an ???'),
    );
  }

  /**
   * @dataProvider providerGenerateRandomString
   */
  public function testGenerateRandomString($len, $chars, $regex)
  {
    $rv = generate_random_string($len, $chars);

    $this->assertRegExp($regex, $rv);
    $this->assertTrue($len == strlen($rv));
  }

  public function providerGenerateRandomString()
  {
    return array(
      array(96, NULL, '/^[[:alnum:]]+$/'),
      array(96, '1234567890', '/^\d+$/'),
    );
  }

  /**
   * @dataProvider providerSafename
   */
  public function testSafename($value, $result)
  {
    $this->assertSame($result, safename($value));
  }

  public function providerSafename()
  {
    return array(
      array('aA0,._-',  'aA0,._-'), // all good
      array('\\\'',     '__'),
      array('`~!@#$%^&*()=+{}[]|";:/?<>',  '__________________________'),
    );
  }

  /**
   * @dataProvider providerZeropad
   */
  public function testZeropad($value, $result)
  {
    $this->assertSame($result, zeropad($value));
  }

  public function providerZeropad()
  {
    return array(
      array(1,     '01'),
      array(100,  '100'),
    );
  }

  /**
   * @dataProvider providerFormatRates
   */
  public function testFormatRates($value, $round, $sf, $result)
  {
    $this->assertSame($result, formatRates($value, $round, $sf));
  }

  public function providerFormatRates()
  {
    return array(
      // simple test; most testing is done against format_si
      array(10240000, 2, 3,  '10.2Mbps'),

      // round & sf
      array(10240000, 4, 4, '10.24Mbps'),
    );
  }

  /**
   * @dataProvider providerFormatStorage
   */
  public function testFormatStorage($value, $round, $sf, $result)
  {
    $this->assertSame($result, formatStorage($value, $round, $sf));
  }

  public function providerFormatStorage()
  {
    return array(
      // simple test; most testing is done against format_bi
      array(102400000, 2, 3,  '97.6MB'),

      // round & sf
      array(102400000, 4, 4, '97.65MB'),
    );
  }

  /**
   * @dataProvider providerFormatSi
   */
  public function testFormatSi($value, $round, $sf, $result)
  {
    $this->assertSame($result, format_si($value, $round, $sf));
  }

  public function providerFormatSi()
  {
    return array(
      // return int
      array(                   1, 2, 3,      '1'),
      array(                1000, 2, 3,     '1k'),
      array(               10000, 2, 3,    '10k'),
      array(              100000, 2, 3,   '100k'),
      array(             1000000, 2, 3,     '1M'),
      array(            10000000, 2, 3,    '10M'),
      array(           100000000, 2, 3,   '100M'),
      array(          1000000000, 2, 3,     '1G'),
      array(         10000000000, 2, 3,    '10G'),
      array(        100000000000, 2, 3,   '100G'),
      array(       1000000000000, 2, 3,     '1T'),
      array(    1000000000000000, 2, 3,     '1P'),
      array( 1000000000000000000, 2, 3,     '1E'),

      // return float
      array(                1100, 2, 3,   '1.1k'),
      array(             1100000, 2, 3,   '1.1M'),
      array(            10100000, 2, 3,  '10.1M'),
      array(          1100000000, 2, 3,   '1.1G'),
      array(       1100000000000, 2, 3,   '1.1T'),
      array(    1100000000000000, 2, 3,   '1.1P'),
      array( 1100000000000000000, 2, 3,   '1.1E'),

      // return negative
      array(               -1000, 2, 3,    '-1k'),
      array(              -10000, 2, 3,   '-10k'),
      array(             -100000, 2, 3,  '-100k'),
      array(            -1000000, 2, 3,    '-1M'),
      array(           -10000000, 2, 3,   '-10M'),
      array(          -100000000, 2, 3,  '-100M'),
      array(         -1000000000, 2, 3,    '-1G'),
      array(        -10000000000, 2, 3,   '-10G'),
      array(       -100000000000, 2, 3,  '-100G'),
      array(      -1000000000000, 2, 3,    '-1T'),
      array(   -1000000000000000, 2, 3,    '-1P'),
      array(-1000000000000000000, 2, 3,    '-1E'),

      // check base 1024
      array(                1024, 2, 3,   '1.02k'),
      array(             1024000, 2, 3,   '1.02M'),

      // round & sf
      array(             1024000, 4, 4,  '1.024M'),
    );
  }

  /**
   * @dataProvider providerFormatBi
   */
  public function testFormatBi($value, $round, $sf, $result)
  {
    $this->assertSame($result, format_bi($value, $round, $sf));
  }

  public function providerFormatBi()
  {
    return array(
      // return int
      array(                   1, 2, 3,      '1'),
      array(                1024, 2, 3,     '1k'),
      array(               10240, 2, 3,    '10k'),
      array(              102400, 2, 3,   '100k'),
      array(             1048576, 2, 3,     '1M'),
      array(            10485760, 2, 3,    '10M'),
      array(           104857600, 2, 3,   '100M'),
      array(          1073741824, 2, 3,     '1G'),
      array(         10737418240, 2, 3,    '10G'),
      array(        107374182400, 2, 3,   '100G'),
      array(       1099511627776, 2, 3,     '1T'),
      array(    1125899906842624, 2, 3,     '1P'),
      array( 1152921504606846976, 2, 3,     '1E'),

      // return float
      array(                1126, 2, 3,   '1.1k'),
      array(             1153466, 2, 3,   '1.1M'),
      array(            10590617, 2, 3,  '10.1M'),
      array(          1181116006, 2, 3,   '1.1G'),
      array(       1209462790553, 2, 3,   '1.1T'),
      array(    1238489897526886, 2, 3,   '1.1P'),
      array( 1268213655067531673, 2, 3,   '1.1E'),

      // negative
      array(                  -1, 2, 3,     '-1'),
      array(               -1024, 2, 3,    '-1k'),
      array(              -10240, 2, 3,   '-10k'),
      array(             -102400, 2, 3,  '-100k'),
      array(            -1048576, 2, 3,    '-1M'),
      array(           -10485760, 2, 3,   '-10M'),
      array(          -104857600, 2, 3,  '-100M'),
      array(         -1073741824, 2, 3,    '-1G'),
      array(        -10737418240, 2, 3,   '-10G'),
      array(       -107374182400, 2, 3,  '-100G'),
      array(      -1099511627776, 2, 3,    '-1T'),
      array(   -1125899906842624, 2, 3,    '-1P'),
      array(-1152921504606846976, 2, 3,    '-1E'),

      // check base 1000
      array(                1000, 2, 3,    '1000'),
      array(             1000000, 2, 3,    '976k'),

      // round & sf
      array(           105000000, 4, 4,  '100.1M'),
      array(           102400000, 4, 4,  '97.65M'),
    );
  }

  /**
   * @dataProvider providerFormatNumber
   */
  public function testFormatNumber($value, $base, $round, $sf, $result)
  {
    $this->assertSame($result, format_number($value, $base, $round, $sf));
  }

  public function providerFormatNumber()
  {
    return array(
      // simple base 1000 tests; most testing is done against format_si
      array( 10240000, '1000', 2, 3,  '10.2M'), // string base
      array( 10240000,   1000, 2, 3,  '10.2M'), // int base
      array( 10240000,   1000, 4, 4, '10.24M'), // round and sf

      // simple base 1024 tests; most testing is done against format_bi
      array(102400000, '1024', 2, 3,  '97.6M'), // string base
      array(102400000,   1024, 2, 3,  '97.6M'), // int base
      array(102400000,   1024, 4, 4, '97.65M'), // round and sf
    );
  }

  /**
   * @dataProvider providerIsValidHostname
   */
  public function testIsValidHostname($value, $result)
  {
    $this->assertSame($result, is_valid_hostname($value));
  }

  public function providerIsValidHostname()
  {
    return array(
      array('router1',          TRUE),
      array('1router',          TRUE),
      array('router-1',         TRUE),
      array('router_1',         TRUE),
      array('router.1',         TRUE),

      array('router1.a.com',    TRUE),
      array('1router.a.com',    TRUE),
      array('router-1.a.com',   TRUE),
      array('router_1.a.com',   TRUE),
      array('router.1.a.com',   TRUE),

      array('-router1',        FALSE),
      array('_router1',        FALSE),
      array('.router1',        FALSE),

      array('router~1',        FALSE),
      array('router/1',        FALSE),
      array('router,1',        FALSE),
      array('router;1',        FALSE),
      array('router 1',        FALSE),
    );
  }

  /**
   * @dataProvider providerFormatTimestamp
   */
  public function testFormatTimestamp($value, $result)
  {
    $this->assertSame($result, format_timestamp($value));
  }

  public function providerFormatTimestamp()
  {
    return array(
      array('Aug 30 2014',      '2014-08-30 00:00:00'),
      array('2012-04-18 14:25', '2012-04-18 14:25:00'),
      array('Star Wars',        'Star Wars'),
    );
  }

  /**
   * @dataProvider providerFormatUnixtime
   */
  public function testFormatUnixtime($value, $format, $result)
  {
    // override local timezone settings or these tests may fail
    date_default_timezone_set('UTC');
    $this->assertSame($result, format_unixtime($value, $format));
  }

  public function providerFormatUnixtime()
  {
    return array(
      array(1409397693,  NULL, '2014-08-30 11:21:33'),
      array(1409397693,  DATE_RFC2822, 'Sat, 30 Aug 2014 11:21:33 +0000'),
    );
  }

  /**
   * @dataProvider providerUnitStringToNumeric
   */
  public function testUnitStringToNumeric($value, $result)
  {
    $this->assertSame($result, unit_string_to_numeric($value));
  }

  public function providerUnitStringToNumeric()
  {
    $results = array(
      array('Sweet',                             'Sweet'), // String should stay string
      array('5',                                     5.0), // Numeric string should become int
      array('5.3',                                   5.3), // Numeric string should become float
      array('12b',                                  12.0),
      array('12B',                                  12.0),
      array('16bit',                                16.0),
      array('666bps',                              666.0),
      array('24Byte',                               24.0),
      array('32 byte',                              32.0), // A single space also works
      array('48bytes',                              48.0),
      array('1500Bps',                            1500.0),
      array('1440kB',                        1440*1024.0),
      array('2000k',                         2000*1024.0),
      array('60 kByte',                        60*1024.0),
      array('20 kbyte',                        20*1024.0),
      array('200kbps',                        200*1000.0), // Communication is 1000-based
      array('5000kbit',                      5000*1000.0),
      array('64kb',                            64*1000.0),
      array('16kBps',                          16*1000.0),
      array('200kbps',                        200*1000.0),
      array('50M',                        50*1024*1024.0),
      array('26MB',                       26*1024*1024.0),
      array('12.5MB',                   12.5*1024*1024.0),
      array('42 MByte',                   42*1024*1024.0),
      array('1 Mbyte',                     1*1024*1024.0),
      array('15Mb',                       15*1000*1000.0),
      array('199MBps',                   199*1000*1000.0),
      array('500Mbit',                   500*1000*1000.0),
      array('1500Mbps',                 1500*1000*1000.0),
      array('10G',                   10*1024*1024*1024.0),
      array('0GB',                    0*1024*1024*1024.0),
      array('6GByte',                 6*1024*1024*1024.0),
      array('3Gbyte',                 3*1024*1024*1024.0),
      array('2Gb',                    2*1000*1000*1000.0),
      array('2.1Gb',                2.1*1000*1000*1000.0), // Test decimal support
      array('5GBps',                  5*1000*1000*1000.0),
      array('15Gbit',                15*1000*1000*1000.0),
      array('7Gbps',                  7*1000*1000*1000.0),
      array('2T',                2*1024*1024*1024*1024.0),
      array('3TB',               3*1024*1024*1024*1024.0),
      array('5TByte',            5*1024*1024*1024*1024.0),
      array('12Tbyte',          12*1024*1024*1024*1024.0),
      array('6Tb',               6*1000*1000*1000*1000.0),
      array('9 TBps',            9*1000*1000*1000*1000.0),
      array('3 Tbit',            3*1000*1000*1000*1000.0),
      array('3.5 Tbit',        3.5*1000*1000*1000*1000.0),
      array('5 Tbps',            5*1000*1000*1000*1000.0),
    );
    return $results;
  }
}

// EOF
