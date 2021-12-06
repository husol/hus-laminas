$(document).ready(function () {
  grecaptcha.ready(function () {
    grecaptcha.execute(captchaSiteKey, {action: 'contact'}).then(function (token) {
      //Verify the token on the server
      $('#reCaptchaToken').val(token);
    });
  });

  $('#btnSendMessage').on('click', function () {
    if (!validateForm('contactForm')) {
      return false;
    }

    callAjax("contact", "save", {
      formData: fetchForm($('#contactForm')),
      gRecaptchaResponse: $('[name=g-recaptcha-response]').val()
    },saveContactCallback);

    return false;
  });
});

function saveContactCallback(result) {
  if (result !== false) {
    $('#contactForm')[0].reset();
    showSuccessBubble("Đã gửi thông tin liên hệ thành công. Chúng tôi sẽ liên hệ bạn trong thời gian sớm nhất.");
  }

  return false;
}
