<input type="hidden" id="submit-state" data-url="<?php echo Router::url('world/nextStateLevel'); ?>">
<?php 

	foreach($states as $lvl=>$state){
			
		//Add javascript 
		$javascript = '';
		if($lvl!='city') $javascript = 'onchange="showRegion(this.value,\''.$lvl.'\')"';						
					
		echo $this->Form->_select($lvl,$state['list'],array('placeholder'=>$state['title'],"class"=>'geo-select',"default"=>$obj->$lvl,"style"=>"width:100%;","javascript"=>$javascript));

	}
 ?>		