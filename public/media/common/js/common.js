function toogleVisibility(element,dur) {
  if($(element).css("display") == "none") $(element).show(dur);
  else $(element).hide(dur)
}

function jumpFromSelect(location, select) {
    if(select.options[select.selectedIndex].value != "") {
        window.location.href = location+select.options[select.selectedIndex].value;
    }
}
