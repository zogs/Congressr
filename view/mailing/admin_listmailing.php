<a href="<?php echo Router::url('admin/mailing/index');?>">Retour</a>
<h2>Mailing lists existantes <a class="btn btn-info" href="<?php echo Router::url('admin/mailing/editlist');?>">Créer une nouvelle liste</a></h2>
<table class="table table-striped table-condensed table-hover">
	<thead>
		<th>Nom de la liste</th>
		<th>Nombre d'adresse</th>
		<th>Action</th>

	</thead>
	<tbody>
		 <?php foreach ($lists as $a): ?>
			
			<form class="form-table " action="<?php echo Router::url('admin/articles/index/'.$type);?>" method="POST">
		 	<tr>
	 			<td><a href="<?php echo Router::url('admin/mailing/editlist/'.$a->list_id);?>"><?php echo $a->name; ?></a></td>
	 			<td><?php echo $a->count; ?></td>	 			
		 		<td>
		 			<a href="<?php echo Router::url('admin/mailing/editlist/'.$a->list_id);?>">Editer</a>
		 			<a href="<?php echo Router::url('admin/mailing/deletelist/'.$a->list_id);?>">Supprimer</a>
		 		</td>				
					
		 	</tr>

		 	<?php echo $this->Form->input('list_id','hidden',array('value'=>$a->list_id)) ;?>
		 	<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
			 </form>
		 <?php endforeach ?>
	</tbody>
</table>