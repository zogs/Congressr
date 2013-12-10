<?php echo Session::flash(); ?>

<h4>Liste des resumés à reviewer</h4>
<table class="table table-striped table-condensed table-hover">
	<?php if(count($resumes)==0): ?>
			<tbody><tr class="warning"><td><small><i>No articles to review for the moment...</i></small></td></tr></tbody>
	<?php else: ?>
		<thead>
			<th>Title</th>
			<th>Comm.</th>
			<th>Status</th>
			<th>Note</th>	
			<th>Action</th>
		</thead>
		<tbody>
			 <?php foreach ($resumes as $a): ?>
				<?php $reviewed = false;?>
				<?php 
	 			if(isset($a->reviewed)){
		 			foreach ($a->reviewed as $review) {
		 				if($review->reviewer_id==Session::user()->getID()) $reviewed = $review;
		 			}
		 		}			 						 			
	 			?>
				<form class="form-table " action="" method="POST">
			 	<tr class="<?php echo (true==$reviewed)? 'info' : 'warning';?>">
		 			<td><a href="<?php echo Router::url('reviewer/review/resume/'.$a->id);?>"><?php echo $a->title; ?></a></td>
		 			<td><?php echo (true==$reviewed)? $reviewed->comm_type : '---'; ?></td>
			 		<td><?php echo (true==$reviewed)? '<span class="label label-info">reviewed</span>' : '<span class="label">En attente</span>';?></td>				
					<td><?php echo (true==$reviewed)? $reviewed->note : '---'; ?></td>	 			
					<td>
						<a href="<?php echo Router::url('reviewer/review/resume/'.$a->id);?>"><?php echo (true==$reviewed)? 'View' : 'Review';?></a>
					</td>	 		
			 	</tr>

			 	<?php echo $this->Form->input('id','hidden',array('value'=>$a->id)) ;?>
			 	<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
				 </form>

			
			 <?php endforeach ?>
		</tbody>
	<?php endif; ?>
</table>

<?php if(!empty($deposed)): ?>
<h4>Liste des résumés étendus à reviewer</h4>
<table class="table table-striped table-condensed table-hover">
	<?php if(count($deposed)==0): ?>
			<tbody><tr class="warning"><td>No articles to review for the moment. </td></tr></tbody>
	<?php else: ?>
		<thead>
			<th>Title</th>
			<th>Status</th>
			<th>Note</th>	
			<th>Action</th>
		</thead>
		<tbody>
			 <?php foreach ($deposed as $a): ?>
			 	
				<?php $reviewed = false;?>
				<?php 
	 			if(isset($a->reviewed)){
		 			foreach ($a->reviewed as $review) {
		 				if($review->reviewer_id==Session::user()->getID()) $reviewed = $review;
		 			}
		 		}			 						 			
	 			?>
				<form class="form-table " action="" method="POST">
			 	<tr class="<?php echo (true==$reviewed)? 'info' : 'warning';?>">
		 			<td><a href="<?php echo Router::url('reviewer/review/deposed/'.$a->resume_id);?>"><?php echo $a->title; ?></a></td>
			 		<td><?php echo (true==$reviewed)? '<span class="label label-info">reviewed</span>' : '<span class="label">En attente</span>';?></td>			
					<td><?php echo (true==$reviewed)? $reviewed->note : '---'; ?></td>	 			
					<td>
						<a href="<?php echo Router::url('reviewer/review/deposed/'.$a->resume_id);?>"><?php echo (true==$reviewed)? 'View' : 'Review';?></a>
					</td>	 		
			 	</tr>

			 	<?php echo $this->Form->input('id','hidden',array('value'=>$a->id)) ;?>
			 	<?php echo $this->Form->input('resume_id','hidden',array('value'=>$a->resume_id)) ;?>
			 	<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
				 </form>

			
			 <?php endforeach ?>
		</tbody>
	<?php endif; ?>
</table>
<?php endif ; ?>
