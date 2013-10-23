

<?php echo Session::flash(); ?>


<h3>
	Liste des résumés déposés	
</h3>
<div class="clearfix">
	<table class="table table-condensed table-hover">
		<thead>
			<th>Title</th>
			<th>Communication</th>
			<th>Date de dépot</th>
			<th>Status</th>
		</thead>
		<tbody>
			<?php if(!empty($resumes)): ?>
			<?php foreach ($resumes as $key => $resume):?>
				<tr class="<?php 
					if($resume->status=='pending') echo 'warning';
					if($resume->status=='reviewed') echo 'info';
					if($resume->status=='accepted') echo 'success';
					if($resume->status=='refused') echo 'error';
				?>">
					<td>
						<a href="<?php echo Router::url('articles/resume/'.$resume->id);?>"><?php echo $resume->title; ?></a>
						<?php if($resume->status=='accepted'):?><br /><a class="btn btn-mini btn-info" href="<?php echo Router::url('articles/deposit/'.$resume->id);?>" style="font-size:80%;">Déposer l'article complet</a><?php endif;?>

					</td>
					<td><?php echo $resume->getCommPrefered(); ?></td>
					<td><?php echo $resume->date; ?></td>
					<td><?php echo $resume->status; ?></td>
				</tr>			
			<?php endforeach;?>
			<?php else:?>
				<tr>
					<td><strong>Pas encore de résumés déposés...</strong></td>
				</tr>
			<?php endif; ?>		
		</tbody>
	</table>
</div>
<a href="<?php echo Router::url('articles/resume');?>" class="btn btn-mini btn-info">Déposer un nouveau résumé</a>
