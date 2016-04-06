<?php
/**
 * Sample layout.
 */
use Core\Language;

?>
<table class="content">
<tr>
<td>
<div class="page-header">
	<h1><?php echo $data['title'] ?></h1>
</div>
<div id="page-example">
<?=$data['url']?>
</div>
</td>
<td width="20%">
	<table width="100%">
	<tr>
		<td>
		Number of words per chunk
		</td>
		<td>
		<input size=10 id="numWords" value=5>
		</td>
	</tr>
	<tr>
		<td>
		Leaf depth
		</td>
		<td>
		<input size=10 id="leafDepth" value=0>
		</td>
	</tr>
	</table>
	<button id="submit">Send request</button>
</td>
</tr>
</table><?
if(strlen($data['url'])>1) {
	?>
<script>
$(document).ready(function(){
	var sendRequest = function(){
			$("#page-example").html("Loading...");
		var num_words = $("#numWords").val();
		var depth = $("#leafDepth").val();
		$.post("/corpo-grabber/download/preview", {"url":"<?=addslashes($data['url'])?>", "num_words":num_words, "depth":depth}, function(data, status){
			$("#page-example").html(data);
		});
	};
	$("#submit").click(sendRequest); 
	sendRequest();
});
</script>
<? 
}