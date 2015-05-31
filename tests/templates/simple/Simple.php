<?php

  $arr = array ('name' => "Chris",
    'value' => 10000,
    'in_ca' => TRUE
  );
  $arr['taxed_value'] = $arr['value'] - ($arr['value'] * 0.4);

  echo json_encode($arr);
