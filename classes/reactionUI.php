<?php
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