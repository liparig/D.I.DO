<?php 
abstract class AnyDocument extends Crud{
	
	protected $SQL_SEARCH = "SELECT * FROM %s WHERE %s";
	protected $id_document_label = null;
	
	protected function __construct($connInstance){
		parent::__construct($connInstance);
	}
	
	public function searchByKeyValue($key_value, $id = null) {
		$where = array ();
		if(!is_null($id) && !is_null($this->id_document_label))
			$where[] = "{$this->id_document_label} = '$id'";
		
		if(!empty($key_value)){ 
			foreach ( $key_value as $key => $value ) {
				if (is_null ( $key ) || is_null ( $value ))
					continue;
				
				if(is_array($value)){
					$value = array_map('Utils::apici', $value);
					$where [] = "key='{$key}' AND value IN (".join(",",$value).")";
				} else {
					$where [] = "key='{$key}' AND value={$value}";
				}
			}
		}
		$where = join ( " AND ", $where );
		$sql = sprintf ( $this->SQL_SEARCH, isset ( $this->VIEW ) ? $this->VIEW : $this->TABLE, $where );
		$this->_connInstance->query ( $sql );
		return $this->_connInstance->allResults();
	}
	
	public function searchByKeys($keys, $id = null){
		if (empty ( $keys ) || !is_array($keys))
			return $this->getAll ();
		
		$where = array();
		
		if(!is_null($id) && !is_null($this->id_document_label))
			$where[] = "{$this->id_document_label} = '$id'";
		
		$keys = join(",", array_map("Utils::apici",$keys));
		$where[] = "key in ($keys)";
		
		$where = join(" AND ", $where);
		
		$sql = sprintf ( $this->SQL_SEARCH, isset ( $this->VIEW ) ? $this->VIEW : $this->TABLE, $where );
		$this->_connInstance->query ( $sql );
		return $this->_connInstance->allResults();
		
	}
	
	public function saveInfo($inputs,$id_parent,$docInputs){
		$existents_input = Utils::getListfromField($this->searchByKeys(array_keys($inputs), $id_parent),null,'key');
		foreach($inputs as $key=>$value){
			
			$this->_connInstance->query("BEGIN");
			if(!isset($existents_input[$key]) || $value != $existents_input[$key]['value']){
				$existents_input[$key]['key'] = $key;
				$existents_input[$key]['value'] = $value;
				$existents_input[$key][$this->id_document_label] = $id_parent;
				Utils::printr($existents_input[$key]);
				
				$object = Utils::stubFill($this->_stub,$existents_input[$key]);
				Utils::printr($object);
				$result = $this->save($object,null);
				
				if(!empty($result['errors'])){
					$this->_connInstance->query("ROLLBACK");
					return json_encode($result);
				}
			}
		}
		$this->_connInstance->query("COMMIT");
		return json_encode(array('errors' => false));
	}
}
?>