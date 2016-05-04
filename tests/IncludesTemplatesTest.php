<?php

// Base observium includes
include(dirname(__FILE__) . '/../includes/defaults.inc.php');
//include(dirname(__FILE__) . '/../config.php'); // Do not include user editable config here
//include(dirname(__FILE__) . '/data/test_definitions.inc.php'); // Fake definitions for testing
include(dirname(__FILE__) . '/../includes/definitions.inc.php');
include(dirname(__FILE__) . '/../includes/functions.inc.php');

class IncludesTemplatesTest extends PHPUnit_Framework_TestCase
{
  /**
  * @dataProvider providerSimpleTemplate
  * @group simple_template
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

  /**
  * @dataProvider providerArrayToXML
  * @group templates
  */
  public function testArrayToXML($array, $result)
  {
    $xml = new SimpleXMLElement('<template/>');
    array_to_xml($array, $xml);
    
    $this->assertSame($result, $xml->asXML());
  }

  public function providerArrayToXML()
  {
    return array(
      array(
        // Array
        array('entity' => 'sensor',
              'name' => 'temp',
              'message' => 'temp alarm',
              'severity' => 'crit',
              'suppress_recovery' => 0,
              'delay' => 0,
              'conditions_and' => 0,
              'conditions' => array('sensor_value > 30',
                                    'sensor_value < 1'),
              'conditions_complex' => 'sensor_value > 30 OR sensor_value < 1',
              'associations' => array(array('device' => array('hostname match timos*'),
                                            'entity' => array('sensor_class is temperature')),
                                      array('device' => array('hostname match cisco*'),
                                            'entity' => array('sensor_class is temperature')))),
        // XML
        '<?xml version="1.0"?>
<template><entity>sensor</entity><name>temp</name><message>temp alarm</message><severity>crit</severity><suppress_recovery>0</suppress_recovery><delay>0</delay><conditions_and>0</conditions_and><conditions>sensor_value &gt; 30</conditions><conditions>sensor_value &lt; 1</conditions><conditions_complex>sensor_value &gt; 30 OR sensor_value &lt; 1</conditions_complex><associations><device>hostname match timos*</device><entity>sensor_class is temperature</entity></associations><associations><device>hostname match cisco*</device><entity>sensor_class is temperature</entity></associations></template>
'
      ),
    );
  }

  /**
  * @dataProvider providerGenerateTemplate
  * @group templates
  */
  public function testGenerateTemplate($type, $array, $result)
  {
    $template = generate_template($type, $array);
    $template = preg_replace('/<template type="' . $type . '".+?>/', '<template>', $template);
    $this->assertSame($result, $template);
  }

  public function providerGenerateTemplate()
  {
    $alert_array = array(
      'alert_test_id' => '4',
      'entity_type' => 'sensor',
      'alert_name' => 'temp',
      'alert_message' => 'temp alarm',
      'conditions' => array(
          array('value' => '30', 'condition' => '>', 'metric' => 'sensor_value'),
          array('value' => '1',  'condition' => '<', 'metric' => 'sensor_value')),
      'and' => '0',
      'severity' => 'crit',
      'delay' => '0',
      'alerter' => 'default',
      'enable' => '1',
      'show_frontpage' => '1',
      'suppress_recovery' => '0',
      'ignore_until' => NULL,
      'associations' => array(
          array('entity_type' => 'sensor',
                'entity_attribs' => array(
                                      array('value' => 'temperature',
                                            'condition' => 'is',
                                            'attrib' => 'sensor_class')),
                'device_attribs' => array(
                                      array('value' => 'timos*',
                                            'condition' => 'match',
                                            'attrib' => 'hostname'))),
          array('entity_type' => 'sensor',
                'entity_attribs' => array(
                                      array('value' => 'temperature',
                                            'condition' => 'is',
                                            'attrib' => 'sensor_class')),
                'device_attribs' => array(
                                      array('value' => 'cisco*',
                                            'condition' => 'match',
                                            'attrib' => 'hostname')))),
      );
    $alert_array1 = array(
      'alert_test_id' => '7',
      'entity_type' => 'storage',
      'alert_name' => 'stor_perc_85',
      'alert_message' => 'Storage exceeds 85% of disk capacity',
      'conditions' => array(
          array('value' => '85', 'condition' => 'ge', 'metric' => 'storage_perc')),
      'and' => '1',
      'severity' => 'crit',
      'delay' => '0',
      'alerter' => 'default',
      'enable' => '1',
      'show_frontpage' => '1',
      'suppress_recovery' => '0',
      'ignore_until' => NULL,
      'associations' => array(
          array('entity_type' => 'storage',
                'entity_attribs' => array(
                                      array('value' => 'hrStorageFixedDisk',
                                            'condition' => 'equals',
                                            'metric' => 'storage_perc',
                                            'attrib' => 'storage_type')),
                'device_attribs' => array(
                                      array ('value' => NULL,
                                             'condition' => NULL,
                                             'metric' => 'storage_perc',
                                             'attrib' => '*')))),
      );
    return array(
      array(
        'alert',
        // Array
        $alert_array,
        // XML
        '<?xml version="1.0"?>
<template><entity_type>sensor</entity_type><name>temp</name><message>temp alarm</message><severity>crit</severity><suppress_recovery>0</suppress_recovery><delay>0</delay><conditions_and>0</conditions_and><conditions>sensor_value &gt; 30</conditions><conditions>sensor_value &lt; 1</conditions><conditions_complex>sensor_value &gt; 30 OR sensor_value &lt; 1</conditions_complex><associations><device>hostname match timos*</device><entity>sensor_class is temperature</entity></associations><associations><device>hostname match cisco*</device><entity>sensor_class is temperature</entity></associations></template>
'
      ),
      array(
        'alert',
        // Array
        $alert_array1,
        // XML
        '<?xml version="1.0"?>
<template><entity_type>storage</entity_type><name>stor_perc_85</name><message>Storage exceeds 85% of disk capacity</message><severity>crit</severity><suppress_recovery>0</suppress_recovery><delay>0</delay><conditions_and>1</conditions_and><conditions>storage_perc ge 85</conditions><conditions_complex>storage_perc ge 85</conditions_complex><associations><device>*</device><entity>storage_type equals hrStorageFixedDisk</entity></associations></template>
'
      ),
    );
  }

}

// EOF
