<?php
/**
 * Sample layout.
 */
use Core\Language;

?>
<div class="page-header">
	<h1 id="header">Step 1: Downloading</h1>
</div>
<div id="page-content">
    <table id="step1-form" width="100%">
        <tr>
            <td width="20%">Search URL</td>
            <td><input id="url" placeholder="eg. http://gazeta.pl"></td>
        </tr>
        <tr>
            <td>Limit search to the amount of time (in seconds)</td>
            <td><input id="exec_time" placeholder="eg. 100"></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;">
                <button id="submit-step1">Send request</button>
            </td>
        </tr>
    </table>
    <div id="licznik"></div>
</div>
<script>
    var url1 = "/corpo-grabber/download/download_httrack";
    var step1 = function() {
        var url = $("#url").val();
        postAjax(url);
    };
    var postAjax = function(url) {
        var exec_time = $("#exec_time").val();
        $("#licznik").html("Loading...");
        $.post(url1, {"url":url, "exec_time":exec_time}, doAfterDownload);
    };
    
    var doAfterDownload = function(data, status) {
        $("#licznik").html(data);
    };
    $("#submit-step1").click(step1);
</script>
