jQuery(document).ready(function myFunction() {
    var input = document.getElementById("overview_employer_image")
    var href = myScript.pluginsUrl + '/inpsyde-employees/inc/Base/Images/avatar.jpg';
    if(input.value.length == 0)
        input.value = href;
  }
);