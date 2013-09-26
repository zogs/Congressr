<?php 

class RedactorController extends usersController {

	public function board(){

		if(Session::user()->getRole()!='redactor' && Session::user()->getRole()!='reviewer') $this->redirect('users/login');

		$this->loadModel('Articles');

		$res = $this->Articles->findResumes(array('conditions'=>array('user_id'=>Session::user()->getID())));

		if(empty($res)) Session::setFlash("Vous n'avez pas encore déposé de résumés... <a href='".Router::url('articles/resume')."'>Déposer un résumé</a>",'warning');

		$d['resumes'] = $res;
		$this->set($d);

	}

	public function resume( $id = null ){

		$this->loadModel('Articles');

		if($this->request->post()){

			$data = $this->request->post();
			
			if($data = $this->Articles->validates($data,'resume')){

				if($id = $this->Articles->saveResume($data)){

					Session::setFlash("Votre résumé a été enregistré.","success");
					$this->redirect('redactor/board');
				}
				else
					Session::setFlash("Error while saving resume","error");
				
			}
				
		}

		if($id != null){

			$resume = $this->Articles->findFirst(array('table'=>'resume','conditions'=>array('id'=>$id)));
			$resume = new Resume ( $resume );

			$authors = $this->Articles->find(array('table'=>'authors','conditions'=>array('id_article'=>$id,'type'=>'resume')));
			$authors = $this->Articles->JOIN('author','*',array('id'=>':id_author'),$authors);
			foreach ($authors as $key => $author) {
				
				$authors[$key] = new Author( $author );
			}

			
		} else {
			$resume = new Resume();
			$authors[] = new Author();
		}
		
		$this->set('resume',$resume);
		$this->set('authors',$authors);
	}

} 




?>