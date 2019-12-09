<?php
$url='https://api.telegram.org/bot1059041833:AAHi7sjrHjDh97eWhF266jTvlkua3glSJ90/sendMessage';
$id="567257249";
$message="the text of the message1";
//$buttons="[['text':'LaResistencia.co'],['text':'LaResistencia.co']]";
//$buttons='{"inline_keyboard": [[{"text":"LaResistencia.co", "url": "http://laresistencia.co"}]]} }';
//$buttons=[["exit"]];
$buttons = [["Кнопка 1"],["Кнопка 2"]];
function sendMessage($url,$id,$message,$buttons = null) {
 
$data = array(
'text' => $message,
'chat_id' => $id
);
 
if($buttons != null) {
$data['reply_markup'] = [
'keyboard' => $buttons,
'resize_keyboard' => true,
'one_time_keyboard' => true,
'parse_mode' => 'HTML',
'selective' => true
];
} else {
}
 
$data_string = json_encode($data);
 
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
'Content-Type: application/json',
'Content-Length: ' . strlen($data_string))
);
 
curl_exec($ch);
curl_close($ch);
echo "1233";
}

sendMessage($url,$id,$message,$buttons);
?>