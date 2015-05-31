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

$rrdfile = get_port_rrdfilename($port, "adsl", TRUE);
if (is_file($rrdfile))
{
  $iid = $id;
  echo("<div class=graphhead>ADSL Line Speed</div>");
  $graph_type = "port_adsl_speed";

  include("includes/print-interface-graphs.inc.php");

  echo("<div class=graphhead>ADSL Line Attenuation</div>");
  $graph_type = "port_adsl_attenuation";

  include("includes/print-interface-graphs.inc.php");

  echo("<div class=graphhead>ADSL Line SNR Margin</div>");
  $graph_type = "port_adsl_snr";

  include("includes/print-interface-graphs.inc.php");

  echo("<div class=graphhead>ADSL Output Powers</div>");
  $graph_type = "port_adsl_power";

  include("includes/print-interface-graphs.inc.php");
}

// EOF
