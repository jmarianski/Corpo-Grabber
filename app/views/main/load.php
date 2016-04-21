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
		<td width="60%" colspan="2">
			<select style="width: 100%" id="project"><?php
                          foreach($data['projects'] as $project) {
                              ?>
				<option value="<?=$project?>"><?=substr($project, 4); //removing "tmp/" prefix?></option>
                                <?php
                          }  
			?></select>
		</td>
	</tr>
	<tr id="load-preview" style="visibility: collapse">
		<td width="40%">
			Wybierz podstronę:
		</td>
		<td width="50%">
                    <select style="width: 100%" id="subsite">
                    </select>
		</td>
		<td width="10%">
                    <button id="preview" width="100%">Podgląd</button>
		</td>
	</tr>
	<tr>
		<td colspan=3 style="text-align:center">
			<button id="submit_load">Wczytaj projekt</button>
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
    var url1 = "/corpo-grabber/download/project";
    
    var loadFiles = function() {
        project = $("#project").val();
        project += "/web/";
        var select = document.getElementById("subsite");
        $("#subsite").empty();
        $.post(url1, {"project":project, "mode":"files"}, function(data, status) {
            if(data!=="error") {
                var array = data.split("<BR>");
                for(var i=0; i<array.length; i++) {
                       var opt = document.createElement("option");
                       opt.value= project+array[i];
                       opt.innerHTML = array[i];
                       select.appendChild(opt);
                }
                document.getElementById("load-preview").style.visibility = "visible";
            }
            else {
                alert("Błąd: projekt nie zawiera stron internetowych. Prawdopodobnie strona ta nie została pobrana poprawnie.");
                document.getElementById("load-preview").style.visibility = "collapse";
            }
        });
    };
    
    var loadSkeleton = function() {
        
    };
    var loadPreview = function() {
        
    };
    
    $("#submit_load").click(loadFiles);
</script>
