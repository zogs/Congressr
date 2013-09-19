<?php
 class Model {

 	static $connections = array();
 	public $conf = 'default'; //Base de donnée à utiliser
 	public $table = false; //Table mysql à utiliser par le model
 	public $db = false; //Connexion courante à la base
 	public $primaryKey = 'id'; //Nom de la clef primaire de la table
 	public $id; //valeur de la clef courante
 	public $action;
 	public $errors = array(); // tableaux des erreurs à afficher
 	public $validates = array();



 	public function __construct() {

		  //Initialisation de la table sql du model
		if($this->table===false){
		  	$this->table = strtolower(str_replace('Model','',get_class($this)));
	  	}
		 
	  	//Recupération de l'évironnement
		$host = $_SERVER['HTTP_HOST'];
		 
	  	//Si la connexion existe déja, return true
	 	 if(isset(Model::$connections[$host])){
			  $this->db = Model::$connections[$host];
			  return true;	
		  }
		  else {

			//Sinon récupération des infos de connexion
			if(isset(Conf::$databases[$host]))
			$conf = Conf::$databases[$host];
			else
			throw new zException("Missing databases connexion for '".$host."'", 1);

		  }
		 
		  //On essaye de se connecter a la base
		  try{
			  //Creation d'un objet PDO
			  $pdo = new PDO('mysql:host='.$conf['host'].';dbname='.$conf['database'].';charset=utf8',
				  $conf['login'],
				  $conf['password'],
				  array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "utf8";',PDO::MYSQL_ATTR_INIT_COMMAND => $this->setTimeZone() ) //Important pour l'encode des carateres
			  );
			  $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING); //Important pour afficher les erreurs
			  Model::$connections[$host] = $pdo; //Attribution de la connexion a une varaible static
			  $this->db = $pdo;	//Attribution de la connexion a une varaible
			 
		  }
		  //Si connexion echoue
		  catch (PDOException $e){
			  //En mode debug on affiche les erreurs
			  if(DEBUG >=1 ){
			  	die($e->getMessage());
			  }
			  //En production on affiche une erreur simple
			  else{
			  	die('Impossible de se connecter a la base de donnees');
			  }
		  }

	 
	  }

 	public function setTimeZone(){

		$now = new DateTime();
		$mins = $now->getOffset() / 60;

		$sgn = ($mins < 0 ? -1 : 1);
		$mins = abs($mins);
		$hrs = floor($mins / 60);
		$mins -= $hrs * 60;

		$offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);

		return "SET time_zone='$offset'";
 	}

 	// ============================
 	// Permet de recuperer des champs de la base de donné à partir d'une requete
 	// @params $req array('conditions'=>array()) Tableaux des conditions de la requete

 	public function find($req)
 	{
 		//Si la table nest pas precisé on prend la table par default du model
 		if(!isset($req['table']))
 			$table = $this->table.' as '.get_class($this);
 		else
 			$table = $req['table'];

 		//requete
 		$sql = 'SELECT ';
 		
 		//champ à récuper
 		if(isset($req['fields']))
 			$sql .= $this->sqlFields($req['fields']);
 		else
 			$sql .= "*";
 		
 		//depuis la table
 		$sql .= ' FROM '.$table.' ';

 		//conditions de la requete sql
 		if(isset($req['conditions']) && $req['conditions'] != ''){

 			$sql .= ' WHERE ';

 			//Si les conditions sont pas un tableau , genre une requete personnelle
 			if(!is_array($req['conditions'])){
 				$sql .= $req['conditions']; 				
 			}
 			else {

 			$cond = array();
 			//On parse toutes les conditions du  tableau
	 			foreach ($req['conditions'] as $k => $v) {

	 				if(!empty($v)){

	 					//On escape les valeurs
		 				if(!is_numeric($v)){ 
		 					$v = '"'.mysql_escape_string($v).'"';	 					
		 				}
	 				
		 				//On incremente le tableau avec les conditions
		 				$cond[] = "$k=$v";
	 				}
	 					 			
	 			}
	 			//Enfin on rassemble les conditions et on les ajoute à la requete sql
	 			$sql .= implode(' AND ',$cond);
 			}
 			
 		} 	


 		//Contruction de l'ordre
 		if(isset($req['order'])){

 			$sql .= ' ORDER BY '.$req['order'];
 		}
 		//Contruction de la LIMIT
 		if(isset($req['limit'])){

 			$sql .= ' LIMIT '.$req['limit'];

 		}
 		
 		//debug($sql);
 		$pre = $this->db->prepare($sql);
 		$pre->execute();

 		if($pre->errorCode()==0)
			return $pre->fetchAll(PDO::FETCH_OBJ); 		
 		else
 			$this->reportPDOError($pre,__FUNCTION__,$sql);	
 		
 	}

 	public function reportPDOError($pdo,$function,$sql){

 		if(DEBUG>=1){

 			$error = $pdo->errorInfo();
 			debug('SQL Error in: function '.$function.', class '.get_class($this).', '.__FILE__);
 			debug($error[2]); 				
 			debug($sql);
 			exit();
 				
		}
		else {

 			$this->controller->e404('An SQL error occured. Please pray the developer to be brave and find the $!#*$ error !!');
		}


 	}
 	//=======================================
 	// Execute une requete sql
 	public function query($sql,$values = array()){
 		
 		$pre = $this->db->prepare($sql);
 		$pre->execute($values);
 		if($pre->errorCode()==0){

 			if($pre->columncount()==0) return true;
 			else return $pre->fetchAll(PDO::FETCH_OBJ);
 			
 		}
 			
 		else
 			$this->reportPDOError($pre,__FUNCTION__,$sql);
 	}


 	//=========================================
 	// Recupere le premier element d'un requete 
 	// $param $req requete sql et conditions

 	public function findFirst($req){

 		return current($this->find($req)); //current renvoi l'element courant , cest a dire le premier element dans ce cas là

 	}

 	public function findCount($conditions = ''){

 		$res = $this->findFirst( array(
				'fields'     => 'COUNT('.$this->primaryKey.') as count',
				'conditions' => $conditions
 			));

 		return $res->count;


 	}

 	/*===========================================================	        
 	Delete
 	@param $obj = object of values 
 	@work delete from default table or $obj->table 
 	@work line with default key or $obj->key equal to $obj->id
 	============================================================*/
 	public function delete($obj){
 		
 		
 		if(is_numeric($obj)){
	 		$sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey}=$obj";
	 		$res = $this->db->query($sql);
			return $res;
		}
		elseif(is_object($obj)){

			if(isset($obj->table))
				$table = $obj->table;			
			else
				$table = $this->table;
			if(isset($obj->key))
				$key = $obj->key;
			else
				$key = $this->primaryKey;


			$sql = "DELETE FROM ".$table." WHERE ".$key."=".$obj->id;
			$res = $this->db->query($sql);
			return $res;
		}
		elseif(is_array($obj)){

			foreach ($obj as $o) {
				$this->delete($o);
			}
		}
 	}


 	/*===========================================================	        
 	Save informations into sql tables
 	@param $data = object of values to insert
 	@work effectue un insert ou un update si primaryKey est présente dans $data
 	@work pass a table in the object to specfify a table
 	@return true with $this->id as lastInsertId()
 	$return false with error in $this->error
 	============================================================*/
 	
 	public function save($data){

 		//If a table is specified
 		if(isset($data->table)){
 			$table = $data->table;
 			unset($data->table);
 		} 		
 		else
 			$table = $this->table;

 		//if a primary key is specified
 		if(isset($data->key)){
 			$primaryKey = $data->key;
 			unset($data->key);
 		}
 		else
 			$primaryKey = $this->primaryKey;


 		$fields = array();
 		$tab = array();
 
 		//Pour chaque champ on cree les tableaux des valeurs 
 		foreach ($data as $k => $v) {

 			if($k!=$primaryKey){ //sauf si il s'agit de la clef primaire

 				if(!empty($v)||$v==0) {	// si la valeur nest pas vide , ou si elle egale 0

		 			$fields[] = "$k=:$k"; //tableaux des valeurs en sql
		 			$tab[":$k"] = $v; //tableau des valeurs pour la fonction execute de PDO
	 			}
	 		}
	 		elseif(!empty($v)){ //Pour la clef primaire sauf si elle est vide

	 			$tab[":$k"] = $v; //on la rajoute a la liste des champs pour l'update
	 			$this->id = $v; // l'id de l'element est sauvegardé pour être utilisé depuis un controlleur
	 		}
 		}
 		// debug($data);
 		// debug($key);
 		//Si la clef existe on fait un update
 		if(isset($data->$primaryKey) && !empty($data->$primaryKey) && $data->$primaryKey!=0){

 			$action = 'update';
 			$sql = 'UPDATE '.$table.' SET '.implode(',',$fields).' WHERE '.$primaryKey.'=:'.$primaryKey;
 			
 			$this->id = $data->$primaryKey; // retourne l'id de l'update


 		} else { //Sinon on fait un inset
 			$action = 'insert';
 			$sql = 'INSERT INTO '.$table.' SET '.implode(',',$fields);
 		}
 		
 		$pre = $this->db->prepare($sql); //prepare la requete
 		
 		$pre->execute($tab); //execute la requete grace au tableaux des valeurs ( :name, :contenu, :date, ...)
 		
 		//Si c'est un insert 'on recupere l'id de l'insertion'
 		if($action=='insert'){
 			$this->id = $this->db->lastInsertId();
 		}

 		//Set the action
 		$this->action = $action;

 		//Si pas d'error retourne true
 		if($pre->errorCode()==0)
			return $this->id; 		
 		else
 			$this->reportPDOError($pre,__FUNCTION__,$sql);	
 		
 	}

 	
 	/*===========================================================	        
 	Save uploaded file
 	@param $name input name
 	@param $newname new name (optional)
 	@param $directory path of the directory where to move uploaded file (optional if defined in the validates rules )
 	$return true | false
 	============================================================*/
 	public function saveFile($name, $newname = NULL, $directory = NULL){

 		if(isset($_FILES)){
 				 				
			if(isset($_FILES[$name]) ){

				$file = $_FILES[$name];

				foreach ($this->validates as $validates_action) {
					
					if(isset($validates_action[$name])) $validates = $validates_action[$name];				
				}

				if(!isset($validates)) throw new zException($name." - No validates rules associated with the file", 1);

				$extension = substr(strrchr($file['name'], '.'),1);

				if(isset($newname)){
					$destname = $newname.'.'.$extension;
				} else {
					$destname = $file['name'];
				}

				if(isset($directory)){
					$destdir = String::directorySeparation($directory);
				} elseif( isset($validates['params']['destination'])) {
					$destdir = String::directorySeparation($validates['params']['destination']);
				}

				$destination = $destdir.DIRECTORY_SEPARATOR.$destname;

				if(move_uploaded_file($file['tmp_name'], $destination)){

					return $destination;
				}
				else {
					throw new zException("Impossible to move the uploaded file", 1);
					
				}
				
			}
 			
 		}
 		return false;
 	}



 	public function increment($data){

 		if ( isset($data['table'])) {
 			$table = $data['table'];
 		}		
 		else {
 			$table = $this->table;
 		}

 		$field = $data['field'];
 		$id = $data['id'];

 		$sql = "UPDATE $this->table SET $field = $field+1 WHERE $this->primaryKey = $id";
 		$pre = $this->db->prepare($sql);
 		$pre->execute();

 		$sql ="SELECT $field FROM $this->table WHERE $this->primaryKey = $id";
 		$pre = $this->db->prepare($sql);
 		$pre->execute();
 		return $pre->fetch(PDO::FETCH_OBJ);
 	}

	

public function validates($data, $rules = null, $field = null){


			$errors = array();

			//On recupere les regles de validation
			if(!$rules){
				$validates = $this->validates;
			}
			else {

				if($field==null)
					$validates = $this->validates[$rules];
				else
					$validates = array($field=>$this->validates[$rules][$field]);
			}
 			
 			//Vérifie les regles de validation pour chaque champs
			foreach ($validates  as $field => $model) { 
					
				//Si la donnée correspondant est manquante -> erreur				
				if(empty($data->$field)){

					//Si il y a plusiers regles
					if(isset($model['rules'])){
						$rules = $model['rules']; 						
						if(in_array('optional',$rules)) $optional=true;
						//if not optional
						if(!isset($optional)){
							$rule = $rules[0];
							$errors[$field] = $rule['message'];
						}
						
					}
					//Si il y a qu'une regle (et que ce nest pas un upload de fichier)
					if(isset($model['rule']) && $model['rule']!='file'){
						
						//Si le champ est optionnel, sauter au prochain champ
						if($model['rule']=='optional') continue;
						
						$errors[$field] = $model['message'];
					}
					
				}
				else{
				
					//Si il y a plusiers regles
					if(isset($model['rules'])){
						$rules = $model['rules']; 						
					} 
					//Si il y a qu'une regle
					if(isset($model['rule'])){
						
						 $rules = array($model);
					}

					//Pour toutes les regles correspondante
					foreach ($rules as $rule) {

						if($rule['rule']=='notEmpty'){
							if(empty($data->$field)) $errors[$field] = $rule['message'];				
						}
						elseif($rule['rule']=='notNull'){
	 						if($data->$field==0) $errors[$field] = $rule['message'];				
	 					}
	 					elseif($rule['rule']=='email'){

	 						$email_regex = '[_a-zA-Z0-9-+]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-z]{2,4})';
	 						if(!preg_match('/^'.$email_regex.'$/',$data->$field)) $errors[$field] = $rule['message'];
	 						
	 					}
	 					elseif(strpos($rule['rule'],'confirm')===0){

	 						$fieldtoconfirm = strtolower(str_replace('confirm', '', $rule['rule']));

	 						if($data->$fieldtoconfirm!=$data->$field) $errors[$field] = $rule['message'];
	 						else unset($data->$field);
	 					}
	 					elseif($rule['rule']=='checkbox' || $rule['rule']=='radio'){
	 						
	 						if(!is_array($data->$field)) $checkboxs = array( $data->$field);
	 						else $checkboxs = $data->$field;
	 						
	 						if(!empty($rule['mustbetrue']))	{

	 							foreach ($rule['mustbetrue'] as $betrue) {
 								
 									if(!in_array($betrue,$checkboxs)) $errors[$field] = $rule['messages'][$betrue];
 								} 	
	 						}
 															 							
	 						foreach ($checkboxs as $checkbox) {
	 								
	 								$data->$checkbox = 1;	 
	 							}	
	 						unset($data->$field);	 						
	 					
	 					}	
						elseif($rule['rule']=='regex'){

							if(!preg_match('/^'.$rule['regex'].'$/',$data->$field)) $errors[$field] = $rule['message'];
						}
						elseif($rule['rule']=='file'){
							continue;
						}
					}				
				}
				//reset optionnal
				$optional=false;
			}

 			//Vérifie les fichiers uploadé
 			if(isset($_FILES)){ 
				
				//Pour chaque fichier uploader
 				foreach ($_FILES as $key => $file) { 					

 					$input = str_replace('input','',$key);

 					//Si le ficher est bien attendu par les regles
 					if(isset($validates[$key]) && $validates[$key]['rule']=='file'){

	 					//Si le fichier n'est pas vide et quil n'y pas d'erreur d'envoi
	 					if($file['error'] == 'UPLOAD_ERR_OK'){
	 						
		 					//Si il y a des regles définies
		 					if($validates[$key]['params']){

			 					//Si il y a une limite de poids 
			 					if($validates[$key]['params']['max_size']) {

			 						//Si le fichier est trop gros
			 						if($file['size']>$validates[$key]['params']['max_size']){

			 							$errors[$input] = $validates[$key]['params']['max_size_error'];
			 						}
			 					}
			 					//Si il y a des extentions spécifiquement authorisées
	 							if($validates[$key]['params']['extentions']){
	 								
	 								$extention = substr(strrchr($file['name'], '.'),1);
		 							$extentions = $validates[$key]['params']['extentions'];

		 							if(!in_array($extention,$extentions)){
		 								$errors[$input] = $validates[$key]['params']['extentions_error'];	 					
	 								} 
	 							}

	 							//If Prevent hidden php code
	 							if($validates[$key]['params']['ban_php_code'] && $validates[$key]['params']['ban_php_code'] == true){
		 							//Vérifie qu'il n'y a pas de code php caché dans l'image				 							
		 							if(strpos(file_get_contents($file['tmp_name']),'<?php')){

		 								throw new zException("Malicious php code detected in uploaded file", 1);
		 								
		 							}	
	 							}
			 				}
		 				}
		 				else {

		 					//if the upload is optional , continue
		 					if(isset($validates[$key]['optional'])) continue;

		 					//if the upload is required
		 					if(isset($validates[$key]['required']))
		 						$errors[$input] =$validates[$key]['message'];
		 				}
		 			}
		 			else {
		 				throw new zException("Uploading a file non-expected", 1);
		 				
		 			}
 				}
 			}

 			$this->errors = $errors;

 			//Si une class Form est lié a ce model
 			if(isset($this->Form)){
 				$this->Form->setErrors($errors); //On lui envoi les erreurs
 			}

 			//Si pas d'erreur , renvoi les données
 			if(empty($errors)){
 				return $data;
 			}
 				
 			return false;
 			 			 		
 	}


 	/*	
	Renvoi le compte des primaryKey depuis un certain temps
	$params $conditions 
	@params $second : temps en seconde à deduire de à partir de maintenant
 	*/
 	public function countNew($params){

 		extract($params);

 		$sql = " SELECT COUNT( ".$this->primaryKey." ) as count ";
 		$sql .=" FROM ".$this->table;
 		$sql .=" WHERE ";
 		if(isset($conditions)){
 			if(!is_array($conditions)){
 				$sql .= $conditions; 				
 			}
 			else {
 			$cond = array();
	 			foreach ($conditions as $k => $v) {
	 				if(!is_numeric($v)){ 
	 					$v = '"'.mysql_escape_string($v).'"';	 					
	 				}
	 				$cond[] = "$k=$v";	 			
	 			}
	 			$sql .= implode(' AND ',$cond);
 			}
 			
 		}
 		$sql .=" AND ".$date_field." > DATE_SUB( CURRENT_TIMESTAMP , INTERVAL $second SECOND)";

		$pre = $this->db->prepare($sql);
 		$pre->execute();
 		return $pre->fetch(PDO::FETCH_OBJ);

 	}

 	public function sqlFields($fields = array() ){

 		if(empty($fields)) return ' * ';
 		
 		$f = '';
		if(is_array($fields))
			$f .= implode(', ',$fields); 			
		elseif(is_string($fields))
			$f .= $fields; 			 		
		else
			$f .= ' * ';
 		return $f; 	
 	}

 	public function sqlConditions($conditions){
 		
 		$c='';
 		if($conditions && !empty($conditions)){
	 		if(is_array($conditions)){

	 			$cond = array();
	 			foreach ($conditions as $k => $v) {
	 				if(!is_numeric($v) && substr($v, 0, 1) != ':' )
	 					$v = $this->db->quote($v);
	 				$cond[] = "$k=$v";
	 			}
	 			$c .= implode(' AND ',$cond);
	 		}
	 		else
	 			$c .= $conditions;
	 	}
	 	else
	 		$c .= '1=1';
	 	return $c;
 	}


 	public function JOIN($table,$fields,$conds,$obj){

	 	$array = array();

	 	$fields = $this->sqlFields($fields);

		$sql = "SELECT ".$fields." FROM ".$table." WHERE ".$this->sqlConditions($conds);
		$pre = $this->db->prepare($sql);

		if(is_object($obj)){

			if(preg_match_all("/:([a-zA-Z0-9\_\-]+)/",$sql,$matches)){
				unset($matches[0]);
				foreach ($matches as $key) {
		
					$pre->bindValue(":".$key[0],$obj->$key[0]);
				}
			}
			
			$pre -> execute();
			$res = $pre->fetch(PDO::FETCH_OBJ);

			if(!empty($res)){
				foreach ($res as $k=>$v) {
					
					$obj->$k = $v;

				}
			}			

			return $obj;

		}
		elseif( is_array($obj)){
			
			foreach ($obj as $row) {

				$array[] = $this->JOIN($table,$fields,$conds,$row);
				
			}

			return $array;
		}
		

	}
}