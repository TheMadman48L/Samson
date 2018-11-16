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

function mySortForKey($a, $b)
{
    foreach ($a as $k => $v) {
        if (array_key_exists($b, $v)) {
            $bArr[] = $v[$b];
        } else {
            throw new Exception("В подмассиве с индексом: {$k} нет элемента с индексом {$b}");
        }
    }

    asort($bArr);

    foreach ($bArr as $k => $v) {
        $aRes[] = $a[$k];
    }

    return $aRes;
}

$a = [
    ['a'=>2,'b'=>1, 'c'=>3],
    ['a'=>1,'b'=>3, 'c'=>1],
    ['a'=>4,'b'=>5, 'c'=>4],
    ['a'=>7,'b'=>2, 'c'=>2],
    ['a'=>2,'b'=>8, 'c'=>5],
];
$b = 'b';
//var_dump(mySortForKey($a, $b));