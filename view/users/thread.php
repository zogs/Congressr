<?php echo Session::flash(); ?>
<div id="thread">
	<div class="left">
		<div class="thread-user">
			<img src="<?php echo Router::webroot($user->avatar);?>" alt="Avatar" />
			<span class='thread-login'><?php echo $user->login;?></span>
		</div>

		<div class="thread-user-protests">
			<span class="thread-left-title">Protests</span>
			<ul>
				<?php
					foreach($protests as $p){

						echo '<li><a href="'.Router::url("manifs/view/".$p->manif_id).'" >'.$p->nommanif.'</a></li>';

					}
				?>
			</ul>
		</div>


	</div>

	<div class="middle">

		<div class="thread-timeline">

			<?php

			echo $this->request('comments','show',array('user',$user->user_id));


			?>

		</div>


	</div>

	<div class="right">

	</div>



</div>


