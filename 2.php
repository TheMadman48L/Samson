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


$db_params = [
    'dsn' => 'mysql:host=localhost;dbname=test_samson;charset=utf8',
    'user' => 'root',
    'pass' => '',
];
try {
    $db = new PDO($db_params['dsn'], $db_params['user'], $db_params['pass']);
}catch (PDOException $e){
    echo $e->getMessage();
}

function importXml($a)
{
    global $db;
    $products = simplexml_load_file($a);
    foreach ($products as $k => $product) {
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
        $queryProduct = "SELECT * FROM a_product WHERE name='{$name}' AND code={$code}";
        $res = $db->query($queryProduct);
        $res = $res->fetchColumn();
        if (!$res) {
            $queryProduct = "INSERT INTO a_product (code, name) VALUES (:code, :name)";
            $res = $db->prepare($queryProduct);
            $res->execute([':code' => $code, ':name' => $name]);
        }

        foreach ($product->Цена as $price) {
            $typePrice = $price->attributes();

            $queryPrice = "SELECT * FROM a_price WHERE code_product={$code} AND type_price='{$typePrice}' AND price={$price}";
            $res = $db->query($queryPrice);
            $res = $res->fetchColumn();
            if (!$res) {
                $queryPrice = "INSERT INTO a_price (code_product, type_price, price) VALUES (:code_product, :type_price, :price)";
                $res = $db->prepare($queryPrice);
                $res->execute([':code_product' => $code, ':type_price' => $typePrice, ':price' => $price]);
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
            $res = $db->query($querySelectProperty);
            $res = $res->fetchColumn();
            if ($res == false) {
                $db->query($queryInsertProperty);
            }
        }

        $category = (array)$product->Разделы;
        foreach ($category as $categoryName) {
            if (is_string($categoryName)) {
                $queryCategory = "SELECT * FROM a_category WHERE name='{$categoryName}'";
                $res = $db->query($queryCategory);
                $res = $res->fetchColumn();
                if ($res == false) {
                    $queryCategory = "INSERT INTO a_category (name) VALUES ('{$categoryName}')";
                    $res = $db->query($queryCategory);
                }
            } else {
                foreach ($categoryName as $categoryKey => $categoryNameChild) {
                    if ($categoryKey) {
                        $categoryParentId = $categoryKey-1;
                        $query = "SELECT id FROM a_category WHERE name='{$categoryName[$categoryParentId]}'";
                        $res = $db->query($query);
                        $parentId = $res->fetch();
                        var_dump($parentId['id']);
                        $querySelectCategory = "SELECT * FROM a_category WHERE name='{$categoryName[$categoryKey]}'
                                                AND parrent_id={$parentId['id']}";
                        $queryInsertCategory = "INSERT INTO a_category (name, parrent_id) 
                                                VALUES ('{$categoryName[$categoryKey]}', {$parentId['id']})";

                    } else {
                        $querySelectCategory = "SELECT * FROM a_category WHERE name='{$categoryName[0]}'";
                        $queryInsertCategory = "INSERT INTO a_category (name) VALUES ('{$categoryName[0]}')";
                    }
                    $res = $db->query($querySelectCategory);
                    $res = $res->fetchColumn();
                    if ($res == false) {
                        $db->query($queryInsertCategory);
                    }
                }
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