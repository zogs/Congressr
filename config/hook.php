<?php 

if(isset($this->request->prefix) && $this->request->prefix == 'admin'){
	$this->layout = 'admin'; 


	//Si l'user n'est pas admin on redirige sur le log in
	if(!Session::user()->isLog() || ( Session::user()->getRole() != 'admin' && Session::user()->getRole()!='chairman')){
		$this->redirect('users/login');
	}
} ?>