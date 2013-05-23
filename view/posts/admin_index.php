<div class="page-header">
	<h1>Articles du blog</h1>
	<h2><?php echo $total ?> articles</h2>

</div>

<table class="table table-striped">
	<thead>
		<th>ID</th>
		<th>En ligne ?</th>
		<th>Titre</th>
		<th>Action</th>
	</thead>
	<tbody>
		 <?php foreach ($posts as $k => $v): ?>
		 	<tr>
		 		<td><?php echo $v->id ?></td>
		 		<td><span class="label<?php echo ($v->online==1)? ' label-success' : ''; ?>"><?php echo ($v->online==1)? 'En ligne' : 'Hors ligne'; ?></span></td>
		 		<td><?php echo $v->name ?></td>
		 		<td>
		 			<a href="<?php echo Router::url('admin/posts/edit/'.$v->id); ?>" >Editer</a>

		 			<a onclick="return confirm('Voulez-vous vraiment supprimer cet élément ?');" href="<?php echo Router::url('admin/posts/delete/'.$v->id); ?>" >Supprimer</a>
		 		</td>
		 	</tr>
		 <?php endforeach ?>
	</tbody>
</table>

<a href="<?php echo Router::url('admin/posts/edit'); ?>" class="btn btn-primary"> Ajouter un article</a>