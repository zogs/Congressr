<?php 
class UsersModel extends Model{

	public $primaryKey = 'user_id';

	public $validates = array(
		'register' => array(
			'login' => array(
				'rules'=>array(
						array(
							'rule'    => 'notEmpty',
							'message' => 'Vous devez choisir un pseudo'		
						),
						array(
							'rule'=>'regex',
							'regex'=>'^[a-zA-Z0-9 \.\-_]+$',
							'message'=>"Pas d'accents ou de caractères interdits..."
						)
					)
				),				
			'email' => array(
				'rules'=>array(
							array(
								'rule' => '[_a-zA-Z0-9-+]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-z]{2,4})',
								'message' => "L'adresse email n'est pas valide"),
							array(
								'rule' => 'notEmpty',
								'Vous devez remplir une adresse email')
							)
				),
			'confirmEmail' => array(
				'rule' => 'confirmEmail',
				'message' => "Vos adresses ne sont pas identiques"
				),
			'password' => array(
				'rule' => 'regex',
				'regex'=> '.{5,20}',
				'message' => "Votre mot de passe doit etre entre 5 et 20 caracteres"
				),
			'confirmPassword' => array(
				'rule' => 'confirmPassword',
				'message' => "Vos mots de passe ne sont pas identiques"
				),
			'prenom' => array(
				'rule'=> 'optional',
				),
			'nom' => array(
				'rule' => 'optional',
				),
			'age' => array(
				'rules'=> array(
							array(

								'rule'=>"regex",
								'regex'=>'19[0-9]{1}[0-9]{1}',
								'message'=> "Between 1900 and 1999..."
							),
							array(
								'rule'=>'optional',								
							)
						)
				)
		),
		'account_info' => array(
			'login' => array(
				'rules'=> array(
							array(
								'rule' => 'notEmpty',
								'message' => 'Your login is empty'
									),
							array('rule' => 'regex',
								'regex'=> '.{5,30}',
								'message' => 'Login between 5 and 20 caracters'
								)
							)
				),
			'email' => array(
				'rule' => 'email',
				'message' => "L'adresse email n'est pas valide"
				)
		),
		'account_profil' => array(

		),
		'recovery_mdp' => array(
			'password' => array(
				'rule' => 'regex',
				'regex' => '.{5,30}',
				'message' => "Votre mot de passe doit etre entre 5 et 20 caracteres"
				),
			'confirm' => array(
				'rule' => 'confirmPassword',
				'message' => "Vos mots de passe ne sont pas identiques"
				)
		),
		'account_password' => array(
			'oldpassword' => array(
				'rule' => 'notEmpty',
				'message' => "Votre mot de passe doit contenir entre 5 et 20 caracteres"
				),
			'password' => array(
				'rule' => 'regex',
				'regex' => '.{5,30}',
				'message' => "Votre mot de passe doit etre entre 5 et 20 caracteres"
				),
			'confirm' => array(
				'rule' => 'confirmPassword',
				'message' => "Vos mots de passe ne sont pas identiques"
				)
		),
		'account_delete' => array(
			'password' => array(
				'rule' => 'notEmpty',
				'message' => "Enter your password"
				)
		),
		'account_avatar'=>array(
			'avatar'=>array(
						'rule'=>'file',
						'params'=>array(
							'destination'=>'media/user/avatar',
							'extentions'=>array('png','gif','jpg','jpeg','JPG','bmp'),
							'extentions_error'=>'Your avatar is not an image file',
							'max_size'=>50000,
							'max_size_error'=>'Your image is too big',
							'ban_php_code'=>true
							),
						)
			),
	);


	public function saveUser($user,$user_id = null){

		//unset action value
		if(isset($user->action)) unset($user->action);
		//set user_id for updating
		if(isset($user_id)) $user->user_id = $user_id;
		//if there is nothing to save
		$tab = (array) $user;
		if( (count($tab)==0 ) || (count($tab)==1 && isset($tab['user_id'])))
			return true;

		//if new user , check unique value
		if(!isset($user->user_id)){

			//check if login already exist			
			$check = $this->findFirst(array(
							'fields'=>'user_id',
							'conditions'=>array('login'=>$user->login)
							));
			if(!empty($check)){
				Session::setFlash("Ce nom d'utilisateur est déjà pris, veuillez en choisir un autre.","error");
				return false;
			}

			//check if mail already in use
			$checkmail = $this->findFirst(array(
							'fields'=>'email',
							'conditions'=>array('email'=>$user->email)
							));
			if(!empty($checkmail)){
				Session::setFlash("Cet email est déjà pris... Vous êtes peut-être déjà inscrit ? Vous avez oublié vorte mot de passe ? Essayer de le <a href='".Router::url('users/recovery')."'>réinitialiser</a> !  ","error");
				return false;
			}

			//initiate default value
			$user->salt = String::random(10);
			$user->hash = md5($user->salt.$user->password);
			$user->codeactiv = String::random(25);
			$user->date_signin = Date::MysqlNow();	
			$user->date_lastlogin = $user->date_signin;
			if(isset($user->password)) unset($user->password);
			if(isset($user->confirm)) unset($user->confirm);
		}		

		if($id = $this->save($user))
			return $id;
		else
			return false;

	}

	public function findUsers($req = array()){

		$sql = 'SELECT ';
 		
 		if(isset($req['fields'])){
			if(is_array($req['fields']))
 				$sql .= implode(', ',$req['fields']);
 			else
 				$sql .= $req['fields'];
 		}
 		else $sql .= '* ';

 		$sql .= " FROM users ";

 		if(isset($req['conditions'])){
 			$sql .= ' WHERE ';
 			$sql .= $this->sqlConditions($req['conditions']);
 		}

 		if(isset($req['order'])){
 			if($req['order'] == 'random') $sql .= ' ORDER BY rand()';
 			else $sql .= ' ORDER BY '.$req['order'];
 		}

 		if(isset($req['limit'])){
			$sql .= ' LIMIT '.$req['limit'];
 		}

 		 // debug($sql);
 		
 		$results = $this->query($sql);


 		
 		$users = array();
 		foreach ($results as $user) {
		
 			$users[] = new User($user); 
 		}

 		return $users;
	}


	public function findFirstUser($req){

		return current($this->findUsers($req));
	}


	public function findParticipations( $fields, $user_ids ){

		$results = array();

		$sql = "SELECT $fields FROM manif_participation as P 
				LEFT JOIN manif_info as M ON M.manif_id = P.manif_id
				LEFT JOIN manif_descr as D ON D.manif_id = P.manif_id AND D.lang='".Session::user()->getLang()."'
				WHERE user_id = :user_id";

		foreach ($user_ids as $user_id) {

			$pre = $this->db->prepare($sql);
			$pre->bindValue(':user_id', $user_id, PDO::PARAM_INT);
			$pre->execute();

			return $pre->fetchAll(PDO::FETCH_OBJ);
			# code...
		}


	}

	public function deleteUser($id){

		$u = new stdClass();
		$u->id = $id;


		if($this->delete($u))
			return true;
		else
			return false;

	}

	public function findUserThread($user_id){


		$sql=" SELECT P.id,
	                 'PROTEST' AS TYPE,
	                 P.date,	                 
	                 D.name as relatedName,
	                 D.manif_id as relatedID,
	                 D.slug     as relatedSlug,
	                 I.logo     as relatedLogo

        		FROM manif_participation as P
        		LEFT JOIN manif_descr as D ON D.manif_id=P.manif_id AND D.lang='".Session::user()->getLang()."'	
        		LEFT JOIN manif_info as I ON I.manif_id=P.manif_id
           		WHERE P.user_id = $user_id
      		UNION
        --   	SELECT `id`,
	       --           'COMMENT' AS TYPE,
	       --           `date`
        --     	FROM `manif_comment`
	       --     	WHERE user_id = $user_id AND reply_to=0
      		-- UNION
       		SELECT C.id,
                	'NEWS' AS TYPE,
                  	C.date,
                  	D.name as relatedName,
                  	D.manif_id as relatedID,
                  	D.slug     as relatedSlug,
                  	I.logo     as relatedLogo
              	FROM manif_comment AS C
              	LEFT JOIN manif_participation AS P ON P.user_id = $user_id
              	LEFT JOIN manif_descr as D ON D.manif_id=P.manif_id AND D.lang='".Session::user()->getLang()."'
              	LEFT JOIN manif_info as I ON I.manif_id=P.manif_id	
             	WHERE C.context_id = P.manif_id AND C.context='manif' AND C.type='news'
			ORDER BY date DESC
			";


		$pre = $this->db->prepare($sql);
 		$pre->execute();
 		return $pre->fetchAll(PDO::FETCH_OBJ);

	}

	
}



class User {

	public $login = 'visitor';
	public $user_id = 0;
	public $role = 'visitor';
	public $avatar = 'img/logo_yp.png';
	public $account = 'visitor';

	public function __construct( $fields = array() ){

		foreach ($fields as $field => $value) {

		$this->$field = $value;
		}
	}

	public function getID(){

		return $this->user_id;
	}

	public function exist(){

		if($this->user_id==0) return false;
		return true;
	}

	public function isLog(){
		if($this->user_id==0) return false;
		return true;
	}
	public function getLogin(){

		if($this->account=='anonym') return 'anonym_'.$this->user_id;
		else return $this->login;
	}

	public function getFullName(){

		$str = '';
		if(isset($this->prenom)) $str .= $this->prenom;
		if(isset($this->nom)) $str .= ' '.$this->nom;

		return $str;
	}

	public function getEmail(){

		if(!empty($this->email)) return $this->email;
		return false;
	}

	public function getAvatar(){

		if(isset($this->avatar)&&!empty($this->avatar)&&file_exists(WEBROOT.DS.$this->avatar)) return $this->avatar;
		else return 'img/logo.png';
	}

	public function getRole(){
		return $this->role;
	}


	public function setLang($lang){
		$this->lang = $lang;
	}

	public function getLang(){
		if(!empty($this->lang)) return $this->lang;
		else false;
	}

	public function isMrZ(){

		if($this->statut=='admin') return true;
		else return false;
	}

	public function getBirthYear(){

		return $this->age;
	}

	public function getAge(){
		return date('Y') - $this->age;
	}
	public function isChairman(){
		if($this->getRole()=='admin' || $this->getRole('chairman')) return true;
		return false;
	}

	public function canSeeResume($author_id){
		if($this->role=='chairman' || $this->role=='admin') return true;
		if($this->user_id == $author_id) return true;
		return false;
	}
	public function canReview(){
		if($this->role=='reviewer' || $this->role=='chairman' ||$this->role=='admin') return true;
		return false;
	}
}
 ?>