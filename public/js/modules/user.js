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

    $("#imgCover").on("click", function () {
      $("#image").trigger("click");
    });

    $("#image").change(function(e){
      //Validate file type / size
      var validImageTypes = ["image/png", "image/jpeg", "image/gif"];
      var files = e.originalEvent.target.files;
      for (var i = 0, len = this.files.length; i < len; i++){
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

    $('#btnSave').on('click', function () {
      if (!validateForm('formUser')) {
        return false;
      }
      callAjax('admin/users', 'update', {formData: fetchForm($('#formUser'))}, updateUserCallback);
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
    showSuccessBubble('Delete User ' + result.name + ' successfully.');
  }
}