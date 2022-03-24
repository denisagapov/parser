<?php
use PhpQuery\PhpQuery as phpQuery;
require 'vendor/autoload.php';
$count=10;
$sitemap = 'https://www.electronictoolbox.com/www.electronictoolbox.com-sitemap2.xml';
$content = file_get_contents($sitemap);
$xml = simplexml_load_string($content); // занесение xml в переменную
$listProducts=array(); // массив для json
$i=0;
foreach ($xml->url as $urlElement) {
    $url = $urlElement->loc;
    $pattern = "/.+\/product\//"; // выборка только товаров из всего списка ссылок
    if (preg_match($pattern, $url)) { // поиск свойств дя товаров
        $link=file_get_contents($url);
        $product=phpQuery::newDocument($link);
        $name=$product->find('.fw-bold')->html();
        $price=$product->find('.price:eq(0)')->html();
        $sku=$product->find('.style')->html();
        $description=$product->find('.description-product-content')->html();
        $descriptionReplace=str_replace(['<p>', '</p>', '<b>', '</b>', '<br>', '</br>', '</li>' , '<li>'], "", $description); // удаление тегов
        $brand=$product->find('.content .option .value span a')->html();
        $stock=$product->find('.product-label-text')->html();
        $scriptSearchCategory=$product->find('script:eq(3)')->html();
        $arrayCategory=preg_match("/'category'[ :]+((?=\[)\[[^]]*\]|(?=\{)\{[^\}]*\}|\"[^']*\")/",$scriptSearchCategory,$ms);
        $category=str_replace('"', '', $ms[1]);
        $discount=$product->find('.price:eq(1)')->html();
        $weight=$product->find('div.option div.value span')->eq(2)->html();
        $dimensions=$product->find('div.option div.value span')->eq(3)->text();
        $images=[];
        $k=0;
        $img=$product->find('img');
        foreach($img as $value){
            $val=$value->getAttribute('src');
            if (strpos($val, '.jpeg')) {
            $images[$k]=$val;
            $k++;
        }
        }
        if (!$stock=="Out of stock") {
            $stock="In stock";
        }
        if ($name!="")
        {
        $listProducts[]= [
            "sku"=>trim($sku),
            "name"=>trim($name),
            "description"=>trim($descriptionReplace),
            "category"=>trim($category),
            "images"=>$images,
            "brand"=>trim($brand),
            "stock"=>trim($stock),
            "price"=>trim($price),
            "priceWithoutDiscount"=>trim($discount),
            "weight"=>trim($weight),
            "dimensions"=>trim($dimensions)
        ];
    }}
    $i++;
    echo number_format((float)(($i/$count)*100),2,'.','') . "%" . "\r";
    if($i == $count) {echo $i . " товаров было спарсено!"; break;}
}
function array_unique_key($array, $key)
{
    $tmp = $key_array = array();
    $i = 0;

    foreach ($array as $val) {
        if (!in_array($val[$key], $key_array)) {
            $key_array[$i] = $val[$key];
            $tmp[$i] = $val;
        }
        $i++;
    }
    return $tmp;
}
$listProductsUnique=array_unique_key($listProducts, 'name');
$json = json_encode($listProducts,JSON_UNESCAPED_SLASHES);
file_put_contents('feed.json',$json);

