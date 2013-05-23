<?php 
/**
 * GESTION DES DATES
 */
 class Date
 {

 	public static function MysqlNow(){

 		return self::timestamp2MysqlDate(time());
 	}

 	public static function timestamp2MysqlDate($timestamp){

 		return date('Y-m-d H:i:s',$timestamp);
 	}

 	public static function dayoftheweek($day,$lang = ''){

 		if(empty($lang)) $lang = Conf::$languageDefault;
 		$days = array(
 						'fr'=>array('Today'=>"Aujourd'hui",'Mon'=>'Lundi','Tue'=>'Mardi','Wed'=>'Mercredi','Thu'=>'Jeudi','Fri'=>'Vendredi','Sat'=>'Samedi','Sun'=>'Dimanche')
		);

		return $days[$lang][$day];
 	}

 	public static function yearsSince($year){

 		$age = 0;
		$current = Date('Y');
		for($i=$year; $i<$current; $i++){
			$age++;
		}
		return $age;
 	}

 	public static function datefr($date) { 

		//tableau des mois de l'année en francais
		$type_mois['00']='00';
		$type_mois['01']='Janvier';   $type_mois['02']='Février';
		$type_mois['03']='Mars';      $type_mois['04']='Avril';
		$type_mois['05']='Mai';       $type_mois['06']='Juin';
		$type_mois['07']='Juillet';   $type_mois['08']='Août';
		$type_mois['09']='Septembre'; $type_mois['10']='Octobre';
		$type_mois['11']='Novembre';  $type_mois['12']='Décembre';
		//on separe la date en jour mois annee
		$split = explode("-",$date); 
		$annee = $split[0]; 
		$num_mois = $split[1]; 
		$jour = $split[2]; 
		//on associe le numero du mois au nom correspondant dans le tableau
		$mois = $type_mois[$num_mois];
		//on retourne la valeur
		return "$jour"." "."$mois"." "."$annee"; 
	}		 

 	// public function month($num){

 	// 	$array = array('fr' => array('Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre')
 	// 			);

 	// 	return $array[Session::getLang()][$num - 1];

 	// }
 } ?>