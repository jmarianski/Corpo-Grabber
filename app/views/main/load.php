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
		<td width="50%">
			<select style="width: 100%" id="project"><?php
                          foreach($data['projects'] as $project) {
                              ?>
                            <option value="<?=utf8_encode($project)?>"><?=  utf8_encode(substr($project, 4))?></option>
                                <?php
                          }  
			?></select>
		</td>
                <td>
			<button  style="width: 100%" id="submit_load">Wczytaj</button>
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
                    <button id="preview_button"  style="width: 100%" >Podgląd</button>
		</td>
	</tr>
	<tr>
		<td colspan=3 style="text-align:center; visibility: collapse" id="button_load">
			<button id="load_tree">Wczytaj drzewo strony</button>
		</td>
	</tr>
</table>
<div id="preview">
</div>
<div id="skeleton">
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
    var project;
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
                document.getElementById("button_load").style.visibility = "visible";
                $("#preview").html(" ");
            }
            else {
                $("#preview").html("Błąd: projekt nie zawiera stron internetowych. \n\
                Prawdopodobnie strona ta nie została pobrana poprawnie.");
                document.getElementById("load-preview").style.visibility = "collapse";
                document.getElementById("button_load").style.visibility = "collapse";
            }
        });
    };
    
    var loadSkeleton = function() {
        path = $("#subsite").val();
        $.post(url1, {"path":path, "mode":"loadSkeleton"}, function(data, status) {
            if(data!="error") {
                $("#skeleton").html(data);
            }
            else
                $("#preview").html("Błąd! Nie ma takiej strony w tym projekcie.");
                
        });
    };
    var loadPreview = function() {
        path = $("#subsite").val();
        $.post(url1, {"path":path, "mode":"loadPreview"}, function(data, status) {
            if(data!="error") {
                	var $frame = $('<iframe style="width:100%; height:500px;">');
			$('#preview').html( $frame );
			setTimeout( function() {
				var doc = $frame[0].contentWindow.document;
				var $body = $('body',doc);
				$body.html(data);
			}, 1 );
                $("#preview_button").html("Schowaj");
                $("#preview_button").unbind("click");
                $("#preview_button").click(hidePrev);
            }
            else
                $("#preview").html("Błąd! Nie ma takiej strony w tym projekcie.");
                
        });
    };
    
    $("#subsite").change(function() {
        $("#preview_button").html("Podgląd");
        $("#preview_button").unbind("click");
        $("#preview_button").click(loadPreview);
    });
    
    var hidePrev = function() {
        $("#preview").html(" ");
        $("#preview_button").html("Podgląd");
        $("#preview_button").unbind("click");
        $("#preview_button").click(loadPreview);
    };
    
    $("#submit_load").click(loadFiles);
    $("#preview_button").click(loadPreview);
    $("#load_tree").click(loadSkeleton);
</script>
