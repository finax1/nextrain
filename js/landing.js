xmlhttp = new XMLHttpRequest();
xmlhttp.onreadystatechange = function () {
    if (this.readyState === 4 && this.status === 200) {
        //document.querySelector("html").innerHTML = this.responseText;
        document.open();
        document.write(this.responseText);
        document.close();
    }
};
xmlhttp.open("GET", "https://nextrain.finax1.at/ls.php", true);
xmlhttp.send();