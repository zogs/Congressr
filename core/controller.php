<?php

class Controller {

	public $request;	
	public $layout      = 'default';
	public $view        = 'default';
	private $rendered   = false;
	private $vars       = array();

	function __construct( $request=null, $external = true ){

		Session::init( $this );

		$this->Form = new Form($this);
		$this->Date = new Date();

		if($request){
			 //ON stocke la request dans l'instance
			$this->request = $request;

			//Systeme de hook pour changer le layer en fonction du prefixe
			require ROOT.DS.'config'.DS.'hook.php'; 

			//check the security token	
			if($external==true) $this->security(); 
		}		
	}


	// Permet de rendre le resultat du controller
	// @params view : la page a rendre

	public function render($view = null){
		
		//Si la page a deja ete rendu on stop la function
		if($this->rendered) return false;

		//On extrait les variables de la méthode appelée
		extract($this->vars);
		
		//Si la vue par default
		if( $this->view == 'default') {
			//Si la page a rendre commence par un '/ '
			if(strpos($view,'/')===0){
				$view = ROOT.DS.'view'.$view.'.php'; //On rend la page demandé
			} else {
				$view = ROOT.DS.'view'.DS.$this->request->controller.DS.$view.'.php'; //Sinon on utlise le systeme MVC
			}
		}
		else { //Sinon une vue personnalisée est appelée
			$view = ROOT.DS.'view'.DS.$this->view.'.php';
		}

		//check if the view exist
		if(!file_exists($view)){
			
			if(Conf::$debug==1){
				$this->e404('The controller :'.$this->request->controller.' has no view :'.$this->request->action);
				exit();
			}
			else {
				$this->e404('This page don\'t work... We\'re sorry :(');
				exit();
			}
		}
		//if view exist start buffer
		ob_start();
		require $view; //execute the view
		$content_for_layout = ob_get_clean(); //get the buffer 

		//load layout and send buffer
		$layout = ROOT.DS.'view'.DS.'layout'.DS.$this->layout.'.php';

		if(!file_exists($layout)){
			if(Conf::$debug>=1)
				throw new zException("Layout ".$this->layout." not found", 1);
										
			else {
				$this->layout = 'default';
				$this->e404('This page don\'t work... We\'re sorry :(');
			}
				
		}
		require $layout;


		//La page a été rendu
		$this->rendered = true;
	
	}

	//Permet de créer une variable du controller
	//@params $key : nom de la variable
	//@params $value : valeur de la variable

	public function set($key,$value=null){

		if(is_array($key)) {
			$this->vars += $key;
		}
		else {
			$this->vars[$key] = $value;
		}
	}


	//Permet de charger un model
	public function loadModel($name){
			
		$classname = $name.'Model';

		if(!isset($this->$name)) {
				
			$this->$name = new $classname();

			if(isset($this->Form)){
				$this->$name->Form = $this->Form;	
			}
	
		}
		else {
			//echo 'Model '.$name.' deja chargé';
		}
		
		
	}

	//Charge les fichiers CSS à insérer dans le <head></head>	
	public function loadCSS(){

		/**		
		* array Conf::$css , css files to load each time, define in Conf
		* array Controller $css_load, css files asked to load by the controller
		*/
		$css = array();
		if(isset(Conf::$css)){
			$css = array_merge(Conf::$css,$css);
		}
		if(isset($this->css_load)){
			$c = $this->css_load;
			if(is_string($c)) $c = array($c);
			if(is_array($c)) $css = array_merge($css,$c);
		}
		foreach ($css as $name => $url) {

			if(strpos($url,'http')!==0) $url = Router::webroot($url);
			echo '<link rel="stylesheet" style="text/css" href="'.$url.'" />';		
		}

	}

	//Charge les fichiers JS à insérer dans le <head></head>
	public function loadJS(){

		/**
		 * array Conf::$js , main JS file of the app
		 * array Conf::$js_dependency , dependency JS files
		 * array Controller $js_load, JS file asked to load by the controller
		 */
		$js = array();
		if(isset(Conf::$js_dependency)){
			$js = array_merge(Conf::$js_dependency,$js);
		}
		if(isset($this->loadJS)){

			if(is_string($this->loadJS)) $this->loadJS = array($this->loadJS);
			if(is_array($this->loadJS)) $js = array_merge($js,$this->loadJS);
		}
		if(isset(Conf::$js_main)){
			if(is_string(Conf::$js_main)) $js[] = Conf::$js_main;
			if(is_array(Conf::$js_main)) $js = array_merge($js,$js_main);
		}
		foreach ($js as $name => $url) {
			
			if(strpos($url,'http')===0) $url = $url;
			else $url = Router::webroot($url);		
			echo '<script type="text/javascript" src="'.$url.'"></script>';
		}
	}
	
	//Rend la vue Erreur 404
	//@param string $message 
	//@param string $oups 
	public function e404($message, $oups = 'Oups'){

		header("HTTP/1.0 404 Not Found"); 
		$this->set('message',$message);
		$this->set('oups',$oups);
		$this->render('/errors/404');
		
		die();
		
	}

	//Rend la vue exception
	// $params obj $error ->msg|code|line|file|context
	public function exception($error){

		$this->set('error',$error);
		$this->render('/errors/exception');
	}

	/**
	 * Call an method of a controller from a view
	 * @param  string $controller name of the controller to call
	 * @param  string $action     name of the method to call
	 * @param  array  $params     additionnal parameters to pass to the method
	 * @return method             method called
	 */
	public function call($controller,$action, $params = array() ){
		//name of the controller
		$controller .= 'Controller';
		//require it
		require_once ROOT.DS.'controller'.DS.$controller.'.php';

		//instance new controller
		$c = new $controller( $this->request , false);	

		$c->Form = $this->Form; //pass form data

		return call_user_func_array(array($c,$action),$params);

	}

	public function redirect($url,$code = null){

		if (headers_sent()) {
		    die("Redirect failed");
		}
		else{
			if($code == 301) {
				header("HTTP/1.1 301 Moved Permanently");
			}
			header("Location: ".Router::url($url));
		}
		exit();		
	}

	public function reload(){

		if (headers_sent()) {
		    die("Redirect failed");
		}
		else{
		    if(isset($_SERVER['HTTP_REFERER']))
				exit(header("Location: ".$_SERVER['HTTP_REFERER']));
			else
				$this->redirect('/');
		}
		
		exit();

	}

	public function getCountryCode(){


		if(Session::getPays()){
			return Session::getPays();
		}
		else if($this->CookieRch->read('CC1')){
			return $this->CookieRch->read('CC1');
		}
		else {
			return Conf::$pays;
		}
	}

	
	public function getFlagLang( $lang = null){

		if(!$lang) $lang = $this->getLang();
		$array = array('en'=>'uk');		
		if(isset($array[$lang])) return $array[$lang];
		else return $lang;
	}

	public function getLang(){		
		

		if(isset($this->request) && $this->request->get('lang')) return $this->request->get('lang');
		if(Session::getLang()) return Session::getLang();	
		//if($this->CookieRch->read('lang')) return $this->CookieRch->read('lang');	
		
		return Conf::$languageDefault;
	}

	public function has($property){

		if(isset($this->$property)) return $this->$property;
		else return false;
	}


	//Permet de vérifier le jeton de securité de la requete par rapport a la session
	//
	public function security(){

		if($this->request->post()){			

			if(!$this->request->post('token')){

				Session::setFlash("Warning security token is missing!!!","error");
				$this->e404('Please log in again');
			}
			else {

				if($this->request->post('token')!=Session::read('token')){
					
					exit('invalid token');
					
					Session::setFlash("Your security token is outdated, please log in again","error");
					$this->e404('Your security token is outdated, please log in again');
					
				}
				if($this->request->post('token')==Session::read('token')){
					unset($this->request->data->token);
				}
				
			

			}			
		}
		elseif($this->request->get()){
			
			if($this->request->get('token')){

				if($this->request->get('token')!=Session::read('token')){

					//Session::setFlash("bad token","error");
					$this->e404('Your security token is outdated, please log in again');
				}
				else {
					unset($this->request->get->token);
				}
			}
		}


	}
}
?>