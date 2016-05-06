<?php
/**
 * Sample layout.
 */
use Core\Language;

?>
<table class="content"  style="padding: 5px">
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
                            <option value="<?=utf8_encode($project)?>"<?php
                                    if(strpos($project, "tmp/".$_POST['url'])===0) {
                                    echo " selected";}
                                    ?>><?=  utf8_encode(substr($project, 4))?></option>
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
	<tr style="text-align:center; visibility: collapse" id="button_load">
		<td colspan=3>
			<button id="load_tree">Wczytaj drzewo strony</button>
		</td>
	</tr>
</table>
<div id="contents">
    <div id="preview" style="border:solid black 1px; visibility:collapse">
    </div>
    <div id="skeleton" style="overflow:scroll; overflow-x: hidden">
    </div>
</div>
</div>
</td>
<td width="20%" id="rightbar">
	<table width="100%">
	<tr>
		<td>
		Ile notek:
		</td>
		<td>
		<select id="numWords">
                    <option value="01">0..1</option>
                    <option value="0n">0..n</option>
                    <option value="11">1..1</option>
                    <option value="1n">1..n</option>
                </select>
		</td>
	</tr>
	<tr id="noterow">
		<td>
		Wpis:
		</td>
		<td>
                    <button id="note" onclick="click_but(this)">Zaznacz</button>
		</td>
	</tr>
	<tr id="authorrow">
		<td>
		Autor:
		</td>
		<td>
                    <button id="author" onclick="click_but(this)">Zaznacz</button>
		</td>
	</tr>
	<tr id="titlerow">
		<td>
		Tytuł:
		</td>
		<td>
                    <button id="title" onclick="click_but(this)">Zaznacz</button>
		</td>
	</tr>
	<tr id="daterow">
		<td>
		Data:
		</td>
		<td>
                    <button id="date" onclick="click_but(this)">Zaznacz</button>
		</td>
	</tr>
	<tr id="textrow">
		<td>
		Treść wpisu:
		</td>
		<td>
                    <button id="text" onclick="click_but(this)">Zaznacz</button>
		</td>
	</tr>
	</table>
	<button id="submit">Send request</button>
</td>
</tr>
</table>
<script src="/corpo-grabber/app/templates/default/js/load.js" type="text/javascript"></script>
<?php
if(strlen($_POST['url'])>0) {
    ?><script>
    window.onload = function() {
        loadFilesParams("<?="tmp/".$_POST['url']."/web/"?>", url1);
    };
</script>
<?php
}
    
