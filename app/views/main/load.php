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
	<h1 id="header">Krok 2: Utwórz wzorzec</h1>
</div>
<div id="page-content">
<table width="100%" id="table">
	<tr>
		<td width="40%">
			Wczytaj projekt:
		</td>
		<td width="60%">
			<select style="width: 100%" id="project"><?php
                          foreach($data['projects'] as $project) {
                              ?>
				<option value="<?=$project?>"><?=substr($project, 4)?></option>
                                <?php
                          }  
			?></select>
		</td>
	</tr>
	<tr>
		<td colspan=2 style="text-align:center">
			<button id="submit_load">Load</button>
		</td>
	</tr>	
</table>
<div id="belowtable">
</div>
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
</table>
<script>
    var url1 = "/corpo-grabber/download/download";

    var postAjax = function(url) {
        $.post(url1, {"url":url, "prefix":prefix}, step1loop);
    };
    $("#submit").prop('disabled', true);
</script>
