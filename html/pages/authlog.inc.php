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
<h2>Observium User Management: Authlog</h2>
<?php

include("usermenu.inc.php");

if ($_SESSION['userlevel'] == '10')
{
  // Pagination
  $vars['pagination'] = TRUE;

  print_authlog($vars);

  $pagetitle[] = 'Authlog';

} else {
  include("includes/error-no-perm.inc.php");
}

// EOF
