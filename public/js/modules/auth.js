$(document).ready(function () {
  $('#btnLogin').on('click', function () {
    if (!validateForm('loginForm')) {
      return false;
    }
    callAjax('auth', 'login', {data: fetchForm($('#loginForm'))}, loginCallback);
    return false;
  });

  $('#btnChangePassword').on('click', function () {
    if (!validateForm('changePasswordForm')) {
      return false;
    }
    callAjax('auth', 'changePassword', {formData: fetchForm($('#changePasswordForm'))}, changePasswordCallback);
    return false;
  });
});

function loginCallback(result) {
  $('#btnLogin').prop("disabled", false);
  if (result !== false) {
    if (result.role == 'CLIENT') {
      window.location.href = "/";
    } else {
      window.location.href = "/admin";
    }
  }
}

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
