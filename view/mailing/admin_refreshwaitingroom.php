<div class="container">
	<?php echo Session::flash(); ?>

	<script type="text/javascript">
	setTimeout(refreshSending, 60000); 

	function refreshSending(){
		window.location.reload(1);
	}
	</script>
</div>