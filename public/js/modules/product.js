$(document).ready(function () {
  if ($('#listProduct').length > 0) {
    getProducts(1);
  }

  $('#searchProduct').on('keyup', debounce(function () {
    setQueryStringParam('keyword', $(this).val());

    getProducts(1);
  }, 1700));

  $("#btnFilterPrice").on('click', function () {
    getProducts(1);
  });

  $('#btnAddToCart').on('click', function () {
    var productID = $(this).data('id');
    var cartJSON = localStorage.getItem('cart');
    var cartProducts = JSON.parse(cartJSON);

    if (cartProducts == null) {
      cartProducts = [];
    }

    var existed = false;
    $.each(cartProducts, function(index, obj) {
      if (obj.id == productID) {
        obj.quantity += 1;
        existed = true;
        return false;
      }
    });

    if (!existed) {
      cartProducts.push({id: productID, quantity: 1})
    }

    localStorage.setItem('cart', JSON.stringify(cartProducts));
    loadCartBadge();
  });
});

function getProducts(page, sortField, sortType) {
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

  var dataObj = fetchForm($("#formFilterPrice"));
  dataObj.page = page;
  dataObj.sort = sort;

  var categoryKind = $_GET('category_kind');
  if (categoryKind != null) {
    dataObj['categoryKind'] = categoryKind;
  }

  var categoryID = $_GET('category_id');
  if (categoryID != null) {
    dataObj['categoryID'] = categoryID;
  }

  var keyword = $_GET('keyword');
  if (keyword != null) {
    dataObj['keyword'] = keyword;
  }

  callAjax('products', 'getProducts', dataObj, getProductsCallback);
}

function getProductsCallback(result) {
  if (result !== false) {
    $('#btnSearch').prop("disabled", false);
    return true;
  }
  return false;
}
