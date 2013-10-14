<a href="<?php echo Router::url('admin/mailing/listmailing');?>">Retour à la gestion des listes</a>

<div class="clearfix">
	
	<h3>Editer une mailing list</h3>
	<form class="form" action="<?php echo Router::url('admin/mailing/editlist');?>" method="POST">

		<?php echo $this->Form->input('list_id','hidden') ;?>
		<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>

		<?php echo $this->Form->input('name','Nom de la liste'); ?>

		<?php echo $this->Form->input('emails','Adresses emails',array('type'=>'textarea','placeholder'=>'Entrer ici la liste des adresses emails à ajouter ou à sauvegarder à la mailing list. Séparez les adresses d\'un espace ou d\'un point-virgule','rows'=>10)) ;?>

		<?php echo $this->Form->input('submit','',array('type'=>'submit',"value"=>"Sauvegarder",'class'=>'btn btn-large btn-primary')) ;?>

	</form>
</div>


<?php if(!empty($list->users)): ?>
<div class="clearfix">
	<table class="table table-striped table-condensed table-hover">
		<thead>
			<th>email</th>
			<th>prenom</th>
			<th>nom</th>
			<th>insti.</th>
			<th>action</th>
		</thead>
		<tbody>
			 <?php foreach ($list->users as $u): 

			 ?>
				
				<tr>
					<td><?php echo $u->email;?></td>
					<td><?php echo $u->prenom;?></td>
					<td><?php echo $u->nom;?></td>
					<td><?php echo $u->institution;?></td>
					<td>
						<a href="<?php echo Router::url('admin/mailing/deleteEmail/'.$u->list_id.'/'.$u->id);?>">Supprimer</a>
					</td>
				</tr>
			 <?php endforeach ?>
		</tbody>
	</table>		
</div>
<?php endif;?>