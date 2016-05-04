<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage web
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

if ($_SESSION['userlevel'] < 10)
{
  print_error_permission();
  return;
}

  // Load SQL config into $database_config
  load_sqlconfig($database_config);

  // cache default and config.php-defined values
  $defined_config = get_defined_settings();
  $default_config = get_default_settings();

  echo('<form id="settings" name="settings" method="post" action="" class="form form-inline">' . PHP_EOL);

  // Pretty inefficient looping everything if section != all, but meh
  // This is only done on this page, so there is no performance issue for the rest of Observium
  foreach ($config_subsections as $section => $subdata)
  {
    if (isset($config_sections[$section]['edition']) && $config_sections[$section]['edition'] != OBSERVIUM_EDITION)
    {
      // Skip sections not allowed for current Observium edition
      continue;
    }

    echo('  <div class="row"> <div class="col-md-12"> <!-- BEGIN SECTION '.$section.' -->' . PHP_EOL);
    if ($vars['section'] == 'all' || $vars['section'] == $section)
    {
      if ($vars['section'] == 'all')
      {
        // When printing all, also print the section name
        echo('  <div class="box box-solid"><div class="box-header"><h3 class="box-title">' . $config_sections[$section]['text'] . '</h3></div></div>' . PHP_EOL);
      }

      foreach ($subdata as $subsection => $vardata)
      {
        echo '<div class="box box-solid">';

        // Print subsection name
        echo('<div class="box-header with-border">' . PHP_EOL);
        echo('  <h3 class="box-title">' . $subsection . '</h3>' . PHP_EOL);
        echo('</div>' . PHP_EOL);

        echo('<div class="box-content no-padding">' . PHP_EOL);
        echo('  <table class="table table-striped table-condensed" style="">' . PHP_EOL);

        $cols = array(
          array(NULL, 'class="state-marker"'),
          array(NULL, 'style="width: 0px;"'),
          array('Description', 'style="width: 40%;"'),
          array(NULL,          'style="width: 50px;"'),
          'Configuration Value',
          array('Use DB',      'style="width: 75px;"'),
        );
        echo(get_table_header($cols));

        foreach ($vardata as $varname => $variable)
        {
          if (isset($variable['edition']) && $variable['edition'] != OBSERVIUM_EDITION)
          {
            // Skip variables not allowed for current Observium edition
            continue;
          }
          $linetype = '';
          // Check if this variable is set in SQL
          if (sql_to_array($varname, $database_config) !== FALSE)
          {
            $sqlset = 1;
            $linetype = '';
            $content = sql_to_array($varname, $database_config, FALSE);
          } else {
            $sqlset = 0;
            $linetype = "disabled";
          }

          // Check if this variable is set in the config. If so, lock it
          if (sql_to_array($varname, $defined_config) !== FALSE)
          {
            $locked   = 1;
            $offtext  = "Locked";
            $offtype = "danger";
            $linetype = 'warning';
            $content  = sql_to_array($varname, $defined_config, FALSE);
          } else {
            $locked   = 0;
            $offtext  = "Default";
            $offtype = "success";
          }

          $htmlname = str_replace('|','__',$varname); // JQuery et al don't like the pipes a lot, replace once here in temporary variable

          echo('  <tr class="' . $linetype . '">' . PHP_EOL);
          echo('    <td class="state-marker"></td>');
          echo('    <td style="width: 5px;"></td>');
          echo('    <td><strong style="color: #0a5f7f;">' . $variable['name'] . '</strong>');
          echo('<br /><i><small>' . escape_html($variable['shortdesc']) . '</small></i>' . PHP_EOL);
          echo('      </td>' . PHP_EOL);
          echo('      <td style="vertical-align:middle;        white-space: nowrap;">' . PHP_EOL);
          echo('<div class="pull-right">');
          if ($locked)
          {
            echo(generate_tooltip_link(NULL, '<i class="oicon-lock-warning"></i>', 'This setting is locked because it has been set in your <strong>config.php</strong> file.'));
            echo '&nbsp;';
          }
          echo(generate_tooltip_link(NULL, '<i class="oicon-question"></i>', 'Variable name to use in <strong>config.php</strong>: $config[\'' . implode("']['",explode('|',$varname)) . '\']'));
          echo('      </div>' . PHP_EOL);
          echo('      </td>'. PHP_EOL);
          echo('      <td style="vertical-align:middle">' . PHP_EOL);

          // Split enum|foo|bar into enum  foo|bar
          list($vartype, $varparams) = explode('|', $variable['type'], 2);
          $params = array();

          // If a callback function is defined, use this to fill params.
          if ($variable['params_call'] && function_exists($variable['params_call']))
          {
            $params = call_user_func($variable['params_call']);
          // Else if the params are defined directly, use these.
          } else if (is_array($variable['params']))
          {
            $params = $variable['params'];
          }
          // Else use parameters specified in variable type (e.g. enum|1|2|5|10)
          else if (!empty($varparams))
          {
            foreach (explode('|', $varparams) as $param)
            {
              $params[$param] = array('name' => nicecase($param));
            }
          }

          if (sql_to_array($varname, $config) === FALSE)
          {
            // Variable is not configured, set $content to its default value so the form is pre-filled
            $content = sql_to_array($varname, $default_config, FALSE);
          } else {
            $content = sql_to_array($varname, $config, FALSE); // Get current value
          }
          //r($varname); r($content); r($sqlset); r($locked);

          $readonly = !($sqlset || $locked);

          echo('      <div id="' . $htmlname . '_content_div">' . PHP_EOL);

          switch ($vartype)
          {
            case 'bool':
              echo('      <div>' . PHP_EOL);
              $item = array('id'       => $htmlname,
                            'size'     => 'small',
                            'on-text'  => 'True',
                            'off-text' => 'False',
                            'readonly' => $readonly,
                            'disabled' => (bool)$locked,
                            'value'    => $content);
              echo(generate_form_element($item, 'switch'));
              //echo('        <input data-toggle="switch-bool" type="checkbox" ' . ($content ? 'checked="1" ' : '') . 'id="' . $htmlname . '" name="' . $htmlname . '" ' . ($locked ? 'disabled="1" ' : '').'>' . PHP_EOL);
              echo('      </div>' . PHP_EOL);
              break;
            case 'enum-array':
              //r($content);
              if ($variable['value_call'] && function_exists($variable['value_call']))
              {
                $values = array();
                foreach ($content as $value)
                {
                  $values[] = call_user_func($variable['value_call'], $value);
                }
                $content = $values;
                unset($values);
              }
              //r($content);
            case 'enum':
              foreach ($params as $param => $entry)
              {
                if (isset($entry['subtext'])) {} // continue
                else if (isset($entry['allowed']))
                {
                  $params[$param]['subtext'] = "Allowed to use " . $config_variable[$entry['allowed']]['name'];
                }
                else if (isset($entry['required']))
                {
                  $params[$param]['subtext'] = '<strong>REQUIRED to use ' . $config_variable[$entry['required']]['name'] . '</strong>';
                }
              }
              //r($params);
              $item = array('id'       => $htmlname,
                            'title'    => 'Any', // only for enum-array
                            'width'    => '150px',
                            'readonly' => $readonly,
                            'disabled' => (bool)$locked,
                            'onchange' => 'switchDesc(\'' . $htmlname . '\')',
                            'values'   => $params,
                            'value'    => $content);
              echo(generate_form_element($item, ($vartype != 'enum-array' ? 'select' : 'multiselect')));
              foreach ($params as $param => $entry)
              {
                if (isset($entry['desc']))
                {
                  echo('      <div id="param_' . $htmlname . '_' .$param. '" style="' . ($content != $param ? ' display: none;' : '') . '">' . PHP_EOL);
                  echo('        ' . $entry['desc'] . PHP_EOL);
                  echo('      </div>' . PHP_EOL);
                }
              }
              break;
            case 'array':
             // FIXME ...
              break;
            case 'password':
            case 'string':
            default:
              $item = array('id'       => $htmlname,
                            //'width'    => '500px',
                            'class'    => 'input-xlarge',
                            'type'     => 'text',
                            'readonly' => $readonly,
                            'disabled' => (bool)$locked,
                            'placeholder' => escape_html($content),
                            'value'    => escape_html($content));
              if ($vartype == 'password')
              {
                $item['type'] = 'password';
                $item['show_password'] = 1;
              }
              echo(generate_form_element($item));
              //echo('         <input name="' . $htmlname . '" style="width: 500px" type="text" ' . ($locked ? 'disabled="1" ' : '') . 'value="' . escape_html($content) . '" />' . PHP_EOL);
              break;
          }

          echo('        <input type="hidden" name="varset_' . $htmlname . '" />' . PHP_EOL);
          echo('      </div>' . PHP_EOL);
          echo('    </td>' . PHP_EOL);
          echo('    <td style="vertical-align:middle">' . PHP_EOL);
          echo('      <div class="pull-right">' . PHP_EOL);
          $item = array('id'       => $htmlname . '_custom',
                        'size'     => 'small',
                        //'width'    => 100,
                        'on-color' => 'primary',
                        'off-color' => $offtype,
                        'on-text'  => 'Custom',
                        'off-text' => $offtext,
                        'onchange' => "toggleAttrib('readonly', '" . $htmlname . "')",
                        //'onchange' => '$(\'#' . $htmlname . '_content_div\').toggle()',
                        'disabled' => (bool)$locked,
                        'value'    => $sqlset && !$locked);
          echo(generate_form_element($item, 'switch'));
          //echo('        <input data-toggle="switch-mini" data-on="primary" data-off="' . $offtype . '" data-on-label="Custom" data-off-label="' . $offtext . '" onchange="$(\'#' . $htmlname . '_content_div\').toggle()" type="checkbox" ' . ($sqlset && !$locked ? 'checked="1" ' : '') . 'name="' . $htmlname . '_custom"' . ($locked ? ' disabled="1"' : '') . '>' . PHP_EOL);
          echo('      </div>' . PHP_EOL);
          echo('    </td>' . PHP_EOL);
          echo('  </tr>' . PHP_EOL);
        }

        echo('  </table>' . PHP_EOL);
        echo '</div>';
        echo '</div>';

      }
      //echo('  <br />' . PHP_EOL);
    }
    echo('  </div> </div> <!-- END SECTION '.$section.' -->' . PHP_EOL);
  }

?>
<div class="row">
<div class="col-sm-12">

  <div class="box box-solid">
  <div class="box-content no-padding">
  <div class="form-actions" style="margin: 0px;">
  <?php
  
  $item = array('id'          => 'submit',
                'name'        => 'Save Changes',
                'class'       => 'btn-primary',
                //'right'       => TRUE,
                'icon'        => 'icon-ok oicon-white',
                'value'       => 'save');
  echo(generate_form_element($item, 'submit'));
  ?>
  </div>
  </div>
  </div>

</div>
</div>

</form>

<script>
  function switchDesc(form_id) {
    var selected = $('#'+form_id+' option:selected').val();
    //console.log(selected);
    $('[id^="param_'+form_id+'_"]').each( function( index, element ) {
      if ($(this).prop('id') == 'param_'+form_id+'_'+selected) {
        $(this).css('display', '');
      } else {
        $(this).css('display', 'none');
      }
      //console.log( $(this).prop('id') );
    });
  }
</script>

<?php

// EOF
