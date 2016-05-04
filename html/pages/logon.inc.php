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

?>
<div class="row" style="margin-top: 50px;">
  <div class="col-sm-12 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2 col-xl-6 col-xl-offset-3">
    <div class="box box-solid" style="background-image: url('images/login-hamster-large.png');  background-position: left 10px top -65px; background-repeat: no-repeat;">
     <div class="login-box">
      <div class="row">
        <div class="col-xs-4 col-sm-4 col-md-4">
        </div>
        <div class="col-xs-8 col-sm-8 col-md-8">
<?php
      $form = array('type'      => 'horizontal',
                    'id'        => 'logonform',
                    //'space'   => '20px',
                    //'title'   => 'Logon',
                    //'icon'    => 'oicon-key',
                    'class'     => NULL, // Use empty class here, to not add additional divs
                    'fieldset'  => array('logon' => 'Please log in:'),
                    );

      $form['row'][0]['username']  = array(
                                      'type'        => 'text',
                                      'fieldset'    => 'logon',
                                      'name'        => 'Username',
                                      'placeholder' => '',
                                      'class'       => 'input-xlarge',
                                      //'width'       => '95%',
                                      'value'       => '');
      $form['row'][1]['password']  = array(
                                      'type'        => 'password',
                                      'fieldset'    => 'logon',
                                      'name'        => 'Password',
                                      'placeholder' => '',
                                      'class'       => 'input-xlarge',
                                      //'width'       => '95%',
                                      'value'       => '');
if (check_extension_exists('mcrypt'))
{
      $form['row'][2]['remember']  = array(
                                      'type'        => 'checkbox',
                                      'fieldset'    => 'logon',
                                      'placeholder' => 'Remember my login');
}
      $form['row'][3]['submit']    = array(
                                      'type'        => 'submit',
                                      'name'        => 'Log in',
                                      'icon'        => 'icon-lock',
                                      //'right'       => TRUE,
                                      'div_class'   => 'controls',
                                      'class'       => 'btn-large');

      print_form($form);
      unset($form);

if (isset($auth_message))
{
  echo('<div class="controls" style="font-weight: bold; color: #cc0000; padding-top: 25px;">' . escape_html($auth_message) . '</div');
}
?>
        </div>
      </div>
     </div>
    </div>
  </div>
</div>

<?php

if (isset($config['login_message']))
{
  echo('<div class=row><div class="col-md-6 col-md-offset-3"><div style="margin-top: 10px;text-align: center; font-weight: bold; color: #cc0000;">'.$config['login_message'].'</div></div></div>');
}
?>
<script type="text/javascript">
<!--
  document.logonform.username.focus();
// -->
</script>

</div>
<?php

// EOF
