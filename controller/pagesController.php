<?php

class PagesController extends Controller {


		public function home(){

			$this->loadModel('Pages');

			$page = $this->Pages->findPageBySlug('home');
			$page = $this->Pages->JOIN_i18n($page,$this->getLang());

			$this->set('page',$page);
			
		}

		//===================
		// Permet de rentre une page
		// $param $id id du post dans la bdd
		public function view($slug){

			//On charge le model
			$this->loadModel('Pages');			
				
			//On cherche la page		
			if(!$this->request->get('lang'))
				$page = $this->Pages->findPageBySlug($slug);
			else {
				$page = $this->Pages->findPageBySlugAndLang($slug,$this->request->get('lang'));
			}

			//Si la page n'existe pas on redirige sur 404
			if(!$page->exist()){
				$this->e404('Page introuvable');
			}
			
			//On cherche le contenu
			$page = $this->Pages->JOIN_i18n($page,$page->lang);

			//Si la page a une methode particuliere
			if(method_exists($this, $page->slug)){
				$method = $page->slug;
				$this->$method();
				return;
			}

			//Si la traduction demandé n'existe pas on cherche la langue par default , si n'existe pas redirege 404
			if(!$page->isTraductionExist() || !$page->isTraductionValid()){
				Session::setFlash("La traduction demandé n'est pas disponible... <a href=".Router::url('pages/view/'.$page->slug.'/?lang='.$page->langDefault).">Cliquez ici</a> pour voir la page dans sa langue d origine ","warning");
				$this->e404('Page introuvable');
			}

			//Atttribution de l'objet $page a une variable page
			$this->set('page',$page);				
		}

		//Permet de recuperer les pages pour le menu
		public function getMenu($menu){

			$this->loadModel('Pages');

			//get requested lang
			$lang = $this->getLang();
			//search all pages to appears in menu
			$pages = $this->Pages->findMenu($menu);
			//debug($pages);			
			//find all traduction for requested language
			$pages = $this->Pages->JOINS_i18n($pages, $lang);
			
			//Unset page that have no traduction for requested lang
			foreach ($pages as $k => $page) {				
				if(!$page->isTraductionExist() || !$page->isTraductionValid() )  unset($pages[$k]);
			}
			
			//return pages if exist
			if(!empty($pages))
				return  $pages;	
			else 
				return array();				
		}


		public function admin_index($menu=null){

			$this->loadModel('Pages');

			if($this->request->post()){

				if($this->Pages->savePage($this->request->post())){

					Session::setFlash("Page sauvegardé !","success");
				}
				else
					Session::setFlash("message","type");
			}

			$lang = $this->getLang();			

			if(!isset($menu))
				$pages = $this->Pages->findPages();
			else
				$pages = $this->Pages->findMenu($menu);

			$traductions = $this->Pages->countPagesTraduction($pages);
			$pages = $this->Pages->JOINS_i18n($pages,$lang);	
			$menus = $this->Pages->findDistinctMenu();		

			if(empty($pages)) $pages = array();

			$d['traductions'] = $traductions;
			$d['menus'] = $menus;
			$d['pages'] = $pages;
			$d['lang'] = $lang;


			$this->set($d);			
		}

		public function admin_delete($id){

			$this->loadModel('Pages');

			if($this->Pages->deleteContent($id)){

				Session::setFlash("Page supprimé","success");

				if($this->Pages->deletei18nContents($id)){
					Session::setFlash("Traductions supprimés","success");
				}
				else {
					Session::setFlash("Error lors de la suppression des traductions","error");
				}				
			}
			else {
				Session::setFlash("Error lors de la suppression","error");
			}
			$this->redirect('admin/pages/index');
		}

		public function admin_edit($id = null){

			$this->loadModel('Pages');
			$d['id'] = $id;

			$lang = $this->getLang();		

			if($this->request->data){

				$new = $this->request->data;
				$new->slug = String::slugify($new->title);

				if($this->Pages->validates($new)){
					
					if($page_id = $this->Pages->savePage($new)){

						if($this->Pages->saveTraduction($new,$page_id)){

							Session::setFlash('Contenu modifié');
							$this->redirect('admin/pages/edit/'.$page_id.'/?lang='.$lang);
						}
					}
				}

				//on recupere la langue des données envoyés
				$lang = $new->lang;
			}
							
			if($id){
				$c = $this->Pages->getContent($id);
				$c = $this->Pages->JOIN_i18n($c,$lang);

				$trad = $this->Pages->findTraduction($id);
				
				$d['id'] = $id;
				$d['trad'] = $trad;
				$d['content'] = $c;
				$this->request->data = $c;
				
			}

			

			$this->set($d);
		}

}

?>