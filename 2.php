<?php

// так как функция strrev() не работает с кириллицей то нашел ее реализацию для мультибайтовых кодировок
function mb_strrev($str){
    $r = '';
    for ($i = mb_strlen($str); $i>=0; $i--) {
        $r .= mb_substr($str, $i, 1);
    }
    return $r;
}

function  convertString($a, $b)
{
    $bReverse = mb_strrev($b);
    $start = mb_strpos($a, $b);
    $end = mb_strlen($b);
    $a = substr_replace($a, $bReverse, $start, $end);
    return $a;
}