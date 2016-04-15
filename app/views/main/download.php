<?php
/**
 * Sample layout.
 */
use Core\Language;

?>
<div class="page-header">
	<h1 id="header">Krok 1: Pobieranie</h1>
</div>
<div id="page-content">
    <table id="step1-form" width="100%">
        <tr>
            <td width="20%">Nazwa projektu</td>
            <td><input id="path" placeholder="Domyślna nazwa: <?=date("Y-m-d");?> <site address>"></td>
        </tr>
        <tr>
            <td width="20%">Adres strony</td>
            <td><input id="url" placeholder="np. http://wiadomosci.dziennik.pl"></td>
        </tr>
        <tr>
            <td>Ogranicz czas pobierania (w sekundach)</td>
            <td><input id="exec_time" placeholder="np. 100"></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;">
                <button id="submit-step1">Send request</button>
            </td>
        </tr>
    </table>
    <div id="Licznik"></div>
    <div id="Log"></div>
</div>
<script>
    var url1 = "/corpo-grabber/download/download_httrack";
    var counter = 0;
    var date = new Date();
    var countdown;
    var step1 = function() {
        var url = $("#url").val();
        postAjax(url);
    };
    var postAjax = function(url) {
        var exec_time = $("#exec_time").val();
        var path = $("#path").val();
        $("#Log").html("Loading...");
        $.post(url1, {"url":url, "exec_time":exec_time, "path":path}, doAfterDownload);
        timer();
    }; 
    var timer = function() {
        time_begin = date.getTime();
        counter = 0;
        $("#Licznik").html("Czas: 0");
            countdown = setInterval(function() {
                counter = counter + 1;
                $("#Licznik").html("Czas: " + counter);
            }, 1000);
    }; 
    var load = function(string) {
        alert(string);
    };
    var doAfterDownload = function(data, status) {
        // first line is project name
    var firstLine = data.substr(0, data.indexOf("<BR>"));
    var rest = data.substr(data.indexOf("<BR>")+4);
        $("#Log").html("Zapisano w " + firstLine+"<BR>"
        +"Poniżej znajdują się logi programu httrack<BR>"+rest
        +"<form method=POST action=\"load\"><input type=hidden name=url value=\""+firstLine+"\"><input type=submit value=\"Idź do edycji\"></form>");
        clearInterval(countdown);
    };
    $("#submit-step1").click(step1);
</script>
