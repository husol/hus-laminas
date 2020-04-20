$(document).ready(function () {
  // $('#btnRegister').on('click', function () {
  //   if (!validateForm('registerForm')) {
  //     return false;
  //   }
  //   grecaptcha.ready(function() {
  //     grecaptcha.execute("6Lf3MaMUAAAAAKVbY-AEehdz-j_q-SxvqK94RKdB", {action: "register"}).then(function (token) {
  //       //Verify the token on the server.
  //       $('#reCaptchaToken').val(token);
  //       callAjax('auth', 'register', {formData: fetchForm($('#registerForm'))}, registerCallback);
  //     });
  //   });
  //   return false;
  // });

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

function registerCallback(result) {
  if (result !== false) {
    window.location.href = "/register-success";
  }
}

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
      window.location.href = "/auth";
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