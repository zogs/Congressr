<div class="page-header">
	<h1>Editer un utilisateur</h1>
</div>

<form class="form-horizontal" action="<?php echo Router::url('admin/users/edit/'.$user->getID()); ?>" method="post">

		
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
	<input type="submit" class="btn btn-primary" value="Envoyer" />
</div>
</form>