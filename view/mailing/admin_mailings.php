
<a href="<?php echo Router::url('admin/mailing/index');?>">Retour</a>
<h2>Liste des mailings<a class="btn btn-info" href="<?php echo Router::url('admin/mailing/editmailing');?>">Créer un nouveau mailing</a></h2>
<table class="table table-striped table-condensed table-hover">
	<thead>
		<th>Nom du mailing</th>
		<th>Status</th>
		<th>Action</th>

	</thead>
	<tbody>
		 <?php foreach ($mailings as $m): ?>			
		 	<tr>
	 			<td><a href="<?php echo Router::url('admin/mailing/editmailing/'.$m->id);?>"><?php echo $m->title; ?></a></td>
	 			<td><?php echo $m->status; ?></td>	 			
		 		<td>
		 			<a href="<?php echo Router::url('admin/mailing/test/'.$a->list_id);?>">Test</a>
		 			<a href="<?php echo Router::url('admin/mailing/editlist/'.$a->list_id);?>">Editer</a>
		 			<a href="<?php echo Router::url('admin/mailing/deletelist/'.$a->list_id);?>">Supprimer</a>
		 		</td>				
					
		 	</tr>
			 </form>
		 <?php endforeach ?>
	</tbody>
</table>