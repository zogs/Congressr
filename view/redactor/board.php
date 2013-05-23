<?php 
echo Session::flash();
 ?>

<h1>Bienvenue <?php echo Session::user()->getLogin();?> !</h1>

<h4>Action</h4>

<p>
	<ul>
		<li><a href="<?php echo Router::url('users/account');?>">Modifier mon compte</a></li>
		<li><a href="<?php echo Router::url('articles/resume');?>">Déposer un résumé</a></li>
	</ul>
</p>

<?php if(!empty($resumes)): ?>
<h4>Liste de résumé déposés</h4>
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
			?>">
				<td><a href="<?php echo Router::url('articles/resume/'.$resume->id);?>"><?php echo $resume->title; ?></a></td>
				<td><?php echo $resume->prefer; ?></td>
				<td><?php echo $resume->date; ?></td>
				<td><?php echo $resume->status; ?></td>
			</tr>
			
		<?php endforeach;?>
		
	</tbody>
</table>
<?php endif; ?>


<?php if(!empty($articles)): ?>
<h4>Liste d'article déposés</h4>
<?php endif; ?>