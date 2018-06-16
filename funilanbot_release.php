<?php
//Get message from user sent
$botToken = "[TelegramBot's token]";
$website = "https://api.telegram.org/bot".$botToken;
$inputdata = file_get_contents('php://input');
$update = json_decode($inputdata, TRUE);
$chat_id = $update["message"]["chat"]["id"];
$chat_username = $update["message"]["chat"]["username"];
$chat_firstname = $update["message"]["chat"]["first_name"];
$message = $update["message"]["text"];

//set mysql's info
$username="[SQL's username]";
$password="[SQL's password]";
$dbname="[SQL's dbname]";


if($message=="/help"){
        sendMessage($chat_id, "此ChatBot針對宜蘭縣內景點、美食及住宿，提供了以下類別供使用者參考，以增加詢問時的效率。\n\r景點：公園/遛小孩/散步/逛街/百貨公司/特色館/博物館/海邊/農場/港口/漁港/步道/廟宇\n\r美食：小吃/餐廳/特產/名產/夜市\n\r住宿：民宿/飯店/汽車旅館\n\r此外，貼心體醒您，若答覆不是您所期望的結果，請再輸入一次以取得所需資訊!");
}else if($message=="/start"){
        sendMessage($chat_id,$chat_firstname."您好，歡迎使用FunilanChatBot :)");
}else{
        //Get user's query by using Luis.ai
        $luisURL="https://westus.api.cognitive.microsoft.com/luis/v2.0/apps/[Application ID]?subscription-key=[Key String]&verbose=true&timezoneOffset=0&q=".urlencode($message);
        $inputdataLuis = file_get_contents($luisURL);
        $querys = json_decode($inputdataLuis, TRUE);
        $message = $querys["intents"][0]["intent"];

        if($message=="打招呼"){
                sendMessage($chat_id,"您好:)");
        }else if($message=="問宜蘭縣景點"){
                if(count($querys["entities"])>0){
                        for ($i=0; $i < count($querys["entities"]); $i++) {
                                $entityType=explode("::",$querys["entities"][$i]["type"]);
                                switch ($entityType[0]) {
                                        case "資訊":
                                                $info=$entityType[1];
                                                $info_value=$querys["entities"][$i]["entity"];
                                                break;
                                        case "城市":
                                                $city=$querys["entities"][$i]["entity"];
                                                break;
                                        default:
                                                sendMessage($chat_id,"error");
                                                break;
                                }
                        }
                        if($info=="類型"){
                                if($city!=null){
                                        $connectSQL = new mysqli("localhost", $username, $password, $dbname);
                                        $sql="SELECT * FROM Place WHERE (PlaceCity LIKE '%".$city."%')&&(PlaceType LIKE '%".$info_value."%')";
                                        $result = $connectSQL->query($sql);
                                        $PlaceName=array();
                                        while ($row = $result->fetch_assoc()) {
                                                array_push($PlaceName,$row['PlaceName']);
                                        }
                                        mysqli_close($connectSQL);
                                        if(count($PlaceName)>0){
                                                $placeChoice = $PlaceName[rand(0,count($PlaceName)-1)];
                                                $emoji="\uD83D\uDD0D";
                                                $placeHref=json_decode('"'.$emoji.'"')."[查看相關資訊](https://www.google.com/search?q=".urlencode($placeChoice).")";
                                                $emoji="\uD83D\uDEA9";
                                                $placeMap=json_decode('"'.$emoji.'"')."[查看位置](https://www.google.com.tw/maps?q=".urlencode($placeChoice).")";
                                                $hashtag="#".$city." #".$info_value;
                                                $totalInfo=$placeChoice."\n\r".$placeHref."\n\r".$placeMap."\n\r".$hashtag;
                                                sendMessage($chat_id,$totalInfo);
                                        }else{
                                                $emoji="\u2757";
                                                sendMessage($chat_id,json_decode('"'.$emoji.'"')."Sorry, 沒有找到符合的資訊".json_decode('"'.$emoji.'"'));
                                        }
                                }else{
                                        $connectSQL = new mysqli("localhost", $username, $password, $dbname);
                                        $sql="SELECT * FROM Place WHERE PlaceType LIKE '%".$info_value."%'";
                                        $result = $connectSQL->query($sql);
                                        $PlaceName=array();
                                        while ($row = $result->fetch_assoc()) {
                                                array_push($PlaceName,$row['PlaceName']);
                                        }
                                        mysqli_close($connectSQL);
                                        if(count($PlaceName)>0){
                                                $placeChoice = $PlaceName[rand(0,count($PlaceName)-1)];
                                                $emoji="\uD83D\uDD0D";
                                                $placeHref=json_decode('"'.$emoji.'"')."[查看相關資訊](https://www.google.com/search?q=".urlencode($placeChoice).")";
                                                $emoji="\uD83D\uDEA9";
                                                $placeMap=json_decode('"'.$emoji.'"')."[查看位置](https://www.google.com.tw/maps?q=".urlencode($placeChoice).")";
                                                $hashtag="#".$city." #".$info_value;
                                                $totalInfo=$placeChoice."\n\r".$placeHref."\n\r".$placeMap."\n\r".$hashtag;
                                                sendMessage($chat_id,$totalInfo);
                                        }else{
                                                $emoji="\u2757";
                                                sendMessage($chat_id,json_decode('"'.$emoji.'"')."Sorry, 沒有找到符合的資訊".json_decode('"'.$emoji.'"'));
                                        }
                                }
                        }else{
                                if($city!=null){
                                        $connectSQL = new mysqli("localhost", $username, $password,$dbname);
                                        $sql="SELECT * FROM Place WHERE PlaceCity LIKE '%".$city."%'";
                                        $result = $connectSQL->query($sql);
                                        $PlaceName=array();
                                        while ($row = $result->fetch_assoc()) {
                                                array_push($PlaceName,$row['PlaceName']);
                                        }
                                        mysqli_close($connectSQL);
                                        if(count($PlaceName)>0){
                                                $placeChoice = $PlaceName[rand(0,count($PlaceName)-1)];
                                                $emoji="\uD83D\uDD0D";
                                                $placeHref=json_decode('"'.$emoji.'"')."[查看相關資訊](https://www.google.com/search?q=".urlencode($placeChoice).")";
                                                $emoji="\uD83D\uDEA9";
                                                $placeMap=json_decode('"'.$emoji.'"')."[查看位置](https://www.google.com.tw/maps?q=".urlencode($placeChoice).")";
                                                $hashtag="#".$city." #景點";
                                                $totalInfo=$placeChoice."\n\r".$placeHref."\n\r".$placeMap."\n\r".$hashtag;
                                                sendMessage($chat_id,$totalInfo);
                                        }else{
                                                $emoji="\u2757";
                                                sendMessage($chat_id,json_decode('"'.$emoji.'"')."Sorry, 沒有找到符合的資訊".json_decode('"'.$emoji.'"'));
                                        }
                                }else{
                                        $emoji="\uD83D\uDE4F";
                                        sendMessage($chat_id,"不好意思，麻煩您輸入完整的鄉鎮市".json_decode('"'.$emoji.'"'));
                                }
                        }
                }else{
                        $emoji="\uD83D\uDE4F";
                        sendMessage($chat_id,"請您換句話，再輸入一次".json_decode('"'.$emoji.'"'));
                }
        }else if($message=="問宜蘭縣美食"){
                if(count($querys["entities"])>0){
                        for ($i=0; $i < count($querys["entities"]); $i++) {
                                $entityType=explode("::",$querys["entities"][$i]["type"]);
                                switch ($entityType[0]) {
                                        case "資訊":
                                                $info=$entityType[1];
                                                $info_value=$querys["entities"][$i]["entity"];
                                                break;
                                        case "城市":
                                                $city=$querys["entities"][$i]["entity"];
                                                break;
                                        default:
                                                sendMessage($chat_id,"error");
                                                break;
                                }
                        }
                        if($info=="類型"){
                                if($city!=null){
                                        $connectSQL = new mysqli("localhost", $username, $password, $dbname);
                                        $sql="SELECT * FROM Eat WHERE (EatCity LIKE '%".$city."%')&&(EatType LIKE '%".$info_value."%')";
                                        $result = $connectSQL->query($sql);
                                        $EatName=array();
                                        while ($row = $result->fetch_assoc()) {
                                                array_push($EatName,$row['EatName']);
                                        }
                                        mysqli_close($connectSQL);
                                        if(count($EatName)>0){
                                                $eatChoice = $EatName[rand(0,count($EatName)-1)];
                                                $emoji="\uD83D\uDD0D";
                                                $eatHref=json_decode('"'.$emoji.'"')."[查看相關資訊](https://www.google.com/search?q=".urlencode($eatChoice).")";
                                                $emoji="\uD83D\uDEA9";
                                                $eatMap=json_decode('"'.$emoji.'"')."[查看位置](https://www.google.com.tw/maps?q=".urlencode($eatChoice).")";
                                                $hashtag="#".$city." #".$info_value;
                                                $totalInfo=$eatChoice."\n\r".$eatHref."\n\r".$eatMap."\n\r".$hashtag;
                                                sendMessage($chat_id,$totalInfo);
                                        }else{
                                                $emoji="\u2757";
                                                sendMessage($chat_id,json_decode('"'.$emoji.'"')."Sorry, 沒有找到符合的資訊".json_decode('"'.$emoji.'"'));
                                        }
                                }else{
                                        $connectSQL = new mysqli("localhost", $username, $password, $dbname);
                                        $sql="SELECT * FROM Eat WHERE EatType LIKE '%".$info_value."%'";
                                        $result = $connectSQL->query($sql);
                                        $EatName=array();
                                        while ($row = $result->fetch_assoc()) {
                                                array_push($EatName,$row['EatName']);
                                        }
                                        mysqli_close($connectSQL);
                                        if(count($EatName)>0){
                                                $eatChoice = $EatName[rand(0,count($EatName)-1)];
                                                $emoji="\uD83D\uDD0D";
                                                $eatHref=json_decode('"'.$emoji.'"')."[查看相關資訊](https://www.google.com/search?q=".urlencode($eatChoice).")";
                                                $emoji="\uD83D\uDEA9";
                                                $eatMap=json_decode('"'.$emoji.'"')."[查看位置](https://www.google.com.tw/maps?q=".urlencode($eatChoice).")";
                                                $hashtag="#".$city." #".$info_value;
                                                $totalInfo=$eatChoice."\n\r".$eatHref."\n\r".$eatMap."\n\r".$hashtag;
                                                sendMessage($chat_id,$totalInfo);
                                        }else{
                                                $emoji="\u2757";
                                                sendMessage($chat_id,json_decode('"'.$emoji.'"')."Sorry, 沒有找到符合的資訊".json_decode('"'.$emoji.'"'));
                                        }
                                }
                        }else{
                                if($city!=null){
                                        $connectSQL = new mysqli("localhost", $username, $password,$dbname);
                                        $sql="SELECT * FROM Eat WHERE EatCity LIKE '%".$city."%'";
                                        $result = $connectSQL->query($sql);
                                        $EatName=array();
                                        while ($row = $result->fetch_assoc()) {
                                                array_push($EatName,$row['EatName']);
                                        }
                                        mysqli_close($connectSQL);
                                        if(count($EatName)>0){
                                                $eatChoice = $EatName[rand(0,count($EatName)-1)];
                                                $emoji="\uD83D\uDD0D";
                                                $eatHref=json_decode('"'.$emoji.'"')."[查看相關資訊](https://www.google.com/search?q=".urlencode($eatChoice).")";
                                                $emoji="\uD83D\uDEA9";
                                                $eatMap=json_decode('"'.$emoji.'"')."[查看位置](https://www.google.com.tw/maps?q=".urlencode($eatChoice).")";
                                                $hashtag="#".$city." #美食";
                                                $totalInfo=$eatChoice."\n\r".$eatHref."\n\r".$eatMap."\n\r".$hashtag;
                                                sendMessage($chat_id,$totalInfo);
                                        }else{
                                                $emoji="\u2757";
                                                sendMessage($chat_id,json_decode('"'.$emoji.'"')."Sorry, 沒有找到符合的資訊".json_decode('"'.$emoji.'"'));
                                        }
                                }else{
                                        $emoji="\uD83D\uDE4F";
                                        sendMessage($chat_id,"不好意思，麻煩您輸入完整的鄉鎮市".json_decode('"'.$emoji.'"'));
                                }
                        }
                }else{
                        $emoji="\uD83D\uDE4F";
                        sendMessage($chat_id,"請您換句話，再輸入一次".json_decode('"'.$emoji.'"'));
                }
        }else if($message=="問宜蘭縣住宿"){
                if(count($querys["entities"])>0){
                        for ($i=0; $i < count($querys["entities"]); $i++) {
                                $entityType=explode("::",$querys["entities"][$i]["type"]);
                                switch ($entityType[0]) {
                                        case "資訊":
                                                $info=$entityType[1];
                                                $info_value=$querys["entities"][$i]["entity"];
                                                break;
                                        case "城市":
                                                $city=$querys["entities"][$i]["entity"];
                                                break;
                                        default:
                                                sendMessage($chat_id,"error");
                                                break;
                                }
                        }
                        if($info=="類型"){
                                if($city!=null){
                                        $connectSQL = new mysqli("localhost", $username, $password, $dbname);
                                        $sql="SELECT * FROM Live WHERE (LiveCity LIKE '%".$city."%')&&(LiveType LIKE '%".$info_value."%')";
                                        $result = $connectSQL->query($sql);
                                        $LiveName=array();
                                        while ($row = $result->fetch_assoc()) {
                                                array_push($LiveName,$row['LiveName']);
                                        }
                                        mysqli_close($connectSQL);
                                        if(count($LiveName)>0){
                                                $liveChoice = $LiveName[rand(0,count($LiveName)-1)];
                                                $emoji="\uD83D\uDD0D";
                                                $liveHref=json_decode('"'.$emoji.'"')."[查看相關資訊](https://www.google.com/search?q=".urlencode($liveChoice).")";
                                                $emoji="\uD83D\uDEA9";
                                                $liveMap=json_decode('"'.$emoji.'"')."[查看位置](https://www.google.com.tw/maps?q=".urlencode($liveChoice).")";
                                                $hashtag="#".$city." #".$info_value;
                                                $totalInfo=$liveChoice."\n\r".$liveHref."\n\r".$liveMap."\n\r".$hashtag;
                                                sendMessage($chat_id,$totalInfo);
                                        }else{
                                                $emoji="\u2757";
                                                sendMessage($chat_id,json_decode('"'.$emoji.'"')."Sorry, 沒有找到符合的資訊".json_decode('"'.$emoji.'"'));
                                        }
                                }
                                else{
                                        $connectSQL = new mysqli("localhost", $username, $password, $dbname);
                                        $sql="SELECT * FROM Live WHERE LiveType LIKE '%".$info_value."%'";
                                        $result = $connectSQL->query($sql);
                                        $LiveName=array();
                                        while ($row = $result->fetch_assoc()) {
                                                array_push($LiveName,$row['LiveName']);
                                        }
                                        mysqli_close($connectSQL);
                                        if(count($LiveName)>0){
                                                $liveChoice = $LiveName[rand(0,count($LiveName)-1)];
                                                $emoji="\uD83D\uDD0D";
                                                $liveHref=json_decode('"'.$emoji.'"')."[查看相關資訊](https://www.google.com/search?q=".urlencode($liveChoice).")";
                                                $emoji="\uD83D\uDEA9";
                                                $liveMap=json_decode('"'.$emoji.'"')."[查看位置](https://www.google.com.tw/maps?q=".urlencode($liveChoice).")";
                                                $hashtag="#".$city." #".$info_value;
                                                $totalInfo=$liveChoice."\n\r".$liveHref."\n\r".$liveMap."\n\r".$hashtag;
                                                sendMessage($chat_id,$totalInfo);
                                        }else{
                                                $emoji="\u2757";
                                                sendMessage($chat_id,json_decode('"'.$emoji.'"')."Sorry, 沒有找到符合的資訊".json_decode('"'.$emoji.'"'));
                                        }
                                }
                        }else{
                                if($city!=null){
                                        $connectSQL = new mysqli("localhost", $username, $password,$dbname);
                                        $sql="SELECT * FROM Live WHERE LiveCity LIKE '%".$city."%'";
                                        $result = $connectSQL->query($sql);
                                        $LiveName=array();
                                        while ($row = $result->fetch_assoc()) {
                                                array_push($LiveName,$row['LiveName']);
                                        }
                                        mysqli_close($connectSQL);
                                        if(count($LiveName)>0){
                                                $liveChoice = $LiveName[rand(0,count($LiveName)-1)];
                                                $emoji="\uD83D\uDD0D";
                                                $liveHref=json_decode('"'.$emoji.'"')."[查看相關資訊](https://www.google.com/search?q=".urlencode($liveChoice).")";
                                                $emoji="\uD83D\uDEA9";
                                                $liveMap=json_decode('"'.$emoji.'"')."[查看位置](https://www.google.com.tw/maps?q=".urlencode($liveChoice).")";
                                                $hashtag="#".$city." #住宿";
                                                $totalInfo=$liveChoice."\n\r".$liveHref."\n\r".$liveMap."\n\r".$hashtag;
                                                sendMessage($chat_id,$totalInfo);
                                        }else{
                                                $emoji="\u2757";
                                                sendMessage($chat_id,json_decode('"'.$emoji.'"')."Sorry, 沒有找到符合的資訊".json_decode('"'.$emoji.'"'));
                                        }
                                }else{
                                        $emoji="\uD83D\uDE4F";
                                        sendMessage($chat_id,"不好意思，麻煩您輸入完整的鄉鎮市".json_decode('"'.$emoji.'"'));
                                }
                        }
                }else{
                        $emoji="\uD83D\uDE4F";
                        sendMessage($chat_id,"請您換句話，再輸入一次".json_decode('"'.$emoji.'"'));
                }
        }else{
                $emoji="\u2753";
                sendMessage($chat_id,json_decode('"'.$emoji.'"')."不好意思，無法理解您的意思，請再輸入一次".json_decode('"'.$emoji.'"'));
        }
}
function sendMessage ($chat_id, $beSend){
        $url = $GLOBALS[website]."/sendMessage?chat_id=".$chat_id."&text=".urlencode($beSend)."&parse_mode=Markdown";
        file_get_contents($url);
}
?>