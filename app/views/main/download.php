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
	<h1 id="header">Step 1: Downloading</h1>
</div>
<div id="page-content">
    <table id="step1-form" width="100%">
        <tr>
            <td width="20%">Search start</td>
            <td><input id="url" placeholder="eg. http://gazeta.pl"></td>
        </tr>
        <tr>
            <td>Limit downloads to prefix</td>
            <td><input id="prefix" placeholder="eg. http://gazeta.pl/articles/sport/ (can be empty)"></td>
        </tr>
        <tr>
            <td>Limit search to number of pages</td>
            <td><input id="pagelimit" placeholder="eg. 100"></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;">
                <button id="submit-step1">Send request</button>
            </td>
        </tr>
    </table>
    <div id="licznik"></div>
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
    var pagesdownloaded = 0;
    var pagelimit = $("#pagelimit").val();
    var url1 = "/corpo-grabber/download/download";
    var prefix = "";
    var step1 = function() {
        var url = $("#url").val();
        prefix = $("#prefix").val();
        pagelimit = $("#pagelimit").val();
        postAjax(url);
    };
    var step1loop = function(data, status) {
        if(data.length>3) {
            pagesdownloaded++;
            if(pagesdownloaded<pagelimit) {
                var urls = data.split("\n");
                for(var i=0; i<urls.length; i++) {
                    postAjax(urls[i]);
            $("#licznik").html(data);
                }
            }
        }
    };
    
    var postAjax = function(url) {
        $.post(url1, {"url":url, "prefix":prefix}, step1loop);
    };
    var sendRequest = function(){
        $("#page-example").html("Loading...");
        var num_words = $("#numWords").val();
        var depth = $("#leafDepth").val();
        $.post("/corpo-grabber/download/preview", {"url":"<?=addslashes($data['url'])?>", "num_words":num_words, "depth":depth}, function(data, status){
                $("#page-content").html(data);
        });
    };
    $("#submit").click(sendRequest);
    $("#submit-step1").click(step1);
    $("#submit").prop('disabled', true);
</script>
