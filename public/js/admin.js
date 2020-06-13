/* CKEditor Usage
let contentEditor;
ClassicEditor
  .create( document.querySelector('#content') )
  .then( editor => {
    contentEditor = editor;
  })
  .catch( error => {
    console.error( error );
  });
//Then you can update content value in the textarea via: $('#content').html(contentEditor.getData()); before we submit form via callAjax.
 */

$(document).ready(function () {

});

function validateForm(formId) {
  var form = document.getElementById(formId);
  if (form.checkValidity() === false) {
    $("#" + formId).addClass('was-validated');
    $("#" + formId).find('select:not([disabled]):required').each(function () {
      if ($(this).val()) {
        $(this).parent().siblings('.invalid-feedback').hide();
        $(this).siblings('.select-dropdown').css('border-color', '#ced4da');
      } else {
        $(this).parent().siblings('.invalid-feedback').show();
        $(this).siblings('.select-dropdown').css('border-color', '#dc3545');
      }
    });
    return false;
  }

  var isErr = false;
  $("#" + formId).find('input.datepicker:not([disabled]):required').each(function () {
    if ($(this).val().length == 0) {
      $("#" + formId).addClass('was-validated');
      $(this).siblings('.invalid-feedback').show();
      $(this).css('border-color', '#dc3545');
      isErr = true;
    } else {
      $(this).siblings('.invalid-feedback').hide();
      $(this).css('border-color', '#ced4da');
    }
  });

  return !isErr;
}

//My Account
function updateMyAccount() {
  callAjax('auth', 'myAccountForm', {}, formMyAccountCallback);
}

function formMyAccountCallback(result) {
  if (result !== false) {
    $('#commonDialog').modal({backdrop: 'static'});

    $("#imgCover").on("click", function () {
      $("#image").trigger("click");
    });

    $("#image").change(function (e) {
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

    $('#btnSave').on('click', function () {
      if (!validateForm('formMyAccount')) {
        return false;
      }
      callAjax('auth', 'updateMyAccount', {formData: fetchForm($('#formMyAccount'))}, updateMyAccountCallback);
    });
  }
  return false;
}

function updateMyAccountCallback(result) {
  if (result !== false) {
    $('#commonDialog').modal('hide');
    showSuccessBubble("Cập nhật Tài khoản của bạn thành công.");
    setTimeout(function () {
      location.reload();
    }, 3000);
  }
  return false;
}
