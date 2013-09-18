<h1>Bienvenue <?php echo Session::user()->getLogin();?> !</h1>

<?php echo Session::flash(); ?>

<?php if(!empty($resumes)): ?>
<h4>Liste des résumés déposés</h4>
<table class="table table-condensed table-hover">
	<thead>
		<th>Title</th>
		<th>Communication</th>
		<th>Date de dépot</th>
		<th>Status</th>
	</thead>
	<tbody>
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
		
	</tbody>
</table>
<?php endif; ?>
