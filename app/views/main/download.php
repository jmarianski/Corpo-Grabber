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
        <tr style="visibility: collapse">
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
    var url1 = "/corpo-grabber/download/download_wget";
    var url2 = "/corpo-grabber/download/download_wget_status";
    var counter = 0;
    var date = new Date();
    var countdown;
    var exec_time;
    var step1 = function() {
        var url = $("#url").val();
        postAjax(url);
    };
    var postAjax = function(url) {
        exec_time = $("#exec_time").val();
        var path = $("#path").val();
        $("#Log").html("Ładowanie...");
        $.post(url1, {"url":url, "exec_time":exec_time, "path":path}, doAfterDownload);
        timer();
    }; 
    
    
    var getLogs = function(path) {
        setTimeout(function() {
             $.post(url2, {"path":path}, function(data) {
                 $("#Log").html(data);
                 getLogs(path);
             });
            }, 1000);
    }; 
    
    
    var timer = function() {
        time_begin = date.getTime();
        counter = 0;
        $("#Licznik").html("Czas: 0");
            countdown = setInterval(function() {
                counter = counter + 1;
                if(exec_time<counter)
                    $("#Log").html("Przetwarzanie (może trwać 3x czasu pobierania)...");
                $("#Licznik").html("Czas: " + counter);
            }, 1000);
    }; 
    var load = function(string) {
        alert(string);
    };
    var doAfterDownload = function(data, status) {

        var code = data.substr(0, data.indexOf(" "));
        var value = data.substr(data.indexOf(" ")+1);
                clearInterval(countdown);
        if(code==="OK") {
            $("#Licznik").html("Rozpoczęto pobieranie, jak chcesz rozpocząć formatowanie, kliknij w link"
            +"<form method=POST action=\"load\"><input type=hidden name=url value=\""+value+"\"><input type=submit value=\"Idź do edycji\"></form>");
            getLogs(value);
        } else {
            $("#Log").html("Niepowodzenie! " + data);
        }
    };
    $("#submit-step1").click(step1);
</script>
