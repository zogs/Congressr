

<div class="error_page">
	<?php 

	echo Session::flash();
	?>	
	<div class="sign">
		<div class="oups"><table><tr><td><?php echo $oups ?></td></tr></table></div>
	</div>
	<div class="bubble">
		<?php echo $message; ?>		
	</div>
</div>