<?php

    $arr = array('grand_parent_id' => 'grand_parent1',
                 'parent_contexts' => array());
        $arr['parent_contexts'][] = array('parent_id' => 'parent1', 'child_contexts' => array(
            array('child_id' => 'parent1-child1'),
            array('child_id' => 'parent1-child2')
        ));

        $arr['parent_contexts'][] = array('parent_id' => 'parent2', 'child_contexts' =>array(
            array('child_id' => 'parent2-child1'),
            array('child_id' => 'parent2-child2')
        ));

  echo json_encode($arr);
