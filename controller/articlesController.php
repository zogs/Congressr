<?php 

class articlesController extends Controller {

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
			if($data = $this->Articles->validates($data,'resume')){

				if($id = $this->Articles->saveResume($data)){

					Session::setFlash("Le résumé a été modifié.","success");
					$this->redirect('admin/articles/view/resume/'.$id);
				}
				else
					Session::setFlash("Error while saving resume","error");
				
			}
				
		}

		if($id==null) $this->redirect('admin/articles/index/resume');

		$article = $this->Articles->findArticleTypeID($type,$id);
		$authors = $this->Articles->findAuthors( $id, $type);
		$d['authors'] = $authors;
		$d[$type] = $article;
		$this->set($d);
	}

	public function admin_index($type){

		$this->loadModel('Articles');
		$this->loadModel('Users');
		

		//Auth
		if(!Session::user()->isChairman()) throw new zException("user have no chairman rights", 1);
		
		//Form
		if($this->request->post()){

			$data = $this->request->post();

			if(isset($data->assignResume)){

				if($this->admin_assign('resume',$data->id,$data->reviewer)){

					Session::setFlash("<strong>Successfull attributing to reviewer !</strong>","success");

					$d['rowAffected'] = $data->id;
					$d['rowClass'] = 'success';
		
					$user = $this->Users->findFirst(array('conditions'=>array('user_id'=>$data->reviewer)));
					$article = $this->Articles->findArticleTypeID('resume',$data->id);

					if($this->sendMailReviewRequest($user,$article,'resume')){

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
		}



		if($type=='resume'){

			$res = $this->Articles->findResumes();
			$res = $this->Articles->joinAssignment($res);
			$res = $this->Articles->joinReviews($res,'resume');
		}

		if($type=='article'){

			$res = $this->Articles->findArticles();
		}

		$d['articles'] = $res;
		$d['type'] = $type;
		$this->set($d);
	}

	private function admin_assign($type,$id,$reviewer_id){

		$this->loadModel('Articles');
		
		$n = new stdClass();
		$n->table = 'assignment';
		$n->type = $type;
		$n->user_id = $reviewer_id;
		$n->article_id = $id;
		$n->date = Date::MysqlNow();

		if($this->Articles->save($n)){

			$this->Articles->updateArticleStatus($id,$type,'pending');

			return true;
			
		}
		else return false;
	}

	public function resume( $id = null ){

		$this->loadModel('Articles');

		if(!Session::user()->canSeeResume()) throw new zException("user can not see reume", 1);
		

		if($this->request->post()){

			$data = $this->request->post();
			
			if($data = $this->Articles->validates($data,'resume')){

				if($id = $this->Articles->saveResume($data)){

					Session::setFlash("Votre résumé a été enregistré.","success");
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



	private function sendMailReviewRequest($user,$article,$type){


		$link = 'http://localhost/congressr/reviewer/review/'.$type.'/'.$article->id.'?lang='.$user->lang;

		//Création d'une instance de swift mailer
		$mailer = Swift_Mailer::newInstance(Conf::getTransportSwiftMailer());

		//Récupère le template et remplace les variables
		$body = file_get_contents('../view/email/reviewRequest.html');
		$body = preg_replace("~{site}~i", Conf::$website, $body);
		$body = preg_replace("~{login}~i", $user->login, $body);
		$body = preg_replace("~{articleName}~i", $article->title, $body);
		$body = preg_replace("~{link}~i", $link, $body);

		//Création du mail
		$message = Swift_Message::newInstance()
		  ->setSubject(Conf::$congressName)
		  ->setFrom('noreply@'.Conf::$websiteDOT, Conf::$website)
		  ->setTo($user->email, $user->login)
		  ->setBody($body, 'text/html', 'utf-8');

		//Envoi du message et affichage des erreurs éventuelles
		if (!$mailer->send($message, $failures))
		{
		    echo "Erreur lors de l'envoi du email à :";
		    print_r($failures);
		}
		else return true;



	}
} 




?>