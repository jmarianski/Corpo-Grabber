var url1 = "/corpo-grabber/download/project";
var project;
var selected = 0;
var colors = ["#aaaaff", "#55ffff", "#ff55ff", "#eeee22", "#ffff55"];

var loadFiles = function() {
    project = $("#project").val();
    project += "/web/";
    loadFilesParams(project, url1);
};

var loadFilesParams = function(project, url) {
    var select = document.getElementById("subsite");
    $("#subsite").empty();
    $("button").attr("disabled", "true");
    $.post(url, {"project":project, "mode":"files"}, function(data, status) {
    $("button").not($("#rightbar button")).attr("disabled", null);
        if(data.indexOf("error")!=0) {
            var array = data.split("<BR>");
            for(var i=0; i<array.length; i++) {
                   var opt = document.createElement("option");
                   opt.value= project+array[i];
                   opt.innerHTML = array[i];
                   select.appendChild(opt);
            }
            document.getElementById("load-preview").style.visibility = "visible";
            document.getElementById("button_load").style.visibility = "visible";
            $("#skeleton").html(" ");
        }
        else {
            $("#skeleton").html("Błąd: projekt nie zawiera stron internetowych. \n\
            Prawdopodobnie strona ta nie została pobrana poprawnie.");
            document.getElementById("load-preview").style.visibility = "collapse";
            document.getElementById("button_load").style.visibility = "collapse";
        }
        disturbed();
        $("#preview").css("visibility", "collapse");
        setSize();
    });
};

var loadSkeleton = function() {
    var path = $("#subsite").val();
    reset_buttons();
    $("button").attr("disabled", "true");
    $.post(url1, {"path":path, "mode":"loadSkeleton"}, function(data, status) {
    $("button").not($("#rightbar button")).attr("disabled", null);
        if(data!="error") {
            remove_selection();
            $("#skeleton").html(data);
            $("#skeleton").css("border", "solid 1px black");
            $("div.skeleton div").click(function(e) {
                e.stopPropagation();
                select_branch(this);});
            $("#rightbar button").attr('disabled', false);
        }
        else {
            $("#preview").html("Błąd! Nie ma takiej strony w tym projekcie.");
            $("#rightbar button").attr('disabled', true);
        }

    });
};
var iframe_body = "";
var loadPreview = function() {
    $("#preview").css("visibility", "visible");
    path = $("#subsite").val();
    if(iframe_body=="") {
        $.post(url1, {"path":path, "mode":"loadPreview"}, function(data, status) {
            if(data!="error") {
                show_iframe(data);
            }
            else
                $("#preview").html("Błąd! Nie ma takiej strony w tym projekcie.");
            setSize();
        });
    }
    else {
            setSize();
            onclick_show_set_interactions();
    }
    
};

var set_colors_to_buttons = function() {
    $("#noterow").css("background-color", colors[0]);
    $("#authorrow").css("background-color", colors[1]);
    $("#titlerow").css("background-color", colors[2]);
    $("#daterow").css("background-color", colors[3]);
    $("#textrow").css("background-color", colors[4]);
};

var sendRequest = function() {
    $("button").attr("disabled", "true");
    var data = {};
    data["fields"] = elements;
    data["ignore"] = [];
    data["research"] = {"note": $("#notein").val(),
    "author" : $("#authorin").val(),
    "date" : $("#datein").val(),
    "title" : $("#titlein").val(),
    "text" : $("#textin").val()};
    var json = JSON.stringify(data);
        $.post(url1, {"mode": "savePattern", "project":project, "data":json}, function(data, status) {
        if(data.indexOf("error")!==0) {
            window.open(data);
        }
        else {
            alert(data);
        }
        $("button").attr("disabled", null);
    });
};

var show_iframe = function(data) {
        iframe_body = data;
        var $frame = $('<iframe style="width:100%"  frameBorder="0">');
        $('#preview').html( $frame );
        setTimeout( function() {
                var doc = $frame[0].contentWindow.document;
                var $body = $('body',doc);
                $body.html(data);
        }, 1 );
        onclick_show_set_interactions();
};

var onclick_show_set_interactions = function() {
        $("#preview_button").html("Schowaj");
        $("#preview_button").unbind("click");
        $("#preview_button").click(hidePrev);
};

$("#subsite").change(function(){disturbed();});

var disturbed = function() {
    $("#preview_button").html("Podgląd");
    $("#preview_button").unbind("click");
    $("#preview_button").click(loadPreview);
    iframe_body = "";
};

var hidePrev = function() {
    // $("#preview").html(" ");
    $("#preview").css("visibility", "collapse");
    $("#preview").css("height", "0px");
    $("#preview_button").html("Podgląd");
    $("#preview_button").unbind("click");
    $("#preview_button").click(loadPreview);
    setSize();
};
var setSize = function() {
    var tmpHeight = $(window).height() - $("#contents").offset().top;
    if($("#preview").has("iframe").length>0 && $("#preview").css("visibility")!="collapse") {
        $('#preview iframe').css('height',tmpHeight/2 - 7+"px");
        $("#preview").css("height", tmpHeight/2+"px");
        $('#skeleton').css('height',tmpHeight/2+"px");
    }
    else {
        $('#skeleton').css('height',tmpHeight+"px");
        $("#preview").css("height", "0px");
    }
    $('#contents').css('height',tmpHeight+"px");
};

var select_branch = function(s) {
    change_color_on_select(s);
};

var remove_selection = function() {
    if($("#"+selected).length>0)
        document.getElementById(selected).style.background = null;
    selected = 0;  
};

var change_color_on_select = function(s) {
    if(selecting!=null && valid_place(selecting, s.id)) {
        elements[selecting] = s.id;
        reload_colors();
        toggle_but(selecting);
    }
};

var get_color_select = function(type) {
    switch(type) {
        case "note": return colors[0]; break;
        case "author": return colors[1]; break;
        case "title": return colors[2]; break;
        case "date": return colors[3]; break;
        case "text": return colors[4]; break;
    }
};

var change_color_on_hover = function(elem) {
    reload_colors();
    elem.style.background = hover_color(selecting, elem.id);
};

var reload_colors = function() {
    $("div.skeleton div" ).css("background-color", "");
    for(var key in elements) {
    var color = [];
        for(var key2 in elements) {
            if(elements[key]==elements[key2])
                color.push(get_color_select(key2));
        }
        if(color.length>1) {
            var string = "linear-gradient(to right, ";
            for(var i=0; i<color.length; i++) {
                if(i>0)
                    string +=", ";
                string += color[i];
            }
            string += ")";
            $(document.getElementById(elements[key])).css("background", 
                string);
        }
        else {
            $(document.getElementById(elements[key])).css("background-color", 
                    color[0]);
        }
    }
};


var change_color_on_unhover = function(elem) {       
    reload_colors();
    $( elem )
        .closest( $("div.skeleton div :hover").not($(elem)) )
        .each(function() {
            $(this).css( "background-color", hover_color(selecting, this.id));
        });


};

var apply_hover_effect = function(id) {
    $("#rightbar button:not(#"+id+")").attr('disabled', true);
            $("div.skeleton div").hover(function(e) {
                e.stopPropagation();
                change_color_on_hover(this);
            }, function(e) {
                change_color_on_unhover(this);
            });
};
var disable_hover_effect = function(id) {
    $("#rightbar button:not(#"+id+")").attr('disabled', false);
    $("div.skeleton div").off("mouseenter mouseleave");
};

var hover_color = function(type, id) {
    if(valid_place(type, id))
        return '#99ff88';
    else
        return 'red';
};

var valid_place = function(type, id) {
    if(elements[type]==id)
        return false;
    else if(type=="note" && Object.keys(elements).length===0){
        return true;
    }
    else if(type=="note") {
        for(var el in elements) {
            if(el!== type && elements[el].indexOf(id)!==0)
                return false;
        }
        return true;
    }
    else if(id.indexOf(elements["note"])==0)
        return true;
    return false;
};
var selecting = null;
var elements = {};
var toggle_but = function(id) {
    if (selecting!=null) {
        if(elements[id] == null)
            $("#" + id).html("Zaznacz");
        else
            $("#" + id).html("Edytuj");
        selecting = null;
        disable_hover_effect(id);
    } else {
        selecting = id;
        $("#" + id).html("Anuluj");
        apply_hover_effect(id);
    }
};

var reset_buttons = function() {
    selecting = null;
    elements = {};
    $("#note").html("Zaznacz");
    $("#author").html("Zaznacz");
    $("#date").html("Zaznacz");
    $("#title").html("Zaznacz");
    $("#text").html("Zaznacz");
};

var click_but = function(elem) {
    toggle_but(elem.id);
};

window.onload = function() {setSize();
    $("#rightbar button").attr('disabled', true);};
window.onresize = function() {setSize();};
$("body").css("overflow", "hidden");


$("#submit_load").click(loadFiles);
$("#preview_button").click(loadPreview);
$("#load_tree").click(loadSkeleton);
$("#submit").click(sendRequest);

