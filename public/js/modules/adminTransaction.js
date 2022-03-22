$(document).ready(function () {
  $('#btnSearch').on('click', function () {
    if (!validateForm('searchForm')) {
      return false;
    }

    $('#btnSearch').prop("disabled", true);
    getTransactions(1);

    return false;
  });

  getTransactions(1);
});

function getTransactions(page, sortField, sortType) {
  if (typeof sortField == 'undefined') {
    sortField = "id";
  }
  if (typeof sortType == 'undefined') {
    sortType = "ASC";
  }

  var sort = {
    field: sortField,
    type: sortType
  };

  callAjax('admin/transactions', 'getTransactions', {
    page: page,
    sort: sort,
    formData: fetchForm($('#searchForm'))
  }, getTransactionsCallback);
}

function getTransactionsCallback(result) {
  if (result !== false) {
    $('#btnSearch').prop("disabled", false);

    $('tr.transaction > td:not(:last-child)').on('click', function () {
      var id = $(this).parent().data('id');

      callAjax('admin/transactions', 'viewTransaction', {recordID: id}, viewTransactionCallback);
    });

    return true;
  }
  return false;
}

function viewTransactionCallback(result) {
  if (result !== false) {
    $('#commonDialog').modal({backdrop: 'static'});
  }
  return false;
}

function updateStatus(id, status) {
  callAjax('admin/transactions', 'updateStatus', {recordID: id, status: status}, updateStatusCallback);
}

function updateStatusCallback(result) {
  if (result !== false) {
    $('#commonDialog').modal('hide');
    showSuccessBubble('Cập nhật trạng thái giao dịch #' + result.id + ' thành công.');
    getTransactions($('.page-item.active > a').html());
  }
  return false;
}
