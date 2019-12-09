<?php
class reactionUI extends PDO
{
	public $user;
	public $iduser;
	public $idmsg;
	public $dt;
	public $ms;
	public $access;
	public $url;

	public function __construct($file = 'my_setting.ini')
    {
        if (!$settings = parse_ini_file($file, TRUE)) throw new exception('Unable11 to open ' . $file . '.');
        $dns = $settings['database']['driver'].':host=' . $settings['database']['host'].((!empty($settings['database']['port'])) ? (';port=' . $settings['database']['port']) : '').';dbname='.$settings['database']['schema'];
        parent::__construct($dns, $settings['database']['username'], $settings['database']['password']);
        $this->access=0;
        $this->url='https://api.telegram.org/bot1059041833:AAHi7sjrHjDh97eWhF266jTvlkua3glSJ90/sendMessage';
    }

	public function inital($unm, $uid, $idmsg, $dt, $ms)
	{
		$this->user=$unm;
		$this->iduser=$uid;
		$this->idmsg=$idmsg;
		$this->dt=$dt;
		$this->ms=$ms;
		if ($this->user=='Тимур') $this->access=1;
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

    function saveMessage()
    {
        if ($this->access==1) {
            $id_message = $this->idmsg;
            $id_user = $this->iduser;
            $uname = $this->user;
            $date = $this->dt;
            $msg = $this->ms;
            $sql = "INSERT INTO lids (id_message, id_user, uname, date, msg) VALUES ($id_message, '$id_user', '$uname', '$date', '$msg')";
            $query = $this->prepare($sql);
            $query->execute();
            $this->saveAuth();
            $this->sendMessage('Здравствуйте',$buttons = null);
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