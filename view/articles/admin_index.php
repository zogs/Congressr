<?php 
	//retrieve list of reviewer
	$reviewers = $this->call('reviewer','index');
	//prepare it for select box
	foreach ($reviewers as $key => $reviewer) {
		$reviewers[$reviewer->user_id] =$reviewer->login;
		unset($reviewers[$key]);

	}


 ?>


<h2>All <?php echo $type;?></h2>
<table class="table table-striped table-condensed table-hover">
	<thead>
		<th>Title</th>
		<th>Mots Clefs</th>
		<th>Comm.</th>
		<th>Status</th>
		<th>Note</th>	
		<th>Reviewer</th>
	</thead>
	<tbody>
		 <?php foreach ($articles as $a): ?>

			<form class="form-table " action="<?php echo Router::url('admin/articles/index/'.$type);?>" method="POST">
		 	<tr class="<?php 
		 			if(isset($a->status) && $a->status=='pending') echo 'warning';
		 			if(isset($a->status) && $a->status=='reviewed') echo 'info';
		 			if(isset($a->status) && $a->status=='accepted') echo 'success';
		 			if(isset($a->status) && $a->status=='refused') echo 'error';
		 			?>">
	 			<td><a href="<?php echo Router::url('admin/articles/view/resume/'.$a->id);?>"><?php echo $a->title; ?></a></td>
	 			<td><?php echo $a->tags; ?></td>
	 			<td><?php echo $a->prefer; ?></td>
		 		<td><?php echo $a->status;?></td>				
				<td><?php echo $a->getAverageNote(); ?></td>	 			
				<td>
					<?php echo $this->Form->_select('reviewer',$reviewers,array("default"=>$a->reviewer_id,'style'=>'width:auto;padding-left:0;')) ;?>
					<input type="Submit"  class="submitAsLink" value="Envoyer" name="assignResume"/>
				</td>	 		
		 	</tr>

		 	<?php echo $this->Form->input('id','hidden',array('value'=>$a->id)) ;?>
		 	<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
			 </form>
		 <?php endforeach ?>
	</tbody>
</table>