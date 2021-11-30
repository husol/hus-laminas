$(document).ready(function () {
  $('#btnSearch').on('click', function () {
    if (!validateForm('searchForm')) {
      return false;
    }

    $('#btnSearch').prop("disabled", true);
    getProducts(1);

    return false;
  });

  $('#btnAdd').on('click', function () {
    callAjax('admin/products', 'form', {}, formProductCallback);
  });

  getProducts(1);
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

  callAjax('admin/products', 'getProducts', {
    page: page,
    sort: sort,
    formData: fetchForm($('#searchForm'))
  }, getProductsCallback);
}

function getProductsCallback(result) {
  if (result !== false) {
    $('#btnSearch').prop("disabled", false);
    return true;
  }
  return false;
}

function editProduct(id) {
  callAjax('admin/products', 'form', {idRecord: id}, formProductCallback);
}

function formProductCallback(result) {
  if (result !== false) {
    $('#commonDialog').modal({backdrop: 'static'});

    $("#imgCover").on("click", function () {
      $("#image").trigger("click");
    });
    $("#imgCover2").on("click", function () {
      $("#image2").trigger("click");
    });
    $("#imgCover3").on("click", function () {
      $("#image3").trigger("click");
    });

    $("#image").change(function(e){
      //Validate file type / size
      var validImageTypes = ["image/png", "image/jpeg", "image/gif"];
      var files = e.originalEvent.target.files;
      for (var i = 0, len = this.files.length; i < len; i++) {
        var n = files[i].name,
          t = files[i].type,
          s = files[i].size;
        if (!validImageTypes.includes(t)) {
          $(this).val("");
          showErrorBubble('image', "Supported image formats are PNG, JPEG, JPG, GIF.");
          return false;
        }
        if (s > 1048576) {// Max file size is 1 MB
          $(this).val("");
          showErrorBubble('image', "Maximum size for image is 1 MB.");
          return false;
        }
      }

      previewImage(this, "imgCover");
    });
    $("#image2").change(function(e){
      //Validate file type / size
      var validImageTypes = ["image/png", "image/jpeg", "image/gif"];
      var files = e.originalEvent.target.files;
      for (var i = 0, len = this.files.length; i < len; i++) {
        var n = files[i].name,
          t = files[i].type,
          s = files[i].size;
        if (!validImageTypes.includes(t)) {
          $(this).val("");
          showErrorBubble('image2', "Supported image formats are PNG, JPEG, JPG, GIF.");
          return false;
        }
        if (s > 1048576) {// Max file size is 1 MB
          $(this).val("");
          showErrorBubble('image2', "Maximum size for image is 1 MB.");
          return false;
        }
      }

      previewImage(this, "imgCover2");
    });
    $("#image3").change(function(e){
      //Validate file type / size
      var validImageTypes = ["image/png", "image/jpeg", "image/gif"];
      var files = e.originalEvent.target.files;
      for (var i = 0, len = this.files.length; i < len; i++) {
        var n = files[i].name,
          t = files[i].type,
          s = files[i].size;
        if (!validImageTypes.includes(t)) {
          $(this).val("");
          showErrorBubble('image3', "Supported image formats are PNG, JPEG, JPG, GIF.");
          return false;
        }
        if (s > 1048576) {// Max file size is 1 MB
          $(this).val("");
          showErrorBubble('image3', "Maximum size for image is 1 MB.");
          return false;
        }
      }

      previewImage(this, "imgCover3");
    });

    $('.selectpicker').selectpicker({
      actionsBox: true,
      liveSearch: true,
      multipleSeparator: '; ',
      size: 17,
      noneSelectedText: 'Chọn một loại sản phẩm ...'
    });

    $('#btnSave').on('click', function () {
      if (!validateForm('formProduct')) {
        return false;
      }
      callAjax('admin/products', 'update', {
        formData: fetchForm($('#formProduct'), false, true),
        upload: true
      }, updateProductCallback);
    });
  }
  return false;
}

function updateProductCallback(result) {
  if (result !== false) {
    $('#commonDialog').modal('hide');
    showSuccessBubble('Cập nhật Sản phẩm ' + result.name + ' thành công.');
    getProducts($('.page-item.active > a').html());
  }
  return false;
}

function deleteProduct(id) {
  if (confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
    callAjax('admin/products', 'delete', {
      idRecord: id
    }, deleteProductCallback);
  }
}

function deleteProductCallback(result) {
  if (result !== false) {
    var page = 1;
    if ($('#listProduct > table > tbody > tr').length > 1) {
      page = $('.page-item.active > a').html();
    }
    getProducts(page);
    showSuccessBubble('Xóa Sản phẩm ' + result.name + ' thành công.');
  }
}
