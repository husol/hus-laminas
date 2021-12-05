$(document).ready(function () {
  if ($('#formRegister').length > 0) {
    grecaptcha.ready(function () {
      grecaptcha.execute(captchaSiteKey, {action: 'register'}).then(function (token) {
        //Verify the token on the server
        $('#reCaptchaToken').val(token);
      });
    });
  }

  $('#btnChangePassword').on('click', function () {
    if (!validateForm('changePasswordForm')) {
      return false;
    }
    callAjax('auth', 'changePassword', {formData: fetchForm($('#changePasswordForm'))}, changePasswordCallback);
    return false;
  });
});

function changePasswordCallback(result) {
  $('#btnChangePassword').prop("disabled", false);
  if (result !== false) {
    showSuccessBubble('Mật khẩu của bạn đã được thay đổi thành công.');
    setTimeout(function () {
      window.location.href = "/sign-in";
    }, 3000);
  }
}

function forgotPassword() {
  callAjax('auth', 'forgotPasswordForm', {}, forgotPasswordFormCallback);
}

function forgotPasswordFormCallback(result) {
  $('#commonDialog').modal({backdrop: 'static'});

  $('#btnSubmit').on('click', function () {
    //Validate
    if (!validateForm('formForgotPassword')) {
      return false;
    }
    $('#btnSubmit').prop("disabled", true);
    callAjax('auth', 'forgotPassword', {formData: fetchForm($('#formForgotPassword'))}, forgotPasswordCallback);
    return false;
  });
}

function forgotPasswordCallback(result) {
  $('#btnSubmit').prop("disabled", false);
  if (result !== false) {
    $('#commonDialog').modal('hide');
    showSuccessBubble("Hãy kiểm tra email của bạn để thay đổi mật khẩu.");
  }
  return false;
}
