<?php 
	//retrieve list of reviewer
	$reviewers = $this->call('reviewer','index');
	//prepare it for select box
	foreach ($reviewers as $key => $reviewer) {
		$reviewers[$reviewer->user_id] =$reviewer->login;
		unset($reviewers[$key]);

	}


 ?>




<?php if(!empty($resumes)): ?>
<h2>Resumés déposés</h2>
<table class="table table-striped table-condensed table-hover">
	<thead>
		<th>Title</th>
		<th>1st Author</th>
		<th>Mots Clefs</th>
		<th>Comm.</th>
		<th>Note</th>	
		<th>Status</th>
	</thead>
	<tbody>
		 <?php foreach ($resumes as $a): ?>
			
			<form class="form-table " action="<?php echo Router::url('admin/articles/index/'.$type);?>" method="POST">
		 	<tr class="<?php 
		 			if(isset($a->status) && $a->status=='pending') echo 'warning';
		 			if(isset($a->status) && $a->status=='reviewed') echo 'info';
		 			if(isset($a->status) && $a->status=='accepted') echo 'success';
		 			if(isset($a->status) && $a->status=='refused') echo 'error';
		 			?>">
	 			<td><a href="<?php echo Router::url('admin/articles/view/resume/'.$a->id);?>"><?php echo $a->title; ?></a></td>
	 			<td><span  style="text-transform:uppercase"><?php echo $a->authors[0]->lastname;?></span>&nbsp;<?php echo $a->authors[0]->firstname;?></td>
	 			<td><?php echo $a->tags; ?></td>
	 			<td><?php echo $a->getCommPrefered(); ?></td>
				<td><?php echo $a->getAverageNote(); ?></td>	 			
		 		<td>
		 			<span class="label label-<?php 
		 			if(isset($a->status) && $a->status=='pending') echo 'warning';
		 			if(isset($a->status) && $a->status=='reviewed') echo 'info';
		 			if(isset($a->status) && $a->status=='accepted') echo 'success';
		 			if(isset($a->status) && $a->status=='refused') echo 'important';
		 			?>"><?php echo $a->status;?></span>
		 			<small><?php if($a->status=='pending' && count($a->assigned)!=0) echo '('.count($a->reviewed).'/'.count($a->assigned).')'; ?></small>
		 		</td>				
					
		 	</tr>

		 	<?php echo $this->Form->input('id','hidden',array('value'=>$a->id)) ;?>
		 	<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
			 </form>
		 <?php endforeach ?>
	</tbody>
</table>
<?php endif; ?>

<?php if(!empty($deposed)): ?>
<h2>Articles étendus déposés</h2>
<table class="table table-striped table-condensed table-hover">
	<thead>
		<th>Title</th>
		<th>1st Author</th>
		<th>Comm. wanted</th>
		<th>Average Note</th>
		<th>Current status</th>
	</thead>
	<tbody>
		<?php foreach ($deposed as $a):?>
			
			<form class="form-table" action="<?php echo Router::url('admin/articles/index/deposed');?>" method="POST">
			<tr class="<?php 
		 			if(isset($a->status) && $a->status=='pending') echo 'warning';
		 			if(isset($a->status) && $a->status=='reviewed') echo 'info';
		 			if(isset($a->status) && $a->status=='accepted') echo 'success';
		 			if(isset($a->status) && $a->status=='refused') echo 'error';
		 			?>">
	 			<td><a href="<?php echo Router::url('admin/articles/view/deposed/'.$a->resume_id);?>"><?php echo $a->title; ?></a></td>
	 			<td><span  style="text-transform:uppercase"><?php echo $a->authors[0]->lastname;?></span>&nbsp;<?php echo $a->authors[0]->firstname;?></td>
	 			<td><?php echo $a->getCommPrefered(); ?></td>
	 			<td><?php echo $a->getAverageNote();?></td>	 			
		 		<td>
		 			<?php echo $a->status;?>
		 			<small><?php if($a->status=='pending' && count($a->assigned)!=0) echo '('.count($a->reviewed).'/'.count($a->assigned).')'; ?></small>
		 		</td>				
					
		 	</tr>

		 	<?php echo $this->Form->input('id','hidden',array('value'=>$a->id)) ;?>
		 	<?php echo $this->Form->input('resume_id','hidden',array('value'=>$a->resume_id)) ;?>
		 	<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
			</form>
		<?php endforeach;?>
	</tbody>
</table>
<?php endif; ?>