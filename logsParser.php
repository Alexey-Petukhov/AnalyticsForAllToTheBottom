<?php

// парсер логов, записывает значения из файла в созданную таблицу в базе данных

require_once 'UserAction.php';


$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "bottom";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$lines = file("logs.txt");

foreach ($lines as $line_num => $line) {
    $userAction = ParseRow($line);
        $date = $userAction->getDate();
        $time = $userAction->getTime();
        $actionKey = $userAction->getUniqActionKey();
        $userIP = $userAction->getUserIP();
        $link = $userAction->getLink();
        $category = $userAction->getCategory();
        $product = $userAction->getProduct();
        $stmt = $conn->prepare("INSERT INTO bottom.logs (date, time, action_key, client_ip, link, category, product) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $date,$time , $actionKey, $userIP, $link, $category, $product);
        $stmt->execute();
}

function GetCategoryAndProduct ($link){
    $arr = [];
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
                // success pay of cart # [2]
            } else {
                $category = $arr_path[0];
                if (isset($arr_path[1])) {
                    $product = $arr_path[1];
                }
                $res = array("category"=>$category, "product"=>$product);//$arr_path;
            }
        }
    }
    return $res;
}

function ParseRow($row) {
    $arr = explode(" ", $row);

    $date = $arr[7];
    $time = $arr[8];
    $uniq = trim($arr[9], "[]");
    $userIP = $arr[11];
    $link = trim($arr[12]);
    $arr = GetCategoryAndProduct($link);
    $category = "";
    if (isset($arr['category'])) {
        $category = $arr["category"];
    }
    $product = "";
    if (isset($arr['product'])) {
        $product = $arr["product"];    }

    $userAction =  new UserAction($date, $time, $uniq, $userIP, $link, $category, $product);
    return $userAction;
}

$sql = "SELECT client_ip FROM logs GROUP BY client_ip";
$ips = $conn->query($sql);

//WriteIpsToDB($conn);

foreach ($ips as $ip_num => $ip){
    $client_ip = $ip["client_ip"];
    echo $client_ip." - is client IP";
    $geo = GetGeoByIp($client_ip);
    $country_code = $geo["country_code"];
    $country = $geo["country"];
    // insert ip and country
    $stmt = $conn->prepare("INSERT INTO country_by_ip (client_ip, country, country_code) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $client_ip, $country, $country_code);
    $stmt->execute();

    $fd = fopen("ip.txt", 'a+') or die("не удалось создать файл");
    fwrite($fd, $geo);
    fwrite($fd,"\n");
    fclose($fd);


    echo $client_ip." - ".$country_code." - ".$country."\n";


}

function WriteIpsToDB($conn){
    $fd = fopen("ip.txt", 'r') or die("не удалось открыть файл");
    $i = 0;
    while(!feof($fd))
    {
        $str = (fgets($fd));
        //print_r($str);
        $ipInfo = json_decode($str);
        $ipAddress = $ipInfo->ipAddress;
        $countryCode = $ipInfo->countryCode;
        //print_r($ipInfo->ipAddress);
        $country = "Unknown country";
        if ($ipInfo->countryCode !== "ZZ"){
            $country = $ipInfo->countryName;
        }
        $i++;
        echo "# ".$i.": ip Info: ip: ".$ipAddress.", country: ".$country. ", country code: ".$countryCode.".\n";
//        $stmt = $conn->prepare("INSERT INTO country_by_ip (client_ip, country, country_code) VALUES (?, ?, ?)");
//        $stmt->bind_param("sss", $ipAddress, $country, $countryCode);
//        $stmt->esudo apt-get install php-soap php-curl
    }
    fclose($fd);
}

function GetGeoByIp($ip){
    echo "in function; ip is - ".$ip. "!!!\n";
    $geo = file_get_contents("http://api.db-ip.com/v2/free/{$ip}");
    $geo = json_decode($geo);

    return json_encode($geo);
}

?>