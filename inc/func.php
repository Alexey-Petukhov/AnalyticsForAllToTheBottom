<?php

require_once 'db.php';

if(!empty($_REQUEST)){
    if(function_exists($_REQUEST['action'])){
        call_user_func($_REQUEST['action']);
    }
    die();
}

function getCountries (){
        $post_country = trim($_POST['country']);
        $sql = "SELECT * 
                FROM bottom.country_by_ip";
        if ($post_country !== ""){
            $sql = $sql." WHERE country = '".$post_country."'";
        }

        // exec sql
        $result = R::getAll( $sql);
        $client_ip = "";
        $country = "";
        $country_code = "";
        $res_array = [];
        $res_json ="";
        $str = "";

        foreach ($result as $k => $v){
            $client_ip = $v['client_ip'];
            $country = $v['country'];
            $country_code = $v['country_code'];
            $id = $v['id_country_by_ip'];
            $arr = array('client_ip'=> $client_ip, 'country' => $country, 'country_code' => $country_code);
            array_push($res_array, $arr);
        }
        $res_json = json_encode($result, JSON_FORCE_OBJECT, 2048);

        $result = $res_json;
        echo $result;
}

function getMostActiveCountry(){
    $sql = "SELECT country_code, country, count(*) as cnt_of_acts 
            FROM (  SELECT logs.id_log, logs.client_ip, country , country_code   
                    FROM bottom.logs 
                    INNER JOIN bottom.country_by_ip ON country_by_ip.client_ip = logs.client_ip) res 
            GROUP BY country_code, country 
            ORDER BY cnt_of_acts desc;";

    $result = R::getAll($sql);
    $res_json = json_encode($result, JSON_FORCE_OBJECT, 2048);
    echo $res_json;

}


function loadPerHour(){
    $sql = "SELECT date, hour(time) as h, count(*) as cnt
            FROM bottom.logs
            GROUP BY date,h;";
    $result = R::getAll($sql);
    $res_array = array();
    foreach ($result as $k => $v){
        $date = $v['date'];
        $hour = $v['h'];
        $cnt = $v['cnt'];
        if (intval($hour)  < 10) {$hour = "0".$hour;}
        $arr = array('date'=>$date, 'hour'=>$hour, 'cnt'=> $cnt);
        array_push($res_array, $arr);
    }
    $res_json = json_encode($res_array, JSON_FORCE_OBJECT, 2048);
    echo $res_json;
}

function requestOFCategory (){
    // select categories
    $sql = "SELECT category 
            FROM bottom.logs  
            WHERE category != \"\" 
            GROUP BY category;";
    $result = R::getAll( $sql);
    $res_array = [];
    foreach ( $result as $k => $v ) {
        // for each category get top of countries
        $category  = $v['category'];
        $sql = "SELECT category, country_code, country , count(*) as cnt_of_acts
                FROM (  SELECT logs.id_log, logs.client_ip, country , country_code , category, product
                        FROM bottom.logs
                        INNER JOIN bottom.country_by_ip ON country_by_ip.client_ip = logs.client_ip) res
                WHERE category = '".$category."'
                GROUP BY category, country_code, country
                ORDER BY category, cnt_of_acts desc;";
        $result = R::getAll( $sql);
        $arr = array("category"=>$category, "top_contries"=>$result);
        array_push($res_array, $arr);
    }
    $res_json = json_encode($res_array, JSON_FORCE_OBJECT, 2048);
    echo $res_json;
}


function parseUrl(){
    $sql = "SELECT link FROM bottom.logs GROUP BY link;";
    $result = R::getAll( $sql);
    $res_arr = [];
    foreach ($result as $k => $v){
            if (intval($k) > 50) {
                break;
            }
        $link = $v['link'];

        $link = parse_url($link);
        $category = "";
        $product = "";
        $query = "";
        $path = $link['path'];

        if (!isset($link['query'])){ // значит в ссылке только категорию и возможно товар

            $arr_path = substr($path, 1, -1);
            $arr_path = explode('/', $arr_path) ;

            if (isset($arr_path[0])){
                $str = $arr_path[0];
                $substr = explode('_', $str);
                if (isset($substr[0]) && strcasecmp($substr[0], "success") == 0) {
                    // success pay of card # [2]
                } else {
                    $category = $arr_path[0];
                    if (isset($arr_path[1])) {
                        $product = $arr_path[1];
                    }
                    $res = array("category"=>$category, "product"=>$product);//$arr_path;
                }
            }


        } else {
            $arr_query = $link['query'];
            $arr_path = substr($path, 1);
            if (isset($arr_path[0])){
                if (strcasecmp($arr_path[0],"cart") == 0){
                    $str = $arr_query;
                    $substr = explode('&', $str);
                    $strGoodsId = "";
                    $strAmount = "";
                    $strCartId = "";
                    if (isset($substr[0])){
                        $strGoodsId = $substr[0];
                        if (isset($substr[1])){
                            $strAmount = $substr[1];
                            if (isset($substr[2])){
                                $strCartId = $substr[2];
                            }
                        }
                    }
                    $arrGoodsId = explode("=", $strGoodsId);
                    if (isset($arrGoodsId[0])){
                        $arrGoodsId = array("goods_id"=>$arrGoodsId[1]);
                    }
                    $arrAmount = explode("=", $strAmount);
                    if (isset($arrAmount[0])){
                        $arrAmount = array("amount"=>$arrAmount[1]);
                    }
                    $arrCartId = explode("=", $strCartId);
                    if (isset($arrCartId[0])){
                        $arrCartId = array("carts_id"=>$arrCartId[1]);
                    }
                    $arrQuery = array($arrGoodsId, $arrAmount,$arrCartId);

                }
                if (strcasecmp ($arr_path[0],"pay") == 0 ){
                    $str = $arr_query;
                    $substr = explode('&', $str);
                    $strUID = "";
                    $strCartId = "";
                    if (isset($substr[0])){
                        $strUID = $substr[0];
                        if (isset($substr[1])){
                            $strCartId = $substr[1];
                        }
                    }
                    $arrUID = explode("=", $strUID);
                    if (isset($arrUID[0] )){
                        $arrUID = array("user_id" => $arrUID[1]);
                    }
                    $arrCartId = explode("=", $strCartId);
                    if (isset($arrCartId[0])){
                        $arrCartId = array("carts_id"=>$arrCartId[1]);
                    }
                    $arrQuery = array($arrUID,$arrCartId);

                }
            }
            $res = array("action"=>$arr_path, "query"=> $arrQuery);
        }
        array_push($res_arr,$res);

//        array_push($res_arr,$link);
    }

    $res_json = json_encode($res_arr, JSON_FORCE_OBJECT, 2048);

    echo $res_json;
}