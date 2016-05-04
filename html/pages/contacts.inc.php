<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// Contact display and editing page.

if ($_SESSION['userlevel'] < 7)
{
  print_error_permission();
  return;
}

  include($config['html_dir']."/includes/alerting-navbar.inc.php");
  include($config['html_dir']."/includes/contacts-navbar.inc.php");

  if ($_SESSION['userlevel'] >= 10 && isset($vars['submit']) && $vars['submit'] == 'contact_add')
  {
    // Only proceed if the contact_method is valid in our transports array
    if (is_array($config['alerts']['transports'][$vars['contact_method']]))
    {
      foreach ($config['alerts']['transports'][$vars['contact_method']]['parameters'] as $section => $parameters)
      {
        foreach ($parameters as $parameter => $description)
        {
          if (isset($vars['contact_' . $vars['contact_method'] . '_' . $parameter]))
          {
            $endpoint_data[$parameter] = $vars['contact_' . $vars['contact_method'] . '_' . $parameter];
          }
        }
      }
      
      dbInsert('alert_contacts', array('contact_descr' => $vars['contact_descr'], 'contact_endpoint' => json_encode($endpoint_data), 'contact_method' => $vars['contact_method']));
    }
  }

  if ($_SESSION['userlevel'] >= 10 && isset($vars['submit']) && $vars['submit'] == 'contact_delete')
  {
    $rows_updated   = dbDelete('alert_contacts',       '`contact_id` = ?', array($vars['contact_id']));
    $assocs_deleted = dbDelete('alert_contacts_assoc', '`contact_id` = ?', array($vars['contact_id']));
  }

?>

<div class="row">
<div class="col-sm-12">

<?php

  // FIXME. Show for anyone > 5 (also for non-ADMIN) and any contacts?
  $contacts = dbFetchRows("SELECT * FROM `alert_contacts` WHERE 1");
  if (count($contacts))
  {
    // We have contacts, print the table.


    echo generate_box_open();
?>

<table class="table table-condensed table-striped table-rounded table-hover">
  <thead>
    <tr>
    <th style="width: 1px"></th>
    <th style="width: 50px">Id</th>
    <th style="width: 100px">Method</th>
    <th style="width: 300px">Description</th>
    <th>Destination</th>
    <th style="width: 60px">Used</th>
    <th style="width: 70px">Status</th>
    <th style="width: 30px"></th>
    </tr>
  </thead>
  <tbody>

<?php

    $modals = '';

    foreach ($contacts as $contact)
    {
      $num_assocs = dbFetchCell("SELECT COUNT(*) FROM `alert_contacts_assoc` WHERE `contact_id` = ?", array($contact['contact_id'])) + 0;

      if ($contact['contact_disabled'] == 1) { $disabled = ""; }

      // If we have "identifiers" set for this type of transport, use those to print a user friendly destination.
      // If we don't, just dump the JSON array as we don't have a better idea what to do right now.
      if (isset($config['alerts']['transports'][$contact['contact_method']]['identifiers']))
      {
        // Decode JSON for use below
        $contact['endpoint_variables'] = json_decode($contact['contact_endpoint'], TRUE);

        // Add all identifier strings to an array and implode them into the description variable
        // We can't just foreach the identifiers array as we don't know what section the variable is in
        foreach ($config['alerts']['transports'][$contact['contact_method']]['identifiers'] as $key)
        {
          foreach ($config['alerts']['transports'][$contact['contact_method']]['parameters'] as $section => $parameters)
          {
            if (isset($parameters[$key]) && isset($contact['endpoint_variables'][$key]))
            {
              $contact['endpoint_identifiers'][] = escape_html($parameters[$key]['description'] . ': ' . $contact['endpoint_variables'][$key]);
            }
          }
        }

        $contact['endpoint_descr'] = implode('<br />', $contact['endpoint_identifiers']);
      }
      else
      {
        $contact['endpoint_descr'] = escape_html($contact['contact_endpoint']);
      }

      echo '    <tr>';
      echo '      <td></td>';
      echo '      <td>'.$contact['contact_id'].'</td>';
      echo '      <td><span class="label">'.$config['alerts']['transports'][$contact['contact_method']]['name'].'</span></td>';
      echo '      <td>'.escape_html($contact['contact_descr']).'</td>';
      echo '      <td><a href="' . generate_url(array('page' => 'contact', 'contact_id' => $contact['contact_id'])) . '">' . $contact['endpoint_descr'] . '</a></td>';
      echo '      <td><span class="label label-info">'.$num_assocs.'</span></td>';
      echo '      <td>' . ($contact['contact_disabled'] ?  '<span class="label label-error">disabled</span>' : '<span class="label label-success">enabled</span>') . '</td>';
      echo '      <td><a href="#contact_del_modal_' . $contact['contact_id'] . '" data-toggle="modal"><i class="oicon-minus-circle"></i></a></td>';
      echo '    </tr>';

      $modals .= '
<div id="contact_del_modal_'.$contact['contact_id'].'" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="contact_delete" aria-hidden="true">
 <form id="contact_del" name="contact_del" method="post" class="form" action="">
  <input type="hidden" name="contact_id" value="'. $contact['contact_id'].'">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel"><i class="oicon-minus-circle"></i> Delete Contact '.$contact['contact_id'].'</h3>
  </div>
  <div class="modal-body">

  <span class="help-block">This will delete the selected contact and any alert assocations.</span>
  <fieldset>
    <div class="control-group">
      <label class="control-label" for="confirm">
        Confirm
      </label>
      <div class="controls">
        <label class="checkbox">
          <input type="checkbox" name="confirm" value="confirm" onchange="javascript: showWarning'.$contact['contact_id'].'(this.checked);" />
          Yes, please delete this contact!
        </label>
        <script type="text/javascript">
        function showWarning'.$contact['contact_id'].'(checked) {
          if (checked) { $(\'#delete_button'.$contact['contact_id'].'\').removeAttr(\'disabled\'); } else { $(\'#delete_button'.$contact['contact_id'].'\').attr(\'disabled\', \'disabled\'); }
        }
      </script>
      </div>
    </div>
  </fieldset>

        <div class="alert alert-message alert-danger" id="warning" style="display:none;">
    <h4 class="alert-heading"><i class="icon-warning-sign"></i> Warning!</h4>
    Are you sure you want to delete this alert association?
  </div>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <button id="delete_button'.$contact['contact_id'].'" type="submit" class="btn btn-danger" name="submit" value="contact_delete" disabled><i class="icon-trash icon-white"></i> Delete Contact</button>
  </div>
 </form>
</div>
';

    }

?>

  </tbody>
</table>

<?php

    echo generate_box_close();

  } else {
    // We don't have contacts. Say so.
    print_warning("There are currently no contacts configured.");
  }

  echo $modals;

?>

  </div> <!-- col-sm-12 -->

</div> <!-- row -->

<!-- Add association -->

<div id="add_contact_modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="add_contact_label" aria-hidden="true">
 <form id="add_contact" name="add_contact" method="post" class="form form-horizontal" action="">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="add_assoc_label"><i class="oicon-sql-join-inner"></i> Add Contact</h3>
  </div>
  <div class="modal-body">

  <fieldset>

      <div class="control-group">
        <label class="control-label" for="contact_method">Method</label>
        <div class="controls">
          <select class="selectpicker" name="contact_method" id="contact_method">
            <?php
            foreach (array_keys($config['alerts']['transports']) as $method)
            {
              echo("<option value='".$method."'");
              echo(">".$config['alerts']['transports'][$method]['name']."</option>");
            }
            ?>
          </select>
        </div>
      </div>

      <div class="control-group">
        <label class="control-label" for="contact_descr">Description</label>
        <div class="controls">
          <input type=text name="contact_descr" size="32" value=""/>
        </div>
      </div>

<?php
  foreach ($config['alerts']['transports'] as $transport => $data)
  {
    echo('      <div id="form_' . $transport . '" class="control-group">' . PHP_EOL);

    if (count($data['parameters']['required']))
    {
      echo('  <h3 id="add_assoc_label"><i class="oicon-sql-join-inner"></i> Required parameters</h3>' . PHP_EOL);

      if (!count($data['parameters']['global'])) { $data['parameters']['global'] = array(); } // Temporary until we separate "global" out.
      // Plan: add defaults for transport types to global settings, which we use by default, then be able to override the settings via this GUI
      // This needs supporting code in the transport to check for set variable and if not, use the global default

      foreach (array_merge($data['parameters']['required'], $data['parameters']['global']) as $parameter => $param_data) // Temporary merge req & global
      {
        echo('        <div class="control-group">' . PHP_EOL);
        echo('          <label class="control-label" for="contact_' . $transport . '_' . $parameter . '">' . $param_data['description'] . '</label>' . PHP_EOL);
        echo('          <div class="controls">' . PHP_EOL);
        echo('            <input type=text name="contact_' . $transport . '_' . $parameter . '" size="32" value=""/>' . PHP_EOL);
        if (isset($param_data['tooltip']))
        {
          echo(generate_tooltip_link(NULL, '<i class="oicon-question"></i>', $param_data['tooltip']));
        }
        echo('          </div>' . PHP_EOL);
        echo('        </div>' . PHP_EOL);
      }
    }

    if (count($data['parameters']['optional']))
    {
      echo('  <h3 id="add_assoc_label"><i class="oicon-sql-join-inner"></i> Optional parameters</h3>' . PHP_EOL);

      foreach ($data['parameters']['optional'] as $parameter => $param_data)
      {
        echo('        <div class="control-group">' . PHP_EOL);
        echo('          <label class="control-label" for="contact_' . $transport . '_' . $parameter . '">' . $param_data['description'] . '<:label>' . PHP_EOL);
        echo('          <div class="controls">' . PHP_EOL);
        echo('            <input type=text name="contact_' . $transport . '_' . $parameter . '" size="32" value=""/>' . PHP_EOL);
        if (isset($param_data['tooltip']))
        {
          echo(generate_tooltip_link(NULL, '<i class="oicon-question"></i>', $param_data['tooltip']));
        }
        echo('          </div>' . PHP_EOL);
        echo('        </div>' . PHP_EOL);
      }
    }
  
    echo('      </div>' . PHP_EOL);
  }
                                                                           
?>

<script type="text/javascript">
<!--
$("#contact_method").change(function() {
  var select = this.value;

<?php

  $count = 0;

  // Generate javascript function which hides all configuration part panels except the ones for the currently chosen transport
  // Alternative would be to hide them all, then unhide the one selected. Hmm...
  foreach ($config['alerts']['transports'] as $transport => $description)
  {
    if ($count == 0)
    {
      echo("  if (select === '" . $transport . "') {" . PHP_EOL);
    } else {
      echo("  } else if (select === '" . $transport . "') {" . PHP_EOL);
    }
    echo("    \$('#form_${transport}').show();" . PHP_EOL);
    foreach ($config['alerts']['transports'] as $ltransport => $ldescription)
    {
      if ($transport != $ltransport)
      {
        echo("    \$('#form_${ltransport}').hide();" . PHP_EOL);
      }
    }

    $count++;
  }
?>
  }
}).change();

// -->
</script>

  </fieldset>

  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <button type="submit" class="btn btn-primary" name="submit" value="contact_add"><i class="icon-ok icon-white"></i> Add Contact</button>
  </div>
 </form>
</div>

<!-- End add assocation -->

<?php

// EOF
