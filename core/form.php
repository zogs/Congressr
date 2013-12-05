<?php 
class Form{


	public $controller;
	private $errors = array();

	public function __construct($controller){

		$this->controller = $controller;
		$this->request = $controller->request;

	}

	public function setErrors($errors){

		$this->errors = $errors;
	}

	public function getErrors(){
		return $this->errors;
	}

	public function wrapInput($id, $label, $input, $params = null){		

		//Gestion des erreurs
		$error = false;
		$classError ='';

		//if it is a checkbox or radio remove '[]' from the id
		$id = str_replace('[]','',$id);
		
		//Si il y a une erreur qui correspond a ce champ
		if(isset($this->errors[$id])){
			
			$error = $this->errors[$id]; // recup l'erreur
			$classError = 'control-error'; // défini la class css d'une erreur
		}

		//If hidden input only return input
		if($label=='hidden') return $input;

		//if submit button return input
		if($label=='submit') return $input;

		

		//Bootstrap html
		$html = '<div class="control-group '.$classError.'" id="control-'.$id.'">';
		$html .= '<label class="control-label" for="input'.$id.'">'.$label.'</label>';
		$html .= '<div class="controls">';

		//Icon
		if(isset($params['icon']))
			$html .= "<i class='icon-form ".$params['icon']."'></i>";

		//Input code
		$html .= $input;	

		//Error helper
		if($error) {
			$html .= '<p class="help-inline help-error">'.$error.'</p>';	
		}		

		//Info helper	
		if(isset($params['helper']))
			$html .= '<p class="help-inline">'.$params['helper'].'</p>';				
		else
			$html .= '<p class="help-inline hide"></p>';	

		$html .= '</div>';
		$html .= '</div>';

		return $html;


	}


	public function Input($id,$label,$params = null){

		return $this->wrapInput($id,$label,$this->_input($id, $label, $params),$params);
	}



	public function _input($id,$label,$options = array()){

		$html ='';

		//Gestion des erreurs
		$error = false;
		$classError ='';

		//Si il y a une erreur qui correspond a ce champ
		if(isset($this->errors[$id])){
			$error = $this->errors[$id]; // recup l'erreur
			$classError = 'error'; // défini la class css d'une erreur
		}

		
		if(isset($options['value']) && $options['value']!=''){
			$value = $options['value'];
		}
		elseif(!isset($this->controller->request->data->$id)){ //Si le champ n'a pas de valeur pré-rempli
			$value='';
		}
		else{ //Sinon on recupere la valeur dans les données passé en post dans l'objet request
			$value = $this->controller->request->data->$id;
		}

		//return if submit button
		if($label=='submit'){

			if(isset($options['class'])) $class = $options['class'];
			else $class = '';
			return '<input type="submit" value="'.$id.'" class="'.$class.'" />';
		}

		//return if hidden button
		if($label=='hidden'){

			return '<input type="hidden" name="'.$id.'" id="'.$id.'" value="'.$value.'" />';
		}		

		
		//Attribut html à rajouter en fin de champs	
		$attr='';	
		if(!empty($options)){			
			foreach ($options as $k => $v) {
				if($k!='type' && $k!='icon'){
					$attr .= " $k=\"$v\"";
				}
			}
		}
		
		//Type de input
		if(!isset($options['type'])){
			$html .= '<input type="text" id="input'.$id.'" name="'.$id.'" value="'.$value.'" '.$attr.'>';
		}
		elseif($options['type']=='email'){
			$html .= '<input type="email" id="input'.$id.'" name="'.$id.'" value="'.$value.'" '.$attr.'>';
		}
		elseif($options['type']=='url'){
			$html .= '<input type="url" id="input'.$id.'" name="'.$id.'" value="'.$value.'" '.$attr.'>';
		}
		elseif($options['type']=='textarea'){
			$html .= '<textarea id="input'.$id.'" name="'.$id.'" '.$attr.'>'.$value.'</textarea>';
		}
		elseif($options['type']=='checkbox'){
			$html .= '<input type="hidden" name="'.$id.'" value="0"><input type="checkbox" name="'.$id.'" value="1" '.(!empty($value)? 'checked' : '').' '.$attr.' />';
		}
		elseif($options['type']=='file'){
			if( !empty($options['src']) && $options['src']!=''){

				$html .= '<div class="input-file-thumbnail"><img src="'.$options['src'].'" /></div>';
			}
			$html .= '<input type="file" class="input-file" id="input'.$id.'" name="'.$id.'" '.$attr.'>';

		}
		elseif($options['type']=='password'){
			$html .= '<input type="password" id="input'.$id.'" name="'.$id.'" value="'.$value.'" '.$attr.'>';
		}
		else{
			$html .= '<input type="'.$options['type'].'" id="input'.$id.'" name="'.$id.'" value="'.$value.'" '.$attr.'>';
		}
		
		return $html;
	}



	public function Select($id,$label,$options,$params = array()){

		$html = $this->wrapInput($id,$label,$this->_select($id,$options,$params),$params);

		return $html;
	}


	public function SelectNumber( $id, $label, $start = 0, $end = 10, $params){

		$html = $this->wrapInput($id, $label,$this->_selectNumber($id,$start,$end,$params),$params);

		return $html;
	}


	public function _selectNumber( $id, $start = 0, $end = 10, $params = null){

		(!empty($params['class']))? $class = $params['class'] : $class = '' ;
		(!empty($params['default']))? $selected = $params['default'] : $selected = '';
		(!empty($params['placeholder']))? $placeholder = $params['placeholder'] : $placeholder = '';
		(!empty($params['javascript']))? $javascript = $params['javascript'] : $javascript = '';

		$years = array();

		if($start<$end) {
			for( $i=$start; $i<=$end; $i++){
				$years[$i] = $i;
			}
		}
		elseif($start>$end) {
			for( $i=$start; $i>=$end; $i--){
				$years[$i] = $i;
			}
		}
		return $this->_select($id,$years,$params);

	}

	/** _SELECT
	* Genere un code html <select...> avec <option></option...>
	*
	* @param $id l'id du champ select
	* @param $class la class du champ select
	* @param $options tableaux des options : array('code'=>'name')
	* @param $selected valeur dont le champ est selected
	* @param $javascript (facultatif)
	* @return $html code html
	*/ 
	public function _select($id,$options,$params = array()){

		(isset($params['class']))? $class = $params['class'] : $class = '' ;
		if(isset($params['default'])){
			$selected = $params['default'];
		}
		elseif(!isset($this->request->data->$id)){ //Si le champ n'a pas de valeur pré-rempli
			$selected=null;
		}
		else{ //Sinon on recupere la valeur dans les données passé en post dans l'objet request
			$selected = $this->request->data->$id;
		}
		(isset($params['placeholder']))? $placeholder = $params['placeholder'] : $placeholder = '';
		(isset($params['javascript']))? $javascript = $params['javascript'] : $javascript = '';
		(isset($params['style']))? $style = $params['style'] : $style = '';

		if(!empty($options))
		{

			$html ='<select id="'.$id.'" name="'.$id.'" class="'.$class.'" style="'.$style.'" '.$javascript.' >';
			$html .= '<option value=" ">'.$placeholder.'</options>';
			
			if(is_object($options)) $options = (array) $options;

			foreach ($options as $key => $line) {
					
					if(is_object($line)) $line = (array) $line;

					if(is_array($line)){

						$keys = array_keys($line);
						$value = $line[$keys[0]];
						$name = $line[$keys[1]];
					}
					else {
						$value = $key;
						$name = $line;
					}
				
					if(isset($selected)&&$selected==$value) $if_selected = 'selected="selected"';
					else $if_selected = '';

					$html .='<option value="'.$value.'" '.$if_selected.'>'.$name.'</option>';								
				
			}
			$html .='</select>';
			return $html;
		}
		else return false;


	}

	/** OPTIONS
	* Genere un code html <option></option...>
	*
	* @param $options tableaux associatifs des options : array('code'=>14,'nom'=>Cal)
	* @param $value nom de la valeur propre au tableau
	* @param $name nom de l'option propre au talbeau
	* @param $selected valeur dont le champ est selected
	* @return $html code html ou flase
	*/ 	
	public function Options($options, $value, $name, $selected){

		if(!empty($options))
		{
			$html = '<option value="">- Toutes -</options>';
			$attr = 'selected="selected"';
			if(is_object($options)) $options = get_objet_vars($options);

			foreach ($options as $line) {
				if(is_object($line)) {				
					$html .='<option value="'.$line->$value.'" '.($line->$value === $selected ? $attr:'').'>'.$line->$name.'</option>';								
				}				
				elseif(is_array($line))	{
					if($line[$value] == $selected) $attr='selected="selected';		
					$html .= '<option value='.$line[$value].' '.($line->$value === $selected ? $attr:'').'>'.$line[$name].'</option>';
				}
			}
			return $html;
		}
		else return false;
	}

	
	/**
	*	Radio
	*	$id name of the field
	*	$label title of the field
	*	$options array of option
	*	$params array('class','default','javascript')
	*/
	public function Radio( $id, $label, $options, $params = null){


		$html = $this->wrapInput($id, $label, $this->_radio($id, $label, $options, $params) ,$params);		

		return $html;
	}

	public function _radio($id, $label, $options, $params){

		(isset($params['class']))? $class = "class='".$params['class']."'" : $class = '' ;
		(isset($params['default']))? $default = $params['default'] : $default = '';
		(isset($arams['javascript']))? $javascript = $params['javascript'] : $javascript = '';
		(isset($params['style']))? $style = $params['style'] : $style = '';

		$html='';

		if(is_object($options)) $options = (array) $options;

		foreach($options as $value => $text){

			$checked = '';
				if(is_array($default)){
					if(in_array($value,$default)) $checked = 'checked="checked"';
				}
				elseif($value==$default) $checked = 'checked="checked"';
			else $checked = '';

			(isset($params['openwrap']))? $html .= $params['openwrap'] : $html .= '';
			$html .= '<input type="radio" '.$class.' name="'.$id.'" value="'.$value.'" id="'.$value.'" style="'.$style.'" '.$checked.' '.$javascript.'>';
			$html .= '<label class="radio" for="'.$value.'">'.$text.'</label>';
			(isset($params['closewrap']))? $html .= $params['closewrap'] : $html .= '';
		}

		return $html;
	}


	public function Checkbox($id, $label, $options, $params = null){
	
		return $this->wrapInput($id, $label,$this->_checkbox($id, $label, $options, $params),$params);
	
	}

	public function _checkbox($id, $label, $options, $params=null){

		(isset($params['class']))? $class = "class='".$params['class']."'" : $class = '' ;
		(isset($params['default']))? $default = $params['default'] : $default = '';
		(isset($arams['javascript']))? $javascript = $params['javascript'] : $javascript = '';

		if(is_object($options)) $options = (array) $options;
		
		$html = '';
			foreach($options as $value => $text){

				$checked = '';
				if(is_array($default)){
					if(in_array($value,$default)) $checked = 'checked="checked"';
				}
				elseif($value==$default) {
					$checked = 'checked="checked"';;
				}
				(isset($params['openwrap']))? $html .= $params['openwrap'] : $html .= '';
				$html .= '<input type="checkbox" '.$class.' name="'.$id.'" value="'.$value.'" id="'.$value.'" '.$checked.' '.$javascript.'>';
				$html .= '<label class="checkbox" for="'.$value.'">'.$text.'</label>';
				(isset($params['closewrap']))? $html .= $params['closewrap'] : $html .= '';
			}

		return $html;

	}
	/*
	public function Textarea($id, $label, $params = null){

		return $this->wrapInput($id,$label,$this->_textarea($id,$label,$params),$params);
	}	
	

	public function _textarea($id, $label, $params = null){

		(isset($params['class']))? $class = "class='".$params['class']."'" : $class = '' ;
		(isset($params['default']))? $default = $params['default'] : $default = '';
		(isset($arams['javascript']))? $javascript = $params['javascript'] : $javascript = '';

		$html = '';

	}
	*/
}

 ?>