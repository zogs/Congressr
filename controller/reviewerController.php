<?php 

class reviewerController extends redactorController {


	public function board(){

		$this->loadModel('Articles');

		//secu
		if(!Session::user()->canReview()) {

			$this->redirect('users/login');
		}
		//list to review
		$resumes = $this->Articles->findAssignmentByUser(Session::user()->getID(),'resume');

		foreach ($resumes as $key => $resume) {
		 	
		 	$r = $this->Articles->findResumes(array('conditions'=>array('id'=>$resume->article_id)));
		 	$resumes[$key] = $r[0];
		 } 

		 $resumes =  $this->Articles->joinReviews($resumes,'resume');
	
		 
		 $d['resumes'] = $resumes;

		 $this->set($d);

	}

	public function review($type,$id){

		$this->loadModel('Articles');

		//secu
		if(!Session::user()->canReview()) $this->e404('You can not review an article');
		if(!is_numeric($id)) throw new zException("Resume id is not numeric", 1);
		if(!$this->Articles->ifReviewerIsAssign($id,Session::user()->getID(),$type )) $this->e404('You are not assign to this article');
		
		//save review
		if($this->request->post('review')){

			if($this->Articles->saveReview($id,Session::user()->getID(),$type,$this->request->post())){

				Session::setFlash('<strong>Article have been successfuly reviewed !</strong>','success');
			}
			else {
				Session::setFlash('Error','danger');
			}
		}
		
		//find resume
		$article = $this->Articles->findResumes(array('conditions'=>array('id'=>$id)));

		//join review
		$article = $this->Articles->joinReviews($article,$type);
		$article = $article[0];
			
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

			unset($user->mailcontent);

			if($id = $this->create($user)){

				Session::setFlash("Reviewer have been created","success");

				$user = $this->Users->findFirst(array('conditions'=>array('user_id'=>$id)));

				if($this->mail_invitation( $user, 'Invitation for reviewer', $mail->mailcontent )){

					Session::setFlash("Your invitation have been sended to ".$mail->email);
				}
				else {
					Session::setFlash("Warning, the mail have not been sended to ".$mail->email.' due to unkowned error','danger');
				}

			}			
		}
	}

	private function mail_invitation( $user, $subject, $content ) {

		$mailer = Swift_Mailer::newInstance(Conf::getTransportSwiftMailer());

		$link = Conf::$websiteURL."/users/validate?c=".urlencode($user->codeactiv)."&u=".urlencode($user->user_id);
		$link =  '<a href="'.$link.'" target="_blank">'.$link.'</a>';
	
		//insére le lien de validation
		$content = preg_replace("~{link}~i",$link,$content);
		//insére le lien de validation en bas du mail
		$content .= '<p><small>N\'oubliez pas de cliquer sur le lien d\'activation : '.$link.'<br />Remeber to click to activation link: '.$link.'</small></p>';


		$message = Swift_Message::newInstance()
		->setSubject($subject)
		->setFrom('noreply@'.Conf::$websiteDOT, Conf::$website)
		->setTo($user->email)
		->setBody($content, 'text/html', 'utf-8');

		if(!$mailer->send($message, $failures)){

			debug($failures);
		}
		else
			return true;
		

	}

} 




?>