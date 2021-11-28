$(document).ready(function () {
  $('#btnSendMessage').on('click', function () {
    if (!validateForm('contactForm')) {
      return false;
    }

    callAjax("contact", "save", {formData: fetchForm($('#contactForm'))},saveContactCallback);

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
