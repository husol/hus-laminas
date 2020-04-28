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
  $('#register').on('click', function () {
    grecaptcha.ready(function () {
      grecaptcha.execute(captchaSiteKey, {action: 'register'}).then(function (token) {
        //Verify the token on the server
        $('#reCaptchaToken').val(token);
      });
    });
    $('#registerDialog').modal({backdrop: 'static'});
  });

  $('#btnRegister').on('click', function () {
    //Validate
    if (!validateForm('formRegister')) {
      return false;
    }
    $('#btnRegister').prop("disabled", true);
    callAjax('auth', 'register', {
      formData: fetchForm($('#formRegister')),
      gRecaptchaResponse: $('[name=g-recaptcha-response]').val()
    }, registerCallback);
    return false;
  });
});

function validateForm(formId) {
  var form = document.getElementById(formId);
  if (form.checkValidity() === false) {
    $("#"+formId).addClass('was-validated');
    $("#"+formId).find('select:not([disabled]):required').each(function () {
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
  $("#"+formId).find('input.datepicker:not([disabled]):required').each(function () {
    if ($(this).val().length == 0) {
      $("#"+formId).addClass('was-validated');
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

function registerCallback(result) {
  if (result !== false) {
    showSuccessBubble("Tài khoản của bạn vừa được đăng ký thành công. Vui lòng kiểm tra email để kích hoạt tài khoản.", 7)
    setTimeout(function () {
      window.location.href = "/sign-in";
    }, 7000);
  }
}