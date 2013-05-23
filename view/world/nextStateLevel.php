<?php if($state != false) {

	$array = array(
		'SelectID'=>$state['lvl'],
		'SelectELEMENT'=> $this->Form->_select($state['lvl'],$state['list'],array("class"=>'geo-select',"placeholder"=>$state['title'],"style"=>"width:100%;","javascript"=>'onchange="showRegion(this.value,\''.$state["lvl"].'\')"'))
		);

	echo json_encode($array);
	//echo $this->Form->Select($ADM,'btn geo-select',$states,'code','name','','onchange="showRegion(this.value,\''.$ADM.'\')"');
}else {
	echo 'empty';
}?>