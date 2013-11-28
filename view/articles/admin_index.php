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
<h2><?php echo count($resumes);?> Resumés déposés</h2>
<form id="tsearch-form" action="" method="post"><input id="tsearch-query" type="text" /> <input type="submit" value="Search" /> <input id="tsearch-clear" type="button" value="Clear" /><span id="tsearch-results"></span></form>
<table class="table table-striped table-condensed table-hover tableSearch tableSort">
	<thead>
		<th>Title</th>
		<th>Authors</th>
		<th>Mots Clefs</th>
		<th>Comm.</th>	
		<th>Status</th>
	</thead>
	<tbody>
		 <?php foreach ($resumes as $r): ?>
			
			<form class="form-table " action="<?php echo Router::url('admin/articles/index/'.$type);?>" method="POST">
		 	<tr class="<?php 
		 			if(isset($r->status) && $r->status=='pending') echo 'warning';
		 			if(isset($r->status) && $r->status=='reviewed') echo 'info';
		 			if(isset($r->status) && $r->status=='accepted') echo 'success';
		 			if(isset($r->status) && $r->status=='refused') echo 'error';
		 			?>">
	 			<td><a href="<?php echo Router::url('admin/articles/view/resume/'.$r->id);?>"><?php echo $r->title; ?></a></td>
	 			<td style="font-size:80%">
	 				<ul>
	 				<?php foreach ($r->authors as $k => $a) :?>
	 					<li>
			 				<span  style="text-transform:uppercase"><?php echo $a->lastname;?></span>
			 				&nbsp;<?php echo $a->firstname;?>
			 				<small><i><?php echo $a->institution;?></i></small>
			 			</li>
	 				<?php endforeach;?>
	 				</ul>
	 			</td>
	 			<td><?php echo $r->tags; ?></td>	 			
	 			<td><?php echo $r->comm_type; ?></td>						
		 		<td>
		 			<span class="label label-<?php 
		 			if(isset($r->status) && $r->status=='pending') echo 'warning';
		 			if(isset($r->status) && $r->status=='reviewed') echo 'info';
		 			if(isset($r->status) && $r->status=='accepted') echo 'success';
		 			if(isset($r->status) && $r->status=='refused') echo 'important';
		 			?>"><?php echo $r->status;?></span>
		 			<small><?php if($r->status=='pending' && count($r->assigned)!=0) echo '('.count($r->reviewed).'/'.count($r->assigned).')'; ?></small>
		 		</td>			 								
		 	</tr>

		 	<?php echo $this->Form->input('id','hidden',array('value'=>$r->id)) ;?>
		 	<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
			 </form>
		 <?php endforeach ?>
	</tbody>
</table>
<?php endif; ?>

<?php if(!empty($deposed)): ?>
<h2><?php echo count($deposed);?> Articles étendus déposés</h2>
<form id="tsearch-form" action="" method="post"><input id="tsearch-query" type="text" /> <input type="submit" value="Search" /> <input id="tsearch-clear" type="button" value="Clear" /><span id="tsearch-results"></span></form>
<table class="table table-striped table-condensed table-hover tableSearch tableSort">
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