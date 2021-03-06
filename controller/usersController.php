<?php 
class UsersController extends Controller{


	public $primaryKey = 'user_id'; //Nom de la clef primaire de la table

	public function login(){

		$this->layout = 'default';
		$this->loadModel('Users');

		if($this->request->data){		
			
			$data = $this->request->data;
			$login = $data->login;

			if(strpos($login,'@'))
				$field = 'email';
			else
				$field = 'login';
			
			$user = $this->Users->findFirstUser(array(
				'fields'=> 'user_id,login,avatar,hash,salt,lang,role',
				'conditions' => array($field=>$login))
			);
			
			
			if(!empty($user)){

				if($user->hash == md5($user->salt.$data->password)){

					unset( $user->hash);
					unset( $user->salt);
					unset($_SESSION['user']);
					unset($_SESSION['token']);
									
					//update last login
					$last = new stdClass();
					$last->date_lastlogin = Date::MysqlNow(time());
					$last->user_id = $user->user_id;
					$this->Users->save($last);

					//write user in session
					$user = new User( $user );
					Session::write('user', $user);
					Session::setToken();				
					Session::setFlash('<strong>Bienvenue '.$user->getLogin().' !</strong> Vous êtes connecté en tant que <strong>'.$user->role.'</strong>');
					
					//redirection
					if($user->role=='redactor')
						$this->redirect('redactor/board');			
					if($user->role=='reviewer')
						$this->redirect('reviewer/board');
					if($user->role=='admin')
						$this->redirect('admin/users/index');

				}
				else {
					Session::setFlash('Mauvais mot de passe','error');
				}						
			}
			else {
				Session::setFlash("Le ".$field." <strong>".$data->login."</strong> n'existe pas dans la base de donnée",'error');
			}
			$data->password = "";				

		}

	}

	public function logout(){
		
		unset($_SESSION['user']);
		unset($_SESSION['token']);
		Session::setFlash('Vous êtes maintenant déconnecté','info',2);	
		$this->reload();


	}	

	/*===========================================================	        
	Register
	@param object of a user
	============================================================*/
	public function register(){

		$this->loadModel('Users');				

		$d = array();		
	
		$data = $this->request->post();

		//if data are send			
		if($data){

			//if conditions accepted
			if(isset($data->accept)&&$data->accept==1){
				unset($data->accept);	

				//validates user data
				if($this->Users->validates($data,'register')){

					//check if login exist
					$check = $this->Users->findFirst(array('fields'=>'user_id','conditions'=>array('login'=>$data->login)));							
					if(!empty($check)) {
						Session::setFlash("Ce login est déjà pris! Veuillez en choisir un autre...","error");
						$this->request->data = $data;

					} else {
						//check if email exist
						$check = $this->Users->findFirst(array('fields'=>'user_id','conditions'=>array('email'=>$data->email)));
						if(!empty($check)) {
						Session::setFlash("L'adresse email <strong>".$data->email."</strong> est déjà prise. Vous êtes peut être déjà inscrit en tant qu'auteur ou reviewer ?<br/> <small>Si vous avez oublié vos identifiants, essayez de <a href='".Router::url('users/recovery')."'>réinitialiser</a> votre mot de passe...</small>","error");
						$this->request->data = $data;

						} else {

							if($id = $this->Users->saveUser($data)){

								$user = $this->Users->findFirstUser(array('conditions'=>array('user_id'=>$id)));

								if(isset($user->status) && $user->status!='group')
									Session::setFlash("<strong>Welcome</strong>","success");

								if($this->sendValidateMail(array('dest'=>$user->email,'user'=>$user->login,'codeactiv' =>$user->codeactiv,'user_id'=>$id)))
								{
									$d['Success'] = true;						
									Session::setFlash("Un email <strong>a été envoyé</strong> à votre boite email. Pour confirmer votre incription, <strong>veuillez cliquer sur le lien</strong> présent dans cette email", "success");
									Session::setFlash("Il est possible que ce email soit placé parmis les <strong>indésirables ou spam</strong> , pensez à vérifier !", "info");
								
								} else {
									$d['Success'] = false;
									Session::setFlash("Il y a eu une erreur lors de l'envoi de l'email de validation", "error");
								}

							} else {

								$d['Success'] = false;
								Session::setFlash("Il y a eu une erreur lors de l'enregistrement dans la base", "error");
							}					
						}
					}
				}  else {				
					Session::setFlash("Veuillez vérifier vos informations",'error');
				}
			}  else {
				Session::setFlash("You forgot to accept the terms of use","error");
			}			
		}


		$d['data'] = $this->request->data;

		$this->set($d);
	}

	public function admin_createUser( $role ){

		$this->loadModel('Users');

		if($data = $this->request->post()){

			$data->valid = 1;

			if($this->Users->saveUser($data)){
				Session::setFlash("L'utilisateur a bien été enregistré.");
			}
			else {
				Session::setFlash("Une erreur a empéché de sauvegarder l'utilisateur",'error');
			}
		}

		$this->set('role',$role);
	
	}


	/*===========================================================	        
	Validate
	Validate the email of the user	
	============================================================*/
	public function validate(){

		$this->loadModel('Users');
		$this->view = 'users/login';

		if($this->request->get('c') && $this->request->get('u') ) {

			$get       = $this->request->get;
			$user_id   = urldecode($get->u);			
			$code_url = urldecode($get->c);

			$user = $this->Users->findFirstUser(array(
				'fields'=>array('login','codeactiv'),
				'conditions'=>array('user_id'=>$user_id)
				));


			if(!empty($user)){

				if($user->codeactiv == $code_url) {
					$data =  new stdClass();
					$data->user_id = $user_id;
					$data->valid = 1;
					$this->Users->save($data);

					Session::setFlash('<strong>Bonjour </strong> '.$user->login.' ! Vous avez validé votre inscription','success');
					Session::setFlash('Vous pouvez vous <strong>connecter</strong> dés maintenant!','info');
									

				}
				else {
					Session::setFlash("Une erreur inconnue est intervenue pendant l'activation",'error');
				}
			}
			else {	
			debug('lol');			
				Session::setFlash("Pas trouvé dans la bdd",'error');
			}

		}

	}


    public function account($action = null){    	

    	$this->loadModel('Users');
    	//$this->layout = 'none';

    	/*======================
			If user is logged
		========================*/
    	if(Session::user()->getID())
    	{

	    	$user_id = Session::user()->getID();
	    	
	    	/*======================
				If POST DATA are sended
			========================*/
	    	if($this->request->data) {							    		

	    		
	    		$data = $this->request->data;

	    		/*====================
	    			MODIFY ACCOUNT
	    		====================*/
	    		if($this->request->post('action')=='account'){

	    			if($this->Users->validates($data,'account_info')){

						$user = $this->Users->findFirstUser(array('fields'=>'login, email','conditions'=>array('user_id'=>$this->request->post('user_id'))));
																
						//If it's the not same user name
						if($user->login != $this->request->post('login'))
							$checklogin = $this->Users->findFirst(array('fields'=>'login','conditions'=>array('login'=>$this->request->post('login'))));
						else
							unset($data->login);							
						

						//if its not the same email
						if($user->email != $this->request->post('email'))							
							$checkemail = $this->Users->findFirst(array('fields'=>'email','conditions'=>array('email'=>$this->request->post('email'))));
						else
							unset($data->email);
							
	    				if(empty($checklogin)){

	    					if(empty($checkemail)){

		    					if($this->Users->saveUser($data,$user_id)){

									Session::setFlash("Your account have been saved !","success");

									//update session login									
									$user = Session::user();
			    					if(isset($data->login)) $user->login = $data->login;
			    					if(isset($data->lang)) $user->lang = $data->lang;			    					
			    					Session::write('user', $user);
			    					
								}
								else{
									Session::setFlash("Your account have not been saved, please retry","error");
								}
							}
							else {
								Session::setFlash("This email is already in use","error");
							}
	    				}
	    				else {
	    					Session::setFlash("This login is already in use","error");
	    				}
	    			}
	    			else {
	    				Session::setFlash("Please review the form","error");
	    			}
	    		}
	    		else {
	    			if($this->request->post('login')) unset($_POST['login']);
	    		}


	    		/*====================
					MODIFY INFO
	    		=====================*/
	    		if($this->request->post('action') == 'profil'){

	    			if($this->Users->validates($data,'account_profil')){



	    				if($this->Users->saveUser($data,$user_id)){

	    					Session::setFlash('Your profil have been saved ! ','success');
	    				}
	    				else {
	    					Session::setFlash("Sorry but something goes wrong please retry",'error');
	    				}
			    		
		    		}
		    		else 
		    			Session::setFlash('Please review your informations','error');
		    	
	    		}


	    		/*===================
	    		 *   MODIFY AVATAR
	    		===================== */
	    		if($this->request->post('action') == 'avatar'){

	    			if($this->Users->validates($data,'account_avatar')){

	    				if($destination = $this->Users->saveFile('avatar','u'.$data->user_id)){

	    					Session::setFlash('Your avatar have been changed ! ', 'success');

	    					$u = new stdClass();
	    					$u->user_id = $data->user_id;
	    					$u->avatar = $destination;
	    					$u->table = 'users';
				 			$this->Users->save($u);
				 				
				 			$u = Session::user();
				 			$u->avatar = $destination;
				 			Session::write('user', $u);
	    				}
	    			}	    			
	    			else
	    				Session::setFlash('Please review your file','error');
	    		}


	    		
	    		/*====================
					MODIFY PASSWORD
	    		=====================*/
	    		if($this->request->post('action') == 'password')
	    		{
	    			
	    			if($data = $this->Users->validates($data,'account_password')){

		    				$db = $this->Users->findFirstUser(array(
		    					'fields' => 'user_id,salt,hash',
		    					'conditions'=> array('user_id'=>$user_id)
		    					));

		    				if($db->hash == md5($db->salt.$this->request->post('oldpassword'))){

		    					$data = new stdClass();
		    					$data->hash = md5($db->salt.$this->request->post('password'));
		    					$data->user_id = $user_id;
		    					
		    					if($this->Users->save($data)){
		    						Session::setFlash('Your password have been changed !');		    						
		    					}
		    					else {
		    						Session::setFlash('Error while saving your password...','error');
		    					}
		    				}
		    				else Session::setFlash('Your old password is not correct','error');
		    		}
		    		else 
		    			Session::setFlash('Please review your informations','error');
	    		}

	    		/*====================
					MODIFY DELETE
	    		=====================*/
	    		if($this->request->post('action') == 'delete'){

	    			if($this->Users->validates($data,'account_delete')){

	    				$db = $this->Users->findFirstUser(array(
	    					'fields'=>'hash,salt',
	    					'conditions'=>array('user_id'=>$user_id)
	    					));
	    				
	    				if($db->hash == md5($db->salt.$this->request->post('password'))){

	    					
	    					$this->Users->delete($user_id);
	    					unset($_SESSION['user']);
	    					$user_id = 0;
	    					Session::setFlash('Your account has been delete... <strong>Wait ? Why did you do that ??</strong>');

	    				}
	    				else
	    					Session::setFlash('Your password is not good','error');

	    			}
	    			else
	    				Session::setFlash('Please review your password','error');

	    		}	    			    			  
		    	
		    }

		    //get account info
	    	$user = $this->Users->findFirstUser(array(
					'conditions' => array('user_id'=>$user_id))
				);	    	    	
	    	// /!\ request->data used by Form class
	    	$this->request->data = $user;

	    	$d['user'] = $user;

	    	//action
	    	if(!isset($action)) $action = 'profil';
	    	$d['action'] = $action;

	    	$this->set($d);
	    }
	    else {

	    	$this->redirect('/');	    	
	    }

    }


	public function recovery(){

		$this->loadModel('Users');

		$action='';
		
		//if user past the link we mailed him
		if($this->request->get('c') && $this->request->get('u') ){

			
			//find that user 
			$user_id = base64_decode(urldecode($this->request->get('u')));
			$user = $this->Users->findFirstUser(array(
				'fields'=>array('user_id','salt'),
				'conditions'=>array('user_id'=>$user_id)));
			
			//check the recovery code
			$code = base64_decode(urldecode($this->request->get('c')));
			$hash = md5($code.$user->salt);
			$user = $this->Users->findFirst(array(
				'table'=>'users_mail_recovery',
				'fields'=>'user_id',
				'conditions'=>'user_id='.$user_id.' AND code="'.$hash.'" AND date_limit >= "'.unixToMySQL(time()).'"'));

			//if this is good
			if(!empty($user)){

				//show password form
				Session::setFlash('Enter your new password','success');
				$action = 'show_form_password';

			}
			else {
				//else the link isnot good anymmore
				Session::setFlash('Your link is not valid anymore. Please ask for a new password reset.','error');
				$action = 'show_form_email';
				
			}

			$d['code'] = $code;
			$d['user_id'] = $user_id;

		}

		//if user enter a new password
		if($this->request->post('password') && $this->request->post('confirm') && $this->request->post('code') && $this->request->post('user')){


			$data    = $this->request->post();
			
			//find that user
			$user_id = $data->user;
			$user = $this->Users->findFirstUser(array(
				'fields'=>array('user_id','salt'),
				'conditions'=>array('user_id'=>$user_id)));

			//check the recovery code
			$code = md5($data->code.$user->salt);
			$user = $this->Users->findFirst(array(
				'table'=>'users_mail_recovery',
				'fields'=>'user_id',
				'conditions'=>'user_id='.$user_id.' AND code="'.$code.'" AND date_limit >= "'.unixToMySQL(time()).'"'));

			//if the code is good
			if(!empty($user)){

				unset($data->code);
				unset($data->user);
				
				//validates the password
				if($this->Users->validates($data,'recovery_mdp')){

					//save new password
					$new = new stdClass();
					$new->salt = randomString(10);
					$new->hash = md5($new->salt.$data->password);
					$new->user_id = $user->user_id;
					if($this->Users->save($new)){

						//find the recovery data 
						$rec = $this->Users->findFirst(array(
							'table'=>'users_mail_recovery',
							'fields'=>array('id'),
							'conditions'=>array('user_id'=>$user_id,'code'=>$code)));

						//supress recovery data
						$del = new stdClass();
						$del->table = 'users_mail_recovery';
						$del->key = 'id';
						$del->id = $rec->id;
						$this->Users->delete($del);

						//redirect to connexion page
						Session::setFlash("Your password have been changed !","success");
						$this->redirect('users/login');
					}
					else {
						$action = 'show_form_password';
						Session::setFlash("Error while saving. Please retry","error");
					}
				}
				else {
					$action = 'show_form_password';
					Session::setFlash("Please review your data","error");
				}

			}
			else
			{
				$action = 'show_form_email';
				Session::setFlash("Error. Please ask for a new password reset.","error");
			}

			$d['code'] = $code;
			$d['user_id'] = $user_id;



		}

		//If user enter his email address
		if( $this->request->post('email') ) {

			$action = 'show_form_email';

			//check his email
			$user = $this->Users->findFirstUser(array(
				'fields'=>array('user_id','email','login','salt'),
				'conditions'=>array('email'=>$this->request->post('email')),				
			));

			if(!empty($user)){

				//check if existant recovery data
				$recov = $this->Users->find(array(
					'table'=>'users_mail_recovery',
					'fields'=>array('id'),
					'conditions'=>array('user_id'=>$user->user_id)
					));

				//if exist, delete it
				if(!empty($recov)){

					$del = new stdClass();
					$del->table = 'users_mail_recovery';
					$del->key = 'id';
					$del->id = $recov[0]->id;
					$this->Users->delete($del);
				}

				//create new recovery data
				$code = randomString(100);

				$rec = new stdClass();				
				$rec->user_id = $user->user_id;
				$rec->code = md5($code.$user->salt);
				$rec->date_limit = unixToMySQL(time() + (2 * 24 * 60 * 60));
				$rec->table = 'users_mail_recovery';
				$rec->key = 'id';

				//save it
				if($this->Users->save($rec)){

					//send email to user
					if($this->sendRecoveryMail(array('dest'=>$user->email,'user'=>$user->login,'code' =>$code,'user_id'=>$user->user_id))){

						Session::setFlash('An email have been send to you.','success');
						Session::setFlash("Please tcheck the spam box if you can't find it.","warning");

					}
					else{
						Session::setFlash('Error while sending the email. users/recovery','error');
						
					}
				}
				else{
					Session::setFlash('Error while saving data. users/recovery','error');
					
				}
			}
			else {
				Session::setFlash('This email is not in our database','error');
			}


		}

		$d['action'] = $action;
		$this->set($d);

	}

	public function check(){

		$this->loadModel('Users');
		$this->layout = 'none';
		$this->view = 'json';

		$d = array();

		if($this->request->get){

			$data = $this->request->get;
			$type = $data->type;
			$value = $data->value;	

			//if empty
			if(empty($value)){

				$d['error'] = 'Must not be empty';
			}
			else {
				
				//check validation model
				$check = new stdClass();
				$check->$type = $value;
				if(!$this->Users->validates($check,'account_info',$type)){
					
					$d['error'] = $this->Users->errors[$type];
				}

				//check reserved words
				if(in_array(strtolower($value),Conf::$reserved[$type]['array'])){

					$d['error'] = Conf::$reserved[$type]['errorMsg'];
				}
			}

			//if no error check existing
			if(empty($d['error'])){

				$user = $this->Users->findFirstUser(array('fields'=> $type,'conditions' => array($type=>$value)));

					if(!empty($user)) {
						$d['error'] = '<strong>'.$value."</strong> is already in use!";
					}
					else {
						$d['available'] = "";
					}

			}
				
		}	
		$this->set($d);	
	}


	public function sendRecoveryMail($data)
	{
		extract($data);

		$lien = Conf::getSiteUrl()."/users/recovery/?c=".urlencode(base64_encode($code))."&u=".urlencode(base64_encode($user_id));

		//Création d'une instance de swift mailer
		$mailer = Swift_Mailer::newInstance(Conf::getTransportSwiftMailer());

		//Récupère le template et remplace les variables
		$body = file_get_contents('../view/email/recoveryPassword.html');
		$body = preg_replace("~{site}~i", Conf::$website, $body);
		$body = preg_replace("~{user}~i", $user, $body);
		$body = preg_replace("~{lien}~i", $lien, $body);

		//Création du mail
		$message = Swift_Message::newInstance()
		  ->setSubject("Change your password")
		 ->setFrom('contact@aic2014.com', 'http://www.aic2014.com')
		  ->setTo($dest, $user)
		  ->setBody($body, 'text/html', 'utf-8')
		  ->addPart("Hey {$user}, copy this link ".$lien." in your browser to change your password.", 'text/plain');

		//Envoi du message et affichage des erreurs éventuelles
		if (!$mailer->send($message, $failures))
		{
		    echo "Erreur lors de l'envoi du email à :";
		    print_r($failures);
		}
		else return true;
	}

	public function sendValidateMail($data)
	{
		extract($data);

		$lien = Conf::getSiteUrl()."/users/validate/?c=".urlencode($codeactiv)."&u=".urlencode($user_id);

		//Création d'une instance de swift mailer
		$mailer = Swift_Mailer::newInstance(Conf::getTransportSwiftMailer());

		//Récupère le template et remplace les variables
		$body = file_get_contents('../view/email/validateAccount.html');
		$body = preg_replace("~{site}~i", Conf::$website, $body);
		$body = preg_replace("~{user}~i", $user, $body);
		$body = preg_replace("~{lien}~i", $lien, $body);

		//Création du mail
		$message = Swift_Message::newInstance()
		  ->setSubject("Validation de l'inscription à ".Conf::$website)
		  ->setFrom('contact@aic2014.com', 'http://www.aic2014.com')
		  ->setTo($dest, $user)
		  ->setBody($body, 'text/html', 'utf-8')
		  ->addPart("Hey {$user}, copy this link ".$lien." in your browser. Welcome on the Protest.", 'text/plain');

		//Envoi du message et affichage des erreurs éventuelles
		if (!$mailer->send($message, $failures))
		{
		    echo "Erreur lors de l'envoi du email à :";
		    print_r($failures);
		}
		else return true;
	}


    


    public function index(){

    	if(Session::user()){
    		$this->thread();
    	}
    	else {
    		$this->redirect('users/login');
    	}
    	
    }

    public function admin_index(){


    	$this->loadModel('Users');
    	$this->loadModel('Worlds');

    	if($this->request->post()){

    		$u = $this->request->post();

    		if($this->Users->save($u)){
    			Session::setFlash("User saved","success");
    		}
    	}

    	$users = $this->Users->findUsers(array('order'=>'role ASC, nom ASC'));
    	$users = $this->Worlds->JOIN_GEO($users);
    	$d['users'] = $users;

    	$this->set($d);
    }

    public function admin_delete($user_id){

    	$this->loadModel('Users');

    	if(!Session::user()->isLog() || ( Session::user()->getRole()!='admin' && Session::user()->getRole()!='chairman' )) throw new zException("you are not allowed to delete user", 1);
    	
    	if($this->Users->deleteUser($user_id))
    		Session::setFlash("User deleted","success");    	
    	else
    		Session::setFlash('Error'); 	

    	$this->redirect('admin/users/index');
    }

    public function admin_edit($user_id){

    	$this->loadModel('Users');

    	if(!Session::user()->isLog() || ( Session::user()->getRole()!='admin' && Session::user()->getRole()!='chairman' )) throw new zException("you are not allowed to edit user", 1);
    	
    	if($this->request->post()){

    		if($this->Users->save($this->request->post())){
    			Session::setFlash("User have been saved","success");
    		}
    		else {
    			Session::setFlash("Error while saving","error");
    		}
    	}

    	$user = $this->Users->findFirstUser(array('conditions'=>array('user_id'=>$user_id)));
    	$d['user'] = $user;

    	$this->set($d);
    }

}

 ?>