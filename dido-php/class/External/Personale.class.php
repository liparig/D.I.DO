<?php
class Personale {
	
	private static $_instance = null;
	private $_persone;
	private $_gruppi;
	private $_email;
	
	private function __construct(){
		ini_set ( "soap.wsdl_cache_enabled", "0" );
		
		$wsdl_url = "http://pimpa.isti.cnr.it/PERSONALE/web-services/dido/dido.wsdl";
		$client = new SoapClient ( $wsdl_url );
			
		$personale = json_decode(json_encode($client->personale()),true);
		$this->_persone = Utils::getListfromField ( $personale, null, "idPersona");
		$this->_cfId = Utils::getListfromField ( $personale, "idPersona", "codiceFiscale");
		$this->_email = Utils::getListfromField ( $personale, "idPersona", "email");
		
		$gruppi = json_decode(json_encode($client->gruppi()),true);
		$this->_gruppi = Utils::getListfromField ( $gruppi, null, "sigla");
		
		$progetti = json_decode(json_encode($client->progetti()),true);
		$this->_progetti = Utils::getListfromField ( $progetti, null, "id");
	}
	
	private function __clone(){}
	private function __wakeup(){}
	
	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new self ();
		}
		return self::$_instance;
	}
	
	public function getPersone(){
		return $this->_persone;
	}
	
	public function getGruppi(){
		return $this->_gruppi;
	}
	
	public function getProgetti(){
		return $this->_progetti;
	}
	
	public function getPersona($id){
		return $this->_persone[$id];
	}

	public function getPersonabyCf($cf){
		return isset($this->_cfId[$cf]) ? $this->_persone[$this->_cfId[$cf]] : false;
	}
	
	public function getPersonabyEmail($email){
		return isset($this->_email[$email]) ? $this->_persone[$this->_email[$email]] : false;
	}

	public function getGruppo($sigla){
		return $this->_gruppi[$sigla];
	}
	
	public function getPeopleByGroupType($type){
		$list = array();
		foreach($this->_persone as $id=>$datiPersona){
			foreach($datiPersona['gruppi'] as $sigla){
				if($this->_gruppi[$sigla]['tipo'] == $type)
					$list[$id] = $datiPersona;
			}
		}
		return $list;
	}
}
?>