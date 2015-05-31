<?php

if (!$os)
{
  if (strstr($sysDescr, "Check Point DDoS Protector")) { $os = "radware"; }
  else if (strstr($sysDescr, "DefensePro")) { $os = "radware"; }

}

// EOF
