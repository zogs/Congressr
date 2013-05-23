<p>
	<span style="color:grey;font-size:small">Erreur <?php echo $error->code;?></span>
	<h3><?php echo $error->msg;?></h3>
	<br />
	<h4><span><?php echo $error->file;?></span>  <span style="color:blue">line <?php echo $error->line;?></span></h4>
	<br />

</p>

<p></p>
<pre><?php print_r($error->context);?></pre>