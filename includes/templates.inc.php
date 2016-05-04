<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 *   These functions perform operations with templates.
 *
 * @package    observium
 * @subpackage templates
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

/* WARNING. This file should be load after config.php! */

/**
 * The function returns content of specific template
 *
 * @param string $type Type of template (currently only 'alert', 'group', 'notification')
 * @param string $subtype Subtype of template type, examples: 'email' for notification, 'device' for group or alert
 * @param string $name Name for template, also can used as name for group/alert/etc (lowercase!)
 *
 * @return string $template Content of specific template
 */
function get_template($type, $subtype, $name = '')
{
  $template      = ''; // If template not found, return empty string
  $template_dir  = $GLOBALS['config']['template_dir'];
  $default_dir   = $GLOBALS['config']['install_dir'] . '/includes/templates';

  if (empty($name))
  {
    // If name empty, than seems as we use filename instead (ie: email_html.tpl, type_somename.xml)
    $basename = basename($subtype);
    list($subtype, $name) = explode('_', $basename, 2);
  }

  switch ($type)
  {
    case 'alert':
    case 'group':
    case 'notification':
      $name = preg_replace('/\.(tpl|xml)$/', '', strtolower($name));
      // Notifications used raw text templates (with mustache format),
      //  all other used XML templates
      // Examples:
      //  /opt/observium/templates/alert/device_myname.xml
      //  /opt/observium/templates/notification/email_html.tpl
      if ($type == 'notification')
      {
        $ext = '.tpl';
      } else {
        $ext = '.xml';
      }
      $template_file = $type . '/' . $subtype . '_' . $name . $ext;
      if (is_file($template_dir . '/' . $template_file))
      {
        // User templates
        $template = file_get_contents($template_dir . '/' . $template_file);
      }
      else if (is_file($default_dir . '/' . $template_file))
      {
        // Default templates
        $template = file_get_contents($default_dir . '/' . $template_file);
      }
      break;
    default:
      print_debug("Template type '$type' with subtype '$subtype' and name '$name' not found!");
  }

  return $template;
}

/**
 * The function returns list of all template files for specific template type(s)
 *
 * @param mixed $types Type name of list of types as array
 * @return array $template_list List of template files with type as array keys
 */
function get_templates_list($types)
{
  $template_list = array(); // If templates not found, return empty list
  $template_dir   = $GLOBALS['config']['template_dir'];
  $default_dir    = $GLOBALS['config']['install_dir'] . '/includes/templates';

  if (!is_array($types))
  {
    $types = array($types);
  }
  foreach ($types as $type)
  {
    switch ($type)
    {
      case 'alert':
      case 'group':
      case 'notification':
        if ($type == 'notification')
        {
          $ext = '.tpl';
        } else {
          $ext = '.xml';
        }
        foreach (glob($default_dir . '/' . $type . '/?*_?*' . $ext) as $filename)
        {
          // Default templates, before user templates for override
          $template_list[$type][] = $filename;
        }
        // Examples:
        //  /opt/observium/templates/alert/device_myname.xml
        //  /opt/observium/templates/notification/email_html.tpl
        foreach (glob($template_dir . '/' . $type . '/?*_?*' . $ext) as $filename)
        {
          // User templates
          $template_list[$type][] = $filename;
        }
        break;
      default:
        print_debug("Template type '$type' unknown!");
    }
  }

  return $template_list;
}

/**
 * The function returns array with all avialable templates
 *
 * @param mixed $types Type name of list of types as array
 * @return array $template_array List of template with type and subtype as keys and name as values
 */
function get_templates_array($types)
{
  $template_array = array(); // If templates not found, return empty array

  $template_list  = get_templates_list($types); // Get templates file list

  foreach ($template_list as $type => $list)
  {
    foreach ($list as $filename)
    {
      $basename = basename($filename);
      $basename = preg_replace('/\.(tpl|xml)$/', '', $basename);
      list($subtype, $name) = explode('_', $basename, 2);
      $template_array[$type][$subtype] = strtolower($name);
    }
  }

  return $template_array;
}

/**
 * This is very-very-very simple template engine (or not simple?),
 * only some basic conversions and uses Mustache/CTemplate syntax.
 *
 * no cache/logging and others, for now support only this tags:
 * standart php comments
 * {{! %^ }} - intext comments
 *  {{var}}  - escaped var
 * {{{var}}} - unescaped var
 * {{var.subvar}} - dot notation vars
 * {{.}}     - implicit iterator
 * {{#var}} some text {{/var}} - if/list condition
 * {{^var}} some text {{/var}} - inverted (negative) if condition
 * options:
 * 'is_file', if set to TRUE, than get template from file $config['install_dir']/includes/templates/$template.tpl
 *            if set to FALSE (default), than use template from variable.
 */
// NOTE, do NOT use this function for generate pages, as adama said!
function simple_template($template, $tags, $options = array('is_file' => FALSE, 'use_cache' => FALSE))
{
  if (!is_string($template) || !is_array($tags))
  {
    // Return false if template not string (or filename) and tags not array
    return FALSE;
  }

  if (isset($options['is_file']) && $options['is_file'])
  {
    // Get template from file
    $template = get_template('notification', $template);

    // Return false if no file content or false file read
    if (!$template) { return FALSE; }
  }

  // Cache disabled for now, i think this can generate huge array
  /**
  $use_cache = isset($options['use_cache']) && $options['use_cache'] && $tags;
  if ($use_cache)
  {
    global $cache;

    $timestamp     = time();
    $template_csum = md5($template);
    $tags_csum     = md5(json_encode($tags));

    if (isset($cache['templates'][$template_csum][$tags_csum]))
    {
      if (($timestamp - $cache['templates'][$template_csum][$tags_csum]['timestamp']) < 600)
      {
        return $cache['templates'][$template_csum][$tags_csum]['string'];
      }
    }
  }
   */

  $string = $template;

  // Removes multi-line comments and does not create
  // a blank line, also treats white spaces/tabs
  $string = preg_replace('![ \t]*/\*.*?\*/[ \t]*[\r\n]?!s', '', $string);

  // Removes single line '//' comments, treats blank characters
  $string = preg_replace('![ \t]*//.*[ \t]*[\r\n]?!', '', $string);

  // Removes in-text comments {{! any text }}
  $string = preg_replace('/{{!.*?}}/', '', $string);

  // Strip blank lines
  //$string = preg_replace('/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/', PHP_EOL, $string);

  // Replace keys, loops and other template sintax
  $string = simple_template_replace($string, $tags);

  /**
  if ($use_cache)
  {
    $cache['templates'][$template_csum][$tags_csum] = array('timestamp' => $timestamp,
                                                            'string'    => $string);
  }
  */

  return $string;
}

function simple_template_replace($string, $tags)
{
  // Note for future: to match Unix LF (\n), MacOS<9 CR (\r), Windows CR+LF (\r\n) and rare LF+CR (\n\r)
  // EOL patern should be: /((\r?\n)|(\n?\r))/
  $patterns = array(
    // {{#var}} some text {{/var}}
    'list_condition'     => '![ \t]*{{#[ \t]*([ \w[:punct:]]+?)[ \t]*}}[ \t]*[\r\n]?(.*?) {{/[ \t]*\1[ \t]*}}[ \t]*([\r\n]?)!s',
    // {{^var}} some text {{/var}}
    'negative_condition' => '![ \t]*{{\^[ \t]*([ \w[:punct:]]+?)[ \t]*}}[ \t]*[\r\n]?(.*?) {{/[ \t]*\1[ \t]*}}[ \t]*([\r\n]?)!s',
    // {{{var}}}
    'var_noescape'       => '!{{{[ \t]*([^}{#\^\?/]+?)[ \t]*}}}!',
    // {{var}}
    'var_escape'         => '!{{[ \t]*([^}{#\^\?/]+?)[ \t]*}}!',
  );
  // Main loop
  foreach ($patterns as $condition => $pattern)
  {
    switch ($condition)
    {
      // LIST condition first!
      case 'list_condition':
      // NEGATIVE condition second!
      case 'negative_condition':
        if (preg_match_all($pattern, $string, $matches))
        {
          foreach ($matches[1] as $key => $var)
          {
            $test_tags = isset($tags[$var]) && $tags[$var];
            if (($condition == 'list_condition'     && $test_tags) ||
                ($condition == 'negative_condition' && !$test_tags))
            {
              $replace = preg_replace('/[\t\ ]+$/', '', $matches[2][$key]);
              //if (!$matches[3][$key])
              //{
              //  // Remove last newline if condition at EOF
              //  $replace = preg_replace('/[\r\n]$/', '', $replace);
              //}
              if ($condition == 'list_condition' && is_array($tags[$var]))
              {
                // Additional remove first newline if pressent
                $replace = preg_replace('/^[\r\n]/', '', $matches[2][$key]);
                // If tag is array, use recurcive repeater
                $repeate = array();
                foreach ($tags[$var] as $item => $entry)
                {
                  $repeate[] = simple_template_replace($replace, $entry);
                }
                $replace = implode('', $repeate);
              }
            } else {
              $replace = '';
            }
            $string = str_replace($matches[0][$key], $replace, $string);
          }
        }
        break;
      // Next var not escaped
      case 'var_noescape':
      // Next var escaped
      case 'var_escape':
        if (preg_match_all($pattern, $string, $matches))
        {
          foreach ($matches[1] as $key => $var)
          {
            if ($var === '.' && is_string($tags))
            {
              // This conversion for implicit iterator {{.}}
              $tags    = array('.' => $tags);
              $subvars = array();
            } else {
              $subvars = explode('.', $var);
            }

            if (isset($tags[$var]))
            {
              // {{ var }}, {{{ var_noescape }}}
              $replace = ($condition === 'var_noescape' ? $tags[$var] : htmlspecialchars($tags[$var], ENT_QUOTES, 'UTF-8'));
            }
            else if (count($subvars) > 1 && is_array($tags[$subvars[0]]))
            {
              // {{ var.with.iterator }}, {{{ var.with.iterator.noescape }}}
              $replace = $tags[$subvars[0]];
              array_shift($subvars);
              foreach ($subvars as $subvar)
              {
                if (isset($replace[$subvar]))
                {
                  $replace = $replace[$subvar];
                } else {
                  unset($replace);
                  break;
                }
              }
              $replace = ($condition === 'var_noescape' ? $replace : htmlspecialchars($replace, ENT_QUOTES, 'UTF-8'));
            } else {
              // By default if tag not exist, remove var from template
              $replace = '';
            }
            $string  = str_replace($matches[0][$key], $replace, $string);
          }
        }
        break;
    }
  }
  //var_dump($string);
  return $string;
}

/**
 * This function convert array based group/alerts to observium xml based template
 *
 * Template attributes:
 *  type            - Type (ie: alert, group, notification)
 *  description     - Description
 *  version         - Template format version
 *  created         - Created date
 *  observium       - Used observium version
 *  id              - Unique template id, based on conditions/associations/text
 *
 * Template params:
 *  entity          - Type of entity
 *  name            - Unique name for current set of params
 *  description     - Description for current set of params
 *  message         - Text message
 *  conditions      - Set of conditions
 *  conditions_and  - 1 - require all conditions, 0 - require any condition
 *  conditions_complex - oneline conditions set (not used for now)
 *  associations    - Set of associations
 *    device        - Set of device associations
 *    entity        - Set of entity associations
 *
 * @param string $type Current template type for generate (alert or group)
 * @param array $params
 * @param boolean $as_xml_object If set to TRUE, return template as SimpleXMLElement object
 *
 * @return mixed XML based template (as string or SimpleXMLElement object if $as_xml_object set to true)
 */
function generate_template($type, $params, $as_xml_object = FALSE)
{
  if (!check_extension_exists('SimpleXML', 'SimpleXML php extension not found, it\'s required for generate templates.'))
  {
    return '';
  }
  // r($params); var_export($params);

  $type = strtolower(trim($type, " '\"\t\n\r\0\x0B")); // Clean template type

  $template_xml = new SimpleXMLElement('<template/>');
  // Template type
  $template_xml->addAttribute('type', $type);
  // Template description
  $template_xml->addAttribute('description', 'Autogenerated observium template');
  // Format version. If something changed in templates format, increase version!
  $template_xml->addAttribute('version', '0.91');
  // Template created date and time
  $template_xml->addAttribute('created', date('r'));
  // Used observium version
  $template_xml->addAttribute('observium', OBSERVIUM_VERSION);

  $template_array = array();
  switch ($type)
  {
    case 'group':
      $template_array['entity_type'] = strtolower(trim($params['entity_type'], " '\"\t\n\r\0\x0B"));
      $template_array['name']        = strtolower(trim($params['group_name'],  " '\"\t\n\r\0\x0B"));
      $template_array['description'] = trim($params['group_descr'], " '\"\t\n\r\0\x0B");

      break;
    case 'alert':
      $template_array['entity_type'] = strtolower(trim($params['entity_type'], " '\"\t\n\r\0\x0B"));
      $template_array['name']        = strtolower(trim($params['alert_name'],  " '\"\t\n\r\0\x0B"));
      //$template_array['description'] = trim($params['alert_descr'], " '\"\t\n\r\0\x0B");
      $template_array['message']     = $params['alert_message'];

      $template_array['severity']        = strtolower(trim($params['severity'],  " '\"\t\n\r\0\x0B"));
      if (in_array($params['suppress_recovery'], array('1', 'on', 'yes', TRUE)))
      {
        $template_array['suppress_recovery'] = 1;
      } else {
        $template_array['suppress_recovery'] = 0;
      }
      $template_array['delay'] = trim($params['delay'], " '\"\t\n\r\0\x0B");
      $template_array['delay'] = (int)$template_array['delay'];

      $template_array['conditions_and'] = (int)$params['and'];
      $and_or = ($params['and'] ? " AND " : " OR ");
      $conds = array();
      if (!is_array($params['conditions']))
      {
        $params['conditions'] = json_decode($params['conditions'], TRUE);
      }
      foreach ($params['conditions'] as $cond)
      {
        if (!is_array($cond))
        {
          $cond = json_decode($cond, TRUE);
        }
        $count = count($cond);
        if (isset($cond['metric']) && $count >= 3)
        {
          $line = $cond['metric'] . ' ' . $cond['condition'] . ' ' . $cond['value'];
        }
        else if ($count === 3)
        {
          $line    = implode(' ', $cond);
        } else {
          continue;
        }
        $conds[] = $line;
      }
      if ($conds)
      {
        $template_array['conditions'] = $conds;
        $template_array['conditions_complex'] = implode($and_or, $conds);
      }

      break;
    case 'notification':
      $template_array['name']        = strtolower(trim($params['name'],  " '\"\t\n\r\0\x0B"));
      $template_array['description'] = trim($params['description'], " '\"\t\n\r\0\x0B");

      $template_array['message']     = $params['message'];
      break;
    default:
      print_error("Unknown template type '$type' passed to " . __FUNCTION__ . "().");
      return '';
  }

  // Associations
  $associations = array();
  foreach ($params['associations'] as $assoc)
  {
    // Each associations set
    if (!is_array($assoc))
    {
      $assoc = json_decode($assoc, TRUE);
    }
    //r($assoc);
    foreach (array('device', 'entity') as $param)
    {
      if (isset($assoc[$param . '_attribs']))
      {
        $association[$param] = array();
        if (!is_array($assoc[$param . '_attribs']))
        {
          $assoc[$param . '_attribs'] = json_decode($assoc[$param . '_attribs'], TRUE);
        }
        foreach ($assoc[$param . '_attribs'] as $attrib)
        {
          if (!is_array($attrib))
          {
            $attrib = json_decode($attrib, TRUE);
          }
          //r($attrib);

          $count = count($attrib);
          if (empty($attrib) || $attrib['attrib'] == '*')
          {
            $association[$param] = array('*');
            break;
          }
          else if (isset($attrib['attrib']) && $count >= 3)
          {
            $line = $attrib['attrib'] . ' ' . $attrib['condition'] . ' ' . $attrib['value'];
          }
          else if ($count === 3)
          {
            $line    = implode(' ', $attrib);
          } else {
            continue;
          }
          $association[$param][] = $line;

        }
      }
    }
    $associations[] = $association;
  }
  //r($associations);
  if ($associations)
  {
    $template_array['associations'] = $associations;
  }

  //foreach (array('device', 'entity') as $param)
  //{
  //  $conds = array();
  //  if (isset($params['assoc_' . $param . '_conditions']))
  //  {
  //    foreach (explode("\n", $params['assoc_' . $param . '_conditions']) as $cond)
  //    {
  //      $line  = trim($cond);
  //      if ($line == "*")
  //      {
  //        $conds = array($line);
  //        break;
  //      }
  //      $count = count(explode(" ", $line, 3));
  //      if ($count === 3)
  //      {
  //        $line    = implode(' ', $cond);
  //        $conds[] = $line;
  //      }
  //    }
  //  }
  //  else if (isset($params[$param . '_attribs']))
  //  {
  //    if (!is_array($params[$param . '_attribs']))
  //    {
  //      $params[$param . '_attribs'] = json_decode($params[$param . '_attribs'], TRUE);
  //    }
  //    foreach ($params[$param . '_attribs'] as $attribs)
  //    {
  //      if (!is_array($attribs))
  //      {
  //        $attribs = json_decode($attribs, TRUE);
  //      }
  //      foreach ($attribs as $cond)
  //      {
  //        $count = count($cond);
  //        if (empty($cond) || $cond['attrib'] == '*')
  //        {
  //          $conds = array('*');
  //          break;
  //        }
  //        else if ($count === 3)
  //        {
  //          if (isset($cond['attrib']))
  //          {
  //            $line = $cond['attrib'] . ' ' . $cond['condition'] . ' ' . $cond['value'];
  //          } else {
  //            $line    = implode(' ', $cond);
  //          }
  //          $attrib[] = $line;
  //        }
  //      }
  //      $conds[] = $attrib;
  //    }
  //  }
  //  r($conds);
  //  if ($conds)
  //  {
  //    $and_or = " AND ";
  //    $template_array['associations'][$param] = $conds;
  //  }
  //}

  // Convert template array to xml
  array_to_xml($template_array, $template_xml);

  // Add unique id, based on conditions/associations (can used for quick compare templates)
  if ($type != 'notification')
  {
    $template_id = md5(serialize(array($template_array['conditions'], $template_array['associations'])));
  } else {
    $template_id = md5($template_array['message']);
  }
  $template_xml->addAttribute('id', $template_id);

  // Name must be safe and not empty!
  if (!empty($template_array['name']))
  {
    $template_array['name'] = safename($template_array['name']);
  } else {
    $template_array['name'] = 'autogenerated_' .$template_id;
  }

  if ($as_xml_object)
  {
    return $template_xml;
  } else {
    // Convert objected template to XML string
    return $template_xml->asXML();
  }
}

/**
 * Very simple combinate multiple templates from generate_template() into one XML templates
 *
 */
function generate_templates($type, $params)
{
  $templates_xml  = '<?xml version="1.0"?>' . PHP_EOL . '<templates>' . PHP_EOL;
  if (!is_array_assoc($params))
  {
    foreach ($params as $entry)
    {
      $template = generate_template($type, $entry);
      $templates_xml .= PHP_EOL . preg_replace('/^\s*<\?xml.+?\?>\s*(<template)/s', '\1', $template);
    }
  } else {
    $template = generate_template($type, $params);
    $templates_xml .= PHP_EOL . preg_replace('/^\s*<\?xml.+?\?>\s*(<template)/s', '\1', $template);
  }

  $templates_xml .= PHP_EOL . '</templates>' . PHP_EOL;

  return($templates_xml);
}

/**
 * Convert an multi-dimensional array to xml.
 * http://stackoverflow.com/questions/1397036/how-to-convert-array-to-simplexml
 *
 * @param object $object Link to SimpleXMLElement object
 * @param array $data Array which need to convert into xml
 */
function array_to_xml(array $data, SimpleXMLElement $object)
{
  foreach ($data as $key => $value)
  {
    if (is_array($value))
    {
      if (is_array_assoc($value))
      {
        // For associative arrays use keys as child object
        $new_object = $object->addChild($key);
        array_to_xml($value, $new_object);
      } else {
        // For sequential arrays use parent key as child
        foreach ($value as $new_value)
        {
          if (is_array($new_value))
          {
            array_to_xml(array($key => $new_value), $object);
          } else {
            $object->addChild($key, $new_value);
          }
        }
      }
    } else {
      //$object->$key = $value; // See note about & here - http://php.net/manual/en/simplexmlelement.addchild.php#112204
      $object->addChild($key, $value);
    }
  }
}

function xml_to_array($xml_string)
{
  $xml   = simplexml_load_string($xml_string);
  // r($xml);
  $json  = json_encode($xml);
  $array = json_decode($json, TRUE);

  return $array;
}

/**
 * Pretty print for xml string
 *
 * @param string $xml An xml string
 * @param boolean $formatted Convert or not output to human formatted xml
 */
function print_xml($xml, $formatted = TRUE)
{
  if ($formatted)
  {
    $xml = format_xml($xml);
  }
  if (is_cli())
  {
    echo $xml;
  } else {

    echo generate_box_open(array('title' => 'Output', 'padding' => TRUE));
    echo '
    <pre class="prettyprint lang-xml small">' . escape_html($xml) . '</pre>
    <span><em>NOTE. XML values always escaped, that why you can see this chars <mark>' . escape_html(escape_html('< > & " \'')) .
              '</mark> instead this <mark>' . escape_html('< > & " \'') . '</mark>. <u>Leave them as is</u>.</em></span>
    <script type="text/javascript">window.prettyPrint && prettyPrint();</script>' . PHP_EOL;
    echo generate_box_close();
  }
}

/**
 * Convert unformatted XML string to human readable string
 *
 * @param string $xml Unformatted XML string
 * @return string Human formatted XML string
 */
function format_xml($xml)
{
  if (!class_exists('DOMDocument'))
  {
    // If not exist class, just return original string
    return $xml;
  }

  $dom = new DOMDocument("1.0");
  $dom->preserveWhiteSpace = FALSE;
  $dom->formatOutput = TRUE;
  $dom->loadXML($xml);

  return $dom->saveXML();
}

/**
 * Send any string to browser as file
 *
 * @param string $string String content for save as file
 * @param string $filename Filename
 * @param array $vars Vars with some options
 */
function download_as_file($string, $filename = "observium_export.xml", $vars = array())
{
  //$echo = ob_get_contents();
  ob_end_clean(); // Clean and disable buffer

  $ext = pathinfo($filename, PATHINFO_EXTENSION);
  if ($ext == 'xml')
  {
    header('Content-type: text/xml');
    if ($vars['formatted'] == 'yes')
    {
      $string = format_xml($string);
    }
  } else {
    header('Content-type: text/plain');
  }
  header('Content-Disposition: attachment; filename="'.$filename.'";');
  header("Content-Length: " . strlen($string));
  header("Pragma: no-cache");
  header("Expires: 0");

  echo($string); // Send string content to browser output

  exit(0); // Stop any other output
}

// EOF
