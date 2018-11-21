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
    $start = mb_strpos($a, $b, $start + $end);
    $a = substr_replace($a, $bReverse, $start, $end);
    return $a;
}

function mySortForKey($a, $b)
{
    foreach ($a as $k => $v) {
        if (array_key_exists($b, $v)) {
            $bArr[$k] = $v[$b];
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

$mysqli = new mysqli("localhost", "root", "", "test_samson", 3306);
if ($mysqli->connect_errno) {
    echo "Не удалось подключиться к MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

function importXml($a)
{
    global $mysqli;
    $products = simplexml_load_file($a);
    foreach ($products as $k => $product) {

        $category = (array)$product->Разделы;
        foreach ($category as $categoryName) {
            if (is_string($categoryName)) {
                $querySelectCategory = "SELECT * FROM a_category WHERE name='{$categoryName}'";
                $res = $mysqli->query($querySelectCategory);
                $count = $res->num_rows;
                if ($count == 0) {
                    $queryCategory = "INSERT INTO a_category (name) VALUES ('{$categoryName}')";
                    $res = $mysqli->query($queryCategory);
                    $res = $mysqli->query($querySelectCategory);
                    $res = $res->fetch_assoc();
                    $categoryId = $res['id'];
                } else {
                    $res = $res->fetch_assoc();
                    $categoryId = $res['id'];
                }
            } else {
                foreach ($categoryName as $categoryKey => $categoryNameChild) {
                    if ($categoryKey) {
                        $categoryParentId = $categoryKey-1;
                        $query = "SELECT id FROM a_category WHERE name='{$categoryName[$categoryParentId]}'";
                        $res = $mysqli->query($query);
                        $parentId = $res->fetch_row();
                        $querySelectCategory = "SELECT * FROM a_category WHERE name='{$categoryName[$categoryKey]}'
                                                AND parrent_id={$parentId[0]}";
                        $queryInsertCategory = "INSERT INTO a_category (name, parrent_id) 
                                                VALUES ('{$categoryName[$categoryKey]}', {$parentId[0]})";

                    } else {
                        $querySelectCategory = "SELECT * FROM a_category WHERE name='{$categoryName[0]}'";
                        $queryInsertCategory = "INSERT INTO a_category (name) VALUES ('{$categoryName[0]}')";
                    }
                    $res = $mysqli->query($querySelectCategory);
                    $count = $res->num_rows;
                    if ($count == 0) {
                        $mysqli->query($queryInsertCategory);
                        $res = $mysqli->query($querySelectCategory);
                        $res = $res->fetch_assoc();
                        $categoryId = $res['id'];
                    } else {
                        $res = $res->fetch_assoc();
                        $categoryId = $res['id'];
                    }
                }
            }
        }

        foreach ($product->attributes() as $attribute => $value) {
            switch ($attribute) {
                case 'Код':
                    $code = $value;
                    break;
                case 'Название':
                    $name = $value;
                    break;
            }
        }
        $queryProduct = "SELECT * FROM a_product WHERE name=? AND code=? AND category_id=?";
        $res = $mysqli->prepare($queryProduct);
        $res->bind_param('isi', $code, $name, $categoryId);
        $res->execute();
        $res = $res->num_rows;
        if ($res == 0) {
            $queryProduct = "INSERT INTO a_product (code, name, category_id) VALUES (?, ?, ?)";
            $res = $mysqli->prepare($queryProduct);
            $res->bind_param('isi', $code, $name, $categoryId);
            $res->execute();
        }

        foreach ($product->Цена as $price) {
            $typePrice = $price->attributes();

            $queryPrice = "SELECT * FROM a_price WHERE code_product=? AND type_price=? AND price=?";
            $res = $mysqli->prepare($queryPrice);
            $res->bind_param('isi', $code, $typePrice, $price);
            $res->execute();
            $res = $res->num_rows;
            if ($res == 0) {
                $queryPrice = "INSERT INTO a_price (code_product, type_price, price) VALUES (?, ?, ?)";
                $res = $mysqli->prepare($queryPrice);
                $res->bind_param('isi', $code, $typePrice, $price);
                $res->execute();
            }
        }

        foreach ($product->Свойства->children() as $propertyName => $propertyValue) {
            if (!empty($propertyValue->attributes())) {
                foreach ($propertyValue->attributes() as $propertyUnit => $propertyUnitValue) ;
                $querySelectProperty = "SELECT * FROM a_property WHERE code_product={$code} AND property='{$propertyName}'
                                        AND property_value='{$propertyValue}' AND property_setting='{$propertyUnit}'
                                        AND setting_value='{$propertyUnitValue}'";
                $queryInsertProperty = "INSERT INTO a_property (code_product, property, property_value, property_setting, setting_value) 
                                        VALUES ({$code}, '{$propertyName}', '{$propertyValue}', '{$propertyUnit}', '{$propertyUnitValue}')";
            } else {
                $querySelectProperty = "SELECT * FROM a_property WHERE code_product={$code} AND property='{$propertyName}'
                                        AND property_value='{$propertyValue}'";
                $queryInsertProperty = "INSERT INTO a_property (code_product, property, property_value) 
                                        VALUES ({$code}, '{$propertyName}', '{$propertyValue}')";
            }
            $res = $mysqli->query($querySelectProperty);
            $res = $res->num_rows;
            if ($res == 0) {
                $mysqli->query($queryInsertProperty);
            }
        }
    }
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
importXml('product.xml');