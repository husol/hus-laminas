$(document).ready(function () {
  getListCart();

  $('#btnUpdateCart').on('click', function () {
    var cartProducts = fetchForm($('#formListCart'));
    var cart = cartProducts.cartProducts;

    localStorage.setItem('cart', JSON.stringify(cart));

    showSuccessBubble("Cập nhật Giỏ hàng thành công.");
    return false;
  });
});

function getListCart(sortField, sortType) {
  if (typeof sortField == 'undefined') {
    sortField = "name";
  }
  if (typeof sortType == 'undefined') {
    sortType = "ASC";
  }

  var sort = {
    field: sortField,
    type: sortType
  };

  // Get cart products from localStorage
  var cartJSON = localStorage.getItem('cart');
  var cartProducts = JSON.parse(cartJSON);
  var dataObj = {
    "cart_products": cartProducts,
    "sort": sort
  };

  callAjax('cart', 'getListCart', dataObj, getListCartCallback);
}

function getListCartCallback(result) {
  if (result !== false) {
    $('#btnSearch').prop("disabled", false);
    return true;
  }
  return false;
}

function removeItemFromCart(id) {
  // Get cart products from localStorage
  var cartJSON = localStorage.getItem('cart');
  var cartProducts = JSON.parse(cartJSON);

  $.each(cartProducts, function (index, product) {
    if (product.id == id) {
      cartProducts.splice(index, 1);
      return false;
    }
  });

  localStorage.setItem('cart', JSON.stringify(cartProducts));

  getListCart();
}

function updateAmount(id) {
  var quantity = $('#product'+id).find("input:first-child").val();
  var price = $('#product'+id).data('price');
  var amount = quantity * price;

  $('#product'+id).find('td:nth-child(6)').html(formatNumber(amount, 0) + ' VND');
}
