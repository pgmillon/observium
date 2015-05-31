<?php

/// Discovery of Sonicwall CPU

if ($device['os'] == "sonicwall")
{
  echo("Sonicwall : ");

  $descr = "Processor";
  $usage = snmp_get($device, ".1.3.6.1.4.1.8741.1.3.1.3.0", "-Ovq");

  if (is_numeric($usage))
  {
    discover_processor($valid['processor'], $device, ".1.3.6.1.4.1.8741.1.3.1.3.0", "0", "Sonicwall CPU", $descr, "1", $usage, NULL, NULL);

  }
}

unset ($processors_array);

?>
