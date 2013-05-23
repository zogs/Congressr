<?php 


/**
* 
*/
class WorldController extends Controller
{
	
 	/*=======================================
 	LOCATE
 	Display states fields that locate an object
 	$param objet or array that may contain location fields ( as CC1, ADM1, ADM..., city)
 	========================================*/
	public function locate($obj = null){

		$this->loadModel('Worlds');
		$this->layout='none';

		//if object is not null
		if($obj){

			if(is_array($obj))
				$obj = (object) $obj; //convert array into obj
			elseif(is_object($obj))
				$obj = $obj;
			else
				return false;			
		}
		else //else set empty object
			$obj = new stdClass();
		
		//create empty location parameter if they don't exist
		$lvl = array('CC1','ADM1','ADM2','ADM3','ADM4','city');
		foreach ($lvl as $key) {
			if(!isset($obj->$key))
				$obj->$key = '';
		}
		
	
		//find states that correspond to the object parameters
		$states = $this->Worlds->findAllStates(array(
								'CC1'=>$obj->CC1,
								'ADM1'=>$obj->ADM1,
								'ADM2'=>$obj->ADM2,
								'ADM3'=>$obj->ADM3,
								'ADM4'=>$obj->ADM4,
								'city'=>$obj->city
								));

		$d['states'] = $states;
		$d['obj'] = $obj;

		$this->set($d);
		$this->view = 'world/locate';
		$this->render();


	}

	public function formLocate($id, $label, $obj = null, $params = null ){

		ob_start();
		$this->locate($obj);
		$html = ob_get_clean();
		$html = $this->Form->wrapInput( $id, $label ,$html, $params);

		echo $html;
	}

	public function getCountry(){

		$this->loadModel('Worlds');
		return $this->Worlds->findCountry();
		
	}

	public function suggestCity(){

		$this->view = 'json';
		$this->layout = 'none';
		$this->loadModel('Worlds');


		if($this->request->get('query')){

			$cities = $this->Worlds->suggestCities(array(
													'prefix'=>$this->request->get('query'),
													'CC1'=>'FR')
													);

			$suggestions = array();
			$citiesCode = array();
			$states = array('CC1','ADM1','ADM2','ADM3','ADM4');

			foreach ($cities as $city) {
				
				foreach ($states as $key) {
					if(isset($city->$key)) $state = $city->$key;
				}
				$suggestions[] = $city->name.' ('.$state.')';
				$citiesCode[] = $city->city_id;			
			}

			$json = array(
						'query'=>$this->request->get('query'),
						'suggestions'=>$suggestions,
						'data'=>$citiesCode
						);
		}

		$this->set($json);
	}

	public function citiesArround(){

		$this->view = "none";
		$this->layout ="default";
		$this->loadModel('Worlds');
		
		if($this->request->get('cityID') && $this->request->get('arround')){

			$city = $this->Worlds->findFirst(array('table'=>'world_cities','fields'=>array('LATITUDE','LONGITUDE'),'conditions'=>array('UNI'=>$this->request->get('cityID'))));		

			$params = array();
			$params['arround'] = $this->request->get('arround');
			$params['Lat'] = $city->LATITUDE;
			$params['Lon'] = $city->LONGITUDE;
			$params['location'] = array('CC1'=>'FR','ADM1'=>'A1');
			$params['km'] = true;

			$cities = $this->Worlds->findCitiesArround($params);

			return $cities;
		}
	}

	public function nextStateLevel(){

 		$this->loadModel('Worlds');
 		$this->layout = 'none';

 		$ADM = $this->request->get('ADM'); 
 		if($ADM=='city') return false;		
 		$ADM1 = '';
 		$ADM2 = '';
 		$ADM3 = '';
 		$ADM4 = '';
 		$ADM_PARENT = '';

 		//set next ADM
 		if($ADM == 'CC1') $ADM = 'ADM1';
 		elseif($ADM == 'ADM1') $ADM = 'ADM2';
 		elseif($ADM == 'ADM2') $ADM = 'ADM3';
 		elseif($ADM == 'ADM3') $ADM = 'ADM4';
 		elseif($ADM == 'ADM4') $ADM = 'city';

 		

 		//Recuperation des parametres
 		if($this->request->get('CC1')){ 			
 			$CC1 = $this->request->get('CC1');
 		} 		
 		else { 
 			$CC1 = $this->getCountryCode();
 		}
 		if($this->request->get('ADM1'))	{
 			$ADM1 = $this->request->get('ADM1');
 			$ADM_PARENT = $this->request->get('ADM1');		
 		}		
 		if($this->request->get('ADM2'))	{
 			$ADM2 = $this->request->get('ADM2');
 			$ADM_PARENT = $this->request->get('ADM2');
 		}
 		if($this->request->get('ADM3'))	{
 			$ADM3 = $this->request->get('ADM3');
			$ADM_PARENT = $this->request->get('ADM3');
 		}		
 		if($this->request->get('ADM4'))	{
 			$ADM4 = $this->request->get('ADM4');
 			$ADM_PARENT = $this->request->get('ADM4');
 		}	

 		//debug('CC1:'.$CC1. ' ADM:'.$ADM.' ADM_PARENT:'.$ADM_PARENT);
		
 		//Find the state in the database , using the get data 		
 		$d['state'] = $this->Worlds->findStates(array(
										 			'CC1'=>$CC1,
										 			'parent'=>$ADM_PARENT,
										 			'level'=>$ADM
 			));

 		//If there is no state, we look for a city
 		if(empty($d['state']['list'])) {

 			$ADM = 'city';
 			$d['state'] = $this->Worlds->findCities($CC1,array(	
 													'CC1' =>$CC1,								 				
									 				'ADM1'=>$ADM1,
									 				'ADM2'=>$ADM2,
									 				'ADM3'=>$ADM3,
									 				'ADM4'=>$ADM4
			));

 		}
		
 		//If there is no city at all we are done
 		if(empty($d['state'])) {

 			$d['state'] = false;
 		}


		//Instanciation de la variable du niveau d'ADM
 		$d['ADM'] = $ADM;
 		
 		//debug($d);
 		$this->set($d);

 	}
} 

?>