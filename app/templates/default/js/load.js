var url1 = "/corpo-grabber/download/project";
var project;
var selected = 0;
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
        setSize();
    });
};

var loadSkeleton = function() {
    path = $("#subsite").val();
    $.post(url1, {"path":path, "mode":"loadSkeleton"}, function(data, status) {
        if(data!="error") {
            $("#skeleton").html(data);
            $("div.skeleton div").click(function(e) {
                e.stopPropagation();
                select_branch(this);});
        }
        else
            $("#preview").html("Błąd! Nie ma takiej strony w tym projekcie.");

    });
};
var loadPreview = function() {
    path = $("#subsite").val();
    $.post(url1, {"path":path, "mode":"loadPreview"}, function(data, status) {
        if(data!="error") {
                    var $frame = $('<iframe style="width:99%; height:500px;">');
                    $('#preview').html( $frame );
                    setTimeout( function() {
                            var doc = $frame[0].contentWindow.document;
                            var $body = $('body',doc);
                            $body.html(data);
                            $frame[0].wrap("<center></center>");
                    }, 1 );
            $("#preview_button").html("Schowaj");
            $("#preview_button").unbind("click");
            $("#preview_button").click(hidePrev);
        }
        else
            $("#preview").html("Błąd! Nie ma takiej strony w tym projekcie.");
        setSize();

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
    setSize();
};
var setSize = function() {
    tmpHeight = $(window).height() - $("#contents").offset().top;
    $('#contents').css('height',tmpHeight+"px");
};

$("#submit_load").click(loadFiles);
$("#preview_button").click(loadPreview);
$("#load_tree").click(loadSkeleton);

var select_branch = function(divider) {
    if(selected==0) {
        selected = divider.id;
        divider.style.background = '#00ff00';
    }
    else if(selected==divider.id) {
        divider.style.background = null;
        selected = 0;
    }
    else {
        document.getElementById(selected).style.background = null;
        selected = divider.id;
        divider.style.background = '#00ff00';
    }
};

window.onload = function() {setSize();};
window.onresize = function() {setSize();};
$("body").css("overflow", "hidden");
