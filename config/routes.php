<?php

class Routes {
	
	public function __construct(){

		//Prefixe
		Router::prefix(Conf::$adminPrefix,'admin');
		//Connect
		Router::connect('/*','pages/home'); //Racine du site ( à laisser en premiere regle !)
		Router::connect('cockpit','cockpit/posts/index');
		Router::connect('blog/:slug-:id','posts/view/id:([0-9]+)/slug:([a-zA-Z0-9\-]+)');
		Router::connect('blog/*','posts/*');
		Router::connect(':slug/*','pages/view/slug:([a-zA-Z0-9\-]+)');
	

	}

}

?>