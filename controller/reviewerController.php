<?php 

class ReviewerController extends usersController {


	public function board(){

		$this->loadModel('Articles');

		//secu
		if(!Session::user()->canReview()) {

			$this->redirect('users/login');
		}
		//list to review
		$resumes = $this->Articles->findAssignmentByUser(Session::user()->getID(),'resume');
		//$extended = $this->Articles->findAssignmentByUser(Session::user()->getID(),'extended');
		$deposed = $this->Articles->findAssignmentByUser(Session::user()->getID(),'deposed');

		foreach ($resumes as $key => $resume) {
		 	
		 	$r = $this->Articles->findResumes(array('conditions'=>array('id'=>$resume->article_id)));
		 	if(!empty($r)) $resumes[$key] = $r[0];
		 } 
		// foreach ($extended as $key => $a) {
		 	
		// 	$r = $this->Articles->findExtended(array('conditions'=>array('id'=>$a->article_id)));
		// 	if(!empty($r)) $extended[$key] = $r[0];
		 //} 
		 foreach ($deposed as $key => $a) {
		 	
		 	$r = $this->Articles->findArticleTypeID('deposed',$a->article_id);
		 	if(!empty($r)) $deposed[$key] = $r;
		 } 

		 $resumes =  $this->Articles->joinReviews($resumes,'resume');
		 //$extended =  $this->Articles->joinReviews($extended,'extended');
		 $deposed = $this->Articles->joinReviews($deposed,'deposed');

		 $d['resumes'] = $resumes;
		// $d['extended'] = $extended;
		 $d['deposed'] = $deposed;

		 $this->set($d);

	}

	public function review($type,$id){

		$this->loadModel('Articles');
		$this->loadJS = array('js/jquery/tiny_mce/tiny_mce.js');

		//secu
		if(!Session::user()->isLog()) {
			Session::setFlash('Veuillez vous connecter pour pouvoir reviewer un article !');
			$this->redirect('users/login');
		}
		if(!Session::user()->canReview()) $this->e404('You can not review an article');
		if(!is_numeric($id)) throw new zException("Resume id is not numeric", 1);
		//if(!$this->Articles->ifReviewerIsAssign($id,Session::user()->getID(),$type )) $this->e404('You are not assign to this article');
		
		//find article
		$article = $this->Articles->findArticleTypeID($type,$id);
		
		//save review
		if($this->request->post('review')){

			if($this->Articles->saveReview($id,Session::user()->getID(),$type,$this->request->post())){

				Session::setFlash("<strong>Merci d'avoir évalué cet article !</strong> <a href='".Router::url('reviewer/board')."''>Vous pouvez retourner à la liste des articles</a>",'success');

				//update article status			
				if($this->Articles->updateArticleStatus($id,$type,'reviewed')){
					Session::setFlash("DEBUG: L'article a été marqué comme reviewé",'info');
				}else {
					Session::setFlash("ERROR: L'article na PAS été marqué comme reviewé",'error');
				}					

			}
			else {
				Session::setFlash('Error','danger');
			}
		}
		

		//if extended join resume
		if($type=='extended') {
			$article = $this->Articles->JOIN('resume','title,user_id',array('id'=>':resume_id'),$article);
			$article = $this->Articles->joinFigures($article);
		}
		if($type=='deposed'){
			$article = $this->Articles->JOIN('resume','title,user_id',array('id'=>':resume_id'),$article);
		}
		//join review
		$article = $this->Articles->joinReview($article,$type);

		//find authors
		$authors = $this->Articles->findAuthors( $id, $type);

		$d['authors'] = $authors;
		$d[$type] = $article;
		$d['type'] = $type;

		$this->set($d);
	}

	public function index(){

		$this->loadModel('Users');

		$res = $this->Users->find(array('conditions'=>array('role'=>'reviewer')));

		return $res;
	}

	public function admin_create(){

		$this->loadModel('Users');
		

		if($this->request->post()){

			$user = $this->request->post();
			$mail = clone $user;
			//$password = $user->password;

			//create login from name
			$user->login = String::slugify(substr($user->prenom,0,1).substr(str_replace(array("-","'"," "),'',$user->nom),0,10));
			$user->password = $user->login.rand(100,999);
			$user->confirm = $user->password;
			$password = $user->password;


			unset($user->mailcontent);

			if($id = $this->createUser($user)){

				Session::setFlash("Reviewer have been created","success");

				$user = $this->Users->findFirst(array('conditions'=>array('user_id'=>$id)));

				$subject = "AIC Dijon 2014 : Invitation à participation au Comité Scientifique ";

				if($this->mail_invitation( $user, $password, $mail->mailcontent, $subject )){

					Session::setFlash("Your invitation have been sended to ".$mail->email);
				}
				else {
					Session::setFlash("Warning, the mail have not been sended to ".$mail->email.' due to unkowned error','danger');
				}

			}			
		}
	}

	private function mail_invitation( $user, $password, $content, $subject ) {

		$mailer = Swift_Mailer::newInstance(Conf::getTransportSwiftMailer());

		$link = Conf::getSiteUrl()."/users/validate/?c=".urlencode($user->codeactiv)."&u=".urlencode($user->user_id);
		$link =  '<a href="'.$link.'" target="_blank">'.$link.'</a>';
	
		//insére le lien de validation
		$content = preg_replace("~{link}~i",$link,$content);
		$content = preg_replace("~{login}~i",$user->login,$content);
		$content = preg_replace("~{password}~i",$password,$content);
		
		


		$message = Swift_Message::newInstance()
		->setSubject($subject)
		->setFrom('contact@aic2014.com', 'http://www.aic2014.com')
		->setTo($user->email)
		->setBody($content, 'text/html', 'utf-8');

		if(!$mailer->send($message, $failures)){

			debug($failures);
		}
		else
			return true;
		

	}

	public function requestChange($type,$id){

		$this->view='reviewer/review';
		$this->loadModel('Users');
		$this->loadModel('Articles');

		if($type!='deposed') throw new 	zException('$type must be "deposed"', 1);
		
		$article = $this->Articles->findArticleTypeID($type,$id);
		$resume = $this->Articles->findArticleTypeID('resume',$article->resume_id);
		$author = $this->Users->findFirst(array('conditions'=>array('user_id'=>$resume->user_id)));
		$reviewer = $this->Users->findFirst(array('conditions'=>array('user_id'=>Session::user()->getID())));


		if($post = $this->request->post()){

			if(!empty($post->textEmail)){

				$subject = Conf::$congressName.' - Demande de modification : '.$resume->title;
				$content = $post->textEmail;

				if($this->mail_request_change($subject,$content,$author->email,$author->prenom.' '.$author->nom,$reviewer->email,$article->resume_id)){

					Session::setFlash("La demande de modification a été envoyé à l'auteur ! Vous serez averti par email quand une nouvelle version de l'article sera disponible");

					$this->Articles->updateArticleStatus($article->id,'deposed','pending');
				}
				else {
					Session::setFlash('Echec de l\'envoi du mail','error');
				}
			}
			else {
				Session::setFlash('La demande doit être rédigé par vos soins','error');
			}
		}

		$this->redirect('reviewer/review/deposed/'.$article->resume_id);


	}

	private function mail_request_change($subject,$content,$authorEmail,$authorName,$reviewerEmail,$articleID){

		$mailer = Swift_Mailer::newInstance(Conf::getTransportSwiftMailer());

		$lien = Conf::getSiteUrl().'/articles/deposit/'.$articleID;

		$body = file_get_contents('../view/email/requestArticleChange.html');
		$body = preg_replace("~{content}~i", $content, $body);
		$body = preg_replace("~{authorName}~i", $authorName, $body);
		$body = preg_replace("~{website}~i", Conf::getSiteUrl(), $body);
		$body = preg_replace("~{link}~i", $lien, $body);	

		$message = Swift_Message::newInstance()
		->setSubject($subject)
		->setFrom('contact@aic2014.com', 'http://www.aic2014.com')
		->setTo($authorEmail)
		->setBody($body,'text/html','utf-8');

		if(!$mailer->send($message,$failures)){

			debug(	$failures);
		}
		else{
			return true;
		}
	}


} 




?>