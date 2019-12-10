<?php
class planFX
{
    public $api_server = 'https://api.planfix.ru/xml/';
    public $api_key = '6c7c7be26c25aaa7b67a0d538d899f1c';
    public $api_secret = '523e109934f51b6af56e0433074cc8cb';
    public $api_token = '551804bdcdab76da99bb570834620545';

    public function __construct()
    {
    }

    public function getUIK($uik)
    {
        //QWEB199
        $requestXml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><request method="contact.getList"><filters><filter><type>4101</type><field>14926</field><operator>equal</operator><value>'.$uik.'</value></filter></filters></request>');
        $requestXml->account = 'amanbolkz';
        $requestXml->pageCurrent = 1;
        $ch = curl_init($this->api_server);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // не выводи ответ на stdout
        curl_setopt($ch, CURLOPT_HEADER, 1);   // получаем заголовки
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // не проверять SSL сертификат
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // не проверять Host SSL сертификата
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->api_key . ':' . $this->api_token);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestXml->asXML());
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $responseBody = substr($response, $header_size);
        curl_close($ch);
        $temp = trim($responseBody);
        $xml = simplexml_load_string($temp);
        $json = json_encode($xml);
        $array = json_decode($json, TRUE);
        return $array;
    }
}

class reactionUI extends PDO
{
    // Глобальные переменные
	public $user;
	public $iduser;
	public $idmsg;
	public $dt;
	public $ms;
	public $access;
	public $url;
	public $maenu1;
	public $planFX;

	// Создадим конструктор ебаный Лего
	public function __construct($unm, $uid, $idmsg, $dt, $ms, $file = 'my_setting.ini')
    {
        // парсим файл подключения
        if (!$settings = parse_ini_file($file, TRUE)) throw new exception('Unable11 to open ' . $file . '.');
        // Создаем подключение к БД
        $dns = $settings['database']['driver'].':host=' . $settings['database']['host'].((!empty($settings['database']['port'])) ? (';port=' . $settings['database']['port']) : '').';dbname='.$settings['database']['schema'];
        parent::__construct($dns, $settings['database']['username'], $settings['database']['password']);
        // Объявляем глобальные переменные
        $this->access=0;
        $this->url='https://api.telegram.org/bot1059041833:AAHi7sjrHjDh97eWhF266jTvlkua3glSJ90/sendMessage';
        $this->menu1=[["Да"],["Нет"]];
        $this->user=$unm;
        $this->iduser=$uid;
        $this->idmsg=$idmsg;
        $this->dt=$dt;
        $this->ms=$ms;
        $this->planFX=new planFX();
        // Проверка на пользователя. Оставь надежду всяк сюда входящий
        if ($this->ms=='777') $this->access=1;
    }

    // Взять id авторизации
	function getAuth()
    {
        $d=date("Y-m-d H:i:s", strtotime("+1 minutes"));
        $id=$this->iduser;
        $stmt = $this->query("SELECT * FROM auth where iduser=$id and active=1 and dt> NOW() - INTERVAL 6 MINUTE");
        $row = $stmt->fetch();
        // проверить
        //$this->saveToBase($d."    ".$row['dt']);
        //$str=$row['iduser']."   ".$row['dt'];
        $str=$row['id'];
        return $str;
    }

    function DeleteAuth()
    {
        $sql = "delete from auth where id=".$this->getAuth();
        $query = $this->prepare($sql);
        $query->execute();
    }

	function saveToBase($res)
    {
        $sql = "INSERT INTO test (text) VALUES ('$res')";
        $query = $this->prepare($sql);
        $query->execute();
    }

    function saveAuth()
    {
        $id_user = $this->iduser;
        $dt=$this->dt;
        $sql = "INSERT INTO auth (iduser, dt, active) VALUES ('$id_user', '$dt', 1)";
        $query = $this->prepare($sql);
        $query->execute();
    }

    function saveLog()
    {
        $id_message = $this->idmsg;
        $id_user = $this->iduser;
        $uname = $this->user;
        $date = $this->dt;
        $msg = $this->ms;
        $sql = "INSERT INTO lids (id_message, id_user, uname, date, msg) VALUES ($id_message, '$id_user', '$uname', '$date', '$msg')";
        $query = $this->prepare($sql);
        $query->execute();
    }

    function StartReaction()
    {
            if ((int)$this->getAuth()>0)
            {
                $this->saveLog();
                switch ($this->ms)
                {
                    case "Да": $this->sendMessage('Набор выдан',$buttons = null); $this->DeleteAuth(); break;
                    case "Нет": $this->sendMessage('Запрос откланен',$buttons = null); $this->DeleteAuth(); break;
                    default:
                        {
                            $this->sendMessage('Да, такой УИК есть, можно выдать набор',$buttons = null);
                            $this->sendMessage('Выдать набор?',$buttons = $this->menu1);
                        }
                }
            }
            else {
                if ($this->access==1) {
                    $this->saveLog();
                    $this->saveAuth();
                    $ms=print_r($this->planFX->getUIK('QWEB199'), true);
                    $this->saveToBase($ms."777777");
                    $this->sendMessage($this->user . ' введите УИК ', $buttons = null);
                } else $this->sendMessage($this->user . ' бот не слушает ваши команды. Авторизуйтесь и выполните их снова', $buttons = null);
            }
    }

    function sendMessage($message,$buttons = null) {
        $data = array(
            'text' => $message,
            'chat_id' => $this->iduser
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

        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );

        curl_exec($ch);
        curl_close($ch);
    }


}
?>