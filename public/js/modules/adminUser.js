$(document).ready(function () {
  $('#btnSearch').on('click', function () {
    if (!validateForm('searchForm')) {
      return false;
    }

    $('#btnSearch').prop("disabled", true);
    getUsers(1);

    return false;
  });

  $('#btnAdd').on('click', function () {
    callAjax('admin/users', 'form', {}, formUserCallback);
  });

  getUsers(1);
});

function getUsers(page, sortField, sortType) {
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

  callAjax('admin/users', 'getUsers', {
    page: page,
    sort: sort,
    formData: fetchForm($('#searchForm'))
  }, getUsersCallback);
}

function getUsersCallback(result) {
  if (result !== false) {
    $('#btnSearch').prop("disabled", false);
    return true;
  }
  return false;
}

function editUser(id) {
  callAjax('admin/users', 'form', {idRecord: id}, formUserCallback);
}

function formUserCallback(result) {
  if (result !== false) {
    $('#commonDialog').modal({backdrop: 'static'});

    $('#btnSave').on('click', function () {
      if (!validateForm('formUser')) {
        return false;
      }

      callAjax('admin/users', 'update', {
        formData: fetchForm($('#formUser'))
      }, updateUserCallback);
    });
  }
  return false;
}

function updateUserCallback(result) {
  if (result !== false) {
    $('#commonDialog').modal('hide');
    showSuccessBubble('Update User ' + result.name + ' successfully.');
    getUsers($('.page-item.active > a').html());
  }
  return false;
}

function deleteUser(id) {
  if (confirm('Are you sure to delete this User?')) {
    callAjax('admin/users', 'delete', {
      idRecord: id
    }, deleteUserCallback);
  }
}

function deleteUserCallback(result) {
  if (result !== false) {
    var page = 1;
    if ($('#listUser > table > tbody > tr').length > 1) {
      page = $('.page-item.active > a').html();
    }
    getUsers(page);
    showSuccessBubble('Delete User ' + result.full_name + ' successfully.');
  }
}