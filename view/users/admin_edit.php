<div class="page-header">
	<h1>Editer un utilisateur</h1>
</div>
<a href="<?php echo Router::url('admin/users/index');?>">Revenir à la liste</a>
<form class="form form-horizontal" action="<?php echo Router::url('admin/users/edit/'.$user->getID()); ?>" method="post">

		
<table class="table table-striped">
<tbody>
	 <?php foreach ($user as $k => $v): ?>
	 	<tr>
 			<?php echo $this->Form->input($k,$k,array('value'=>$v)) ;?>
	 	</tr>
	 <?php endforeach ?>
</tbody>
</table>




<?php echo $this->Form->input('token','hidden',array('value'=>Session::token())) ;?>

<div class="actions">
	<input type="submit" class="btn btn-large btn-warning" value="Sauvergarder les changements" />
</div>
</form>