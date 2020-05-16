var acc = document.getElementsByClassName("flipicon");
var clock = document.getElementById('clock');
let sharedLink = "";
for (i = 0; i < acc.length; i++) {
    acc[i].addEventListener("click", function() {
        this.classList.toggle("active");
        var panel = this.previousElementSibling;
        if (panel.style.maxHeight!="0px"){
            panel.style.maxHeight = "0px";
        } else {
            panel.style.maxHeight = panel.scrollHeight + "px";
        }
    });
}
let backrounds = document.getElementsByClassName("traindiv");
function time() {
    var d = new Date();
    let h = `${d.getHours()}`.padStart(2, '0');
    let m = `${d.getMinutes()}`.padStart(2, '0');
    let s = `${d.getSeconds()}`.padStart(2, '0');
    clock.textContent = h + ":" + m + ":" + s;
}
setInterval(time, 1000);
function sharePage(){
navigator.share({
    title: document.title,
    text: document.title,
    url: document.URL,
})}
function copyLink() {
    var copyTextarea = document.querySelector('.js-copytextarea');
    copyTextarea.focus();
    copyTextarea.select();
    try {
        var successful = document.execCommand('copy');
        var msg = successful ? 'successful' : 'unsuccessful';
        console.log('Copying text command was ' + msg);
    } catch (err) {
        console.log('Oops, unable to copy');
    }
}
if($(window).width()>799){
    document.title="NexTRAIN";
}
let as = document.querySelectorAll("a");
function addCss (fileName) {
    var link = $("<link />",{
        rel: "stylesheet",
        type: "text/css",
        href: fileName
    });
    $('head').append(link);
}
function getLink() {
    xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            alert(this.responseText);
            document.getElementById("address").innerHTML=this.responseText;
        }
        else {
            document.getElementById("address").innerHTML="Fehler beim generieren...";
        }
    };
    xmlhttp.open("GET", "https://nextrain.finax1.at/getlink/"+encodeURIComponent(window.btoa(document.URL)), true);
    xmlhttp.send();
}
function changeView(evt, name) {
    let i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tabs");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" activeT", "");
    }
    document.getElementById(name).style.display = "block";
    evt.currentTarget.className += " activeT";
}
function loading() {
    $("<style>#loader{visibility: visible; opacity: 1;}#headerd, #achtung, #estimatedInfo,#search, #infodiv, #globalMessages, #NXTresult, #erroresult, #specialMessages, #trainProperties {\n" +
        "    filter: opacity(0.4);\n" +
        "}</style>").appendTo("body");
    addCss('../css/loading.css');
}
function openshare() {
    document.getElementById("sharenex").classList.add("activeshare");
    let elms = document.getElementsByClassName("opacityable");
    for(let i=0; i<elms.length;i++){
        elms[i].style.filter="opacity(0.4)";
    }
}
function closeshare() {
    document.getElementById("sharenex").classList.remove("activeshare");
    let elms = document.getElementsByClassName("opacityable");
    for(let i=0; i<elms.length;i++){
        elms[i].style.filter="none";
    }
}
[].forEach.call(document.querySelectorAll("a"), function (elm) {
   elm.addEventListener('click', loading);
});
window.onbeforeunload=loading;
if (!!window.performance && window.performance.navigation.type === 2) {
    location.reload();
}
setTimeout(function () {
    let viewheight = $(window).height();
    let viewwidth = $(window).width();
    let viewport = document.querySelector("meta[name=viewport]");
    viewport.setAttribute("content", "height=" + viewheight + ", width=" + viewwidth + ", initial-scale=1.0");
}, 300);
$(document).ready(function () {
    'use strict';
    var orientationChange = function () {
        var $element = $('.selector');
        $element.css('height', '100vh'); // Change this to your own original vh value.
        $element.css('height', $element.height() + 'px');
    };
    var s = screen;
    var o = s.orientation || s.msOrientation || s.mozOrientation;
    o.addEventListener('change', function () {
        setTimeout(function () {
            orientationChange();
        }, 250);
    }, false);
    orientationChange();
});
document.getElementById("loginBTN").click();