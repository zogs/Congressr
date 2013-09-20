<?php

class Request{

	public $url; //url appelé par l'utilisateur
	public $page = 1; //Pagination
	public $prefix =false; //Prefixes
	public $data = false; //Donnees de formulaire
	public $get = false;
	public $controller;
	public $action;
	public $params;	

	//Permet de récupérer la requete url demandé
	public function __construct(){
		

		$this->url = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';
		debug($this->url);
		//$this->url = str_replace(BASE_URL."/", "", 
		//$_SERVER['REQUEST_URI']); //Recuperation du PATH_INFO 

		//Récuperation des données GET dans un objet
		if(!empty($_GET)){

			$this->get = new stdClass();
			foreach ($_GET as $k => $v) {
				// if(!is_numeric($v)){
				// 	if(is_array($v)){
				// 		$arr = array();
				// 		foreach ($v as $key => $value) {
				// 			$arr[$key] = mysql_real_escape_string($value);
				// 		}
				// 		$v = $arr;
				// 	}
				// 	else
				// 		$v = mysql_real_escape_string($v);
				// }
				$this->get->$k = $v;
			}						

		}
			
		if(!isset($this->get->page) || $this->get->page <=0 ){
			 $this->page = 1;
		}
		else {
			$this->page = round($this->get->page);
		}


		//Récuperation des données POST dans un objet
		if(!empty($_POST)){
			$this->data = new stdClass();
			foreach ($_POST as $k => $v) {
				// if(!is_numeric($v)){
				// 	if(is_array($v)){
				// 		$arr = array();
				// 		foreach ($v as $key => $value) {
				// 			$arr[$key] = mysql_real_escape_string($value);
				// 		}
				// 		$v = $arr;
				// 	}
				// 	else
				// 		$v = mysql_real_escape_string($v);
				// }
				$this->data->$k = $v;
			}

		}

		

	}

	//Renvoi le parametre GET demandé ou renvoi false
	public function get($param = null){	
		
		if($param){	
			if(isset($this->get->$param)){
				return $this->get->$param;
			}
			else return false;
		}
		else {
			if(!empty($_GET)){
				return $this->get;
			} 
			else {
				return false;
			}
		} 
	}


	public function post($param = null){

		if($param){
			if(isset($this->data->$param)){
				return $this->data->$param;
			}
			else return false;
		}
		else {
			if(!empty($_POST)){
				return $this->data;
			}
			else {
				return false;
			}
		}
	}


}