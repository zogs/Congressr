<?php

class MailingController extends Controller {
	
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
				
				$results = array();
				$results['sended'] = array();
				$results['errors'] = array();
				foreach ($emails as $email) {
					
					if($this->send_freemailing($email,$title,$content,$path)){
						$results['sended'][] = $email;
					}
					else{
						$results['errors'][] = $email;
					}
				}

				if(!empty($results['sended'])){
					Session::setFlash(count($results['sended']).' emails envoyés ! ','success');
				}

				if(!empty($results['errors'])){
					Session::setFlash(count($results['errors']). ' erreurs d\'envoi... ('.implode(' ; ',$results['errors']).')');
				}
				
			}

		}

		$lists = $this->Mailing->findMailingList();
		$selectLists = array();
		foreach ($lists as $key => $l) {
			$selectLists[$l->list_id] = $l->name;
		}

		$this->set('selectLists',$selectLists);
	}

	private function send_freemailing($dest,$title,$content,$pj = null){		

		//Création d'une instance de swift mailer
		$mailer = Swift_Mailer::newInstance(Conf::getTransportSwiftMailer());

		//Récupère le template et remplace les variables
		$body = file_get_contents('../view/email/freeMailing.html');
		$body = preg_replace("~{content}~i", $content, $body);
		$body = preg_replace("~{congress}~i", Conf::$congressName, $body);
		$body = preg_replace("~{contact}~i", Conf::$congressContactEmail, $body);
		$body = preg_replace("~{title}~i", $title, $body);


		//Création du mail
		$message = Swift_Message::newInstance()
		 ->setSubject($title)
		 ->setFrom(Conf::$congressContactEmail,Conf::$congressName)
		 ->setTo($dest)
		 ->setBody($body, 'text/html', 'utf-8');

		  if(!empty($pj)){
		  	$pj = Swift_Attachment::FromPath($pj);
		  	$message->attach($pj);
		  }

		
		//Envoi du message et affichage des erreurs éventuelles
		if (!$mailer->send($message, $failures))
		{
		   return $dest;
		}
		else return true;

	}


	public function admin_resumes(){

		$this->loadModel('Articles');

		if($this->request->post()){

			$data = $this->request->post();			

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

		if($this->request->post()){

			$data = $this->request->post();			

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

		if($this->request->post()){

			$data = $this->request->post();			

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

				$this->mailArticleAcceptedOral($article);
				$this->mailArticleAcceptedPoster($article);

			}
		}

		Session::setFlash('Test mails sended');

		$this->redirect('admin/articles/mailing');
	}

	public function admin_sendArticleMailing(){

		$this->loadModel('Articles');

		$articles = $this->Articles->findDeposed(array('mailed'=>0));
		$articles = $this->Articles->JOIN('users','prenom,nom,email,lang',array('user_id'=>':user_id'),$articles);

		$signature = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$this->getLang(),'article'=>'signature')));
		$this->signature = $signature->content;

		$count = array();
		$count['refused'] = 0;
		$count['accepted'] = 0;
		$count['pending'] = 0;
		$count['reviewed'] = 0;
		$count['total'] = 0;
		$count['poster'] = 0;
		$count['oral'] = 0;
		$errors = array();

		foreach ($articles as $art) {
			
			if($art->status=='accepted'){				

				$count['accepted']++;
				if($art->comm_type=='poster'){

					$count['poster']++;
					if($email = $this->mailArticleAcceptedPoster($art)){
						$count['total']++;
						$this->Articles->setArticleMailed($art->id);
					}
					else $errors[] = array('poster'=>$email);
					continue;					
				}
				if($art->comm_type=='oral'){

					$count['oral']++;
					if($email = $this->mailArticleAcceptedOral($art)){
						$count['total']++;
						$this->Articles->setArticleMailed($art->id);
					}
					else $errors[] = array('oral'=>$email);
					continue;
				}
				
			}

			//else if pending or reviewed
			$count[$art->status]++;	
		}

		Session::setFlash($count['total'].' emails sended, '.$count['accepted'].' accepted, '.$count['refused']. 'refused, '.$count['oral'].' oral, '.$count['poster'].' poster');
		if(!empty($count['pending']) || !empty($count['reviewed'])) Session::setFlash('Not mailed : '.$count['pending'].' pending, '.$count['reviewed'].' reviewed','info');
		if(!empty($errors)) Session::setFlash(count($errors).' errors : '.implode(' - ',$errors),'danger');
		else Session::setFlash('<strong>Bon congrès à tous !</strong>','info');
		$this->redirect('admin/articles/mailing');
	}

	public function admin_sendResumeMailing(){

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

		if(!empty($refused))
			$this->mailingResume($refused,'refused');

		if(!empty($accepted['oral']))
			$this->mailingResume($accepted['oral'],'oral');

		if(!empty($accepted['poster']))
			$this->mailingResume($accepted['poster'],'poster');

		
		//Flash
		Session::setFlash(count($resumes). ' resumes, '.count($accepted['poster']).' poster, '.count($accepted['oral']).' oral, '.count($refused).' refused, '.count($pending).' still pending ,'.count($reviewed).' reviewed with no decision','info');		

		$this->redirect('admin/articles/mailing');
	}

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

				$this->mailingResume($resume,'refused');
				$this->mailingResume($resume,'poster');
				$this->mailingResume($resume,'oral');

			}
		}

		Session::setFlash('Test mails sended');

		$this->redirect('admin/articles/mailing');
	}

	private function mailingResume($resumes,$status){

		$this->loadModel('Articles');

		//find the appropriate content depending of the status
		$content = '';
		if($status=='refused'){
			$content = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$resume->lang,'article'=>'resume','result'=>'refused')));
			if(empty($content)) $content = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>Conf::$languageDefault,'article'=>'resume','result'=>'refused')));
			$content = $content->content;	
		}
		if($status=='poster'){
			$content = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$resume->lang,'article'=>'resume','result'=>'accepted','comm_type'=>'poster')));
			if(empty($content)) $content = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>Conf::$languageDefault,'article'=>'resume','result'=>'accepted','comm_type'=>'poster')));
			$content = $content->content;			
		}
		if($status=='oral'){
			$content = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$resume->lang,'article'=>'resume','result'=>'accepted','comm_type'=>'oral')));
			if(empty($content)) $content = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>Conf::$languageDefault,'article'=>'resume','result'=>'accepted','comm_type'=>'oral')));
			$content = $content->content;
		}

		//get the template of the mail
		$template = file_get_contents('../view/email/resumeMailing.html');

		//execute mailing
		$this->sendMailingResume($resumes,$content,$template,$status);


	}


	private function sendMailingResume($resumes = array(), $content = '', $template = '', $status = ''){

		$errors = array();
		foreach ($resumes as $resume) {
			
			if($fail = !$this->sendMailResume($resume,$content,$template)){
				$errors[] = $fail;
			}
		}

		$sended = count($resumes) - count($errors);
		Session::setFlash($status.' : '.$sended.' email sended ','success');
		
		if(!empty($errors)) 
			Session::setFlash($status.' : '.count($errors).' errors :'.implode(' ; ',$errors));

	}
	private function sendMailResume($resume,$content,$email){

		//Création d'une instance de swift mailer
		$mailer = Swift_Mailer::newInstance(Conf::getTransportSwiftMailer());

		//Récupère le template et remplace les variables
		$body = $email;
		$body = preg_replace("~{content}~i", $content, $body);
		$body = preg_replace("~{congress}~i", Conf::$congressName, $body);
		$body = preg_replace("~{lastname}~i", $resume->nom, $body);
		$body = preg_replace("~{title}~i", $resume->title, $body);
		$body = preg_replace("~{signature}~i", $this->signature, $body);

		//Création du mail
		$message = Swift_Message::newInstance()
		  ->setSubject(Conf::$congressName)
		  ->setFrom('contact@aic2014.com', 'http://www.aic2014.com')
		  ->setTo($resume->email, $resume->nom)
		  ->setBody($body, 'text/html', 'utf-8');

		
		//Envoi du message et affichage des erreurs éventuelles
		if (!$mailer->send($message, $failures))
		{
		   return $resume->email;
		}
		else return true;

	}




	private function mailArticleAcceptedPoster($article){

		$this->loadModel('Articles');
		$content = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$article->lang,'article'=>'deposed','result'=>'accepted','comm_type'=>'poster')));
		if(empty($content)) $content = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>Conf::$languageDefault,'article'=>'deposed','result'=>'accepted','comm_type'=>'poster')));
		$content = $content->content;

		//Création d'une instance de swift mailer
		$mailer = Swift_Mailer::newInstance(Conf::getTransportSwiftMailer());

		//Récupère le template et remplace les variables
		$body = file_get_contents('../view/email/articlesMailing.html');
		$body = preg_replace("~{content}~i", $content, $body);
		$body = preg_replace("~{congress}~i", Conf::$congressName, $body);
		$body = preg_replace("~{lastname}~i", $article->nom, $body);
		$body = preg_replace("~{title}~i", $article->title, $body);
		$body = preg_replace("~{signature}~i", $this->signature, $body);

		//Création du mail
		$message = Swift_Message::newInstance()
		  ->setSubject(Conf::$congressName)
		 ->setFrom('contact@aic2014.com', 'http://www.aic2014.com')
		  ->setTo($article->email, $article->nom)
		  ->setBody($body, 'text/html', 'utf-8');

		
		//Envoi du message et affichage des erreurs éventuelles
		if (!$mailer->send($message, $failures))
		{
		   return $article->email;
		}
		else return true;

	}

	private function mailArticleAcceptedOral($article){

		$this->loadModel('Articles');
		$content = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$article->lang,'article'=>'deposed','result'=>'accepted','comm_type'=>'oral')));
		if(empty($content)) $content = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>Conf::$languageDefault,'article'=>'deposed','result'=>'accepted','comm_type'=>'oral')));
		$content = $content->content;

		//Création d'une instance de swift mailer
		$mailer = Swift_Mailer::newInstance(Conf::getTransportSwiftMailer());

		//Récupère le template et remplace les variables
		$body = file_get_contents('../view/email/articlesMailing.html');
		$body = preg_replace("~{content}~i", $content, $body);
		$body = preg_replace("~{congress}~i", Conf::$congressName, $body);
		$body = preg_replace("~{lastname}~i", $article->nom, $body);
		$body = preg_replace("~{title}~i", $article->title, $body);
		$body = preg_replace("~{signature}~i", $this->signature, $body);

		//Création du mail
		$message = Swift_Message::newInstance()
		  ->setSubject(Conf::$congressName)
		  ->setFrom('contact@aic2014.com', 'http://www.aic2014.com')
		  ->setTo($article->email, $article->nom)
		  ->setBody($body, 'text/html', 'utf-8');

		
		//Envoi du message et affichage des erreurs éventuelles
		if (!$mailer->send($message, $failures))
		{
		   return $article->email;
		}
		else return true;

	}
}