<?php

class PagesModel extends Model {
	
	public function savePage($data){

		$p = new stdClass();
		$p->online = $data->online;
		$p->menu = $data->menu;
		$p->type = 'page';

		if(!empty($data->id)){
			$p->id = $data->id;
		}

		$p->table = 'contents';
		$p->key = 'id';

		if($this->save($p)){
			return true;
		}
	}
}
?>