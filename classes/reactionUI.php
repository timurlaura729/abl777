<?php
class reactionUI extends PDO
{
	public $user;
	public $iduser;
	public $idmsg;
	public $dt;
	public $ms;

	public function __construct($file = 'my_setting.ini')
    {
        if (!$settings = parse_ini_file($file, TRUE)) throw new exception('Unable11 to open ' . $file . '.');
        $dns = $settings['database']['driver'].':host=' . $settings['database']['host'].((!empty($settings['database']['port'])) ? (';port=' . $settings['database']['port']) : '').';dbname='.$settings['database']['schema'];
        parent::__construct($dns, $settings['database']['username'], $settings['database']['password']);
    }

	public function inital($unm, $uid, $idmsg, $dt, $ms)
	{
		$this->user=$unm;
		$this->iduser=$uid;
		$this->idmsg=$idmsg;
		$this->dt=$dt;
		$this->ms=$ms;
	}
	
	function saveToBase($res)
    {
        $sql = "INSERT INTO test (text) VALUES ('$res')";
        $query = $this->prepare($sql);
        $query->execute();
    }
	
		function saveMessage()
    {
		$id_message=$this->idmsg;
		$id_user=$this->iduser;
		$uname=$this->user;
		$date=$this->dt;
		$msg=$this->ms;
        $sql = "INSERT INTO lids (id_message, id_user, uname, date, msg) VALUES ($id_message, '$id_user', '$uname', '$date', '$msg')";
        $query = $this->prepare($sql);
        $query->execute();
    }
	
	

}
?>