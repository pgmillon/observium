<?php

$arr = array('parent' => array(
               'child' => 'child works',
             ),

             'grandparent' => array(
               'parent' => array(
                 'child' => 'grandchild works',
               ),
             )
       );

echo json_encode($arr);
