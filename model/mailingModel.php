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
		'editsignature'=>array(
			'name'=>array(
				'rule'=>'notEmpty',
				'message'=>"Le nom ne peut être vide"
				)
			),
		'editmailing'=>array(
			'addpj'=>array(
				'rule'=>'file',
				'optional'=>'optional',
				'params'=>array(
					'destination'=>'media/mailing/pj',
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

	public function saveMailingList($data){

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

	public function findSignatures(){

		return $this->find(array('table'=>'mailing_signature'));
	}

	public function getSignatureById($id){
		if(empty($id)) return '';
		$s = $this->findFirst(array('table'=>'mailing_signature','conditions'=>array('id'=>$id)));
		return $s->content;
	}

	public function getlistByID($lid){

		return $this->findFirst(array('table'=>'mailing_mailinglist','conditions'=>array('list_id'=>$lid)));
	}

	public function getEmailsByListID($lid){

		return $this->find(array('table'=>'mailing_email','conditions'=>array('list_id'=>$lid),'order'=>'email ASC'));
	}

	public function getEmailsByUserList($list){

		if(empty($list)) return array();
		if($list=='reviewers') return $this->find(array('table'=>'users','conditions'=>array('role'=>'reviewer'),'fields'=>'user_id,login,prenom,nom,email,job'));
		if($list=='redactors') return $this->find(array('table'=>'users','conditions'=>array('role'=>'redactor'),'fields'=>'user_id,login,prenom,nom,email,job'));

		$articles = array();
		if($list=='resume_oral') $articles = $this->find(array('table'=>'resume','conditions'=>array('status'=>'accepted','comm_type'=>'oral')));
		if($list=='resume_poster') $articles = $this->find(array('table'=>'resume','conditions'=>array('status'=>'accepted','comm_type'=>'poster')));
		if($list=='resume_refused') $articles = $this->find(array('table'=>'resume','conditions'=>array('status'=>'refused')));
		if($list=='resume_pending') $articles = $this->find(array('table'=>'resume','conditions'=>array('status'=>'pending')));
		if($list=='resume_reviewed') $articles = $this->find(array('table'=>'resume','conditions'=>array('status'=>'reviewed')));
		if($list=='article_oral') $articles =  $this->getDeposedByStatusAndComm('accepted','oral');
		if($list=='article_poster') $articles =  $this->getDeposedByStatusAndComm('accepted','poster');
		if($list=='article_refused') $articles = $this->find(array('table'=>'deposed','conditions'=>array('status'=>'refused')));
		if($list=='article_pending') $articles = $this->find(array('table'=>'deposed','conditions'=>array('status'=>'pending')));
		if($list=='article_reviewed') $articles = $this->find(array('table'=>'deposed','conditions'=>array('status'=>'reviewed')));

		$articles = $this->JOIN('users','user_id,prenom,nom,email,job',array('user_id'=>':user_id'),$articles);
		return $articles;		
		
	}

	public function getDeposedByStatusAndComm($status,$comm)
	{
		$sql = "SELECT * 
			FROM deposed as D 
			JOIN resume as R ON D.resume_id=R.id
			WHERE D.status=:status
			AND R.comm_type=:comm
			";

		return $this->query($sql,array(
			'status' => $status,
			'comm' => $comm
			));
		
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

	public function findMailingToSendByCron(){

		$m = $this->find(array('table'=>'mailing_sending','conditions'=>array('method'=>'cron','finished'=>0)));
		foreach ($m as $k => $v) {
			$m[$k] = new Mailing($v);
		}
		return $m;
	}

	public function findEmailsToSend($method,$limit = 10){

		$emails = $this->find(array('table'=>'mailing_mailtosend','conditions'=>array('method'=>$method,'sended'=>0),'limit'=>$limit));		
		foreach ($emails as $key => $value) {
				$value->email = unserialize($value->email);	
				$emails[$key] = $value;
		}		
		return $emails;
	}

	public function findEmailsToSendByMailingId($mid,$limit=10){

		$m = $this->find(array('table'=>'mailing_mailtosend','conditions'=>array('mid'=>$mid,'sended'=>0),'limit'=>$limit));
		return $m;
	}

	public function findNumberRestRefreshMailing($mid){

		$m = $this->findFirst(array('table'=>'mailing_mailtosend','fields'=>'count(id) as count','conditions'=>array('mid'=>$mid,'sended'=>0)));
		return $m->count;
	}

	public function isNoMoreMailToSendForMailing($mid){

		$m = $this->findFirst(array('table'=>'mailing_mailtosend','conditions'=>array('mid'=>$mid,'sended'=>0),'limit'=>1));
		if(empty($m)) return true;
		else return false;
	}

	public function deleteMailSendedByMailingId($mid){

		$sql = 'DELETE FROM mailing_mailtosend WHERE mid=:mid AND sended=1';
		$tab = array('mid'=>$mid);
		$this->query($sql,$tab);
		return true;
	}

	public function markEmailAsSended($id){

		$sql = 'UPDATE mailing_mailtosend SET sended=1,date=:date WHERE id=:id';
		$tab = array(':id'=>$id,':date'=>Date::mysqlNow());
		$this->query($sql,$tab);
		return true;
	}

	public function getMailingbyId($mid){
		if(empty($mid)) return new Mailing();
		$m = $this->findFirst(array('table'=>'mailing_sending','conditions'=>array('id'=>$mid)));
		$m = new Mailing($m);
		return $m;
	}

	public function findMailing(){

		$mailings =  $this->find(array('table'=>'mailing_sending','order'=>'date_created DESC'));		
		foreach ($mailings as $k => $m) {			
			$mailings[$k] = new Mailing($m);			
		}
		return $mailings;
	}

	public function saveMailing($m){

		$m->table = 'mailing_sending';
		$m->key = 'id';

		if($this->save($m)) return true;
		return false;
	}

	public function deleteMailing($mid){

		$sql = 'DELETE FROM mailing_sending WHERE id='.$mid;

		if($this->query($sql)) return true;
		return false;
	}

	public function saveMailToSend($emails,$mid,$action){

		foreach ($emails as $k => $v) {
		
			$new = new stdClass();
			$new->table = 'mailing_mailtosend';
			$new->email = serialize($v);
			$new->method = $action;
			$new->mid = $mid;

			$this->save($new);
		}

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

	public function exist(){
		if(!empty($this->id)) return true;
		return false;
	}

	public function getSendingDate(){
		if($this->date_sended!=='0000-00-00 00:00:00') return $this->date_sended;
		return false;
	}

	public function getFinishedDate(){
		if($this->date_finished!=='0000-00-00 00:00:00') return $this->date_finished;
	}

	public function getStatus(){
		if($this->getFinishedDate()) return 'Finished: '.$this->getFinishedDate();
		if(!empty($this->status)) return $this->status;

	}

	public function getMailingListId(){
		if(!empty($this->mailinglist_id)) return $this->mailinglist_id;
		return 0;
	}
	public function getMethod(){
		if(!empty($this->method)) return $this->method;
		return false;		
	}

}

?>