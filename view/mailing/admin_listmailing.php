<a href="<?php echo Router::url('admin/mailing/index');?>">Retour</a>
<h3>Mailing lists existantes <a class="btn btn-mini btn-info" href="<?php echo Router::url('admin/mailing/editlist');?>">Créer une nouvelle liste</a></h3>
<table class="table table-striped table-condensed table-hover">
	<thead>
		<th>Nom de la liste</th>
		<th>Nombre d'adresse</th>
		<th>Action</th>

	</thead>
	<tbody>
		 <?php foreach ($lists as $a): ?>

		 	<tr>
	 			<td><a href="<?php echo Router::url('admin/mailing/editlist/'.$a->list_id);?>"><?php echo $a->name; ?></a></td>
	 			<td><?php echo $a->count; ?></td>	 			
		 		<td>
		 			<a href="<?php echo Router::url('admin/mailing/editlist/'.$a->list_id);?>">Editer</a>
		 			<a href="<?php echo Router::url('admin/mailing/deletelist/'.$a->list_id);?>">Supprimer</a>
		 		</td>				
					
		 	</tr>		 				
		 <?php endforeach ?>
	</tbody>
</table>