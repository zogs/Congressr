<?php

class PostsController extends Controller {



		function index(){
			$perPage = 2;
			$this->loadModel('Posts');
			$conditions = array('online'=>1,'type'=>'post');			
			$d['posts'] = $this->Posts->find(array(
				'conditions'=>array('online'=>1,'type'=>'post'),
				'limit'=>(($this->request->page-1)*$perPage).','.$perPage
			));
			$d['total'] = $this->Posts->findCount($conditions);
			$d['nbpage'] = ceil($d['total']/$perPage);
			$this->set($d);

		}


		//===================
		// Permet de rentre une page
		// $param $id id du post dans la bdd
		function view($id,$slug){

				//On charge le model
				$this->loadModel('Posts');
				//On creer un tableaux des conditions sql
				$conditions = array('id'=>$id,'online'=>1,'type'=>'post');
				//On utlise la methode findFirst du model
				$d['post'] = $this->Posts->findFirst(array(
					'fields'    => 'id,slug,content,name',
					'conditions'=>$conditions//En envoyant les parametres
					));

				//Si le resultat est vide on dirige sur la page 404
				if(empty($d['post'])){
					$this->e404('Page introuvable');
				}
				//Si le slug n'est pas le bon on redirige vers la bonne url
				if($slug!=$d['post']->slug){
					$this->redirect("posts/view/id:$id/slug:".$d['post']->slug,301);
				}
				//Atttribution de l'objet $page a une variable page
				$this->set($d);

				
		}
		function admin_edit($id = null){
			$this->loadModel('Posts');

			$d['id'] ='';
			if($this->request->data){
				if($this->Posts->validates($this->request->data)){
					$this->request->data->type = 'post';
					$this->request->data->created = date('Y-m-d h:i:s');
					$this->Posts->save($this->request->data);
					$id = $this->Posts->id; //retourne l'id de l'update
					Session::setFlash('Le contenu a bien été modifié');
					$this->redirect('admin/posts/index');
				}
				else{
					Session::setFlash('Toi pas bien rentrer données!','error');
				}				

			}	
			else {
				if($id){			
					$this->request->data = $this->Posts->findFirst(array(
						'conditions' => array('id' => $id)
						));
					$d['id'] = $id; 
				}

			}		
			

			$this->set($d);




		}
		function admin_index(){
			$perPage = 10;
			$this->loadModel('Posts');
			$conditions = array('type'=>'post');
			$d['posts'] = $this->Posts->find(array(
				'fields'     => 'id,name,online',
				'conditions' =>array('type'=>'post'),
				'limit'      =>(($this->request->page-1)*$perPage).','.$perPage
			));
			$d['total'] = $this->Posts->findCount($conditions);
			$d['nbpage'] = ceil($d['total']/$perPage);
			$this->set($d);
		}

		function admin_delete($id){

			$this->loadModel('Posts');
			if($this->Posts->delete($id)){
				Session::setFlash('Le contenu a bien été supprimé');	
			}
			else {
				Session::setFlash('Erreur dans la suppression','error');
			}
			
			$this->redirect('admin/posts/index');


		}
}

?>