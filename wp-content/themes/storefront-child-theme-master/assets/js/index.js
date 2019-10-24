window.onload = function(){
  displayDateAndPrice();
}

function displayDateAndPrice(){
  var element = document.getElementsByClassName('groupbuy-time');
  var append =  document.getElementsByClassName('get-price-and-date');

  for (i = element.length -1; i>=0; i--){
    element[i].firstChild.remove();
    var text = element[i].innerText += " left";
    jQuery(append[i]).append('<span>'+text+'</span');
    element[i].remove();
  }
}