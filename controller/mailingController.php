<?php

class MailingController extends Controller {

	private static $mailBody  = array();
	
	public function admin_index(){


	}

	public function admin_listmailing(){

		$this->loadModel('Mailing');

		$lists = $this->Mailing->findMailingList();

		$this->set('lists',$lists);

	}

	public function admin_deleteEmail($lid,$eid){

		$this->loadModel('Mailing');

		if($this->Mailing->deleteEmail($eid)){
			Session::setFlash('Email supprimé');
		}

		$this->redirect('admin/mailing/editlist/'.$lid);
	}

	public function admin_deletelist($lid){

		$this->loadModel('Mailing');

		if($this->Mailing->deleteList($lid)){
			Session::setFlash('Liste supprimé');
		}

		$this->redirect('admin/mailing/listmailing');
	}

	public function admin_editlist($lid = null){

		$this->loadModel('Mailing');

		if($data = $this->request->post()){

			if($this->Mailing->validates($data,'editlist')){

				if($lid = $this->Mailing->saveMailing($data)){

					Session::setFlash('La mailing list a bien été enregistré !');
					$this->redirect('admin/mailing/editlist/'.$lid);
				}
				else {
					Session::setFlash('Error save mailing list','error');
				}
			}
		}

		if($lid){

			$list = $this->Mailing->getListByID($lid);
			$list->users = $this->Mailing->getEmailsByListID($lid);
			$emails = '';
			foreach ($list->users as $u) {
				$emails .= $u->email.';';				
			}
			$list->emails = $emails;
		}
		else{
			$list = new stdClass();
		}


		$this->request->data = $list;

		$this->set('list',$list);

	}

	public function admin_freemailing(){

		$this->loadModel('Mailing');

		$timer = microtime(true);

		if($data = $this->request->post()){

			if($data = $this->Mailing->validates($data,'freemailing')){

				$emails = array();
				$content = '';
				$title = '';
				$path = '';

				if(!empty($data->list_id)){
					$e = $this->Mailing->getEmailsByListID($data->list_id);
					foreach ($e as $k => $v) {
						$e[$k] = $v->email; 
					}
					$emails = array_merge($emails,$e);
				}

				if(!empty($data->emails)){

					$e = String::findEmailsInString($data->emails);
					$emails = array_merge($emails,$e);
				}

				if(!empty($data->title)){
					$title = $data->title;
				}

				if(!empty($data->content)){

					$content = $data->content;
				}
				
				if(!empty($_FILES['pj']['name'])){

					if($path = $this->Mailing->saveFile('pj')){

						$path = WEBROOT.DS.$path;
					}

				}
				
				//creating message
				//récupère le template et remplace les variables
				$body = file_get_contents('../view/email/freeMailing.html');
				$body = preg_replace("~{content}~i", $content, $body);
				$body = preg_replace("~{congress}~i", Conf::$congressName, $body);
				$body = preg_replace("~{contact}~i", Conf::$congressContactEmail, $body);
				$body = preg_replace("~{title}~i", $title, $body);

				//Création du mail
				$message = Swift_Message::newInstance()
				 ->setSubject($title)
				 ->setFrom(Conf::$congressContactEmail,Conf::$congressName)
				 ->setBody($body, 'text/html', 'utf-8');

				 //attach pj
				  if(!empty($pj)){
				  	$pj = Swift_Attachment::FromPath($pj);
				  	$message->attach($pj);
				  }

				//sending message
				$results = array();
				$results['sended'] = array();
				$results['errors'] = array();
				//make group of recipients
				$emailsNbRecipients = array_chunk($emails, Conf::$mailingNbRecipients);

				foreach ($emailsNbRecipients as $emails) {

					if(!$failures = $this->sendMailing($emails,$message)){
						$results['errors'] = array_merge($results['errors'],$failures);
					}
					else{
						$results['sended'] = array_merge($results['sended'],$emails);
					}

					sleep(Conf::$mailingTimeBetween2Sending);
				}				

				if(!empty($results['sended'])){
					Session::setFlash(count($results['sended']).' emails envoyés ! ','success');
				}

				if(!empty($results['errors'])){
					Session::setFlash(count($results['errors']). ' erreurs d\'envoi... ('.implode(' ; ',$results['errors']).')');
				}

				Session::setFlash('Envoi effectué en '.round(microtime(true) - $timer,5).' secondes','warning');
				
			}

		}

		//data for the page
		$lists = $this->Mailing->findMailingList();
		$selectLists = array();
		foreach ($lists as $key => $l) {
			$selectLists[$l->list_id] = $l->name;
		}

		$this->set('selectLists',$selectLists);
	}

	private function sendMailing($emails = array(), $message){		

		if(count($emails)==1){
			if(is_int($name))
				$message->setTo($address);
			else
				$message->setTo(array($address => $name));	
		}
		else {
			foreach ($emails as $name => $address) {
				if(is_int($name))
					$message->addTo($address);
				else
					$message->addTo(array($address => $name));			
			}		
		}

		if (!Conf::getMailer()->send($message, $failures))
		  	return $failures;
		else 
			return true;
	}


	public function admin_resumes(){

		$this->loadModel('Articles');

		if($data = $this->request->post()){
			if($id = $this->Articles->saveMailingContent($data,$this->getLang())){
				Session::setFlash("Le contenu du mail a été sauvé.","success");				
			}
			else
				Session::setFlash("Error while saving mailing content","error");							
		}

		$d['resumeRefused'] = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$this->getLang(),'article'=>'resume','result'=>'refused')));
		$d['resumeAcceptedPoster'] = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$this->getLang(),'article'=>'resume','result'=>'accepted','comm_type'=>'poster')));
		$d['resumeAcceptedOral'] = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$this->getLang(),'article'=>'resume','result'=>'accepted','comm_type'=>'oral')));		
		$d['signature'] = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$this->getLang(),'article'=>'signature','result'=>'none','comm_type'=>'none')));


		$this->set($d);
	}

	public function admin_articles(){

		$this->loadModel('Articles');

		if($data = $this->request->post()){
			if($id = $this->Articles->saveMailingContent($data,$this->getLang())){
				Session::setFlash("Le contenu du mail a été sauvé.","success");				
			}
			else
				Session::setFlash("Error while saving mailing content","error");							
		}

		$d['articleAcceptedPoster'] = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$this->getLang(),'article'=>'deposed','result'=>'accepted','comm_type'=>'poster')));
		$d['articleAcceptedOral'] = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$this->getLang(),'article'=>'deposed','result'=>'accepted','comm_type'=>'oral')));
		$d['signature'] = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$this->getLang(),'article'=>'signature','result'=>'none','comm_type'=>'none')));

		$this->set($d);
	}

	public function admin_mailing(){

		$this->loadModel('Articles');

		if($data = $this->request->post()){

			if($id = $this->Articles->saveMailingContent($data,$this->getLang())){
				Session::setFlash("Le contenu du mail a été sauvé.","success");				
			}
			else
				Session::setFlash("Error while saving mailing content","error");					
			
		}

		$d['resumeRefused'] = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$this->getLang(),'article'=>'resume','result'=>'refused')));
		$d['resumeAcceptedPoster'] = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$this->getLang(),'article'=>'resume','result'=>'accepted','comm_type'=>'poster')));
		$d['resumeAcceptedOral'] = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$this->getLang(),'article'=>'resume','result'=>'accepted','comm_type'=>'oral')));
		$d['articleAcceptedPoster'] = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$this->getLang(),'article'=>'deposed','result'=>'accepted','comm_type'=>'poster')));
		$d['articleAcceptedOral'] = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$this->getLang(),'article'=>'deposed','result'=>'accepted','comm_type'=>'oral')));
		$d['signature'] = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$this->getLang(),'article'=>'signature','result'=>'none','comm_type'=>'none')));


		$this->set($d);
	}




	/**
	 * find all the resumes and send each a email with the comitee decision
	 * @return redirect and flash the number of mail sended
	 */
	public function admin_sendResumeMailing(){

		$timer = microtime(true);

		$this->loadModel('Articles');

		$resumes = $this->Articles->findResumes(array('mailed'=>0));
		$resumes = $this->Articles->JOIN('users','prenom,nom,email,lang',array('user_id'=>':user_id'),$resumes);
		$resumes = $this->Articles->joinReviews($resumes,'resume');

		$signature = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$this->getLang(),'article'=>'signature')));
		$this->signature = $signature->content;				
		
		$refused = array();
		$accepted = array();
		$accepted['oral'] = array();
		$accepted['poster'] = array();
		$pending = array();
		$reviewed = array();

		foreach($resumes as $r){

			if($r->status=='refused')
				$refused[] = $r;
			if($r->status=='accepted' && $r->comm_type=='oral')
				$accepted['oral'][] = $r;
			if($r->status=='accepted' && $r->comm_type=='poster')
				$accepted['poster'] = $r;
			if($r->status=='pending')
				$pending[] = $r;
			if($r->status=='reviewed')
				$reviewed[] = $r;
		}

		if(!empty($refused)){

			$body = $this->getResumeMailDecisionBody('refused');
			$this->sendMailingResumeByStatus($refused,$body,'refused');
		}

		if(!empty($accepted['oral'])){
			$body = $this->getResumeMailDecisionBody('oral');
			$this->sendMailingResumeByStatus($accepted['oral'],$body,'oral');
		}

		if(!empty($accepted['poster'])){
			$body = $this->getResumeMailDecisionBody('poster');
			$this->sendMailingResumeByStatus($accepted['poster'],$body,'poster');
		}

		
		//Flash
		Session::setFlash(count($resumes). ' resumes, '.count($accepted['poster']).' poster, '.count($accepted['oral']).' oral, '.count($refused).' refused, '.count($pending).' still pending ,'.count($reviewed).' reviewed with no decision','info');		
		Session::setFlash('Envoi effectué en '.round(microtime(true) - $timer,5).' secondes','warning');

		$this->redirect('admin/articles/resumes');
	}

	/**
	 * find resume mail decision contents
	 * @param  string $status refused|poster|oral
	 * @param  string $lang   lang du mail
	 * @return string         body of the mail
	 */
	private function getResumeMailDecisionBody($status,$lang='fr'){

		//check and return the body if in memory
		if(isset(self::$mailBody['resume'][$lang][$status])) return self::$mailBody['resume'][$lang][$status];

		//load model
		$this->loadModel('Articles');

		//find the appropriate content depending of the status
		$content = '';
		if($status=='refused'){
			$content = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$lang,'article'=>'resume','result'=>'refused')));
			if(empty($content)) $content = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>Conf::$languageDefault,'article'=>'resume','result'=>'refused')));
			$content = $content->content;	
		}
		if($status=='poster'){
			$content = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$lang,'article'=>'resume','result'=>'accepted','comm_type'=>'poster')));
			if(empty($content)) $content = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>Conf::$languageDefault,'article'=>'resume','result'=>'accepted','comm_type'=>'poster')));
			$content = $content->content;			
		}
		if($status=='oral'){
			$content = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$lang,'article'=>'resume','result'=>'accepted','comm_type'=>'oral')));
			if(empty($content)) $content = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>Conf::$languageDefault,'article'=>'resume','result'=>'accepted','comm_type'=>'oral')));
			$content = $content->content;
		}

		//get the template of the mail
		$body = file_get_contents('../view/email/resumeMailing.html');
		$body = preg_replace("~{content}~i", $content, $body);
		$body = preg_replace("~{congress}~i", Conf::$congressName, $body);
		$body = preg_replace("~{signature}~i", $this->signature, $body);

		//set body in memory
		self::$mailBody['resume'][$lang][$status] = $body;

		return $body;
	}

	/**
	 * send decisions to an array of resumes depending of the status of the resumes
	 * @param  array  $resumes array of resume object
	 * @param  string $body    body of the mail
	 * @param  string $status  status of the resumes
	 * @return true         set flash of success and errors
	 */
	private function sendMailingResumeByStatus($resumes = array(),$body = '', $status = ''){

		//creation du message
		$message = Swift_Message::newInstance()
		  ->setSubject(Conf::$congressName)
		  ->setFrom(Conf::$congressContactEmail, Conf::getSiteUrl());
		  
		//boucle sur tous les résumé 
		$errors = array();
		foreach ($resumes as $resume) {
			
			//personnalise le contenu du mail
			$body = str_replace("{lastname}", $resume->nom, $body);
			$body = str_replace("{title}", $resume->title, $body);
			$message->setBody($body, 'text/html', 'utf-8');			
			$message->setTo(array($resume->email=>$resume->nom));			
			
			//Envoi du message et affichage des erreurs éventuelles
			if (!Conf::getMailer()->send($message, $failures)){
			   	$errors = array_merge($errors,$failures);
			}
		}


		$sended = count($resumes) - count($errors);
		Session::setFlash($status.' : '.$sended.' email sended ','success');
		
		if(!empty($errors)) 
			Session::setFlash($status.' : '.count($errors).' errors :'.implode(' ; ',$errors));

		return true;

	}

	/**
	 * send resume decision test mail to all the chairman and admin of the congress
	 * @return redirect to previous page
	 */
	public function admin_sendTestResumeMailing(){

		$this->loadModel('Articles');
		$this->loadModel('Users');

		$admins = $this->Users->findUsers(array('conditions'=>array('role'=>'admin')));
		$chairmans = $this->Users->findUsers(array('conditions'=>array('role'=>'chairman')));
		$admins = array_merge($admins,$chairmans);

		$langs = $this->Articles->find(array('table'=>'mailing','fields'=>'DISTINCT lang'));

		foreach ($langs as $lang) {
			
			foreach ($admins as $admin) {
				
				$resume = $this->Articles->findResumes(array('conditions'=>array('status'=>'accepted'),'limit'=>1));
				$resume = $resume[0];
				$resume->lang = $lang->lang;
				$resume->nom = (!empty($admin->nom))? $admin->nom : 'LASTNAME';
				$resume->email = $admin->email;
				$resume = array($resume);

				$signature = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$lang->lang,'article'=>'signature')));
				$this->signature = $signature->content;

				$status = array('refused','poster','oral');
				foreach ($status as $s) {
					$body = $this->getResumeMailDecisionBody($s,$lang);
					$this->sendMailingResumeByStatus($resume,$body,$s);
				}
			}
		}

		Session::setFlash('Test mails sended');

		$this->redirect('admin/articles/resumes');
	}


	public function admin_sendTestArticleMailing(){

		$this->loadModel('Articles');
		$this->loadModel('Users');

		$admins = $this->Users->findUsers(array('conditions'=>array('role'=>'admin')));
		$chairmans = $this->Users->findUsers(array('conditions'=>array('role'=>'chairman')));
		$admins = array_merge($admins,$chairmans);

		$article = $this->Articles->findDeposed(array('conditions'=>array('status'=>'accepted'),'limit'=>1));
		if(empty($article)){
			$article = new stdClass();
			$article->title="Article de test";
			$article->status="accepted";
			$article->comm_type="oral";
			$article->lang="fr";
		}		
		else $article = $article[0];

		$langs = $this->Articles->find(array('table'=>'mailing','fields'=>'DISTINCT lang'));

		foreach ($langs as $lang) {
			
			foreach ($admins as $admin) {
								
				$article->lang = $lang->lang;
				$article->nom = (!empty($admin->nom))? $admin->nom : 'LASTNAME';
				$article->email = $admin->email;

				$signature = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$lang->lang,'article'=>'signature')));
				$this->signature = $signature->content;

				$status = array('refused','poster','oral');
				foreach ($status as $s) {
					$body = $this->getArticleMailDecisionBody($s,$lang);
					$this->sendArticleDecisionByCommType($resume,$body,$s);
				}

			}
		}

		Session::setFlash('Test mails sended');

		$this->redirect('admin/articles/articles');
	}

	/**
	 * find article mail decision contents
	 * @param  string $status refused|poster|oral
	 * @param  string $lang   lang du mail
	 * @return string         body of the mail
	 */
	private function getArticleMailDecisionBody($status,$lang='fr'){

		//check if the body is in memory
		if(isset(self::$mailBody['article'][$lang][$status])) return self::$mailBody['article'][$lang][$status];


		$this->loadModel('Articles');

		//find the appropriate content depending of the status
		$content = '';
		if($status=='poster'){			
			$c = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$lang,'article'=>'deposed','result'=>'accepted','comm_type'=>'poster')));
			if(empty($c)) $c = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>Conf::$languageDefault,'article'=>'deposed','result'=>'accepted','comm_type'=>'poster')));
			$content = $c->content;					
		}
		if($status=='oral'){		
			$c = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$lang,'article'=>'deposed','result'=>'accepted','comm_type'=>'oral')));
			if(empty($c)) $c = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>Conf::$languageDefault,'article'=>'deposed','result'=>'accepted','comm_type'=>'oral')));
			$content = $c->content;							
		}

		//get the template of the mail
		$body = file_get_contents('../view/email/articlesMailing.html');
		$body = preg_replace("~{content}~i", $content, $body);
		$body = preg_replace("~{congress}~i", Conf::$congressName, $body);
		$body = preg_replace("~{signature}~i", $this->signature, $body);

		//set the body in memory 
		self::$mailBody['article'][$lang][$status] = $body;


		return $body;
	}
	public function admin_sendArticleMailing(){

		$timer = microtime(true);

		$this->loadModel('Articles');

		$articles = $this->Articles->findDeposed(array('mailed'=>0));
		$articles = $this->Articles->JOIN('users','prenom,nom,email,lang',array('user_id'=>':user_id'),$articles);

		$signature = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$this->getLang(),'article'=>'signature')));
		$this->signature = $signature->content;

		foreach ($articles as $a) {
			
			if($a->comm_type=='poster'){
				$posters[] = $a;
			}
			if($a->comm_type=='oral'){
				$orals[] = $a;
			}
		}

		if(!empty($posters)){

			$body = $this->getArticleMailDecisionBody('poster');
			$this->sendArticleDecisionByCommType($posters,$body,'poster');
		}

		if(!empty($orals)){
			$body = $this->getArticleMailDecisionBody('oral');
			$this->sendArticleDecisionByCommType($orals,$body,'oral');
		}

		//Flash
		Session::setFlash(count($articles). ' articles, '.count($posters).' posters, '.count($orals).' orals','info');		
		Session::setFlash('Envoi effectué en '.round(microtime(true) - $timer,5).' secondes','warning');

		$this->redirect('admin/articles/articles');

	}

	/**
	 * send decisions to an array of resumes depending of the status of the resumes
	 * @param  array  $resumes array of resume object
	 * @param  string $body    body of the mail
	 * @param  string $status  status of the resumes
	 * @return true         set flash of success and errors
	 */
	private function sendArticleDecisionByCommType($articles = array(),$body = '', $comm = ''){

		//creation du message
		$message = Swift_Message::newInstance()
		  ->setSubject(Conf::$congressName)
		  ->setFrom(Conf::$congressContactEmail, Conf::getSiteUrl());
		  
		//boucle sur tous les résumé 
		$errors = array();
		foreach ($articles as $article) {
			
			//personnalise le contenu du mail
			$body = str_replace("{lastname}", $article->nom, $body);
			$body = str_replace("{title}", $article->title, $body);
			$message->setBody($body, 'text/html', 'utf-8');			
			$message->setTo(array($article->nom=>$article->email));			
			
			//Envoi du message et affichage des erreurs éventuelles
			if (!Conf::getMailer()->send($message, $failures)){
			   	$errors = array_merge($errors,$failures);
			}
		}


		$sended = count($articles) - count($errors);
		Session::setFlash($comm.' : '.$sended.' email sended ','success');
		
		if(!empty($errors)) 
			Session::setFlash($comm.' : '.count($errors).' errors :'.implode(' ; ',$errors));

		return true;

	}

}