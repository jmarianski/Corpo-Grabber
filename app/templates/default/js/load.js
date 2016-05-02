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
            remove_selection();
            $("#skeleton").html(data);
            $("div.skeleton div").click(function(e) {
                e.stopPropagation();
                select_branch(this);});
            $("#rightbar button").prop('disabled', false);
        }
        else {
            $("#preview").html("Błąd! Nie ma takiej strony w tym projekcie.");
            $("#rightbar button").prop('disabled', true);
        }

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

var remove_selection = function() {
    if($("#"+selected).length>0)
        document.getElementById(selected).style.background = null;
    selected = 0;  
};

var change_color_on_select = function(s) {
    var s_color = '#99ff88';
    if($(s).data("color")=="" || $(s).data("color")=="transparent") {
        // color data is empty
        $(s).data("color", s_color);
        s.style.background = s_color;
    }
    else {
        // there was a color
        // it means we have to make a gradient
        if($(s).data("color")==s_color) {
            $(s).data("color", "");
            s.style.background = null;
        }
        else {
            $(s).data("color", s_color);
            s.style.background = s_color;
        }
    }
};
var change_color_on_hover = function(elem) {
    elem.style.background = "#FF0000";
};

var set_default_color = function(elem) {
    $(elem).css("background-color", $(elem).data("color"));
};



var change_color_on_unhover = function(elem) {        
    $(elem).css("background-color", $(elem).data("color"));

};

var apply_hover_effect = function(elem) {
    $("#rightbar button:not(#"+elem.id+")").prop('disabled', true);
            $("div.skeleton div").hover(function(e) {
                if(e.target.id==this.id)
                    change_color_on_hover(this);
                else
                    set_default_color(this);
            }, function(e) {
                change_color_on_unhover(this);
                if(e.target.id!=this.id)
                    alert("test");
            });
};
var disable_hover_effect = function(elem) {
    $("#rightbar button:not(#"+elem.id+")").prop('disabled', false);
    $("div.skeleton div").off("mouseenter mouseleave");
};

var valid_place = function(id1) {
    
};
var selecting = null;
var elements = [];

var click_but = function(elem) {
    if (selecting!=null) {
        if(elements[elem.id] == null)
            $("#" + elem.id).html("Zaznacz");
        else
            $("#" + elem.id).html("Edytuj");
        selecting = null;
        disable_hover_effect(elem);
    } else {
        selecting = elem.id;
        $("#" + elem.id).html("Anuluj");
        apply_hover_effect(elem);
    }
};

window.onload = function() {setSize();
    $("#rightbar button").prop('disabled', true);};
window.onresize = function() {setSize();};
$("body").css("overflow", "hidden");









