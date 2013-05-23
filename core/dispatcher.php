<?php

class Dispatcher{

	var $request;
	var $error;

	function __construct() {

		//Intanciation d'un objet requete
		$this->request = new Request();
		
		//Appel de la class Router pour decortiquer la requete url
		Router::parse($this->request->url,$this->request);

		//Appel de la methode loadController
		$controller = $this->loadController();
		$action = $this->request->action;

		//Si il ya un prefix on ajoute le prefixe a l'action
		if($this->request->prefix){
			$action = $this->request->prefix.'_'.$action;
		}
		
		if(!in_array($action,array_diff(get_class_methods($controller),get_class_methods('Controller')))){ //Si la methode demandé n'est pas une methode du controlleur on renvoi sur error()
			$this->error("Le controller ".$this->request->controller." n'a pas de méthode ".$action);
		}
		
		//Appel de la methode demandé sur le controller demandé
		call_user_func_array(array($controller,$action),$this->request->params);

		//Appel le rendu du controlleur Auto rendering
		$controller->render($action);

	}

 
	// Permet d'inclure le bon controlleur
	function loadController() {


		//nom du controller
		$name = ucfirst($this->request->controller).'Controller'; //On recupere le nom du controller ( en lui mettant une majuscule ^^)

		//autoload du controller
		$controller =  new $name($this->request, true); //retourne une instance du bon controleur ( representé par le $name ! )

		return $controller;
	}

	//Renvoi un controlleur error
	function error($message){

		$controller = new Controller($this->request, false);
		$controller->e404($message);

	}
}