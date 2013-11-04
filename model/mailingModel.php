<?php 
class MailingModel extends Model {

	public $validates = array(
		'editlist'=> array(
			'name'=>array(
				'rule'=>'notEmpty',
				'message' => 'Veuillez remplir un name de liste'),
			'emails'=>array(
				'rule'=>'notEmpty',
				'message'=>'Veuillez remplir avec au moins une adresse email'),
			)
		,
		'editmailing'=>array(
			'addpj'=>array(
				'rule'=>'file',
				'params'=>array(
					'destination'=>'media/pj',
					//'extentions'=>array('doc'),
					//'extentions_error'=>'Your document is not a .doc file',
					//'max_size'=>500000,
					//'max_size_error'=>'Your document is too big',
					//'ban_php_code'=>true					
					)
				)
			,
			'title'=>array(
				'rule'=>'notEmpty',
				'message'=>'Le titre ne peut être vide'
				)
			,
			'object'=>array(
				'rule'=>'notEmpty',
				'message'=>"L'objet de l'email ne peut être vide"
				)
			,
			'content'=>array(
				'rule'=>'notEmpty',
				'message'=>'Le contenu du mail ne peut être vide'
				)
			)
		);

	public function saveMailing($data){

		$list_id = $this->saveList($data);
		
		if($this->saveEmails($data->emails,$list_id)){
			return $list_id;
		}

		return false;
	}

	public function deleteEmail($eid){

		$sql = 'DELETE FROM mailing_email WHERE id='.$eid;

		if($this->query($sql)) return true;
		return false;
	}

	public function deleteList($lid){

		$sql = 'DELETE FROM mailing_mailinglist WHERE list_id='.$lid;

		$emails = $this->getEmailsByListID($lid);
		foreach ($emails as $email) {
			
			$this->deleteEmail($email->id);
		}

		if($this->query($sql)) return true;
		return false;
	}

	public function saveEmails($emails,$list_id){
		
		$emails = str_replace('<','',$emails);
		$emails = str_replace('>','',$emails);
		
		$emails = String::findEmailsInString($emails);
		
		$dest = array();

		foreach ($emails as $email) {			

			$mail = explode('@',$email);
			
			$addr = $mail[0];
			$domain = $mail[1];

			$addr = explode('.',$addr);
			$domain = explode('.',$domain);

			$user = array();

			$user['email'] = $email;
			if(count($addr)>1){
				$user['prenom'] = $addr[0];
				$user['nom'] = $addr[1];				
			}
			$user['institution'] = $domain[0];
		
			$dest[] = $user;
		}

		foreach ($dest as $user) {
			
			$save = new stdClass();
			$save->email = $user['email'];
			$save->list_id = $list_id;
			$save->institution = $user['institution'];
			$save->table = 'mailing_email';
			if(!empty($user['prenom'])) $save->prenom = $user['prenom'];
			if(!empty($user['nom'])) $save->nom = $user['nom'];

			$check = $this->findFirst(array('table'=>'mailing_email','conditions'=>array('list_id'=>$list_id,'email'=>$user['email'])));
			if(!empty($check)){
				$save->key = 'id';
				$save->id = $check->id;
			}

			$this->save($save);
		}

		return true;
	}

	public function saveList($data){

		if(!empty($data->list_id))
			$check = $this->findFirst(array('table'=>'mailing_mailinglist','conditions'=>array('list_id'=>$data->list_id)));

		$list = new stdClass();
		$list->name = $data->name;
		$list->table='mailing_mailinglist';
		if(!empty($check)){
			$list->key = 'list_id';
			$list->list_id=$check->list_id;
		}


		if($id = $this->save($list))
			return $id;
		return false;

	}

	public function getlistByID($lid){

		return $this->findFirst(array('table'=>'mailing_mailinglist','conditions'=>array('list_id'=>$lid)));
	}

	public function getEmailsByListID($lid){

		return $this->find(array('table'=>'mailing_email','conditions'=>array('list_id'=>$lid)));
	}

	public function findMailingList(){

		$li = $this->find(array('table'=>'mailing_mailinglist'));

		foreach ($li as $l) {
			
			$emails = $this->find(array('table'=>'mailing_email','conditions'=>array('list_id'=>$l->list_id)));
			$l->emails = $emails;
			$l->count = count($emails);
		}

		return $li;
	}

	public function findMailingbyId($mid){
		if(empty($mid)) return new Mailing();
		$m = $this->findFirst(array('table'=>'mailing_sending','conditions'=>array('id'=>$mid)));
		$m = new Mailing($m);
		return $m;
	}

	public function findMailing(){

		$mailings =  $this->find(array('table'=>'mailing_sending'));
		foreach ($mailings as $m) {
			$m = new Mailing($m);
		}
		return $mailings;
	}
}

class Mailing {

	public $id = '';
	public $title = '';
	public $status = '';	


	public function __construct( $fields = array() ){

		foreach ($fields as $key => $value) {
			$this->$key = $value;
		}
	}
}

?>