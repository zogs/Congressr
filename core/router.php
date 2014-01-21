<?php

class Router{

		
	static $routes = array();
	static $prefixes = array();

	/**
	 * Configure un prefixe
	 */
	static function prefix($url,$prefix){
		self::$prefixes[$url] = $prefix;
	}

	/**
	 * Permet de passer une url
	 */	
	static function parse($url,$request){
		//On enleve les / au debut et a la fin
		$url = trim($url,'/');

		//Si mon url est vide
		if(empty($url)){

			$url = Router::$routes[0]['url'];
		}
		else { //Sinon on regarde si les url correspondent aux regles des catchers et si oui on retroune l'adresse réécrite dans l'objet request
			
			$match = false;
			foreach (Router::$routes as $v) {

				if(!$match && preg_match($v['redirreg'], $url, $match)){
					$url = $v['origin'];
					foreach ($match as $k => $v) {
						$url = str_replace(':'.$k.':', $v, $url);
					}
					$match = true;
				}
			}
		}

		//Si l'url ne correspond pas a une regle on parse selon la regle de base /controller/action/params
		//On separe l'url 
		$params = explode('/',$url);

		//On vérifie si il y  a un prefixe
		if(in_array($params[0],array_keys(self::$prefixes))){
			$request->prefix = self::$prefixes[$params[0]];
			array_shift($params);
		}

		//On met dans un tableau les differentes parties de l'url		
		$request->controller = $params[0]; //le controller en premier
		$request->action = isset($params[1])? $params[1] : 'index'; //l'action en second
		foreach (self::$prefixes as $k => $v) { //On ajoute le prefixe au nom de l'action
			if(strpos($request->action,$v.'_') === 0){
				$request->prefix = $v;
				$request->action = str_replace($v.'_', '', $request->action);
			}
		}
		$request->params = array_slice( $params , 2); //et tout le reste en parametres

		
		return true;

	}


	/**
	* Permet de connecter une url à une action particuliere
	*
	**/

	static function connect($redir,$url){

		$r           = array();
		$r['params'] = array();
		$r['url']    = $url;
		$r['originreg'] = preg_replace('/([a-z0-9]+):([^\/]+)/','${1}:(?P<${1}>${2})',$url);
		$r['originreg'] = str_replace('/*', '(?P<args>/?.*)', $r['originreg']);
		$r['originreg'] = '/^'.str_replace('/', '\/', $r['originreg']).'$/';

		$r['origin'] = preg_replace('/([a-z0-9]+):([^\/]+)/', ':${1}:', $url);
		$r['origin'] = str_replace('/*',':args:',$r['origin']);


		$params = explode('/', $url);
		foreach ($params as $k => $v) {
			if(strpos($v,':')){
				$p = explode(':', $v);
				$r['params'][$p[0]] = $p[1];

			}
		}

		$r['redirreg'] = $redir;
		$r['redirreg'] = str_replace('/*','(?P<args>/?.*)',$r['redirreg']);
		foreach ($r['params'] as $k => $v) {
			$r['redirreg'] = str_replace(":$k", "(?P<$k>$v)", $r['redirreg']);
		}
		$r['redirreg'] = '/^'.str_replace('/', '\/', $r['redirreg']).'$/';

		$r['redir'] = preg_replace('/:([a-z0-9]+)/', ':${1}:', $redir);
		$r['redir'] = str_replace('/*',':args:',$r['redir']);

		self::$routes[] = $r;

		// debug(self::$routes);


	}


	/**
	*Permet de générer une url à partir d'une url originael
	* controller/action(/:param/:param/:param...)
	**/
	static function url($url){

		trim($url,'/');
		foreach (self::$routes as $v ){

			if(preg_match($v['originreg'], $url, $match)){		
				
				$url = $v['redir'];	
				foreach($match as $k=>$w){				
						$url = str_replace(":$k:", $w, $url);				
					
				}											
			}
		}

		//Boucle prefixes
		foreach (self::$prefixes as $k => $v) {
			if(strpos($url, $v) === 0){
				$url = preg_replace('/'.$v.'/',$k,$url,1);
			}
		}

		//Add lang
		$url = Router::addLang($url);
		//Security token
		$url = Router::addToken($url);	
		//Correction 
		$url = str_replace('//','/',BASE_URL.'/'.$url);

		return $url;
	}

	static function addLang($url){

		if(isset($_GET['lang'])){
			if(strpos($url,'?')){
				if(!strpos($url,'lang')) $url .= '&lang='.$_GET['lang'];

			}
			else
				$url .= '/?lang='.$_GET['lang'];

		}
		return $url;
	}

	static function addToken($url){

		//Security token
		if(isset($_SESSION['token'])){

			//Si il y a des parametres dans l'url
			if(strpos($url,'?')&&strpos($url,'=')){
				$url .= '&token='.$_SESSION['token'];
			}			
			return $url;			
		}
		else
			return $url;
	}


	/**
	* Redirige sur la racine /webroot
	* 
	**/
	static function webroot($url){
		$url = trim($url,'/');
		$url = Router::addToken($url);//add security token		
		$url = str_replace('//','/',BASE_URL.'/'.$url);//URL Correction 

		return $url;

	}

	static function url_get_param($url, $name) {
    parse_str(parse_url($url, PHP_URL_QUERY), $vars);
    return isset($vars[$name]) ? $vars[$name] : null;
	}

}