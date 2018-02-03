<?php

function d($val, $return = false){
    if($return){
        $r = print_r($val, $return);
        return $r;
    } else{
        echo '<pre>';
        print_r($val);
        echo '</pre>';
    }
}

require_once 'Templater.php';

$temlater = new Templater('/testTemplate.tmpl');

$temlater->assign('title', 'My Super Title');
$temlater->assign('myValue', 'bla bla bla');

// d($temlater);

$temlater->render();