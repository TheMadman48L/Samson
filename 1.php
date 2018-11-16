<?php

function findSimple($a, $b)
{
    $simpleNums = array();
    for ($i = $a; $i <= $b; $i++) {
        $isSimple = true;
        for ($j = 2; $j <= sqrt($i); $j++) {
            if ($i % $j == 0) {
                $isSimple = false;
                break;
            }
        }
        if ($isSimple) {
            $simpleNums[] = $i;
        }
    }
    return $simpleNums;
}

function createTrapeze($a)
{
    $trapezeArr = array_chunk($a, 3);
    foreach ($trapezeArr as $v) {
        $resArr[] = [
            'a' => $v[0],
            'b' => $v[1],
            'c' => $v[2],
            ];
    }
    return $resArr;
}

function squareTrapeze($a)
{
    foreach ($a as $k => $v) {
        $square = ($v['a'] + $v['b']) * $v['c'] * 0.5;
        $a[$k]['s'] = $square;
    }
    return $a;
}

function getSizeForLimit($a, $b)
{

    foreach ($a as $k => $v) {
        if ($v['s'] > $b) {
            unset($a[$k]);
        }
    }
    return $a;
}

function getMin($a)
{
    $min = current($a);
    //можно также реализовать через array_shift() если в дальнейшем в данной функции не предстоит работать с
    // передаваемым массивом так, как первый элемент будет удален из массива внутри функции,
    // но временно записан в переменную min
    //$min = array_shift($a);
    foreach ($a as $v) {
        if ($v < $min) {
            $min = $v;
        }
    }
    return $min;
}

function printTrapeze($a)
{
    $table = '<table border="1" cellpadding="5">';
        $table .= '<thead>';
            $table .= '<th>a</th>';
            $table .= '<th>b</th>';
            $table .= '<th>c</th>';
            $table .= '<th>s</th>';
        $table .= '</thead>';
    foreach ($a as $k => $v) {
        $bg = 'bgcolor="white"';
        if ($v['s'] % 2 != 0) {
            $bg = 'bgcolor="#f0f8ff"';
        }
        $table .= "<tr {$bg}>";
            $table .= "<td>{$v['a']}</td>";
            $table .= "<td>{$v['b']}</td>";
            $table .= "<td>{$v['c']}</td>";
            $table .= "<td>{$v['s']}</td>";
        $table .= '</tr>';
    }
    $table .= '</table>';
    return $table;
}

abstract class BaseMath
{
    public function exp1($a, $b, $c)
    {
        return $a * pow( $b,  $c);
    }

    public function exp2($a, $b, $c)
    {
        return pow($a / $b, $c);
    }

    abstract public function getValue();
}

class F1 extends BaseMath
{
    public $a;
    public $b;
    public $c;
    public function __construct($a, $b, $c)
    {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
    }

    public function getValue()
    {
        $exp1 = $this->exp1($this->a, $this->b, $this->c);
        $exp2 = $this->exp2($this->a, $this->b, $this->c);
        $min = min($this->a, $this->b, $this->c);
        $f = $exp1 + pow($exp2 % 3, $min);
        return $f;
    }
}

//var_dump(findSimple(1, 150));

$a = [1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3];

$a = createTrapeze($a);
//var_dump($a);

$a = squareTrapeze($a);
//var_dump($a);

//echo printTrapeze($a);

$b = 30;
$a = getSizeForLimit($a, $b);
//var_dump($a);

$nums = [-3, 4, 5, 2, -5, 7];
//var_dump(getMin($nums));

$f = new F1(7, 3, 2);
//var_dump($f->getValue());
