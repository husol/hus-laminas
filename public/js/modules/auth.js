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

function changepasswordCallback(result) {
  $('#btnChangePassword').prop("disabled", false);
  if (result !== false) {
    showSuccessBubble('Your password is changed successfully.');
    window.location.href = "/auth";
  }
}

function forgotpassword() {
  callAjax('auth', 'forgotpasswordform', {}, forgotpasswordformCallback);
}

function forgotpasswordformCallback(result) {
  $('#commonDialog').modal({backdrop: 'static'});

  $('#email').keypress(function (e) {
    var key = e.which;
    if(key == 13) {// The enter key code
      $('#btnSubmit').trigger('click');
      return false;
    }
  });

  $('#btnSubmit').on('click', function () {
    //Validate
    if ($.trim($('#email').val()) == '') {
      showErrorBubble('#email', 'Email is required and not empty');
      return false;
    }
    $('#btnSubmit').prop("disabled", true);
    callAjax('auth', 'forgotpassword', {data: fetchForm($('#formForgotPassword'))}, resetpasswordCallback);
  });
}

function resetpasswordCallback(result) {
  $('#btnSubmit').prop("disabled", false);
  if (result !== false) {
    $('#commonDialog').modal('hide');
    showSuccessBubble("Please check your email to reset your password.");
  }
  return false;
}