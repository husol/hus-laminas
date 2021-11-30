$(document).ready(function () {
  $('#btnSearch').on('click', function () {
    if (!validateForm('searchForm')) {
      return false;
    }

    $('#btnSearch').prop("disabled", true);
    getCategories(1);

    return false;
  });

  $('#btnAdd').on('click', function () {
    callAjax('admin/categories', 'form', {}, formCategoryCallback);
  });

  getCategories(1);
});

function getCategories(page, sortField, sortType) {
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

  callAjax('admin/categories', 'getCategories', {
    page: page,
    sort: sort,
    formData: fetchForm($('#searchForm'))
  }, getCategoriesCallback);
}

function getCategoriesCallback(result) {
  if (result !== false) {
    $('#btnSearch').prop("disabled", false);
    return true;
  }
  return false;
}

function editCategory(id) {
  callAjax('admin/categories', 'form', {idRecord: id}, formCategoryCallback);
}

function formCategoryCallback(result) {
  if (result !== false) {
    $('#commonDialog').modal({backdrop: 'static'});

    $('.selectpicker').selectpicker({
      actionsBox: true,
      liveSearch: true,
      multipleSeparator: '; ',
      size: 17,
      noneSelectedText: 'Chọn một lớp sản phẩm ...'
    });

    $('#btnSave').on('click', function () {
      if (!validateForm('formCategory')) {
        return false;
      }
      callAjax('admin/categories', 'update', {formData: fetchForm($('#formCategory'))}, updateCategoryCallback);
    });
  }
  return false;
}

function updateCategoryCallback(result) {
  if (result !== false) {
    $('#commonDialog').modal('hide');
    showSuccessBubble('Cập nhật Loại sản phẩm ' + result.name + ' thành công.');
    getCategories($('.page-item.active > a').html());
  }
  return false;
}

function deleteCategory(id) {
  if (confirm('Bạn có chắc muốn xóa loại sản phẩm này?')) {
    callAjax('admin/categories', 'delete', {
      idRecord: id
    }, deleteCategoryCallback);
  }
}

function deleteCategoryCallback(result) {
  if (result !== false) {
    var page = 1;
    if ($('#listCategory > table > tbody > tr').length > 1) {
      page = $('.page-item.active > a').html();
    }
    getCategories(page);
    showSuccessBubble('Xóa Loại sản phẩm ' + result.name + ' thành công.');
  }
}
