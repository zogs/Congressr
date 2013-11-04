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

	public function deposit($resume_id = null){

		$this->loadModel('Articles');


		//security
		if(!Session::user()->isLog()) {
			Session::setFlash('<strong>Votre session a expiré. Veuillez vous <strong>reconnecter</strong>...');
			$this->redirect('users/login');
		}
		if(!is_numeric($resume_id)) $this->e404("Cet article n'existe pas");
		if($resume_id){
			$resume = $this->Articles->findArticleTypeID('resume',$resume_id);
			if(!Session::user()->canSeeResume($resume->user_id)){
				Session::setFlash("Désolé... Vous ne pouvez accéder à cet article.",'error');
				$this->redirect('articles/resume');
			}
		}
		


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

					if($id = $this->Articles->save($s)){

						$this->loadModel('Users');
						$user = $this->Users->findFirstUser(array('conditions'=>array('user_id'=>$data->user_id)));		
						$this->sendMailArticleSuccefullyDeposed($user->getLogin(),$user->getEmail(),'deposed',$id,$data->title);
						Session::setFlash("Votre document a été enregistré ! Vous recevrez un email quand il aura été évalué par le comité scientifique");
		
					}

				}
			}

		}


		$d['resume'] = $resume;
		$d['deposed'] = $deposed;
		$this->set($d);
	}

	public function resume( $id = null ){

		$this->loadModel('Articles');
		$this->loadModel('Users');

		//security
		if(!Session::user()->isLog()){
			Session::setFlash('<strong>Votre session a expiré. Veuillez vous <strong>reconnecter</strong>...');
			$this->redirect('users/login');
		}
		if($id){
			$resume = $this->Articles->findArticleTypeID('resume',$id);
			if(!Session::user()->canSeeResume($resume->user_id)){
				Session::setFlash("Vous ne pouvez accéder à cet article.",'error');
				$this->redirect('articles/resume');
			}
		}		
		

		if($this->request->post()){

			$data = $this->request->post();
			
			if($data = $this->Articles->validates($data,'resume')){

				if($id = $this->Articles->saveResume($data)){

					Session::setFlash("Merci, votre résumé <strong>a bien été enregistré !</strong> Vous serez averti par email dès qu'il aura été accepté ou refusé","success");

					$user = $this->Users->findFirstUser(array('conditions'=>array('user_id'=>$data->user_id)));					
					$this->sendMailArticleSuccefullyDeposed($user->getLogin(),$user->getEmail(),'resume',$id,$data->title);

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


	private function sendMailArticleSuccefullyDeposed($userLogin,$userEmail,$articleType,$articleId,$articleTitle){

		
		$subject = 'Votre résumé a été déposé !';
		$body = file_get_contents('../view/email/confirmResumeDeposed.html');
		$link = Conf::getSiteUrl().'/articles/resume/'.$articleId;
		
		if($articleType=='deposed'){
			$subject = 'Votre article étendu a été déposé !';
			$body = file_get_contents('../view/email/confirmArticleDeposed.html');
			$link = Conf::getSiteUrl().'/articles/deposed/'.$articleId;

		}

		$mailer = Swift_Mailer::newInstance(Conf::getTransportSwiftMailer());

		$body = preg_replace("~{subject}~i", $subject, $body);
		$body = preg_replace("~{articleTitle}~i", $articleTitle, $body);
		$body = preg_replace("~{userLogin}~i", $userLogin, $body);
		$body = preg_replace("~{link}~i", $link, $body);
		$body = preg_replace("~{website}~i", Conf::getsiteUrl(), $body);


		$message = Swift_Message::newInstance()
		  ->setSubject($subject.' - '.Conf::$congressName)
		 ->setFrom('contact@aic2014.com', 'http://www.aic2014.com')
		  ->setTo($userEmail, $userLogin)
		  ->setBody($body, 'text/html', 'utf-8');

		  if (!$mailer->send($message, $failures))
		{
		    echo "Erreur lors de l'envoi du email à :";
		    print_r($failures);
		}
		else return true;
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

?>