


<h2>
	<?php echo count($users);?> Utilisateurs 
	<a class="btn btn-info" href="<?php echo Router::url('admin/reviewer/create');?>">Créer un reviewer</a>
	<a class="btn btn-warning" href="<?php echo Router::url('admin/users/createUser/redactor');?>">Créer un auteur</a>
	<a class="btn btn-danger" href="<?php echo Router::url('admin/users/createUser/admin');?>">Créer un admin</a>
</h2>
<form id="tsearch-form" action="" method="post"><input id="tsearch-query" type="text" /> <input type="submit" value="Search" /> <input id="tsearch-clear" type="button" value="Clear" /><span id="tsearch-results"></span></form>
<table class="table table-striped table-hover tableSearch tableSort">
	<thead>
		<th>Login</th>
		<th>Role</th>
		<th>Prenom</th>
		<th>Nom</th>				
		<th>Pays</th>
		<th>ID</th>
		<th>Action</th>
	</thead>
	<tbody>
		 <?php foreach ($users as $user): ?>
		 	<tr>
			<form class="form " action="<?php echo Router::url('admin/users/index');?>" method="POST">
	 			<td><?php echo $user->login ?></td>
	 			<td>
	 				<span class="label <?php 
	 								if($user->valid==0) echo '';
	 								elseif($user->getRole()=="admin") echo "label-inverse";
	 								elseif($user->getRole()=="chairman") echo "label-primary";
	 								elseif($user->getRole()=="reviewer") echo "label-info";
	 								elseif($user->getRole()=="redactor") echo "label-success";
	 								else echo 'label-warning';
							?>"><?php echo $user->getRole();?></span>
	 			</td>
		 		<td><?php echo $user->prenom; ?></td>
		 		<td><?php echo $user->nom;?></td>				
				<td><?php echo $user->CC1;?></td>	
				<td><?php echo $user->user_id;?></td>	 		
		 		<td>
					<?php echo $this->Form->_select('role',array('redactor'=>'redactor','reviewer'=>'reviewer','chairman'=>'chairman','admin'=>'admin'),array('default'=>$user->getRole(),'style'=>"display:inline;width:auto;") );?>
					<input type="Submit"  class="submitAsLink" value="Sauver" />
		 			<a href="<?php echo Router::url('admin/users/edit/'.$user->user_id); ?>" >Editer</a>
		 			<a onclick="return confirm('Voulez-vous vraiment supprimer cet élément ?');" href="<?php echo Router::url('admin/users/delete/'.$user->getID()); ?>" >Supprimer</a>
		 		</td>
		 	<?php echo $this->Form->input('user_id','hidden',array('value'=>$user->getID())) ;?>
		 	<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>
			 </form>
		 	</tr>
		 <?php endforeach ?>
	</tbody>
</table>

