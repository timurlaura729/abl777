<?php 
date_default_timezone_set("Asia/Almaty");
$postData = file_get_contents('php://input');
$data = json_decode($postData, true);
$idmsg=$data['message']['message_id'];
$uid=$data['message']['from']['id'];
$unm=$data['message']['from']['first_name'];
$dt=date("Y-m-d H:i:s", $data['message']['date']);
$ms=$data['message']['text'];
//$msg= print_r($data, true);
require_once("classes/reactionUI.php");
$reactionUI = new reactionUI();
//$reactionUI->saveToBase($msg);
$reactionUI->inital($unm, $uid, $idmsg, $dt, $ms);
$reactionUI->saveMessage();
?>