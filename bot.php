<?php
ob_start();
error_reporting(0);
date_Default_timezone_set('Asia/Tashkent');

/*
Ushbu kod @uzanimedia_bot ni asl kodi bo'lib, ushbu kodni @TokhtasinovUz ( Tokhtasinov Saidabror ) tuzib chiqgan.
Mehnatimni qadrlaysz degan umiddaman. Hammaga raxmat !!!
*/

define('API_TOKEN', getenv('BOT_TOKEN') ?: "8832961212:AAEEDiqTSB6FgYECvvXDhaGlrxYtalezFL0");
$obito_us = "6016128292";
$admins = file_get_contents("admin/admins.txt");
$admin = explode("\n",$admins);
$studio_name = file_get_contents("admin/studio_name.txt");
array_push($admin,$obito_us,1476661984);
$user = file_get_contents("admin/user.txt");
$bot = bot('getme',['bot'])->result->username;
$soat = date('H:i');
$sana = date("d.m.Y");

require ("sql.php");

function getAdmin($chat){
$url = "https://api.telegram.org/bot".API_TOKEN."/getChatAdministrators?chat_id=@".$chat;
$result = file_get_contents($url);
$result = json_decode ($result);
return $result->ok;
}

function deleteFolder($path){
if(is_dir($path) === true){
$files = array_diff(scandir($path), array('.', '..'));
foreach ($files as $file)
deleteFolder(realpath($path) . '/' . $file);
return rmdir($path);
}else if (is_file($path) === true)
return unlink($path);
return false;
}

/* function joinchat($id,$start=null){
global $bot,$status;
$array = [];
$get = file_get_contents("admin/kanal.txt");
$ex = explode("\n",$get);
if($get == null){
return true;
}else{
for($i=1;$i<=count($ex)-1;$i++){
$url = explode("\n",$get)[$i]; 
$name = bot('getchat',['chat_id'=>$url])->result->title;
$ret = bot('getChatMember',['chat_id'=>$url,'user_id'=>$id]);
$stat = $ret->result->status;
if($stat != "creator" and $stat != "administrator" and $stat != "member"){
$array[]=['text'=>"$name",'url'=>"https://t.me/".str_replace('@','',$url)];
$uns = true;
}else{
$array[]=['text'=>"$name",'url'=>"https://t.me/".str_replace('@','',$url)];
}
}
$keyboard2=array_chunk($array,1);
if($start !== null){
$keyboard2[]=[['text'=>"✅Tekshirish",'url'=>"https://t.me/$bot?start=$start"]];
}
if($uns == true and $status == "Oddiy"){
bot('sendMessage',[
'chat_id'=>$id,
'text'=>"<b>📌 Kechirasiz Botdan foydalanish uchun pastdegi kanallarga obuna boling 

Homiy kanalga obuna boling ✅</b>",
'parse_mode'=>"html",
'disable_web_page_preview'=>true,
'reply_markup'=>json_encode(['inline_keyboard'=>$keyboard2]),
]);  
exit();
}else{
return true;
}
}
} */

function getFreshChannelLink($channelId, $storedLink, $connect){
$chatResult = bot('getChat', ['chat_id'=>$channelId]);
if(isset($chatResult->result->username)){
    $link = 'https://t.me/'.$chatResult->result->username;
}elseif(isset($chatResult->result->invite_link) && $chatResult->result->invite_link){
    $link = $chatResult->result->invite_link;
}else{
    return $storedLink;
}
if($link !== $storedLink){
    $esc = $connect->real_escape_string($link);
    $cId = $connect->real_escape_string($channelId);
    $connect->query("UPDATE `channels` SET `channelLink`='$esc' WHERE `channelId`='$cId'");
}
return $link;
}

function joinchat($userId,$key=null){
global $connect,$status,$bot;
$query = $connect->query("SELECT * FROM `channels`");
if($query->num_rows > 0){
$noSubs = 0;
$button = [];
while($row = $query->fetch_assoc()){
$channelId = $row['channelId'];
$channelLink = getFreshChannelLink($channelId, $row['channelLink'], $connect);
if($row['channelType']==="request"){
$checkRequest = $connect->query("SELECT * FROM `joinRequests` WHERE `channelId` = '$channelId' AND `userId` = '$userId'");
if($checkRequest->num_rows == 0){
$noSubs++;
$button[] = ['text'=>"$noSubs - kanal",'url'=>$channelLink];
}
}elseif($row['channelType']==="lock"){
$chatMemberResult = bot('getChatMember',['chat_id'=>$channelId,'user_id'=>$userId]);
$stat = isset($chatMemberResult->result->status) ? $chatMemberResult->result->status : null;
if(!in_array($stat, ['creator','administrator','member','restricted'])){
$noSubs++;
$button[] = ['text'=>"$noSubs - kanal",'url'=>$channelLink];
}
}
}
$keyboard = array_chunk($button,1);
if($key !== null){
$keyboard[]=[['text'=>"✅Tekshirish",'url'=>"https://t.me/$bot?start=$key"]];
}
$reply_markup = json_encode(['inline_keyboard'=>$keyboard]);
if($noSubs > 0){
sms($userId,"<b>Botdan foydalanish uchun quyidagi kanallarga obuna bo\'ling yokida zayafka tashlang❗️</b>",$reply_markup);
exit();
}else return true;
}else return true;
}

function accl($d,$s,$j=false){
return bot('answerCallbackQuery',[
'callback_query_id'=>$d,
'text'=>$s,
'show_alert'=>$j
]);
}

function del(){
global $cid,$mid,$cid2,$mid2;
return bot('deleteMessage',[
'chat_id'=>$cid2.$cid,
'message_id'=>$mid2.$mid,
]);
}


function edit($id,$mid,$tx,$m){
return bot('editMessageText',[
'chat_id'=>$id,
'message_id'=>$mid,
'text'=>$tx,
'parse_mode'=>"HTML",
'disable_web_page_preview'=>true,
'reply_markup'=>$m,
]);
}



function sms($id,$tx,$m){
return bot('sendMessage',[
'chat_id'=>$id,
'text'=>$tx,
'parse_mode'=>"HTML",
'disable_web_page_preview'=>true,
'reply_markup'=>$m,
]);
}



function get($h){
return file_get_contents($h);
}

function put($h,$r){
file_put_contents($h,$r);
}

function bot($method,$datas=[]){
        $url = "https://api.telegram.org/bot".API_TOKEN."/".$method;
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$datas);
        $res = curl_exec($ch);
        if(curl_error($ch)){
                var_dump(curl_error($ch));
        }else{
                return json_decode($res);
        }
}

function containsEmoji($string) {
        // Emoji Unicode diapazonlarini belgilash
        $emojiPattern = '/[\x{1F600}-\x{1F64F}]/u'; // Emotikonlar
        $emojiPattern .= '|[\x{1F300}-\x{1F5FF}]'; // Belgilar va piktograflar
        $emojiPattern .= '|[\x{1F680}-\x{1F6FF}]'; // Transport va xaritalar
        $emojiPattern .= '|[\x{1F700}-\x{1F77F}]'; // Alkimyo belgilar
        $emojiPattern .= '|[\x{1F780}-\x{1F7FF}]'; // Har xil belgilar
        $emojiPattern .= '|[\x{1F800}-\x{1F8FF}]'; // Suv belgilari
        $emojiPattern .= '|[\x{1F900}-\x{1F9FF}]'; // Odatdagilar
        $emojiPattern .= '|[\x{1FA00}-\x{1FA6F}]'; // Qisqichbaqasimon belgilar
        $emojiPattern .= '|[\x{2600}-\x{26FF}]';   // Turli xil belgilar va piktograflar
        $emojiPattern .= '|[\x{2700}-\x{27BF}]';   // Dingbatlar
        $emojiPattern .= '/u';
 
        // Regex orqali tekshirish
        return preg_match($emojiPattern, $string) === 1;
}

function removeEmoji($string) {
        // Emoji Unicode diapazonlarini belgilash
        $emojiPattern = '/[\x{1F600}-\x{1F64F}]/u'; // Emotikonlar
        $emojiPattern .= '|[\x{1F300}-\x{1F5FF}]'; // Belgilar va piktograflar
        $emojiPattern .= '|[\x{1F680}-\x{1F6FF}]'; // Transport va xaritalar
        $emojiPattern .= '|[\x{1F700}-\x{1F77F}]'; // Alkimyo belgilar
        $emojiPattern .= '|[\x{1F780}-\x{1F7FF}]'; // Har xil belgilar
        $emojiPattern .= '|[\x{1F800}-\x{1F8FF}]'; // Suv belgilari
        $emojiPattern .= '|[\x{1F900}-\x{1F9FF}]'; // Odatdagilar
        $emojiPattern .= '|[\x{1FA00}-\x{1FA6F}]'; // Qisqichbaqasimon belgilar
        $emojiPattern .= '|[\x{2600}-\x{26FF}]';   // Turli xil belgilar va piktograflar
        $emojiPattern .= '|[\x{2700}-\x{27BF}]';   // Dingbatlar
        $emojiPattern .= '/u';
 
        // Regex orqali tekshirish
        return preg_replace($emojiPattern, '', $string);
}

function adminsAlert($message){
global $admin;
foreach($admin as $adm){
sms($adm,$message,null);
}
}

function delkey(){
global $cid,$cid2;
$elid=sms($cid.$cid2,"⏳",json_encode(['remove_keyboard'=>true]))->result->messgae_id;
bot('deleteMessage',[
'chat_id'=>$cid.$cid2,
'message_id'=>$elid,
]);
}

$rawInput = isset($GLOBALS['_RAW_INPUT']) ? $GLOBALS['_RAW_INPUT'] : file_get_contents('php://input');
$update = json_decode($rawInput);
$message = $update->message;
$cid = $message->chat->id;
$name = $message->chat->first_name;
$tx = $message->text;
$step = file_get_contents("step/$cid.step");
$mid = $message->message_id;
$type = $message->chat->type;
$text = $message->text;
$uid= $message->from->id;
$name = $message->from->first_name;
$familya = $message->from->last_name;
$bio = $message->from->about;
$username = $message->from->username;
$chat_id = $message->chat->id;
$message_id = $message->message_id;
$reply = $message->reply_to_message->text;
$nameru = "<a href='tg://user?id=$uid'>$name $familya</a>";

$botdel = $update->my_chat_member->new_chat_member; 
$botdelid = $update->my_chat_member->from->id; 
$userstatus= $update->status; 

$joinRequest = $update->chat_join_request;
$joinChatId = $joinRequest->chat->id;
$joinUserId = $joinRequest->from->id;

if(isset($joinRequest) and !empty($joinChatId) and !empty($joinUserId)){
$query = $connect->query("SELECT * FROM `joinRequests` WHERE `channelId` = '$joinChatId' AND `userId` = '$joinUserId'");
if($query->num_rows == 0){
$connect->query("INSERT INTO `joinRequests` (`channelId`, `userId`) VALUES ('$joinChatId', '$joinUserId')");
}
}

//inline uchun metodlar
$data = $update->callback_query->data;
$qid = $update->callback_query->id;
$id = $update->inline_query->id;
$query = $update->inline_query->query;
$query_id = $update->inline_query->from->id;
$cid2 = $update->callback_query->message->chat->id;
$mid2 = $update->callback_query->message->message_id;
$callfrid = $update->callback_query->from->id;
$callname = $update->callback_query->from->first_name;
$calluser = $update->callback_query->from->username;
$surname = $update->callback_query->from->last_name;
$about = $update->callback_query->from->about;
$nameuz = "<a href='tg://user?id=$callfrid'>$callname $surname</a>";
$giveStatus = file_get_contents("admin/giveaway.txt");
$giveText = file_get_contents("admin/giveaway_text.txt");

if(isset($data)){
$chat_id=$cid2;
$message_id=$mid2;
}

$photo = $message->photo;
$file = (is_array($photo) && count($photo) > 0) ? $photo[count($photo)-1]->file_id : null;

//tugmalar
if(file_get_contents("tugma/key1.txt")){
        }else{
                if(file_put_contents("tugma/key1.txt","🔎 Anime izlash"));
        }
if(file_get_contents("tugma/key2.txt")){
        }else{
                if(file_put_contents("tugma/key2.txt","💎 VIP"));
        }
if(file_get_contents("tugma/key3.txt")){
        }else{
                if(file_put_contents("tugma/key3.txt","💰 Hisobim"));
        }
if(file_get_contents("tugma/key4.txt")){
        }else{
                if(file_put_contents("tugma/key4.txt","➕ Pul kiritish"));
        }
if(file_get_contents("tugma/key5.txt")){
        }else{
                if(file_put_contents("tugma/key5.txt","📚 Qo'llanma"));
        }
if(file_get_contents("tugma/key6.txt")){
        }else{
                if(file_put_contents("tugma/key6.txt","💵 Reklama va Homiylik"));
        }
        
//pul va referal sozlamalar

if(file_get_contents("admin/valyuta.txt")){
        }else{
                if(file_put_contents("admin/valyuta.txt","so'm"));
}

if(file_get_contents("admin/vip.txt")){
        }else{
                if(file_put_contents("admin/vip.txt","25000"));
}

if(file_get_contents("admin/holat.txt")){
        }else{
                if(file_put_contents("admin/holat.txt","Yoqilgan"));
}

if(file_exists("admin/anime_kanal.txt")==false){
file_put_contents("admin/anime_kanal.txt","@username");
}

//matnlar
if(file_get_contents("matn/start.txt")){
}else{
if(file_put_contents("matn/start.txt","✨"));
}

$res = mysqli_query($connect,"SELECT*FROM user_id WHERE user_id=$chat_id");
while($a = mysqli_fetch_assoc($res)){
$user_id = $a['user_id'];
$status = $a['status'];
$taklid_id = $a['refid'];
$from_id = $a['id'];
$usana = $a['sana'];
}

$res = mysqli_query($connect,"SELECT*FROM kabinet WHERE user_id=$chat_id");
while($a = mysqli_fetch_assoc($res)){
$k_id = $a['user_id'];
$pul = $a['pul'];
$pul2 = $a['pul2'];
$odam = $a['odam'];
$ban = $a['ban'];
}

$key1 = file_get_contents("tugma/key1.txt");
$key2 = file_get_contents("tugma/key2.txt");
$key3 = file_get_contents("tugma/key3.txt");
$key4 = file_get_contents("tugma/key4.txt");
$key5 = file_get_contents("tugma/key5.txt");
$key6 = file_get_contents("tugma/key6.txt");

$test = file_get_contents("step/test.txt");
$test1 = file_get_contents("step/test1.txt");
$test2 = file_get_contents("step/test2.txt");
$turi = file_get_contents("tizim/turi.txt");
$anime_kanal = file_get_contents("admin/anime_kanal.txt");

$narx = file_get_contents("admin/vip.txt");
$kanal = file_get_contents("admin/kanal.txt");
$valyuta = file_get_contents("admin/valyuta.txt");
$start = str_replace(["%first%","%id%","%botname%","%hour%","%date%"], [$name,$cid,$bot,$soat,$sana],file_get_contents("matn/start.txt"));
$qollanma = str_replace(["%first%","%id%","%hour%","%date%","%user%","%botname%",], [$name,$cid,$soat,$sana,$user,$bot],file_get_contents("matn/qollanma.txt"));
$from_id = mysqli_fetch_assoc(mysqli_query($connect,"SELECT*FROM user_id WHERE user_id = ".($cid2 ?: $cid)))['id'];
$pul3 = mysqli_fetch_assoc(mysqli_query($connect,"SELECT*FROM kabinet WHERE user_id = ".($cid2 ?: $cid)))['pul'];
$odam2 = mysqli_fetch_assoc(mysqli_query($connect,"SELECT*FROM kabinet WHERE user_id = ".($cid2 ?: $cid)))['odam'];
$photo = file_get_contents("matn/photo.txt");
$homiy = file_get_contents("matn/homiy.txt");
$holat = file_get_contents("admin/holat.txt");

mkdir("tizim");
mkdir("step");
mkdir("admin");
mkdir("tugma");
mkdir("matn");

$panel = json_encode([
'resize_keyboard'=>true,
'keyboard'=>[
[['text'=>"📊 Statistika"],['text'=>"✉ Xabar Yuborish"]],
[['text'=>"📬 Post tayyorlash"],
['text'=>"🎥 Animelar sozlash"]],
[['text'=>"📢 Kanallar"],['text'=>"📃 Matnlar"]],
[['text'=>"📋 Adminlar"],['text'=>"🤖 Bot holati"]],
[['text'=>"◀️ Orqaga"]]
]
]);

$asosiy = $panel;

$menu = json_encode([
'resize_keyboard'=>true,
'keyboard'=>[
[['text'=>"$key1"]],
[['text'=>"$key5"],
['text'=>"$key6"]],
]
]);



$menus = json_encode([
'resize_keyboard'=>true,
'keyboard'=>[
[['text'=>"$key1"]],
[['text'=>"$key5"],
['text'=>"$key6"]],
[['text'=>"🗄 Boshqarish"]],
]
]);


/*Profilim sozlamlari*/
$profilim = json_encode([
'resize_keyboard'=>true,
'keyboard'=>[
[['text'=>"$key3"],['text'=>"$key4"]],
[['text'=>"◀️ Orqaga"]]
]
]);
/*Profilim sozlamlari*/


/*konkrus sozlamlar*/

$boshqarish = json_encode([
'resize_keyboard'=>true,
'keyboard'=>[
[['text'=>"🗄 Boshqarish"]],
]
]);

if(in_array($cid,$admin)){
$menyu = $menus;
}else{
$menyu = $menu;
}

if(in_array($cid2,$admin)){
$menyus = $menus;
}else{
$menyus = $menu;
}



//<---- @AlijonovUz ---->//
if($text){
if($ban == "ban"){
exit();
}
}

if($data){
$ban = mysqli_fetch_assoc(mysqli_query($connect,"SELECT*FROM kabinet WHERE user_id = $cid2"))['ban'];
        if($ban == "ban"){
        exit();
}
}

if(isset($message)){
if(!$connect){
bot('sendMessage',[
'chat_id' =>$cid,
'text'=>"⚠️ <b>Xatolik!</b>

<i>Botdan ro'yxatdan o'tish uchun, /start buyrug'ini yuboring!</i>",
'parse_mode' =>'html',
]);
exit();
}
}

if($text){
 if($holat == "O'chirilgan"){
        if(in_array($cid,$admin)){
}else{
        bot('sendMessage',[
        'chat_id'=>$cid,
        'text'=>"⛔️ <b>Bot vaqtinchalik o'chirilgan!</b>

<i>Botda ta'mirlash ishlari olib borilayotgan bo'lishi mumkin!</i>",
'parse_mode'=>'html',
]);
exit();
}
}
}

//Kodning ushbu qismi <---@Padshakh_dev----> tomonidan tahrirlandi>//
if($text == "/admin" or $text == "/panel") {
     if (in_array($cid, $admin))
    bot('sendMessage',[
        'chat_id' => $cid,
        'text' => "admin panelga hush kelibsiz",
        'parse_mode' => "html",
        'reply_markup' => $panel,
        ]);

    exit;
} else {
    if (!in_array($cid, $admin)) {
        bot('sendMessage', [
            'text' => "Siz admin emassiz",
        ]);
    }
}

if($data){
 if($holat == "O'chirilgan"){
        if(in_array($cid2,$admin)){
}else{
        bot('answerCallbackQuery',[
                'callback_query_id'=>$qid,
                'text'=>"⛔️ Bot vaqtinchalik o'chirilgan!

Botda ta'mirlash ishlari olib borilayotgan bo'lishi mumkin!",
                'show_alert'=>true,
                ]);
exit();
}
}
}

if(isset($message)){
$result = mysqli_query($connect,"SELECT * FROM user_id WHERE user_id = $cid");
$row = mysqli_fetch_assoc($result);
if(!$row){
mysqli_query($connect,"INSERT INTO user_id(`user_id`,`status`,`sana`) VALUES ('$cid','Oddiy','$sana')");
}
}

if(isset($message)){
$result = mysqli_query($connect,"SELECT * FROM kabinet WHERE user_id = $cid");
$row = mysqli_fetch_assoc($result);
if(!$row){
mysqli_query($connect,"INSERT INTO kabinet(`user_id`,`pul`,`pul2`,`odam`,`ban`) VALUES ('$cid','0','0','0','unban')");
}
}

if($text == "/start" or $text=="◀️ Orqaga"){
sms($cid,$start,$menyu);
if(file_exists("step/$cid.step")) unlink("step/$cid.step");
exit();
}

if($data == "result"){
del();
if(joinchat($cid2)==true){
sms($cid2,$start,$menyu);
exit();
}
}

//<---- @AlijonovUz ---->//

if(mb_stripos($text,"/start ")!==false and $text != "/start anipass"){
$id = str_replace('/start ','',$text);
if(joinchat($cid,$id)==1){
$rew = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM animelar WHERE id = $id"));
if($rew){
$cs = $rew['qidiruv'] + 1;
mysqli_query($connect,"UPDATE animelar SET qidiruv = $cs WHERE id = $id");
bot('sendPhoto',[
'chat_id'=>$cid,
'photo'=>$rew['rams'],
'caption'=>"<b>‣ Nomi: $rew[nom]</b>

‣ Qismi: $rew[qismi]
‣ Davlati: $rew[davlat]
‣ Tili: $rew[tili]
‣ Yili: $rew[yili]
‣ Janri: $rew[janri]

‣Qidirishlar soni: $cs

‣ $anime_kanal",
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>">YUKLAB OLISH <",'callback_data'=>"yuklanolish=$id=1"]]
]
])
]);
exit();
}else{
sms($cid,$start,$menyu);
exit();
}
}
}



if($data=="close")del();

if($text == $key1 and joinchat($cid)==1){
sms($cid,"<b>🔍Qidiruv tipini tanlang :</b>",json_encode([
'inline_keyboard'=>[
[['text'=>"🏷Anime nomi orqali",'callback_data'=>"searchByName"],['text'=>"⏱ So'ngi yuklanganlar",'callback_data'=>"lastUploads"]],
[['text'=>"💬Janr orqali qidirish",'callback_data'=>"searchByGenre"]],
[['text'=>"📌Kod orqali",'callback_data'=>"searchByCode"],['text'=>"👁️ Eng ko'p ko'rilgan",'callback_data'=>"topViewers"]],
[['text'=>"📚Barcha animelar",'callback_data'=>"allAnimes"]]
]]));
exit();
}

if($data=="searchByName"){
sms($cid2,"<b>Anime nomini yuboring:</b>",$back);
exit();
}

if($data=="lastUploads"){
if($status=="VIP"){
$a =$connect->query("SELECT * FROM `animelar` ORDER BY `sana` DESC LIMIT 0,10");
$i=1;
while($s = mysqli_fetch_assoc($a)){
$uz[] = ['text'=>"$i - $s[nom]",'callback_data'=>"loadAnime=$s[id]"];
}
$keyboard2=array_chunk($uz,1);
$kb=json_encode([
'inline_keyboard'=>$keyboard2,
]);
edit($cid2,$mid2,"<b>⬇️ Qidiruv natijalari:</b>",$kb);
exit();
}else{
bot('answerCallbackQuery',[
'callback_query_id'=>$qid,
'text'=>"Ushbu funksiyadan foydalanish uchun $key2 sotib olishingiz zarur!",
'show_alert'=>true,
]);
}
}

if($data=="topViewers"){
if($status=="VIP"){
$a =$connect->query("SELECT * FROM `animelar` ORDER BY `qidiruv` ASC LIMIT 0,10");
$i=1;
while($s = mysqli_fetch_assoc($a)){
$uz[] = ['text'=>"$i - $s[nom]",'callback_data'=>"loadAnime=$s[id]"];
$i++;
}
$keyboard2=array_chunk($uz,1);
$kb=json_encode([
'inline_keyboard'=>$keyboard2,
]);
edit($cid2,$mid2,"<b>⬇️ Qidiruv natijalari:</b>",$kb);
exit();
}else{
bot('answerCallbackQuery',[
'callback_query_id'=>$qid,
'text'=>"Ushbu funksiyadan foydalanish uchun $key2 sotib olishingiz zarur!",
'show_alert'=>true,
]);
}
}

if(mb_stripos($data,"loadAnime=")!==false){
$n=explode("=",$data)[1];
del();
$rew = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM animelar WHERE id = $n"));
$a = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM `anime_datas` WHERE `id` = $n ORDER BY `qism` ASC LIMIT 1"));
if(in_array($cid2,$admin)) $delKey="🗑️ O‘chirish";
bot('sendPhoto',[
'chat_id'=>$cid2,
'photo'=>$rew['rams'],
'caption'=>"<b>‣ Nomi: $rew[nom]</b>

‣ Qismi: $rew[qismi]
‣ Davlati: $rew[davlat]
‣ Tili: $rew[tili]
‣ Yili: $rew[yili]
‣ Janri: $rew[janri]

‣Qidirishlar soni: $rew[qidiruv]

‣ $anime_kanal",
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>">YUKLAB OLISH <",'callback_data'=>"yuklanolish=$n=$a[qism]"]],
[['text'=>"$delKey",'callback_data'=>"deleteAnime=$n=1"]],
]
])
]);
}

if(mb_stripos($data,"deleteAnime=")!==false){
$n=explode("=",$data)[1];
$res=explode("=",$data)[2];
if($res=="1"){
del();
sms($cid2,"<b>❗O‘chirishga ishonchingiz komilmi?</b>",json_encode([
'inline_keyboard'=>[
[['text'=>"✅ Tasdiqlash",'callback_data'=>"deleteEpisode=$n=$nid=2"]],
[['text'=>"🔙 Orqaga",'callback_data'=>"yuklanolish=$n=$nid"]]
]]));
}elseif($res=="2"){
mysqli_query($connect,"DELETE FROM animelar WHERE id = $n");
mysqli_query($connect,"DELETE FROM anime_datas WHERE id = $n");
del();
sms($cid2,"<b>Bosh menyuga qaytdingiz,</b> anime o‘chirildi!",null);
}
}


if(mb_stripos($data,"yuklanolish=")!==false){
$n=explode("=",$data)[1];
$nid=explode("=",$data)[2];
$last=explode("=",$data)[3];
$curr=ceil($nid/25)*25;
$nn=$curr-25;
del();
if(isset($last)){
$rew = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM anime_datas WHERE id = $n AND qism = $last"));
bot($media_type,[
'chat_id'=>$cid2,
$media_key=>$rew['file_id'],
'caption'=>"<b>$cnom</b>

$last-qism",
'parse_mode'=>"html"
]);
}else{
$rew = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM animelar WHERE id = $n"));
$media_type = ($first_char == 'B') ? 'sendVideo' : 'sendPhoto'; 
$media_key = ($first_char == 'B') ? 'video' : 'photo'; 
bot($media_type,[
'chat_id'=>$cid2,
$media_key=>$rew['rams'],
'caption'=>"<b>‣ Nomi: $rew[nom]</b>

‣ Qismi: $rew[qismi]
‣ Davlati: $rew[davlat]
‣ Tili: $rew[tili]
‣ Yili: $rew[yili]
‣ Janri: $rew[janri]

‣Qidirishlar soni: $rew[qidiruv]

‣ $anime_kanal",
'parse_mode'=>"html"
]);
}

$cc = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM animelar WHERE id = $n"));
  $cnom = $cc['nom'];

  $rew = mysqli_query($connect, "SELECT * FROM anime_datas WHERE id = $n LIMIT 300");
    while ($a = mysqli_fetch_assoc($rew)) {
        bot('sendVideo', [
            'chat_id' => $cid2,
            'video' => $a['file_id'], // file_id ni olish
            'caption' => "{$a['qism']}-Qism", // Captionni qism raqami bilan qo'shish
        ]);
    }

  
  unlink("profil/Playlists/$cid2.name.txt");
  unlink("profil/Playlists/$cid2.image.txt");
  unlink("profil/Playlists/$cid2.id.txt");
  unlink("profil/likes/$cid2.like.txt");
    unlink("profil/likes/$cid2.deslike.txt");

  $rew = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM anime_datas WHERE id = $n AND qism = $nid"));
//       if($status== "VIP"){
//         bot('sendVideo', [
//     'chat_id' => $cid2,
//     'video' => $a['file_id'],
//     'caption' => "<b>$cnom</b>


// $nid-qism",
//     'parse_mode' => "html",
//     'protect_content' => false,
//     'reply_markup' => $kb
//   ]);
// }else{
//     $res = bot('sendVideo', [
//     'chat_id' => $cid2,
//     'video' => $rew['file_id'],
//     'caption' => "<b>$cnom</b>


// $nid-qism",
//     'parse_mode' => "html",
//     'protect_content' => true,
//     'reply_markup' => $kb
//   ]);
// }

}


if (mb_stripos($data, "deleteEpisode=") !== false) {
        $n = explode("=", $data)[1];
        $nid = explode("=", $data)[2];
        $res = explode("=", $data)[3];
        if ($res == "1") {
                del();
                sms($cid2, "<b>❗O‘chirishga ishonchingiz komilmi?</b>", json_encode([
                        'inline_keyboard' => [
                                [['text' => "✅ Tasdiqlash", 'callback_data' => "deleteEpisode=$n=$nid=2"]],
                                [['text' => "🔙 Orqaga", 'callback_data' => "yuklanolish=$n=$nid"]]
                        ]
                ]));
        } elseif ($res == "2") {
                mysqli_query($connect, "DELETE FROM anime_datas WHERE id = $n AND qism = $nid");
                del();
                sms($cid2, "<b>Bosh menyuga qaytdingiz,</b> animening $nid-qismi o‘chirildi!", null);
        }
}

if (mb_stripos($data, "pagenation=") !== false) {
    $parts = explode("=", $data);
    $anime_id = $parts[1];
    $current_episode = $parts[2];
    $action = $parts[3];

    // Sahifani hisoblash
    $current_page = ceil($current_episode / 25);
    $start_from = ($current_page - 1) * 25;

    if ($action === "back") {
        $start_from = max($start_from - 25, 0);
    } elseif ($action === "next") {
        $start_from += 25;
    }

    $anime = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM animelar WHERE id = $anime_id"));
    if (!$anime) {
        accl($qid, " Anime topilmadi.", true);
        exit;
    }

    $anime_name = $anime['nom'];

    $episodes = mysqli_query($connect, "SELECT * FROM anime_datas WHERE id = $anime_id LIMIT $start_from, 25");
    $total_episodes = mysqli_num_rows($episodes);

    if ($total_episodes == 0) {
        accl($qid, " Qismlar topilmadi.", true);
        exit;
    }

    $buttons = [];
    while ($episode = mysqli_fetch_assoc($episodes)) {
        $episode_number = $episode['qism'];
        if ($episode_number == $current_episode) {
            $buttons[] = ['text' => "[\uD83D\uDCC0] - $episode_number", 'callback_data' => "null"];
        } else {
            $buttons[] = ['text' => "$episode_number", 'callback_data' => "yuklanolish=$anime_id=$episode_number=$current_episode"];
        }
    }


    $keyboard = array_chunk($buttons, 3);

    $keyboard[] = [
        ['text' => "⬅️ Orqaga", 'callback_data' => "pagenation=$anime_id=$current_episode=back"],
        ['text' => "❌ Yopish", 'callback_data' => "close"],
        ['text' => "➡️ Keyingi", 'callback_data' => "pagenation=$anime_id=$current_episode=next"]
    ];

    $reply_markup = json_encode(['inline_keyboard' => $keyboard]);

    $current = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM anime_datas WHERE id = $anime_id LIMIT $start_from, 1"));

    if ($current) {
        bot('deleteMessage', [
            'chat_id' => $cid2,
            'message_id' => $message_id
        ]);

if($status == 'VIP'){
      bot('sendVideo', [
            'chat_id' => $cid2,
            'video' => $current['file_id'],
            'caption' => "<b>$anime_name</b>\n\n$current_episode-qism",
            'parse_mode' => "html",
            'reply_markup' => $reply_markup
        ]);
} elseif($status == 'Oddiy') {
          bot('sendVideo', [
            'chat_id' => $cid2,
            'video' => $current['file_id'],
            'caption' => "<b>$anime_name</b>\n\n$current_episode-qism",
            'parse_mode' => "html",
            'protect_content' => true,
            'reply_markup' => $reply_markup
        ]);
}
    } else {
        accl($qid, "Qismlar topilmadi.", true);
    }

    unlink("profil/likes/$cid2.like.txt");
    unlink("profil/likes/$cid2.deslike.txt");
    unlink("profil/Playlists/$cid2.name.txt");
    unlink("profil/Playlists/$cid2.image.txt");
    unlink("profil/Playlists/$cid2.id.txt");
}

if($data=="allAnimes"){
$result = mysqli_query($connect,"SELECT * FROM animelar");
$count = mysqli_num_rows($result);
$text = "$bot anime botida mavjud bo'lgan barcha animelar ro'yxati 
Barcha animelar soni : $count ta\n\n";
$counter = 1;
while($row = mysqli_fetch_assoc($result)){
$text .= "---- | $counter | ----
Anime kodi : $row[id]
Nomi : $row[nom]
Janri : $row[janri]\n\n";
$counter++;
}
put("step/animes_list_$cid2.txt",$text);
del();
bot('sendDocument',[
'chat_id'=>$cid2,
'document'=>new CURLFile("step/animes_list_$cid2.txt"),
'caption'=>"<b>📝{$bot} Anime botida mavjud bo'lgan $count ta animening ro'yxati</b>",
'parse_mode'=>"html"
]);
unlink("step/animes_list_$cid2.txt");
}

if($data=="searchByCode"){
del();
sms($cid2,"<b>📌 Anime kodini kiriting:</b>",$back);
put("step/$cid2.step",$data);
}


if($step=="searchByCode"){
$rew = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM animelar WHERE id = $text"));
if($rew){
$cs = $rew['qidiruv'] + 1;
mysqli_query($connect,"UPDATE animelar SET qidiruv = $cs WHERE id = $text");
bot('sendPhoto',[
'chat_id'=>$cid,
'photo'=>$rew['rams'],
'caption'=>"<b>‣ Nomi: $rew[nom]</b>

‣ Qismi: $rew[qismi]
‣ Davlati: $rew[davlat]
‣ Tili: $rew[tili]
‣ Yili: $rew[yili]
‣ Janri: $rew[janri]

‣Qidirishlar soni: $cs

‣ $anime_kanal",
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>">YUKLAB OLISH <",'callback_data'=>"yuklanolish=$text=1"]]
]
])
]);
exit();
}else{
sms($cid,"<b>[ $text ] kodiga tegishli anime topilmadi😔</b>

• Boshqa Kod yuboring",null);
exit();
}
}

if($data=="searchByGenre"){
if($status=="VIP"){
del();
sms($cid2,"<b>🔍 Qidirish uchun anime janrini yuboring.</b>
📌Namuna: Syonen",$back);
put("step/$cid2.step",$data);
}else{
bot('answerCallbackQuery',[
'callback_query_id'=>$qid,
'text'=>"Ushbu funksiyadan foydalanish uchun $key2 sotib olishingiz zarur!",
'show_alert'=>true,
]);
}
}

if($step=="searchByGenre"){
if(isset($text)){
$text = mysqli_real_escape_string($connect,$text);
$rew = mysqli_query($connect,"SELECT * FROM animelar WHERE janri LIKE '%$text%' LIMIT 0,10");
$c = mysqli_num_rows($rew);
$i = 1;
while($a = mysqli_fetch_assoc($rew)){
$k[]=['text'=>"$i. $a[nom]",'callback_data'=>"loadAnime=".$a['id']];
$i++;
}
$keyboard2=array_chunk($k,1);
$kb=json_encode([
'inline_keyboard'=>$keyboard2,
]);
if(!$c){
sms($cid,"<b>[ $text ] jariga tegishli anime topilmadi😔</b>

• Boshqa janrni alohida yuboring",null);
exit();
}else{
bot('sendMessage',[
'chat_id'=>$cid,
'reply_to_message_id'=>$mid,
'text'=>"<b>⬇️ Qidiruv natijalari:</b>",
'parse_mode'=>"html",
'reply_markup'=>$kb
]);
exit();
}
}
}

// <---- @obito_us ---->


if($text == $key5 and joinchat($cid)==1){
if($qollanma == null){
sms($cid,"<b>🙁 Qo'llanma qo'shilmagan!</b>",null);
exit();
}else{
sms($cid,$qollanma,null);
exit();
}
}

if($text == $key6 and joinchat($cid)==1){
if($homiy == null){
sms($cid,"<b>🙁 Homiylik qo'shilmagan!</b>",null);
exit();
}else{
sms($cid,$homiy,json_encode([
'inline_keyboard'=>[
[['text'=>"☎️ Administrator",'url'=>"tg://user?id=$obito_us"]]
]]));
exit();
}
}

//<----- Admin Panel ------>

if($text == "🗄 Boshqarish"){
if(in_array($cid,$admin)){
bot('SendMessage',[
'chat_id'=>$cid,
'text'=>"<b>Admin paneliga xush kelibsiz!</b>",
'parse_mode'=>'html',
'reply_markup'=>$panel,
]);
unlink("step/$cid.step");
unlink("step/test.txt");
unlink("step/$cid.txt");
exit();
}
}

if($data == "boshqarish"){
        bot('deleteMessage',[
        'chat_id'=>$cid2,
        'message_id'=>$mid2,
        ]);
        bot('SendMessage',[
        'chat_id'=>$cid2,
        'text'=>"<b>Admin paneliga xush kelibsiz!</b>",
        'parse_mode'=>'html',
        'reply_markup'=>$panel,
        ]);
        exit();
}

if($text == "📬 Post tayyorlash" and in_array($cid, $admin)) {
    sms($cid, "<b>🆔 Anime kodini kiriting:</b>", $boshqarish);
    put("step/$cid.step", 'createPost');
    exit();
}

if($step == "createPost" and in_array($cid, $admin)) {
    $rew = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM animelar WHERE id = $text"));
    
    if($rew) {
        bot('sendPhoto', [
            'chat_id' => $cid,
            'photo' => $rew['rams'],
            'caption' => "<b>. .  ──── •✧🌸✧🌸✧• ────  . .</b>
‣ <b>Nomi :</b> $rew[nom]
‣ <b>Qism :</b> $rew[qismi]
‣ <b>Janri :</b> $rew[janri]
‣ <b>Tili :</b> $rew[tili]
‣ <b>Ko'rish :</b> <a href='https://t.me/$bot?start=$text'>Tomosha qilish</a>",
            'parse_mode' => "html",
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => "🌸Tomosha qilish 🌸", 'url' => "https://t.me/$bot?start=$text"]],
                    [['text' => "$anime_kanal'ga yuborish", 'callback_data' => "smstoanime_kanal=$text"]]
                ]
            ])
        ]);
        
        sms($cid, "<b>Postingiz tayyorlandi!</b>", $panel);
        unlink("step/$cid.step");
        exit();
    } else {
        sms($cid, "<b>[ $text ] kodiga tegishli anime topilmadi😔</b>\n\n• Boshqa Kod yuboring", null);
        exit();
    }
}

if(mb_stripos($data, "smstoanime_kanal=") !== false) {
    $text = explode("=", $data)[1];
    $rew = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM animelar WHERE id = $text"));
    
    bot('sendPhoto', [
        'chat_id' => $anime_kanal,
        'photo' => $rew['rams'],
        'caption' => "<b>. .  ──── •✧🌸✧🌸✧• ────  . .</b>
‣ <b>Nomi :</b> $rew[nom]
‣ <b>Qism :</b> $rew[qismi]
‣ <b>Janri :</b> $rew[janri]
‣ <b>Tili :</b> $rew[tili]
‣ <b>Ko'rish :</b> <a href='https://t.me/$bot?start=$text'>Tomosha qilish</a>",
        'parse_mode' => "html",
        'reply_markup' => json_encode([
            'inline_keyboard' => [
                [['text' => "🌸Tomosha qilish 🌸", 'url' => "https://t.me/$bot?start=$text"]]
            ]
        ])
    ]);
    
    sms($cid2, "<b>✅ Postingiz kanalga yuborildi!</b>", $panel);
    exit();
}



 if ($d == 'sendMessage' && $cfid == $obito_us) {
    $k = json_encode(['inline_keyboard' => [
        [['text' => 'ðŸ‘¤ Userga', 'callback_data' => 'user_message']],
        [['text' => 'ðŸ‘¥ Oddiy xabar', 'callback_data' => 'simple_message']],
        [['text' => 'ðŸ“© Forward xabar', 'callback_data' => 'forward_message']],
        [['text' => 'ðŸ”™ Ortga', 'callback_data' => 'back']]
    ]]);
    e($ccid, $cmid, "O'zingizga kerakli xabar turini tanlang !:", $k);
    exit();
}

if ($d == "user_message" && $cfid == $obito_us) {
    e($ccid,$cmid, "Foydalanuvchi ID sini kiriting:");
    file_put_contents("step/$ccid.step", 'waiting_user_id');
    exit();
}

if ($step == 'waiting_user_id' && $fid == $obito_us && $txt) {
    file_put_contents("step/$cid.step", "waiting_message_$txt");
    s($cid, "Foydalanuvchiga yuboriladigan xabarni kiriting:");
    exit();
}

if (preg_match('/waiting_message_(\d+)/', $step, $matches) && $fid == $obito_us && $txt) {
    $user_id = $matches[1];
    sendToUser($cid, $user_id, $txt);
    unlink("step/$cid.step");
    exit();
}

if ($d == "simple_message" && $cfid == $obito_us) {
    e($ccid,$cmid, "Hamma foydalanuvchilarga yuboriladigan xabarni kiriting:");
    file_put_contents("step/$ccid.step", 'waiting_broadcast');
    exit();
}

if ($step == 'waiting_broadcast' && $fid == $obito_us && $txt) {
    broadcastMessage($cid, $txt);
    unlink("step/$cid.step");
    exit();
}

if ($d == "forward_message" && $cfid == $obito_us) {
    e($ccid,$cmid, "Forward qilinadigan xabarni yuboring:");
    file_put_contents("step/$ccid.step", 'waiting_forward_message');
    exit();
}

if ($step == 'waiting_forward_message' && $fid == $obito_us) {
    $forward_mid = $mid;
    broadcastMessage($cid, null, true, $forward_mid);
    unlink("step/$cid.step");
    exit();
}

if ($d == 'back') {
    unlink("step/$ccid.step");
    ($cfid == $obito_us) ? showAdminPanel($ccid, $cmid) : showMainMenu($ccid, $cmid);
    exit();
}
// <---- @obito_us ---->
if ($tx == "/stat" or $tx == "📊 Statistika") {
    // Check if the database connection is established
    if ($connect) {
        // Query for total users
        $use = mysqli_query($connect, "SELECT COUNT(*) AS total FROM kabinet");
        if ($use) {
            $row = mysqli_fetch_assoc($use);
            $users = $row['total'];
        } else {
            $users = 0; // Default value if query fails
        }
        
        // Query for users added today
        $sana = date("d.m.Y"); // Hozirgi kunni olish (d.m.Y formatida)
        $to = mysqli_query($connect, "SELECT COUNT(*) AS today FROM user_id WHERE sana='$sana'");
        if ($to) {
            $row = mysqli_fetch_assoc($to);
            $today = $row['today'];
        } else {
            $today = 0; // Default value if query fails
        }
        
        // Calculate the date for 30 days ago (d.m.Y formatida)
        $date_30_days_ago = date('d.m.Y', strtotime('-30 days'));

        // Query for users added in the last 30 days
        $last_30_days_query = mysqli_query($connect, "SELECT COUNT(*) AS last_30_days FROM user_id WHERE STR_TO_DATE(sana, '%d.%m.%Y') >= STR_TO_DATE('$date_30_days_ago', '%d.%m.%Y')");
        if ($last_30_days_query) {
            $row = mysqli_fetch_assoc($last_30_days_query);
            $last_30_days = $row['last_30_days'];
        } else {
            $last_30_days = 0; // Default value if query fails
        }
        
        // Calculate the date for 7 days ago (d.m.Y formatida)
        $date_7_days_ago = date('d.m.Y', strtotime('-7 days'));

        // Query for users added in the last 7 days
        $last_7_days_query = mysqli_query($connect, "SELECT COUNT(*) AS last_7_days FROM user_id WHERE STR_TO_DATE(sana, '%d.%m.%Y') >= STR_TO_DATE('$date_7_days_ago', '%d.%m.%Y')");
        if ($last_7_days_query) {
            $row = mysqli_fetch_assoc($last_7_days_query);
            $last_7_days = $row['last_7_days'];
        } else {
            $last_7_days = 0; // Default value if query fails
        }
        
        $load = sys_getloadavg();

        // Send message with statistics
        sms($cid, "📊 Bot statistikasi:

<b>💡 O'rtacha yuklanish:</b> <code>$load[0]</code>
• Jami botga kirganlar: $users ta
• Bugun qoʻshilganlar: $today ta
• Oxirgi 7 kunda qoʻshilganlar: $last_7_days ta
• Oxirgi 30 kunda qoʻshilganlar: $last_30_days ta", null);
    } else {
        // Handle database connection error
        sms($cid, "❌ Database connection error.", null);
    }

    exit;
}
// <---- @obito_us ---->

// <---- @obito_us ---->

if($text == "📋 Adminlar"){
if(in_array($cid,$admin)){
        if($cid == $obito_us){
        bot('SendMessage',[
        'chat_id'=>$obito_us,
        'text'=>"<b>Quyidagilardan birini tanlang:</b>",
        'parse_mode'=>'html',
        'reply_markup'=>json_encode([
        'inline_keyboard'=>[
   [['text'=>"➕ Yangi admin qo'shish",'callback_data'=>"add"]],
   [['text'=>"📑 Ro'yxat",'callback_data'=>"list"],['text'=>"🗑 O'chirish",'callback_data'=>"remove"]],
        [['text'=>"Orqaga",'callback_data'=>"boshqarish"]]
        ]
        ])
        ]);
        exit();
}else{  
bot('SendMessage',[
        'chat_id'=>$cid,
        'text'=>"<b>Quyidagilardan birini tanlang:</b>",
        'parse_mode'=>'html',
        'reply_markup'=>json_encode([
        'inline_keyboard'=>[
   [['text'=>"📑 Ro'yxat",'callback_data'=>"list"]],
[['text'=>"Orqaga",'callback_data'=>"boshqarish"]]
        ]
        ])
        ]);
        exit();
}
}
}

if($data == "admins"){
if($cid2 == $obito_us){
        bot('deleteMessage',[
        'chat_id'=>$cid2,
        'message_id'=>$mid2,
        ]);     
bot('SendMessage',[
        'chat_id'=>$obito_us,
        'text'=>"<b>Quyidagilardan birini tanlang:</b>",
        'parse_mode'=>'html',
        'reply_markup'=>json_encode([
        'inline_keyboard'=>[
   [['text'=>"➕ Yangi admin qo'shish",'callback_data'=>"add"]],
   [['text'=>"📑 Ro'yxat",'callback_data'=>"list"],['text'=>"🗑 O'chirish",'callback_data'=>"remove"]],
        [['text'=>"Orqaga",'callback_data'=>"boshqarish"]]
        ]
        ])
        ]);
        exit();
}else{
bot('deleteMessage',[
        'chat_id'=>$cid2,
        'message_id'=>$mid2,
        ]);     
bot('SendMessage',[
        'chat_id'=>$cid2,
        'text'=>"<b>Quyidagilardan birini tanlang:</b>",
        'parse_mode'=>'html',
        'reply_markup'=>json_encode([
        'inline_keyboard'=>[
   [['text'=>"📑 Ro'yxat",'callback_data'=>"list"]],
[['text'=>"Orqaga",'callback_data'=>"boshqarish"]]
        ]
        ])
        ]);
        exit();
}
}

if($data == "list"){
$add = str_replace($obito_us,"",$admins);
if($admins == $obito_us){
        $text = "<b>Yordamchi adminlar topilmadi!</b>";
}else{
                $text = "<b>👮 Adminlar ro'yxati:</b>
$add";
}
     bot('editMessageText',[
        'chat_id'=>$cid2,
       'message_id'=>$mid2,
       'text'=>$text,
'parse_mode'=>'html',
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"Orqaga",'callback_data'=>"admins"]],
]
])
]);
}

if($data == "add"){
bot('deleteMessage',[
'chat_id'=>$cid2,
'message_id'=>$mid2,
]);
bot('SendMessage',[
'chat_id'=>$obito_us,
'text'=>"<b>Kerakli foydalanuvchi ID raqamini yuboring:</b>",
'parse_mode'=>'html',
'reply_markup'=>$boshqarish
]);
file_put_contents("step/$cid2.step",'add-admin');
exit();
}
if($step == "add-admin" and $cid == $obito_us){
$result = mysqli_query($connect,"SELECT * FROM user_id WHERE user_id = '$text'");
$row = mysqli_fetch_assoc($result);
if(!$row){
bot('SendMessage',[
'chat_id'=>$cid,
'text'=>"<b>Ushbu foydalanuvchi botdan foydalanmaydi!</b>

Boshqa ID raqamni kiriting:",
'parse_mode'=>'html',
]);
exit();
}elseif((mb_stripos($admins, $text)!==false) or ($text != $obito_us)){
file_put_contents("admin/admins.txt","\n".$text,FILE_APPEND);
bot('SendMessage',[
'chat_id'=>$obito_us,
'text'=>"<code>$text</code> <b>adminlar ro'yxatiga qo'shildi!</b>",
'parse_mode'=>'html',
'reply_markup'=>$panel
]);
unlink("step/$cid.step");
exit();
}else{
bot('SendMessage',[
'chat_id'=>$cid,
'text'=>"<b>Ushbu foydalanuvchi adminlari ro'yxatida mavjud!</b>

Boshqa ID raqamni kiriting:",
'parse_mode'=>'html',
]);
exit();
}
}

if($data == "remove"){
bot('deleteMessage',[
'chat_id'=>$cid2,
'message_id'=>$mid2,
]);
bot('SendMessage',[
'chat_id'=>$obito_us,
'text'=>"<b>Kerakli foydalanuvchi ID raqamini yuboring:</b>",
'parse_mode'=>'html',
'reply_markup'=>$boshqarish
]);
file_put_contents("step/$cid2.step",'remove-admin');
exit();
}
if($step == "remove-admin" and $cid == $obito_us){
$result = mysqli_query($connect,"SELECT * FROM user_id WHERE user_id = '$text'");
$row = mysqli_fetch_assoc($result);
if(!$row){
bot('SendMessage',[
'chat_id'=>$cid,
'text'=>"<b>Ushbu foydalanuvchi botdan foydalanmaydi!</b>

Boshqa ID raqamni kiriting:",
'parse_mode'=>'html',
]);
exit();
}elseif((mb_stripos($admins, $text)!==false) or ($text != $obito_us)){
$files = file_get_contents("admin/admins.txt");
$file = str_replace("\n".$text."","",$files);
file_put_contents("admin/admins.txt",$file);
bot('SendMessage',[
'chat_id'=>$obito_us,
'text'=>"<code>$text</code> <b>adminlar ro'yxatidan olib tashlandi!</b>",
'parse_mode'=>'html',
'reply_markup'=>$panel
]);
unlink("step/$cid.step");
exit();
}else{
bot('SendMessage',[
'chat_id'=>$cid,
'text'=>"<b>Ushbu foydalanuvchi adminlari ro'yxatida mavjud emas!</b>

Boshqa ID raqamni kiriting:",
'parse_mode'=>'html',
]);
exit();
}
}

//<---- @AlijonovUz ---->//

if($text == "🤖 Bot holati"){
        if(in_array($cid,$admin)){
        if($holat == "Yoqilgan"){
                $xolat = "O'chirish";
        }
        if($holat == "O'chirilgan"){
                $xolat = "Yoqish";
        }
        bot('SendMessage',[
        'chat_id'=>$cid,
        'text'=>"<b>Hozirgi holat:</b> $holat",
        'parse_mode'=>'html',
        'reply_markup'=>json_encode([
        'inline_keyboard'=>[
[['text'=>"$xolat",'callback_data'=>"bot"]],
[['text'=>"Orqaga",'callback_data'=>"boshqarish"]]
]
])
]);
exit();
}
}

if($data == "xolat"){
        if($holat == "Yoqilgan"){
                $xolat = "O'chirish";
        }
        if($holat == "O'chirilgan"){
                $xolat = "Yoqish";
        }
        bot('deleteMessage',[
        'chat_id'=>$cid2,
        'message_id'=>$mid2,
        ]);
        bot('SendMessage',[
        'chat_id'=>$cid2,
        'text'=>"<b>Hozirgi holat:</b> $holat",
        'parse_mode'=>'html',
        'reply_markup'=>json_encode([
        'inline_keyboard'=>[
[['text'=>"$xolat",'callback_data'=>"bot"]],
[['text'=>"Orqaga",'callback_data'=>"boshqarish"]]
]
])
]);
exit();
}

if($text == "📢 Kanallar"){
        if(in_array($cid,$admin)){
        bot('SendMessage',[
        'chat_id'=>$cid,
        'text'=>"<b>Quyidagilardan birini tanlang:</b>",
        'parse_mode'=>'html',
        'reply_markup'=>json_encode([
        'inline_keyboard'=>[
        [['text'=>"🔐 Majburiy obunalar",'callback_data'=>"majburiy"]],
        [['text'=>"📌 Qo'shimcha kanalar",'callback_data'=>"qoshimchakanal"]],
        ]
        ])
        ]);
        exit();
}
}

if($data == "kanallar"){
        bot('deleteMessage',[
        'chat_id'=>$cid2,
        'message_id'=>$mid2,
        ]);
        bot('SendMessage',[
        'chat_id'=>$cid2,
        'text'=>"<b>Quyidagilardan birini tanlang:</b>",
        'parse_mode'=>'html',
        'reply_markup'=>json_encode([
        'inline_keyboard'=>[
        [['text'=>"🔐 Majburiy obunalar",'callback_data'=>"majburiy"]],
        [['text'=>"📌 Qo'shimcha kanalar",'callback_data'=>"qoshimchakanal"]],
]
        ])
        ]);
        exit();
}

if($data == "qoshimchakanal"){  
     bot('editMessageText',[
        'chat_id'=>$cid2,
       'message_id'=>$mid2,
'text'=>"<b>Qo'shimcha kanallar sozlash bo'limidasiz:</b>",
'parse_mode'=>'html',
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"🎥 Drama kanal",'callback_data'=>"anime-kanal"]],
[['text'=>"◀️ Orqaga",'callback_data'=>"kanallar"]]
]
])
]);
}

if($data == "anime-kanal"){
        bot('deleteMessage',[
                'chat_id'=>$cid2,
                'message_id'=>$mid2,
                ]);
                bot('SendMessage',[
                'chat_id'=>$cid2,
        'text'=>"<i>Kanalingiz manzilini yuborishdan avval botni kanalingizga admin qilib olishingiz kerak!</i>
        
📢 <b>Kerakli kanalni manzilini yuboring:
        
Namuna:</b> <code>@username</code>",
        'parse_mode'=>'html',
        'reply_markup'=>$boshqarish,
        ]);
        file_put_contents("step/$cid2.step","anime-kanal");
        exit();
}

if($step == "anime-kanal"){
if(in_array($cid,$admin)){
if(isset($text)){               
if(mb_stripos($text, "@")!==false){
$get = bot('getChat',[
'chat_id'=>$text
]);
$types = $get->result->type;
$ch_name = $get->result->title;
$ch_user = $get->result->username;
if(getAdmin($ch_user)== true){
file_put_contents("admin/anime_kanal.txt",$text);
bot('SendMessage',[
'chat_id'=>$cid,
'text'=>"<b>✅ Drama kanal $text.</b>",
'parse_mode'=>'html',
'reply_markup'=>$panel
]);
unlink("step/$cid.step");
exit();
}else{
bot('sendMessage',[
'chat_id'=>$cid,
'text'=>"<b>Bot ushbu kanalda admin emas!</b>

Qayta urinib ko'ring:",
'parse_mode'=>'html',
]);
exit();
}
}else{
bot('SendMessage',[
'chat_id'=>$cid,
'text'=>"<b>Kanal manzilini to'g'ri yuboring:</b>
Namuna: <code>@username</code>",
'parse_mode'=>'html',
]);
exit();
}
}
}
}

if($data == "majburiy"){        
     bot('editMessageText',[
        'chat_id'=>$cid2,
       'message_id'=>$mid2,
'text'=>"<b>🔐Majburiy obunalarni sozlash bo'limidasiz:</b>",
'parse_mode'=>'html',
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"➕ Qo'shish",'callback_data'=>"qoshish"]],
[['text'=>"📑 Ro'yxat",'callback_data'=>"royxat"],['text'=>"🗑 O'chirish",'callback_data'=>"ochirish"]],
[['text'=>"🔙Ortga",'callback_data'=>"kanallar"]]
]
])
]);
}

if($data=="cancel" and in_array($cid2,$admin)){
del();
sms($cid2,"<b>✅Bekor qilindi !</b>",$panel);
}

if($data == "qoshish"){
del();
sms($cid2,"<b>💬Kanal idsini yuboring !</b>",$boshqarish);
file_put_contents("step/$cid2.step","addchannel=id");
exit();
}

if(stripos($step,"addchannel=")!==false and in_array($cid,$admin)){
$ty=str_replace("addchannel=",'',$step);
if($ty=="id" and (is_numeric($text) or stripos($text,"-100")!==false)){
if(stripos($text,"-100")!==false) $text=str_replace("-100",'',$text);
$text="-100".$text;
file_put_contents("step/addchannel.txt",$text);
sms($cid,"<b>🔗Kanal havolasini kiriting !</b>",null);
file_put_contents("step/$cid2.step","addchannel=link");
exit();
}elseif(stripos($text,"https://")!==false){
if(stripos($text,"https://t.me/")!==false or stripos($text,"https://telegram.dog/")!==false or stripos($text,"https://telegram.me/")!==false){
file_put_contents("step/addchannelLink.txt",$text);
delkey();
sms($cid,"<b>⚠️Ushbu kanal zayafka kanal sifatiga qo'shilsinmi ?</b>",json_encode([
'inline_keyboard'=>[
[['text'=>"✅Ha",'callback_data'=>"addChannel=request"],['text'=>"❌Yo‘q",'callback_data'=>"addChannel=lock"]],
[['text'=>"🚫Bekor qilish",'callback_data'=>"cancel"]]
]]));
unlink("step/$cid2.step");
exit();
}else{
sms($cid,"<b>📍Faqat telegram uchun ishlaydi !</b>",null);
exit();
}
}
}

if(stripos($data,"addChannel=")!==false and in_array($cid2,$admin)){
$ty=str_replace("addChannel=",'',$data);
$channelId=file_get_contents("step/addchannel.txt");
$channelLink=file_get_contents("step/addchannelLink.txt");
$sql = "INSERT INTO `channels`(`channelId`,`channelType`,`channelLink`) VALUES ('$channelId','$ty','$channelLink')";
if($connect->query($sql)){
del();
sms($cid2,"<b>✅Majburiy obunaga kanal ulandi !</b>",$panel);
unlink("step/addchannel.txt");
unlink("step/addchannelLink.txt");
}else accl($qid,"⚠️Tizimda xatolik!\n\n".$connect->error,1);
}


if($data == "ochirish"){
$query=$connect->query("SELECT * FROM `channels`");
if($query->num_rows>0){
$soni=$query->num_rows;
$text="<b>✂️Kanalni uzish uchun kanal raqami ustiga bosing !</b>\n";
$co=1;
while($row=$query->fetch_assoc()){
$text .="\n<b>$co.</b> ".$row['channelLink']." | ".$row['channelType'];
$uz[]=['text'=>"🗑️".$co,'callback_data'=>"channelDelete=".$row['id']];
$co++;
}
$e=array_chunk($uz,5);
$e[]=[['text'=>"🔙Ortga",'callback_data'=>"majburiy"]];
$json=json_encode(['inline_keyboard'=>$e]);
$text .= "\n\n<b>Ulangan kanallar soni:</b> $soni ta";
edit($cid2,$mid2,$text,$json);
}else accl($qid,"Hech qanday kanallar ulanmagan!",1);
}

if(stripos($data,"channelDelete=")!==false and in_array($cid2,$admin)){
$ty=str_replace("channelDelete=",'',$data);
$sql = "DELETE FROM `channels` WHERE `id` = '$ty'";
if($connect->query($sql)){
accl($qid,"Kanal uzildi✔️");
$query=$connect->query("SELECT * FROM `channels`");
if($query->num_rows>0){
$soni=$query->num_rows;
$text="<b>✂️Kanalni uzish uchun kanal raqami ustiga bosing !</b>\n";
$co=1;
$uz=[];
while($row=$query->fetch_assoc()){
$text .="\n<b>$co.</b> ".$row['channelLink']." | ".$row['channelType'];
$uz[]=['text'=>"🗑️".$co,'callback_data'=>"channelDelete=".$row['id']];
$co++;
}
$e=array_chunk($uz,5);
$e[]=[['text'=>"🔙Ortga",'callback_data'=>"majburiy"]];
$json=json_encode(['inline_keyboard'=>$e]);
$text .= "\n\n<b>Ulangan kanallar soni:</b> $soni ta";
edit($cid2,$mid2,$text,$json);
}else{
del();
sms($cid2,"<b>☑️Majburiy obuna ulangan kanallar qolmadi !</b>",$panel);
}
}else accl($qid,"⚠️Tizimda xatolik!\n\n".$connect->error,1);
}

if($data == "royxat"){
$query=$connect->query("SELECT * FROM `channels`");
if($query->num_rows>0){
$soni=$query->num_rows;
$text="<b>📢 Kanallar ro'yxati:</b>\n";
$co=1;
while($row=$query->fetch_assoc()){
$text .="\n<b>$co.</b> ".$row['channelLink']." | ".$row['channelType'];
}
$text .= "\n\n<b>Ulangan kanallar soni:</b> $soni ta";
edit($cid2,$mid2,$text,json_encode([
'inline_keyboard'=>[
[['text'=>"🔙Ortga",'callback_data'=>"majburiy"]],
]]));
}else accl($qid,"Hech qanday kanallar ulanmagan!",1);
}

if($data == "bot"){
if($holat == "Yoqilgan"){
file_put_contents("admin/holat.txt","O'chirilgan");
     bot('editMessageText',[
        'chat_id'=>$cid2,
       'message_id'=>$mid2,
       'text'=>"<b>Muvaffaqiyatli o'zgartirildi!</b>",
'parse_mode'=>'html',
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"◀️ Orqaga",'callback_data'=>"xolat"]],
]
])
]);
}else{
file_put_contents("admin/holat.txt","Yoqilgan");
     bot('editMessageText',[
        'chat_id'=>$cid2,
       'message_id'=>$mid2,
       'text'=>"<b>Muvaffaqiyatli o'zgartirildi!</b>",
'parse_mode'=>'html',
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"◀️ Orqaga",'callback_data'=>"xolat"]],
]
])
]);
}
}

//<---- @AlijonovUz ---->//

if($text == "⚙ Asosiy sozlamalar"){
                if(in_array($cid,$admin)){
        bot('SendMessage',[
        'chat_id'=>$cid,
        'text'=>"<b>Asosiy sozlamalar bo'limidasiz.</b>",
        'parse_mode'=>'html',
        'reply_markup'=>$asosiy,
        ]);
        exit();
}
}

$delturi = file_get_contents("tizim/turi.txt");
$delmore = explode("\n",$delturi);
$delsoni = substr_count($delturi,"\n");
$key=[];
for ($delfor = 1; $delfor <= $delsoni; $delfor++) {
$title=str_replace("\n","",$delmore[$delfor]);
$key[]=["text"=>"$title - ni o'chirish","callback_data"=>"del-$title"];
$keyboard2 = array_chunk($key, 1);
$keyboard2[] = [['text'=>"➕ Yangi to'lov tizimi qo'shish",'callback_data'=>"new"]];
$pay = json_encode([
'inline_keyboard'=>$keyboard2,
]);
}

if($text == "💳 Hamyonlar"){
                if(in_array($cid,$admin)){
if($turi == null){
bot('SendMessage',[
        'chat_id'=>$cid,
        'text'=>"<b>Quyidagilardan birini tanlang:</b>",
        'parse_mode'=>'html',
                'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"➕ Yangi to'lov tizimi qo'shish",'callback_data'=>"new"]],
]
])
]);
exit();
}else{
        bot('SendMessage',[
        'chat_id'=>$cid,
        'text'=>"<b>Quyidagilardan birini tanlang:</b>",
        'parse_mode'=>'html',
                'reply_markup'=>$pay
]);
exit();
}
}
}

if($data == "hamyon"){
if($turi == null){
bot('deleteMessage',[
        'chat_id'=>$cid2,
        'message_id'=>$mid2,
        ]);
bot('SendMessage',[
        'chat_id'=>$cid2,
        'text'=>"<b>Quyidagilardan birini tanlang:</b>",
        'parse_mode'=>'html',
                'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"➕ Yangi to'lov tizimi qo'shish",'callback_data'=>"new"]],
]
])
]);
exit();
}else{
        bot('deleteMessage',[
        'chat_id'=>$cid2,
        'message_id'=>$mid2,
        ]);
bot('SendMessage',[
        'chat_id'=>$cid2,
        'text'=>"<b>Quyidagilardan birini tanlang:</b>",
        'parse_mode'=>'html',
                'reply_markup'=>$pay
]);
exit();
}
}

//<---- @obito_us ---->//

if(mb_stripos($data,"del-")!==false){
        $ex = explode("-",$data);
        $tur = $ex[1];
        $k = str_replace("\n".$tur."","",$turi);
   file_put_contents("tizim/turi.txt",$k);
bot('deleteMessage',[
        'chat_id'=>$cid2,
        'message_id'=>$mid2,
        ]);
bot('SendMessage',[
        'chat_id'=>$cid2,
        'text'=>"<b>To'lov tizimi o'chirildi!</b>",
                'parse_mode'=>'html',
        'reply_markup'=>$asosiy
]);
deleteFolder("tizim/$tur");
}

        /*$test = file_get_contents("step/test.txt");
   $k = str_replace("\n".$test."","",$turi);
   file_put_contents("tizim/turi.txt",$k);
deleteFolder("tizim/$test");
unlink("step/test.txt");
exit();*/

if($data == "new"){
        bot('deleteMessage',[
        'chat_id'=>$cid2,
        'message_id'=>$mid2,
   ]);
   bot('sendMessage',[
   'chat_id'=>$cid2,
   'text'=>"<b>Yangi to'lov tizimi nomini yuboring:</b>",
   'parse_mode'=>'html',
   'reply_markup'=>$boshqarish
        ]);
        file_put_contents("step/$cid2.step",'turi');
        exit();
}

if($step == "turi"){
if(in_array($cid,$admin)){
if(isset($text)){
mkdir("tizim/$text");
file_put_contents("tizim/turi.txt","$turi\n$text");
        file_put_contents("step/test.txt",$text);
        bot('SendMessage',[
        'chat_id'=>$cid,
        'text'=>"<b>Ushbu to'lov tizimidagi hamyoningiz raqamini yuboring:</b>",
        'parse_mode'=>'html',
        ]);
        file_put_contents("step/$cid.step",'wallet');
        exit();
}
}
}


if($step == "wallet"){
if(in_array($cid,$admin)){
if(is_numeric($text)=="true"){
file_put_contents("tizim/$test/wallet.txt","$wallet\n$text");
        bot('SendMessage',[
        'chat_id'=>$cid,
        'text'=>"<b>Ushbu to'lov tizimi orqali hisobni to'ldirish bo'yicha ma'lumotni yuboring:</b>

<i>Misol uchun, \"Ushbu to'lov tizimi orqali pul yuborish jarayonida izoh kirita olmasligingiz mumkin. Ushbu holatda, biz bilan bog'laning. Havola: @AlijonovUz</i>\"",
'parse_mode'=>'html',
        ]);
        file_put_contents("step/$cid.step",'addition');
        exit();
}else{
bot('SendMessage',[
'chat_id'=>$cid,
'text'=>"<b>Faqat raqamlardan foydalaning!</b>",
'parse_mode'=>'html',
]);
exit();
}
}
}

if($step == "addition"){
                if(in_array($cid,$admin)){
        if(isset($text)){
file_put_contents("tizim/$test/addition.txt","$addition\n$text");
        bot('SendMessage',[
        'chat_id'=>$cid,
        'text'=>"<b>Yangi to'lov tizimi qo'shildi!</b>",
        'parse_mode'=>'html',
        'reply_markup'=>$asosiy,
        ]);
        unlink("step/$cid.step");
        unlink("step/test.txt");
        exit();
}
}
}

// <---- @obito_us ---->

if(mb_stripos($step,"editEpisode-")!==false){
$ex = explode("-",$step);
$tip = $ex[1];
$id = $ex[2];
$qism_raqami = $ex[3];
if($tip=="file_id"){
if(isset($message->video)){
$file_id = $message->video->file_id;
mysqli_query($connect,"UPDATE anime_datas SET file_id='$file_id' WHERE id = $id AND qism = $qism_raqami");
sms($cid,"<b>✅ Saqlandi.</b>",null);
unlink("step/$cid.step");
exit();
}else{
sms($cid,"<b>❗Faqat videodan foydalaning.</b>",null);
exit();
}
}else{
if(is_numeric($text)){
mysqli_query($connect,"UPDATE anime_datas SET $tip='$text' WHERE id = $id AND qism = $qism_raqami");
sms($cid,"<b>✅ Saqlandi.</b>",null);
unlink("step/$cid.step");
exit();
}else{
sms($cid,"<b>❗Faqat raqamlardan foydalaning.</b>",null);
exit();
}
}
}

// <---- @obito_us ---->

if($text == "🎥 Animelar sozlash" and in_array($cid,$admin)){
sms($cid,"<b>Quyidagilardan birini tanlang:</b>",json_encode([
'inline_keyboard'=>[
[['text'=>"➕ Anime qo'shish",'callback_data'=>"add-anime"]],
[['text'=>"📥 Qism qo'shish",'callback_data'=>"add-episode"]],
[['text'=>"📝 Anime tahrirlash",'callback_data'=>"edit-anime"]],
]]));
exit();
}

if($data == "add-anime"){
del();
sms($cid2,"<b>🍿 Anime nomini kiriting:</b>",$boshqarish);
put("step/$cid2.step","anime-name");
}

if($step == "anime-name" and in_array($cid,$admin)){
if(isset($text)){
if(containsEmoji($text)==false){
$text = $connect->real_escape_string($text);
put("step/test.txt",$text);
sms($cid,"<b>🎥 Jami qismlar sonini kiriting:</b>",$boshqarish);
put("step/$cid.step","anime-episodes");
exit();
}else{
sms($cid,"<b>⚠️ Anime qo'shishda emoji va shunga o'xshash maxsus belgilardan foydalanish taqiqlangan!</b>

Qayta urining",null);
}
}
}

if($step == "anime-episodes" and in_array($cid,$admin)){
if(isset($text)){
$text = $connect->real_escape_string($text);
put("step/test2.txt",$text);
sms($cid,"<b>🌍 Qaysi davlat ishlab chiqarganini kiriting:</b>",$boshqarish);
put("step/$cid.step","anime-country");
exit();
}
}

if($step == "anime-country" and in_array($cid,$admin)){
if(isset($text)){
$text = $connect->real_escape_string($text);
put("step/test3.txt",$text);
sms($cid,"<b>🇺🇿 Qaysi tilda ekanligini kiriting:</b>",$boshqarish);
put("step/$cid.step","anime-language");
exit();
}
}

if($step == "anime-language" and in_array($cid,$admin)){
if(isset($text)){
$text = $connect->real_escape_string($text);
put("step/test4.txt",$text);
sms($cid,"<b>📆 Qaysi yilda ishlab chiqarilganini kiriting:</b>",$boshqarish);
put("step/$cid.step","anime-year");
exit();
}
}

if($step == "anime-year" and in_array($cid,$admin)){
if(isset($text)){
$text = $connect->real_escape_string($text);
put("step/test5.txt",$text);
sms($cid,"<b>🎞 Janrlarini kiriting:</b>

<i>Na'muna: Drama, Fantastika, Sarguzash</i>",$boshqarish);
put("step/$cid.step","anime-genre");
exit();
}
}

if($step == "anime-genre" and in_array($cid,$admin)){
if(isset($text)){
$text = $connect->real_escape_string($text);
put("step/test6.txt",$text);
sms($cid,"<b>🏞 Rasmini yuboring:</b>",$boshqarish);
put("step/$cid.step","anime-picture");
exit();
}
}

if($step == "anime-picture" and in_array($cid,$admin)){
if(isset($message->photo)){
$file_id = $message->photo[count($message->photo)-1]->file_id;
$nom = get("step/test.txt");
$qismi = get("step/test2.txt");
$davlati = get("step/test3.txt");
$tili = get("step/test4.txt");
$yili = get("step/test5.txt");
$janri = get("step/test6.txt");
$date = date('H:i d.m.Y');
if($connect->query("INSERT INTO `animelar` (`nom`, `rams`, `qismi`, `davlat`, `tili`, `yili`, `janri`, `qidiruv`, `sana`) VALUES ('$nom', '$file_id', '$qismi', '$davlati', '$tili', '$yili', '$janri', '0', '$date')")==TRUE){
$code = $connect->insert_id;
sms($cid,"<b>✅ Anime qo'shildi!</b>

<b>Anime kodi:</b> <code>$code</code>",$panel);
unlink("step/$cid.step");
unlink("step/test.txt");
unlink("step/test2.txt");
unlink("step/test3.txt");
unlink("step/test4.txt");
unlink("step/test5.txt");
unlink("step/test6.txt");
exit();
}else{
sms($cid,"<b>⚠️ Xatolik!</b>\n\n<code>$connect->error</code>",$panel);
unlink("step/$cid.step");
unlink("step/test.txt");
unlink("step/test2.txt");
unlink("step/test3.txt");
unlink("step/test4.txt");
unlink("step/test5.txt");
unlink("step/test6.txt");
exit();
}
}
}

if($data == "add-episode"){
del();
sms($cid2,"<b>🔢 Anime kodini kiriting:</b>",$boshqarish);
put("step/$cid2.step","episode-code");
}

if($step == "episode-code" and in_array($cid,$admin)){
if(is_numeric($text)){
$text = $connect->real_escape_string($text);
put("step/test.txt",$text);
sms($cid,"<b>🎥 Ushbu kodga tegishlik anime qismini yuboring:</b>",$boshqarish);
put("step/$cid.step","episode-video");
exit();
}
}

if($step == "episode-video" and in_array($cid,$admin)){
if(isset($message->video)){
$file_id = $message->video->file_id;
$id = get("step/test.txt");
$qism = $connect->query("SELECT * FROM anime_datas WHERE id = $id")->num_rows;
$qismi = $qism+1;
$sana = date('H:i:s d.m.Y');
if($connect->query("INSERT INTO anime_datas(id,file_id,qism,sana) VALUES ('$id','$file_id','$qismi','$sana')")==TRUE){
$code = $connect->insert_id;
sms($cid,"<b>✅ $id raqamli animega $qismi-qism yuklandi!</b>

<i>Yana yuklash uchun keyingi qismni yuborsangiz bo'ldi</i>",null);
exit();
}else{
sms($cid,"<b>⚠️ Xatolik!</b>\n\n<code>$connect->error</code>",$panel);
unlink("step/$cid.step");
unlink("step/test.txt");
unlink("step/test2.txt");
exit();
}
}
}

if($data=="edit-anime"){
edit($cid2,$mid2,"<b>Tahrirlamoqchi bo'lgan animeni tanlang:</b>",json_encode([
'inline_keyboard'=>[
[['text'=>"Anime ma'lumotlarini",'callback_data'=>"editType-animes"]],
[['text'=>"Anime qismini",'callback_data'=>"editType-anime_datas"]]
]]));
}

if(mb_stripos($data,"editType-")!==false){
$ex = explode("-",$data)[1];
put("step/$cid2.tip",$ex);
del();
sms($cid2,"<b>Anime kodini kiriting:</b>",$boshqarish);
put("step/$cid2.step","edit-anime");
}

if($step == "edit-anime"){
$tip=get("step/$cid.tip");
if($tip=="animes"){
$result=mysqli_query($connect,"SELECT * FROM animelar WHERE id = $text");
$row=mysqli_fetch_assoc($result);
if($row){
$kb=json_encode([
'inline_keyboard'=>[
[['text'=>"Nomini tahrirlash",'callback_data'=>"editAnime-nom-$text"]],
[['text'=>"Qismini tahrirlash",'callback_data'=>"editAnime-qismi-$text"]],
[['text'=>"Davlatini tahrirlash",'callback_data'=>"editAnime-davlat-$text"]],
[['text'=>"Tilini tahrirlash",'callback_data'=>"editAnime-tili-$text"]],
[['text'=>"Yilini tahrirlash",'callback_data'=>"editAnime-yili-$text"]],
[['text'=>"Janrini tahrirlash",'callback_data'=>"editAnime-janri-$text"]],
]]);
sms($cid,"<b>❓ Nimani tahrirlamoqchisiz?</b>",$kb);
unlink("step/$cid2.step");
exit();
}else{
sms($cid,"<b>❗ Anime mavjud emas, qayta urinib ko'ring!</b>",null);
exit();
}
}else{
$result=mysqli_query($connect,"SELECT * FROM animelar WHERE id = $text");
$row=mysqli_fetch_assoc($result);
if($row){
sms($cid,"<b>Qism raqamini yuboring:</b>",$boshqarish);
put("step/$cid.step","anime-epEdit=$text");
exit();
}else{
sms($cid,"<b>❗ Anime mavjud emas, qayta urinib ko'ring!</b>",null);
exit();
}
}
}

if(mb_stripos($step,"anime-epEdit=")!==false){
$ex = explode("=",$step);
$id = $ex[1];
$result=mysqli_query($connect,"SELECT * FROM anime_datas WHERE id = $id AND qism = $text");
$row=mysqli_fetch_assoc($result);
if($row){
$kb=json_encode([
'inline_keyboard'=>[
[['text'=>"Anime kodini tahrirlash",'callback_data'=>"editEpisode-id-$id-$text"]],
[['text'=>"Qismini tahrirlash",'callback_data'=>"editEpisode-qism-$id-$text"]],
[['text'=>"Videoni tahrirlash",'callback_data'=>"editEpisode-file_id-$id-$text"]],
]]);
sms($cid,"<b>❓ Nimani tahrirlamoqchisiz?</b>",$kb);
unlink("step/$cid.step");
exit();
}else{
sms($cid,"<b>❗ Ushbu animeda $text-qism mavjud emas, qayta urinib ko'ring.</b>",null);
exit();
}
}

if(mb_stripos($data,"editAnime-")!==false){
del();
sms($cid2,"<b>Yangi qiymatini kiriting:</b>",$boshqarish);
put("step/$cid2.step",$data);
}

if(mb_stripos($step,"editAnime-")!==false){
$ex = explode("-",$step);
$tip = $ex[1];
$id = $ex[2];
if($tip=="qismi" and $tip=="yili"){
if(is_numeric($text)){
mysqli_query($connect,"UPDATE animelar SET `$tip`='$text' WHERE id = $id");
sms($cid,"<b>✅ Saqlandi.</b>",null);
unlink("step/$cid.step");
exit();
}else{
sms($cid,"<b>❗Faqat raqamlardan foydalaning.</b>",null);
exit();
}
}else{
if(isset($text)){
mysqli_query($connect,"UPDATE animelar SET `$tip`='$text' WHERE id = $id");
sms($cid,"<b>✅ Saqlandi.</b>",null);
unlink("step/$cid.step");
exit();
}else{
sms($cid,"<b>❗Faqat matnlardan foydalaning.</b>",null);
exit();
}
}
}

if(mb_stripos($data,"editEpisode-")!==false){
del();
sms($cid2,"<b>Yangi qiymatini kiriting:</b>",$boshqarish);
put("step/$cid2.step",$data);
}

if(mb_stripos($step,"editEpisode-")!==false){
$ex = explode("-",$step);
$tip = $ex[1];
$id = $ex[2];
$qism_raqami = $ex[3];
if($tip=="file_id"){
if(isset($message->video)){
$file_id = $message->video->file_id;
mysqli_query($connect,"UPDATE anime_datas SET `file_id`='$file_id' WHERE id = $id AND qism = $qism_raqami");
sms($cid,"<b>✅ Saqlandi.</b>",null);
unlink("step/$cid.step");
exit();
}else{
sms($cid,"<b>❗Faqat videodan foydalaning.</b>",null);
exit();
}
}else{
if(is_numeric($text)){
mysqli_query($connect,"UPDATE anime_datas SET `$tip`='$text' WHERE id = $id AND qism = $qism_raqami");
sms($cid,"<b>✅ Saqlandi.</b>",null);
unlink("step/$cid.step");
exit();
}else{
sms($cid,"<b>❗Faqat raqamlardan foydalaning.</b>",null);
exit();
}
}
}

// <---- @obito_us ---->
        

if(isset($message) and empty($step)){
if(joinchat($cid)==true){
$text = mysqli_real_escape_string($connect,$text);
$rew = mysqli_query($connect,"SELECT * FROM animelar WHERE nom LIKE '%$text%' LIMIT 0,10");
$c = mysqli_num_rows($rew);
$i = 1;
while($a = mysqli_fetch_assoc($rew)){
$k[]=['text'=>"$i. $a[nom]",'callback_data'=>"loadAnime=".$a['id']];
$i++;
}
$keyboard2=array_chunk($k,1);
$kb=json_encode([
'inline_keyboard'=>$keyboard2,
]);
if(!$c){
sms($cid,"🙁 Natija mavjud emas!",null);
}else{
bot('sendMessage',[
'chat_id'=>$cid,
'reply_to_message_id'=>$mid,
'text'=>"<b>⬇️ Qidiruv natijalari:</b>",
'parse_mode'=>"html",
'reply_markup'=>$kb
]);
}
}
}

//<---- @obito_us ---->//