$(document).ready(function () {
  getUsers(1);
});

function getUsers(page, sortField, sortType) {
  if (typeof sortField == 'undefined') {
    sortField = "updated_at";
  }
  if (typeof sortType == 'undefined') {
    sortType = "DESC";
  }

  var sort = {
    field: sortField,
    type: sortType
  };

  callAjax('admin/users', 'getUsers', {
    page: page,
    sort: sort,
  }, getUsersCallback);
}

function getUsersCallback(result) {
  if (result !== false) {

    return true;
  }
  return false;
}