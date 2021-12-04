$(document).ready(function () {
  $('#btnSearch').on('click', function () {
    if (!validateForm('searchForm')) {
      return false;
    }

    $('#btnSearch').prop("disabled", true);
    getContacts(1);

    return false;
  });

  getContacts(1);
});

function getContacts(page, sortField, sortType) {
  if (typeof sortField == 'undefined') {
    sortField = "full_name";
  }
  if (typeof sortType == 'undefined') {
    sortType = "ASC";
  }

  var sort = {
    field: sortField,
    type: sortType
  };

  callAjax('admin/contacts', 'getContacts', {
    page: page,
    sort: sort,
    formData: fetchForm($('#searchForm'))
  }, getContactsCallback);
}

function getContactsCallback(result) {
  if (result !== false) {
    $('#btnSearch').prop("disabled", false);
    return true;
  }
  return false;
}

function updateStatus(id, status) {
  callAjax('admin/contacts', 'updateStatus', {recordID: id, status: status}, updateStatusCallback);
}

function updateStatusCallback(result) {
  if (result !== false) {
    $('#commonDialog').modal('hide');
    showSuccessBubble('Cập nhật trạng thái liên hệ ' + result.full_name + ' thành công.');
    getContacts($('.page-item.active > a').html());
  }
  return false;
}
