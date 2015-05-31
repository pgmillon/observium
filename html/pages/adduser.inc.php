<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2014, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

?>
<h2>Observium User Management: Add User</h2>
<?php

include("usermenu.inc.php");

if ($_SESSION['userlevel'] == '10')
{
  $pagetitle[] = "Add user";
  $errors = array();

  if (auth_usermanagement())
  {
    if ($_POST['action'] == "add")
    {
      if ($_POST['new_username'])
      {
        if (!auth_user_exists($_POST['new_username']))
        {
          if (isset($_POST['can_modify_passwd']))
          {
            $_POST['can_modify_passwd'] = 1;
          } else {
            $_POST['can_modify_passwd'] = 0;
          }

          if (adduser($_POST['new_username'], $_POST['new_password'], $_POST['new_level'], $_POST['new_email'], $_POST['new_realname'], $_POST['can_modify_passwd'], $_POST['new_description']))
          {
            print_success('User ' . $_POST['new_username'] . ' added!');
          }
        } else {
          print_error('User with this name already exists!');
        }
      } else {
        $errors["username"] = "<span class=\"help-inline\">Please enter a username!</span>";
      }

      if (!$_POST['new_password'])
      {
        $errors["passwd"] = "<span class=\"help-inline\">Please enter a password</span>";
      }
    }

?>
<!--  <ul class="nav nav-tabs" id="addBillTab">
    <li class="active"><a href="#properties" data-toggle="tab">User Properties</a></li>
  </ul> -->

  <div class="tabcontent tab-content" id="addUserTabContent" style="min-height: 50px; padding-bottom: 18px;">
    <div class="tab-pane fade active in" id="properties">
      <form name="form1" method="post" action="adduser/" class="form-horizontal">
        <input type="hidden" name="action" value="add">
        <fieldset>
          <legend>User Properties</legend>
          <div class="control-group<?php if (isset($errors["username"])) { echo " error"; } ?>">
            <label class="control-label" for="new_username"><strong>Username</strong></label>
            <div class="controls">
              <input class="col-lg-4" type="text" name="new_username" value="<?php echo $_POST['new_username']; ?>">
              <?php if (isset($errors["username"])) { echo $errors["username"]; } ?>
            </div>
          </div>
          <div class="control-group<?php if (isset($errors["passwd"])) { echo " error"; } ?>">
            <label class="control-label" for="new_password"><strong>Password</strong></label>
            <div class="controls">
              <input class="col-lg-4" type="password" name="new_password" value="<?php echo $_POST['new_password']; ?>">
              <?php if (isset($errors["passwd"])) { echo $errors["passwd"]; } ?>
              &nbsp;<input type="checkbox" checked="checked" name="can_modify_passwd"> Allow the user to change his password.
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="new_realname"><strong>Real Name</strong></label>
            <div class="controls">
              <input class="col-lg-4" type="text" name="new_realname" value="<?php echo $_POST['new_realname']; ?>">
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="new_level"><strong>User Level</strong></label>
            <div class="controls">
              <select name="new_level" class="col-lg-2">
                <option <?php if ($_POST['new_level'] == "1") { echo "selected"; } ?> value="1">Normal User</option>
                <option <?php if ($_POST['new_level'] == "5") { echo "selected"; } ?> value="5">Global Read</option>
                <option <?php if ($_POST['new_level'] == "10") { echo "selected"; } ?> value="10">Administrator</option>
              </select>
            </div>
          </div>
        </fieldset>
        <fieldset>
          <legend>Optional Information</legend>
          <div class="control-group">
            <label class="control-label" for="new_email"><strong>E-mail</strong></label>
            <div class="controls">
              <input class="col-lg-4" type="text" name="new_email" value="<?php echo $_POST['new_email']; ?>">
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="new_description"><strong>Description</strong></label>
            <div class="controls">
              <input class="col-lg-4" type="text" name="new_description" value="<?php echo $_POST['new_description']; ?>">
            </div>
          </div>
        </fieldset>
        <div class="form-actions">
          <button type="submit" class="btn btn-primary"><i class="icon-ok-sign icon-white"></i> <strong>Add User</strong></button>
        </div>
      </form>
    </div>
  </div>
<?php
  } else {
    echo("<div class=\"alert alert-error\">Auth module does not allow user management!</div>");
  }
} else {
  include("includes/error-no-perm.inc.php");
}

// EOF
