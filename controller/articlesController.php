<?php 

class ArticlesController extends Controller {

	public function index( $type ){

		$this->loadModel('Articles');

		if($type=='resume'){

			$res = $this->Articles->findResumes();			
		}


	}

	public function admin_view($type,$id = null){

		$this->loadModel('Articles');
		
		if($this->request->post()){

			$data = $this->request->post();			
			if($data = $this->Articles->validates($data,$type)){

				if($id = $this->Articles->saveArticle($data,$type)){

					Session::setFlash("L'article a été modifié.","success");
					//$this->redirect('admin/articles/view/'.$type.'/'.$id);
				}
				else
					Session::setFlash("Error while saving article","error");
				
			}
				
		}

		if($id==null) $this->redirect('admin/articles/index/'.$type);

		$article = $this->Articles->findArticleTypeID($type,$id);
		$article->assigned = $this->Articles->findAssignment($id,$type);
		$article = $this->Articles->joinReview($article,$type);
			
		$authors = $this->Articles->findAuthors( $id, $type);
		$d['authors'] = $authors;
		$d['type'] = $type;
		$d[$type] = $article;
		$this->set($d);
	}

	public function admin_index($type){

		$this->loadModel('Articles');
		$this->loadModel('Users');
		
		//Auth
		if(!Session::user()->isChairman()) throw new zException("user have no chairman rights", 1);
		
		if($type=='resume'){

			$res = $this->Articles->findResumes();		
			$res = $this->Articles->joinAssignments($res,'resume');
			$res = $this->Articles->joinReviews($res,'resume');
			$res = $this->Articles->joinAuthors($res);
			$d['resumes'] = $res;

			if(empty($res)) Session::setFlash('Pas encore de résumés déposés','warning');

		}

		if($type=='deposed'){

			$res = $this->Articles->findDeposed();
			$res = $this->Articles->JOIN('resume','comm_type,title',array('id'=>':resume_id'),$res);
			$res = $this->Articles->joinAssignments($res,'deposed');
			$res = $this->Articles->joinReviews($res,'deposed');
			$res = $this->Articles->joinAuthors($res,'resume','resume_id');
			$d['deposed'] = $res;	

			if(empty($res)) Session::setFlash('Pas encore d\'articles déposés','warning');		
		}
		
		$d['type'] = $type;
		$this->set($d);
	}

	public function admin_assign($type,$id){

		$this->loadModel('Articles');
		$this->loadModel('Users');
		
		//Auth
		if(!Session::user()->isChairman()) throw new zException("user have no chairman rights", 1);
		
		//Form
		if($this->request->post()){

			$data = $this->request->post();

			if($this->Articles->saveAssignment($type,$id,$data->reviewer)){

				Session::setFlash("<strong>Successfull attributing to reviewer !</strong>","success");

	
				$user = $this->Users->findFirst(array('conditions'=>array('user_id'=>$data->reviewer)));
				$article = $this->Articles->findArticleTypeID($type,$id);

				if($this->sendMailReviewRequest($user->login,$user->email,$user->lang,$id,$article->title,$type)){

					Session::setFlash("An request email have been sended to the reviewer");
				}
				else {
					Session::setFlash('The request email could not been sended. Please do it manually at '.$user->email,'danger');
				}
			}
			else {
				Session::setFlash("Assignment error","danger");
			}
			
		}

		$this->redirect('admin/articles/view/'.$type.'/'.$id);

	}

	public function admin_decision($type,$id){

		$this->loadModel('Articles');
		$this->loadModel('Users');
		
		//Auth
		if(!Session::user()->isChairman()) throw new zException("user have no chairman rights", 1);

		if($data = $this->request->post()){

			if($this->Articles->updateArticleStatus($id,$type,$data->decision)){

				Session::setFlash("<strong>Marked as ".$data->decision.'</strong>','success');

			}

			if($comm = $this->Articles->updatedArticleCommunication($id,$type)){
				Session::setFlash("<strong>Communication as ".$comm.'</strong>','success');
			}
		}

		$this->redirect('admin/articles/view/'.$type.'/'.$id);
	}

	public function deposit($resume_id){

		//secu
		if(!Session::user()->isLog()) {
			Session::setFlash('Vous devez vous connecter pour déposer un article');
			$this->redirect('users/login');
		}
		if(!is_numeric($resume_id)) throw new Exception("$resume_id must be a numeric value", 1);
		

		$this->loadModel('Articles');

		if(!empty($resume_id)){
			$resume = $this->Articles->findFirst(array('table'=>'resume','conditions'=>array('id'=>$resume_id)));
			$resume = new Resume($resume);

			$resume->authors = $this->Articles->findAuthors( $resume_id, 'resume');

			$deposed = $this->Articles->findDeposedByResumeId($resume->id);

		}
		else {
			$resume = new Resume();	
			$deposed = new Deposed();		
		}
		

		if($data = $this->request->post()){

			if($this->Articles->validates($data,'deposit')){
				
				$filename = strtoupper(String::slugify($resume->authors[0]->lastname)).'_'.substr(String::slugify($resume->title),0,50);

				if($destination = $this->Articles->saveFile('deposed',$filename)){	

					$s = new stdClass();
					$s->table = 'deposed';
					$s->resume_id = $resume->id;
					$s->user_id = Session::user()->getID();
					$s->title = $resume->title;
					$s->status = 'pending';
					$s->filename = $filename;
					$s->filepath = str_replace('\\','/',$destination); //use / instead of \ because its will be used as URL

					//update if exist
					$exist = $this->Articles->findFirst(array('table'=>'deposed','fields'=>'id','conditions'=>array('resume_id'=>$this->request->post('resume_id'))));
					if(!empty($exist)){
						//set id for update
						$s->key = 'id';
						$s->id = $exist->id;

						//send a request directly to the reviewers
						//find reviewers
						$assigned = $this->Articles->findAssignmentByArticle($resume->id,'deposed');
						$reviewers = $this->Articles->JOIN('users','login,email,lang',array('user_id'=>':user_id'),$assigned);
						$sended = array();
						$errors = array();
						foreach ($reviewers as $r) {
							
							if($this->sendMailReviewRequest($r->login,$r->email,$r->lang,$resume->id,$resume->title,'deposed')){
								$sended[] = $r->login;
							}
							else{
								$errors[] = $r->login;
							}
						}						
						if(!empty($sended)) Session::setFlash('Degug : demande de reviewing envoyé à '.implode(',',$sended));
						if(!empty($errors)) Session::setFlash('Debug : Echec de l\'envoi pour : '.implode(',',$errors),'error');

					}

					if($this->Articles->save($s)){
						Session::setFlash("Votre document a été enregistré ! Vous recevrez un email quand il aura été évalué par le comité scientifique");
		
					}

				}
			}

		}


		$d['resume'] = $resume;
		$d['deposed'] = $deposed;
		$this->set($d);
	}

	public function extended($resume_id){
		
		$this->loadModel('Articles');
		$this->loadJS = 'js/jquery/tiny_mce/tiny_mce.js';

		if(!Session::user()->canSeeResume())  throw new zException("user can not see reume", 1);

		if($data = $this->request->post()){

			if($resume_id = $this->Articles->saveExtended($data)){

				Session::setFlash("Merci, votre résumé étendu a bien été enregistré ! ","success");
				//$this->redirect('articles/extended/'.$resume_id);			
			}
			else
				Session::setFlash("Error while saving resume","error");
		}

		if(!empty($resume_id)){
			$resume = $this->Articles->findFirst(array('table'=>'resume','conditions'=>array('id'=>$resume_id)));
			$resume = new Resume($resume);
			$extended = $this->Articles->findFirst(array('table'=>'extended','conditions'=>array('resume_id'=>$resume_id)));
			$extended = new Extended($extended);
			$figures = $this->Articles->find(array('table'=>'figures','conditions'=>array('resume_id'=>$resume_id),'order'=>'number ASC'));
			foreach ($figures as $key => $figure) {
				$figures[$key] = new Figure($figure);
			}						
		}
		else {
			$resume = new Resume();
			$extended = new Extended();
			
		}

		if(empty($figures)) $figures = array(new Figure());

		$d['resume'] = $resume;
		$d['extended'] = $extended;
		$d['figures'] = $figures;
		$this->set($d);
	}

	public function resume( $id = null ){

		$this->loadModel('Articles');

		if(!Session::user()->canSeeResume()) throw new zException("user can not see reume", 1);
		

		if($this->request->post()){

			$data = $this->request->post();
			
			if($data = $this->Articles->validates($data,'resume')){

				if($id = $this->Articles->saveResume($data)){

					Session::setFlash("Merci, votre résumé a bien été enregistré ! Vous serez averti par email dès qu'il aura été accepté ou refusé","success");
					$this->redirect('articles/resume/'.$id);
				}
				else
					Session::setFlash("Error while saving resume","error");
				
			}
				
		}

		if($id != null){

			$resume = $this->Articles->findFirst(array('table'=>'resume','conditions'=>array('id'=>$id)));
			$resume = new Resume ( $resume );

			$authors = $this->Articles->findAuthors( $id, 'resume');
			
		} else {
			$resume = new Resume();
			$authors[] = new Author();
		}
		
		$this->set('resume',$resume);
		$this->set('authors',$authors);
	}



	private function sendMailReviewRequest($userLogin,$userEmail,$userLang,$articleId,$articleTitle,$type){


		$link = Conf::getSiteUrl().'/reviewer/review/'.$type.'/'.$articleId.'?lang='.$userLang;

		//Création d'une instance de swift mailer
		$mailer = Swift_Mailer::newInstance(Conf::getTransportSwiftMailer());

		//Récupère le template et remplace les variables
		$body = file_get_contents('../view/email/reviewRequest.html');
		$body = preg_replace("~{site}~i", Conf::$website, $body);
		$body = preg_replace("~{login}~i", $userLogin, $body);
		$body = preg_replace("~{articleName}~i", $articleTitle, $body);
		$body = preg_replace("~{link}~i", $link, $body);

		//Création du mail
		$message = Swift_Message::newInstance()
		  ->setSubject(Conf::$congressName)
		 ->setFrom('contact@aic2014.com', 'http://www.aic2014.com')
		  ->setTo($userEmail, $userLogin)
		  ->setBody($body, 'text/html', 'utf-8');

		//Envoi du message et affichage des erreurs éventuelles
		if (!$mailer->send($message, $failures))
		{
		    echo "Erreur lors de l'envoi du email à :";
		    print_r($failures);
		}
		else return true;



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

				$signature = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$lang->lang,'article'=>'signature')));
				$this->signature = $signature->content;

				$this->mailAcceptedOral($resume);
				$this->mailAcceptedPoster($resume);
				$this->mailRefusedResume($resume);

			}
		}

		Session::setFlash('Test mails sended');

		$this->redirect('admin/articles/mailing');
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
		
		$count = array();
		$count['refused'] = 0;
		$count['accepted'] = 0;
		$count['pending'] = 0;
		$count['reviewed'] = 0;
		$count['total'] = 0;
		$count['poster'] = 0;
		$count['oral'] = 0;
		$errors = array();
		
		foreach ($resumes as $resume) {
			

			if($resume->status=='refused') {
				$res = $this->mailRefusedResume($resume);
				if(true===$res) {
					$count['refused']++;
					$count['total']++;
					$this->Articles->setResumeMailed($resume->id);
				}
				else $errors[] = array('refused'=>$res);
				continue;				
			}

			if($resume->status=='accepted') {

				$count['accepted']++;

				if($resume->comm_type=='poster'){

					$res = $this->mailAcceptedPoster($resume);
					if(true===$res) {

						$count['poster']++;
						$count['total']++;
						$this->Articles->setResumeMailed($resume->id);
					}
					else $errors[] = array('poster'=>$res);
					continue;
				}
				if($resume->comm_type=='oral'){

					$res = $this->mailAcceptedOral($resume);
					if(true===$res) {
						$count['oral']++;
						$count['total']++;
						$this->Articles->setResumeMailed($resume->id);
					}
					else $errors[] = array('oral'=>$res);
					continue;				
				}
			}

			//else if pending or reviewed
			$count[$resume->status]++;	
		}

		
		Session::setFlash($count['total'].' emails sended, '.$count['accepted'].' accepted, '.$count['refused']. 'refused');

		if(!empty($count['pending']) || !empty($count['reviewed'])) Session::setFlash('Not mailed : '.$count['pending'].' pending, '.$count['reviewed'].' reviewed','info');

		if(!empty($errors)) Session::setFlash(count($errors).' errors : '.implode(' - ',$errors),'danger');

		$this->redirect('admin/articles/mailing');
	}

	private function mailRefusedResume($resume){

		$this->loadModel('Articles');
		$content = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$resume->lang,'article'=>'resume','result'=>'refused')));
		if(empty($content)) $content = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>Conf::$languageDefault,'article'=>'resume','result'=>'refused')));
		$content = $content->content;

		//Création d'une instance de swift mailer
		$mailer = Swift_Mailer::newInstance(Conf::getTransportSwiftMailer());

		//Récupère le template et remplace les variables
		$body = file_get_contents('../view/email/resumeMailing.html');
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

	private function mailAcceptedPoster($resume){

		$this->loadModel('Articles');
		$content = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$resume->lang,'article'=>'resume','result'=>'accepted','comm_type'=>'poster')));
		if(empty($content)) $content = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>Conf::$languageDefault,'article'=>'resume','result'=>'accepted','comm_type'=>'poster')));
		$content = $content->content;

		//Création d'une instance de swift mailer
		$mailer = Swift_Mailer::newInstance(Conf::getTransportSwiftMailer());

		//Récupère le template et remplace les variables
		$body = file_get_contents('../view/email/resumeMailing.html');
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

	private function mailAcceptedOral($resume){

		$this->loadModel('Articles');
		$content = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>$resume->lang,'article'=>'resume','result'=>'accepted','comm_type'=>'oral')));
		if(empty($content)) $content = $this->Articles->findFirst(array('table'=>'mailing','conditions'=>array('lang'=>Conf::$languageDefault,'article'=>'resume','result'=>'accepted','comm_type'=>'oral')));
		$content = $content->content;

		//Création d'une instance de swift mailer
		$mailer = Swift_Mailer::newInstance(Conf::getTransportSwiftMailer());

		//Récupère le template et remplace les variables
		$body = file_get_contents('../view/email/resumeMailing.html');
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




?>