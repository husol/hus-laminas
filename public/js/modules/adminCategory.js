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
      noneSelectedText: 'Select a category ...'
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
    showSuccessBubble('Update Category ' + result.name + ' successfully.');
    getCategories($('.page-item.active > a').html());
  }
  return false;
}

function deleteCategory(id) {
  if (confirm('Are you sure to delete this Category?')) {
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
    showSuccessBubble('Delete Category ' + result.name + ' successfully.');
  }
}