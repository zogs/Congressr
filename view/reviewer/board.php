<?php echo Session::flash(); ?>

<h3>Bienvenue <?php echo Session::user()->getLogin();?> !</h3>


<h4>Liste des resumés à reviewer</h4>
<table class="table table-striped table-condensed table-hover">
	<?php if(count($resumes)==0): ?>
			<tbody><tr class="warning"><td>No articles to review for the moment. Please come back later</td></tr></tbody>
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
			 	<tr class="<?php echo (true==$reviewed)? 'success' : 'warning';?>">
		 			<td><a href="<?php echo Router::url('reviewer/review/resume/'.$a->id);?>"><?php echo $a->title; ?></a></td>
		 			<td><?php echo (true==$reviewed)? $reviewed->comm_type : '---'; ?></td>
			 		<td><?php echo (true==$reviewed)? 'reviewed' : '---';?></td>				
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

<?php if(!empty($extended)): ?>
<h4>Liste des articles à reviewer</h4>
<table class="table table-striped table-condensed table-hover">
	<?php if(count($extended)==0): ?>
			<tbody><tr class="warning"><td>No articles to review for the moment. </td></tr></tbody>
	<?php else: ?>
		<thead>
			<th>Title</th>
			<th>Status</th>
			<th>Note</th>	
			<th>Action</th>
		</thead>
		<tbody>
			 <?php foreach ($extended as $a): ?>
				<?php $reviewed = false;?>
				<?php 
	 			if(isset($a->reviewed)){
		 			foreach ($a->reviewed as $review) {
		 				if($review->reviewer_id==Session::user()->getID()) $reviewed = $review;
		 			}
		 		}			 						 			
	 			?>
				<form class="form-table " action="" method="POST">
			 	<tr class="<?php echo (true==$reviewed)? 'success' : 'warning';?>">
		 			<td><a href="<?php echo Router::url('reviewer/review/extended/'.$a->id);?>"><?php echo $a->title; ?></a></td>
			 		<td><?php echo $a->status;?></td>				
					<td><?php echo (true==$reviewed)? $reviewed->note : '---'; ?></td>	 			
					<td>
						<a href="<?php echo Router::url('reviewer/review/extended/'.$a->id);?>"><?php echo (true==$reviewed)? 'View' : 'Review';?></a>
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

<?php if(!empty($deposed)): ?>
<h4>Liste des articles à reviewer</h4>
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
			 	<tr class="<?php echo (true==$reviewed)? 'success' : 'warning';?>">
		 			<td><a href="<?php echo Router::url('reviewer/review/deposed/'.$a->resume_id);?>"><?php echo $a->title; ?></a></td>
			 		<td><?php echo $a->status;?></td>				
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
