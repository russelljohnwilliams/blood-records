window.onload = function(){
  displayDateAndPrice();
  remove('price');
  remove('woocommerce-loop-product__title');
  p()
}

function remove(object){
  var objectBeGone = document.getElementsByClassName(object);
  jQuery(objectBeGone).remove();
}

function displayDateAndPrice(){
  var element = document.getElementsByClassName('groupbuy-time');
  var append =  document.getElementsByClassName('get-price-and-date');
  for (i = element.length -1; i>=0; i--){
 
    element[i].firstChild.remove();
    var text = element[i].innerText += " left";
    text = text.replace("D", " d");
    jQuery(append[i]).append('<span>'+text+'</span');
    element[i].remove();
  }
}

function p(){
  var p = jQuery('.product > p:eq(4)').nextAll("p")
  p.remove()
}



