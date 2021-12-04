$(document).ready(function () {
  getListCart();

  $('#btnUpdateCart').on('click', function () {
    var cartProducts = fetchForm($('#formListCart'));
    var cart = cartProducts.cartProducts;

    localStorage.setItem('cart', JSON.stringify(cart));

    loadCartBadge();
    showSuccessBubble("Cập nhật Giỏ hàng thành công.");
    return false;
  });

  $('#btnConfirmCart').on('click', function () {
    callAjax('cart', 'confirm', {data: fetchForm($('#formListCart'))}, confirmCartCallback);

    return false;
  });
});

function confirmCartCallback(result) {
  if (result !== false) {
    showModal('commonDialog');

    $('#btnSave').on('click', function () {
      callAjax('cart', 'save', {formData: fetchForm($('#transactionForm'))}, saveCartCallback);

      return false;
    });

    return false;
  }
}

function saveCartCallback(result) {
  if (result !== false) {
    hideModal("commonDialog");
    showSuccessBubble('Xác nhận đơn hàng thành công. Chúng tôi sẽ liên hệ bạn trong thời gian sớm nhất.');
    localStorage.removeItem('cart');

    setTimeout(function () {
      window.location.reload(true);
    }, 3000);

    return false;
  }
}

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
  loadCartBadge();
}

function updateAmount(id) {
  var quantity = $('#product'+id).find("input:first-child").val();
  var price = $('#product'+id).data('price');
  var amount = $('#product'+id).find('td:nth-child(6)').html().replace( /\D/g, '');
  var totalAmount = $('#product'+id).closest('table').find('tfoot > tr > th:last-child > span').html().replace( /\D/g, '');

  totalAmount -= amount;
  amount = quantity * price;
  totalAmount += amount;

  $('#product'+id).find('td:nth-child(6)').html(formatNumber(amount, 0) + ' VND');
  $('#product'+id).closest('table').find('tfoot > tr > th:last-child > span').html(formatNumber(totalAmount, 0) + ' VND');
}
