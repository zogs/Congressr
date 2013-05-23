<div class="page-header">
	<h1>Editer une page</h1>
</div>

<form class="form-horizontal" action="<?php echo Router::url('admin/pages/edit/'.$id); ?>" method="post">

<?php echo $this->Form->input('slug','Slug de la page');  ?>
<?php echo $this->Form->input('id','hidden');  ?>

<div class="control-group">
	<label for="" class="control-label">Contenus</label>
	<div class="controls">
		
		<table class="table table-striped">
			<tbody>
				 <?php foreach ($contents as $k => $v): ?>
				 	<tr>
			 			<td><?php echo $v->title ?></td>
				 		<td><span class="label<?php echo ($v->online==1)? ' label-success' : ''; ?>"><?php echo ($v->online==1)? 'En ligne' : 'Hors ligne'; ?></span></td>
				 		<td><?php echo Conf::$languageCodes[$v->lang];?></td>
				 		<td>
				 			<a href="<?php echo Router::url('admin/contents/edit/'.$v->id); ?>" >Editer</a>

				 			<a onclick="return confirm('Voulez-vous vraiment supprimer cet élément ?');" href="<?php echo Router::url('admin/contents/delete/'.$v->id); ?>" >Supprimer</a>
				 		</td>
				 		<td><?php echo $v->date;?></td>
				 		<td><?php echo $v->id ?></td>
				 	</tr>
				 <?php endforeach ?>
			</tbody>
		</table>

		<a href="<?php echo Router::url('admin/contents/edit?context_id='.$id.'&context_type=page');?>">Ajouter un contenu à la page</a>

	</div>
</div>
		
<?php echo $this->Form->input('online','En ligne',array("type"=>"checkbox")); ?>
<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>

<div class="actions">
	<input type="submit" class="btn btn-primary" value="Envoyer" />
</div>
</form>