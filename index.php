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

$temlater = new Templater('headerDir/deepFolder/index');

$temlater->assign('headerVar', 'QQQQQQQQQ');
$temlater->assign('title', 'My Super Title');
$temlater->assign('myValue', 'Bla bla bla');
$temlater->assign('someArr', [0 => 'qwe', 1 => 'asd', 2 => 'zxc']);

// d($temlater);

$temlater->display();