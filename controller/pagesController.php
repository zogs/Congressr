<?php

class PagesController extends Controller {


		public function home(){

			$this->redirect('pages/view/4');
		}
	
		//===================
		// Permet de rentre une page
		// $param $id id du post dans la bdd
		public function view($id){

				//On charge le model
				$this->loadModel('Contents');
					
				//On cherche la page		
				$page = $this->Contents->findFirst(array('conditions'=>array('id'=>$id,'type'=>'page','online'=>1)));

				//Si la page n'existe pas on redirige sur 404
				if(empty($page)){
					$this->e404('Page introuvable');
				}

				//On cherche le contenu
				$page = $this->Contents->i18n($page, array('lang'=>$this->getLang(),'valid'=>1));
				//Si la traduction demandé n'existe pas on cherche la langue par default , si n'existe pas redirege 404
				if(empty($page->lang)){
					Session::setFlash("La traduction demandé n'est pas disponible...","warning");
					$page = $this->Contents->i18n($page,array('lang'=>Conf::$languageDefault,'valid'=>1));
					if(empty($page->lang)) $this->e404('Page introuvable');
				}


				//Atttribution de l'objet $page a une variable page
				$this->set('page',$page);

				
		}

		//Permet de recuperer les pages pour le menu
		public function getMenu(){

			$this->loadModel('Contents');

			//get requested lang
			$lang = $this->getLang();
			//search all pages to appears in menu
			$pages = $this->Contents->find(array('conditions'=>array('type'=>'page','menu'=>1)));
			//find all traduction for requested language
			$pages = $this->Contents->i18n($pages, array('lang'=>$lang));
			//Unset page that have no traduction for requested lang
			foreach ($pages as $k => $p) {				
				if(!isset($p->lang)) unset($pages[$k]);
			}

			//return pages if exist
			if(!empty($pages))
				return  $pages;	
			else 
				return array();				
		}


		public function admin_index(){

			$this->loadModel('Contents');
			$this->loadModel('Pages');

			if($this->request->post()){

				if($this->Pages->savePage($this->request->post())){

					Session::setFlash("Page sauvegardé !","success");
				}
				else
					Session::setFlash("message","type");
			}

			$lang = $this->getLang();

			$pages = $this->Contents->findPages();
			$pages = $this->Contents->JOINS_i18n($pages,$lang,'strict');			
				

			if(empty($pages)) $pages = array();

			$d['pages'] = $pages;
			$d['lang'] = $lang;

			$this->set($d);

			
		}

}

?>