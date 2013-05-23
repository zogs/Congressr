<?php echo Session::flash(); ?>

<h3>Bienvenue <?php echo Session::user()->getLogin();?> !</h3>


<h4>Liste des resumés à reviewer</h4>
<table class="table table-striped table-condensed table-hover">
	<?php if(count($resumes)==0): ?>
			<tbody><tr class="warning"><td>No articles to review for the moment. Please come back later</td></tr></tbody>
	<?php else: ?>
		<thead>
			<th>Title</th>
			<th>Mots Clefs</th>
			<th>Comm.</th>
			<th>Status</th>
			<th>Note</th>	
			<th>Action</th>
		</thead>
		<tbody>
			 <?php foreach ($resumes as $a): ?>

				<form class="form-table " action="" method="POST">
			 	<tr class="<?php 
			 			if(isset($a->status) && $a->status=='pending') echo 'warning';
			 			if(isset($a->status) && $a->status=='reviewed') echo 'info';
			 			if(isset($a->status) && $a->status=='accepted') echo 'success';
			 			if(isset($a->status) && $a->status=='decline') echo 'decline';
			 			?>">
		 			<td><a href="<?php echo Router::url('reviewer/review/resume/'.$a->id);?>"><?php echo $a->title; ?></a></td>
		 			<td><?php echo $a->tags; ?></td>
		 			<td><?php echo $a->prefer; ?></td>
			 		<td><?php echo $a->status;?></td>				
					<td><?php echo $a->getAverageNote(); ?></td>	 			
					<td>
						<a href="<?php echo Router::url('reviewer/review/resume/'.$a->id);?>">Review it</a>
					</td>	 		
			 	</tr>

			 	<?php echo $this->Form->input('id','hidden',array('value'=>$a->id)) ;?>
			 	<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
				 </form>
			 <?php endforeach ?>
		</tbody>
	<?php endif; ?>
</table>
</p>