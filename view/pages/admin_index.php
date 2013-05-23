<div class="page-header">
	<h1>Pages du site</h1>
	<a href="?lang=fr">Francais</a> <a href="?lang=en">English</a>
		<br />
	

</div>


<table class="table table-striped">
	<thead>
		<th>Titre</th>
		<th>En ligne ?</th>
		<th>Menu</th>		
		<th>Action</th>
		<th>Date</th>
		<th>ID</th>
	</thead>
	<tbody>

		 <?php foreach ($pages as $k => $v): ?>
			<form class="form " action="<?php echo Router::url('admin/pages/index');?>" method="POST">
		 	<tr>
	 			<td><?php echo $v->title ?></td>
		 		<td>
		 			<span class="label<?php echo ($v->online==1)? ' label-success' : ''; ?>"><?php echo ($v->online==1)? 'En ligne' : 'Hors ligne'; ?></span>
		 			<?php echo $this->Form->_input('online','',array('type'=>'checkbox','value'=>$v->online)) ;?>
		 		</td>

		 		<td>
		 			<span class="label<?php echo ($v->menu==1)? ' label-success' : ''; ?>">Menu</span>
		 			<?php echo $this->Form->_input('menu','',array('type'=>'checkbox','value'=>$v->menu)) ;?>
		 		</td>
		 		
		 		<td>
					<input type="Submit"  class="submitAsLink" value="Sauver" />

		 			<a href="<?php echo Router::url('admin/contents/edit/'.$v->content_id.'?lang='.$lang); ?>" >Editer</a>

		 			<a onclick="return confirm('Voulez-vous vraiment supprimer cet élément ?');" href="<?php echo Router::url('admin/contents/delete/'.$v->id); ?>" >Supprimer</a>


		 		</td>
		 		<td><?php echo $v->date;?></td>
		 		<td><?php echo $v->id ?></td>
		 	</tr>
		 	<?php echo $this->Form->input('id','hidden',array('value'=>$v->id)) ;?>
		 	<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
			 </form>
		 <?php endforeach ?>
	</tbody>
</table>

	


</form>
<a href="<?php echo Router::url('admin/contents/edit?type=page'); ?>" class="btn btn-primary"> Ajouter une page</a>