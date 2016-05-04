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

if ($_SESSION['userlevel'] < 5)
{
  print_error_permission();
  return;
}

// Alert test display and editing page.
include($config['html_dir']."/includes/alerting-navbar.inc.php");

$check = dbFetchRow("SELECT * FROM `alert_tests` WHERE `alert_test_id` = ?", array($vars['alert_test_id']));

if ($_SESSION['userlevel'] == 10 && $vars['submit'])
{
  // We are editing. Lets see what we are editing.
  if ($vars['submit'] == "check_conditions")
  {
    $conds = array(); $cond_array = array();
    foreach (explode("\n", $vars['check_conditions']) AS $cond)
    {
      list($cond_array['metric'], $cond_array['condition'], $cond_array['value']) = explode(" ", trim($cond), 3);
      $conds[] = $cond_array;
    }
    $conds = json_encode($conds);
    $rows_updated = dbUpdate(array('conditions' => $conds, 'and' => $vars['alert_and']), 'alert_tests', '`alert_test_id` = ?',array($vars['alert_test_id']));
  }
  elseif ($vars['submit'] == "assoc_add")
  {
    $d_conds = array(); $cond_array = array();
    foreach (explode("\n", trim($vars['assoc_device_conditions'])) AS $cond)
    {
      list($cond_array['attrib'], $cond_array['condition'], $cond_array['value']) = explode(" ", trim($cond), 3);
      $d_conds[] = $cond_array;
    }
    $d_conds = json_encode($d_conds);

    $e_conds = array(); $cond_array = array();
    foreach (explode("\n", trim($vars['assoc_entity_conditions'])) AS $cond)
    {
      list($cond_array['attrib'], $cond_array['condition'], $cond_array['value']) = explode(" ", trim($cond), 3);
      $e_conds[] = $cond_array;
    }
    $e_conds = json_encode($e_conds);
    $rows_updated = dbInsert('alert_assoc', array('alert_test_id' => $vars['alert_test_id'], 'entity_type' => $check['entity_type'], 'device_attribs' => $d_conds, 'entity_attribs' => $e_conds));
  }
  elseif ($vars['submit'] == "delete_assoc")
  {
    $rows_updated = dbDelete('alert_assoc', '`alert_assoc_id` = ?', array($vars['assoc_id']));
  }
  elseif ($vars['submit'] == "assoc_conditions")
  {
    $d_conds = array(); $cond_array = array();
    foreach (explode("\n", trim($vars['assoc_device_conditions'])) AS $cond)
    {
      list($cond_array['attrib'], $cond_array['condition'], $cond_array['value']) = explode(" ", trim($cond), 3);
      $d_conds[] = $cond_array;
    }
    $d_conds = json_encode($d_conds);

    $e_conds = array(); $cond_array = array();
    foreach (explode("\n", trim($vars['assoc_entity_conditions'])) AS $cond)
    {
      list($cond_array['attrib'], $cond_array['condition'], $cond_array['value']) = explode(" ", trim($cond), 3);
      $e_conds[] = $cond_array;
    }
    $e_conds = json_encode($e_conds);
    $rows_updated = dbUpdate(array('device_attribs' => $d_conds, 'entity_attribs' => $e_conds), 'alert_assoc', '`alert_assoc_id` = ?', array($vars['assoc_id']));
  }
  elseif ($vars['submit'] == "alert_details")
  {
    $rows_updated = dbUpdate(array('alert_name' => $vars['alert_name'], 'alert_message' => $vars['alert_message'],
      'severity' => $vars['alert_severity'], 'delay' => $vars['alert_delay'], 'suppress_recovery' => (isset($vars['alert_send_recovery']) ? 0 : 1)), 'alert_tests', '`alert_test_id` = ?', array($vars['alert_test_id']));
  }

  if ($rows_updated > 0)
  {
    $update_message = $rows_updated . " Record(s) updated.";
    $updated = 1;
  } elseif ($rows_updated = '-1') {
    $update_message = "Record unchanged. No update necessary.";
    $updated = -1;
  } else {
    $update_message = "Record update error.";
    $updated = 0;
  }

  if ($updated && $update_message)
  {
    print_message($update_message);
  } elseif ($update_message) {
    print_error($update_message);
  }

  // Refresh the $check array to reflect the updates
  $check = dbFetchRow("SELECT * FROM `alert_tests` WHERE `alert_test_id` = ?", array($vars['alert_test_id']));
}

// Process the alert checker to add classes and colours and count status.
humanize_alert_check($check);

/// End bit to go in to function

?>

<div class="row">
  <div class="col-md-12">

<?php
  $box_args = array('title' => 'Checker Details',
                    'header-border' => TRUE,
                   );

  if ($_SESSION['userlevel'] == '10')
  {
    $box_args['header-controls'] = array('controls' => array('edit'   => array('text' => 'Edit',
                                                                               'icon' => 'icon-edit',
                                                                               'url'  => '#edit_alert_modal',
                                                                               'data' => 'data-toggle="modal"'),
                                                             'delete' => array('text' => 'Delete',
                                                                               'icon' => 'icon-trash',
                                                                               'url'  => '#delete_alert_modal',
                                                                               'data' => 'data-toggle="modal"')));
  }

  echo generate_box_open($box_args);

 #if ($_SESSION['userlevel'] >= '10') { echo '      <div class="title pull-right"><a href="'.generate_url($vars, array('edit' => "TRUE")).'"><i class="oicon-gear"></i> Edit</a></div>'; }

  echo('
        <table class="' . OBS_CLASS_TABLE_STRIPED . '">
         <thead>
          <tr>
            <th style="width: 5%;">Test ID</th>
            <th style="width: 15%;">Entity Type</th>
            <th style="width: 20%;">Name</th>
            <th style="width: 45%;">Message</th>
            <th style="width: 5%;">Options</th>
            <th style="width: 5%;">Delay</th>
            <th style="width: 10%;">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>' . $check['alert_test_id'] . '</td>
            <td><i class="' . $config['entities'][$check['entity_type']]['icon'] . '"></i> ' . nicecase($check['entity_type']) . '</td>
            <td>' . escape_html($check['alert_name']) . '</td>
            <td><i>' . escape_html($check['alert_message']) . '</i></td>
            <td>');
  if ($check['suppress_recovery'])
  {
    echo('<div style="text-decoration: line-through" title="Recovery notification suppressed">R</div>');
  }
  echo('</td>
            <td>' . $check['delay'] . '</td>
            <td><i>' . $check['status_numbers'] . '</i></td>
          </tr>
        </tbody></table>' . PHP_EOL);

  echo generate_box_close();
  echo('
  </div>
</div>');

  $assocs = dbFetchRows("SELECT * FROM `alert_assoc` WHERE `alert_test_id` = ?", array($vars['alert_test_id']));

?>
<div class="row">
  <div class="col-md-4">
<?php

  $box_args = array('title' => 'Check Conditions',
                    'header-border' => TRUE,
                   );

  if ($_SESSION['userlevel'] == '10')
  {
    $box_args['header-controls'] = array('controls' => array('edit'   => array('text' => 'Edit',
                                                                               'icon' => 'icon-edit',
                                                                               'url'  => '#conditions_modal',
                                                                               'data' => 'data-toggle="modal"')));
  }

  echo generate_box_open($box_args);

  echo '<table class="' . OBS_CLASS_TABLE_STRIPED . '">
        <thead><th>';
  if ($check['and'] == "1")
  {
    echo('Requires <strong>ALL</strong> conditions to match:');
  } else {
    echo('Requires <strong>ANY</strong> condition to match:');
  }
  echo '</tr></thead>';

  $conditions = json_decode($check['conditions'], TRUE);

  $condition_text_block = array();
  foreach ($conditions as $condition)
  {
    $condition_text_block[] = escape_html($condition['metric'].' '.$condition['condition'].' '.$condition['value']);
  }

  echo '<tr><td>';
  echo '<code>'.implode($condition_text_block,'<br />').'</code>' ;
  echo '</td></tr>';
  echo '</table>';

  echo generate_box_close();
?>

  </div>

  <div class="col-md-8">

<?php

  $box_args = array('title' => 'Associations',
                    'header-border' => TRUE,
                   );

  if ($_SESSION['userlevel'] == '10')
  {
    $box_args['header-controls'] = array('controls' => array('edit'   => array('text' => 'Add',
                                                                               'icon' => 'icon-plus',
                                                                               'url'  => '#add_assoc_modal',
                                                                               'data' => 'data-toggle="modal"')));
  }

  echo generate_box_open($box_args);

  echo '<table class="' . OBS_CLASS_TABLE_STRIPED . '">';

?>
  <thead><tr>
    <th style="width: 45%;">Device Match</th>
    <th style="">Entity Match</th>
    <th style="width: 10%;"></th>
  </tr></thead>

<?php

foreach ($assocs as $assoc_id => $assoc)
{
  echo('<tr>');
  echo('<td>');
  echo('<code>');
  $assoc['device_attribs'] = json_decode($assoc['device_attribs'], TRUE);
  $assoc_dev_text = array();
  if (is_array($assoc['device_attribs']))
  {
    foreach ($assoc['device_attribs'] as $attribute)
    {
      echo(escape_html($attribute['attrib']).' ');
      echo(escape_html($attribute['condition']).' ');
      echo(escape_html($attribute['value']));
      echo('<br />');
      $assoc_dev_text[] = $attribute['attrib'].' '.$attribute['condition'].' '.$attribute['value'];
    }
  } else {
    echo("*");
  }

  echo("</code>");
  echo('</td>');
  echo('<td><code>');
  $assoc['entity_attribs'] = json_decode($assoc['entity_attribs'], TRUE);
  $assoc_entity_text = array();
  if (is_array($assoc['entity_attribs']))
  {
    foreach ($assoc['entity_attribs'] as $attribute)
    {
      echo(escape_html($attribute['attrib']).' ');
      echo(escape_html($attribute['condition']).' ');
      echo(escape_html($attribute['value']));
      echo('<br />');
      $assoc_entity_text[] = $attribute['attrib'].' '.$attribute['condition'].' '.$attribute['value'];
    }
  } else {
    echo("*");
  }
  echo '</code></td>';
  echo '<td style="text-align: right;">';

  if ($_SESSION['userlevel'] == 10)
  {
    echo '<a href="#assoc_edit_modal_' . $assoc['alert_assoc_id'] . '" data-toggle="modal"><i class="icon-cog text-muted"></i></a>&nbsp;
          <a href="#assoc_del_modal_' . $assoc['alert_assoc_id'] . '" data-toggle="modal"><i class="icon-trash text-danger"></i></a>';
  }

  echo('</td>');
  echo('</tr>');

$modals .= '
<div id="assoc_del_modal_'.$assoc['alert_assoc_id'].'" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="delete_alert" aria-hidden="true">
 <form id="edit" name="edit" method="post" class="form" action="">
  <input type="hidden" name="assoc_id" value="'. $assoc['alert_assoc_id'].'">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel"><i class="oicon-minus-circle"></i> Delete Assocation Rule '.$assoc['alert_assoc_id'].'</h3>
  </div>
  <div class="modal-body">

  <span class="help-block">This will delete the selected association rule.</span>
  <fieldset>
    <div class="control-group">
      <label class="control-label" for="confirm">
        Confirm
      </label>
      <div class="controls">
        <label class="checkbox">
          <input type="checkbox" name="confirm" value="confirm" onchange="javascript: showWarning'.$assoc['alert_assoc_id'].'(this.checked);" />
          Yes, please delete this association rule!
        </label>

        <script type="text/javascript">
        function showWarning'.$assoc['alert_assoc_id'].'(checked) {
          if (checked) { $(\'#delete_button'.$assoc['alert_assoc_id'].'\').removeAttr(\'disabled\'); } else { $(\'#delete_button'.$assoc['alert_assoc_id'].'\').attr(\'disabled\', \'disabled\'); }
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
    <button id="delete_button'.$assoc['alert_assoc_id'].'" type="submit" class="btn btn-danger" name="submit" value="delete_assoc" disabled><i class="icon-trash icon-white"></i> Delete Association</button>
  </div>
 </form>
</div>
';

$modals .= '
<div id="assoc_edit_modal_'.$assoc['alert_assoc_id'].'" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
 <form id="edit" name="edit" method="post" class="form" action="">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel"><i class="oicon-sql-join-inner"></i> Edit Association Conditions #'.$assoc['alert_assoc_id'].'</h3>
  </div>
  <div class="modal-body">

  <input type="hidden" name="assoc_id" value="'.$assoc['alert_assoc_id'].'">
  <span class="help-block">Please exercise care when editing here.</span>

  <fieldset>
    <div class="control-group">
      <label>Device match</label>
      <div class="controls">
        <textarea class="col-md-12" rows="4" name="assoc_device_conditions">'.escape_html(implode("\n", $assoc_dev_text)).'</textarea>
      </div>
    </div>

    <div class="control-group">
      <label>Entity match</label>
      <div class="controls">
        <textarea class="col-md-12" rows="4" name="assoc_entity_conditions">'.escape_html(implode("\n", $assoc_entity_text)).'</textarea>
      </div>
    </div>
  </fieldset>

  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <button type="submit" class="btn btn-primary" name="submit" value="assoc_conditions"><i class="icon-ok icon-white"></i> Save Changes</button>
  </div>
 </form>
</div>
';

} // End assocation loop

echo('</table>');

echo generate_box_close();

echo('
  </div>
</div>');

echo $modals;

echo '
<div class="row" style="margin-top: 10px;">
  <div class="col-md-12">';

  if ($vars['view'] == 'alert_log')
  {
    print_alert_log($vars);
  } else {
    $vars['pagination'] = TRUE;
    print_alert_table($vars);
  }

echo '

  </div>
</div>';

?>

<?php
if ($_SESSION['userlevel'] == 10) {
?>

<div id="conditions_modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
 <form id="edit" name="edit" method="post" class="form" action="">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel"><i class="oicon-traffic-light"></i> Edit Check Conditions</h3>
  </div>
  <div class="modal-body">
  <span class="help-block">Please exercise care when editing here.</span>
  <fieldset>
    <div class="control-group">
      <div class="controls">
      <select name="alert_and" class="selectpicker">
        <option value="0" data-icon="oicon-or" <?php if ($check['and'] == '0') { echo 'selected'; } ?>>Require any condition</option>
        <option value="1" data-icon="oicon-and" <?php if ($check['and'] == '1') { echo 'selected'; } ?>>Require all conditions</option>
      </select>
     </div>
    </div>
    <div class="control-group">
      <div class="controls">
        <textarea class="col-md-12" rows="4" name="check_conditions"><?php echo(escape_html(implode($condition_text_block, PHP_EOL))); ?></textarea>
      </div>
    </div>
  </fieldset>

  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <button type="submit" class="btn btn-primary" name="submit" value="check_conditions"><i class="icon-ok icon-white"></i> Save Changes</button>
  </div>
 </form>
</div>

<div id="delete_alert_modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="delete_alert" aria-hidden="true">
 <form id="edit" name="edit" method="post" class="form" action="<?php echo(generate_url(array('page' => 'alert_checks'))); ?>">
  <input type="hidden" name="alert_test_id" value="<?php echo($check['alert_test_id']); ?>" />
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel"><i class="oicon-minus-circle"></i> Delete Alert Checker <?php echo($check['alert_test_id']); ?></h3>
  </div>
  <div class="modal-body">

  <span class="help-block">This will completely delete the alert checker and all device/entity associations.</span>
  <fieldset>
    <div class="control-group">
      <label class="control-label" for="confirm">
        Confirm
      </label>
      <div class="controls">
        <label class="checkbox">
          <input type="checkbox" name="confirm" value="confirm" onchange="javascript: showWarning(this.checked);" />
          Yes, please delete this alert checker!
        </label>

 <script type="text/javascript">
        function showWarning(checked) {
          $('#warning').toggle();
          if (checked) {
            $('#delete_button').removeAttr('disabled');
          } else {
            $('#delete_button').attr('disabled', 'disabled');
          }
        }
      </script>

</div>
    </div>
  </fieldset>

        <div class="alert alert-message alert-danger" id="warning" style="display:none;">
    <h4 class="alert-heading"><i class="icon-warning-sign"></i> Warning!</h4>
      This checker and its associations will be completely deleted!
  </div>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <button id="delete_button" type="submit" class="btn btn-danger" name="submit" value="delete_alert_checker" disabled><i class="icon-trash icon-white"></i> Delete Alert</button>
  </div>
 </form>
</div>

<!-- Add association -->

<div id="add_assoc_modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="add_assoc_label" aria-hidden="true">
 <form id="add_assoc" name="add_assoc" method="post" class="form" action="">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="add_assoc_label"><i class="oicon-sql-join-inner"></i> Edit Association Conditions</h3>
  </div>
  <div class="modal-body">

  <span class="help-block">Please exercise care when editing here.</span>

  <fieldset>
    <div class="control-group">
      <label>Device match</label>
      <div class="controls">
        <textarea class="col-md-12" rows="4" name="assoc_device_conditions"></textarea>
      </div>
    </div>

    <div class="control-group">
      <label>Entity match</label>
      <div class="controls">
        <textarea class="col-md-12" rows="4" name="assoc_entity_conditions"></textarea>
      </div>
    </div>
  </fieldset>

  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <button type="submit" class="btn btn-primary" name="submit" value="assoc_add"><i class="icon-ok icon-white"></i> Add Assocation</button>
  </div>
 </form>
</div>

<!-- End add assocation -->

<?php } ?>

<div id="edit_alert_modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
 <form id="edit" name="edit" method="post" class="form" action="">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel"><i class="oicon-sql-join-inner"></i> Edit Checker Details</h3>
  </div>
  <div class="modal-body">

  <input type="hidden" name="alert_test_id" value="<?php echo $check['alert_test_id']; ?>" />
<?php /*
<!-- FIXME This entire form is copied almost verbatim from add_alert_check.inc.php - functionize? note col-md-12 instead of 11 --> */ ?>
  <fieldset>
    <div class="control-group">
      <label class="control-label" for="alert_name">Alert Name</label>
      <div class="controls">
        <input type="text" name="alert_name" size="32" value="<?php echo(escape_html($check['alert_name'])); ?>" placeholder="Alert name"/>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="alert_message">Message</label>
      <div class="controls">
        <textarea class="form-control col-md-12" name="alert_message" rows="3" placeholder="Alert message."><?php echo(escape_html($check['alert_message'])); ?></textarea>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="alert_delay">Alert Delay</label>
      <div class="controls">
        <input type="text" name="alert_delay" size="40" value="<?php echo($check['delay']); ?>" placeholder="&#8470; of checks to delay alert." />
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="alert_send_recovery">Send recovery notification</label>
      <div class="controls"><?php /* HALP. If I enable data-toggle on the below switch/checkbox I don't get the data in the form submission anymore, wtf am I missing? */ ?>
        <input type=checkbox id="alert_send_recovery" name="alert_send_recovery" <?php if (!$check['suppress_recovery']) { echo "checked"; } ?> nope-no-data-toggle="switch-mini" dcata-on-color="danger" dcata-off-color="primary" >
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="alert_severity">Severity</label>
      <div class="controls">
        <select selected name="alert_severity" class="selectpicker"><?php /* There is no database field for this, so we hardcode this */ ?>
          <!-- This option is unimplemented, so anything other than critical is hidden for now -->
          <!-- <option value="info">Informational</option> -->
          <!-- <option value="warn">Warning</option> -->
          <option <?php echo($check['severity']  == 'crit' ? 'selected' : '') ?> value="crit" data-icon="oicon-exclamation-red">Critical</option>
        </select>
      </div>
    </div>
  </fieldset>

  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <button type="submit" class="btn btn-primary" name="submit" value="alert_details"><i class="icon-ok icon-white"></i> Save Changes</button>
  </div>
 </form>
</div>

<?php

// EOF
