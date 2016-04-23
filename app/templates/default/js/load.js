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
    var path = $("#subsite").val();
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
    var tmpHeight = $(window).height() - $("#contents").offset().top;
    $('#contents').css('height',tmpHeight+"px");
};

$("#submit_load").click(loadFiles);
$("#preview_button").click(loadPreview);
$("#load_tree").click(loadSkeleton);

var select_branch = function(s) {
    change_color_on_select(s);
};

var change_color_on_select = function(s) {
    var s_color = '#99ff88';
    if(selected==0) {
        selected = s.id;
        s.style.background = s_color;
    }
    else if(selected==s.id) {
        s.style.background = null;
        selected = 0;
    }
    else {
        document.getElementById(selected).style.background = null;
        selected = s.id;
        s.style.background = s_color;
    }
};

window.onload = function() {setSize();};
window.onresize = function() {setSize();};
$("body").css("overflow", "hidden");
