function populateOverlay(name, description, github, Linkedin, xing, facebook) {

    var overlay = document.getElementById("info");
    overlay.style.display = "block";
    overlay.innerHTML = "<div>" + name + "</div><div>" + description + "</div><div>" + github + "</div><div>" + Linkedin + " </div><div>" + xing + "</div><div>" + facebook + "</div>";

}

function off() {
    var overlay = document.getElementById("info");
    overlay.style.display = "none";
}