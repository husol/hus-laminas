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
  var isErr = $_GET('is_error');

  if (isErr == 403) {
    showErrorBubble('#login', 'Vui lòng đăng nhập để có thể Mua hàng');
  }

  $('#login').on('click', function () {
    callAjax('auth', 'index', {}, loginFormCallback);

    return false;
  });

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

  $('.btnBuy').on('click', function () {
    var productID = $(this).data('id');
    var cartJSON = localStorage.getItem('cart');
    var cartProducts = JSON.parse(cartJSON);

    if (cartProducts == null) {
      cartProducts = [];
    }

    var existed = false;
    $.each(cartProducts, function(index, obj) {
      if (obj.id == productID) {
        obj.quantity += 1;
        existed = true;
        return false;
      }
    });

    if (!existed) {
      cartProducts.push({id: productID, quantity: 1})
    }

    localStorage.setItem('cart', JSON.stringify(cartProducts));

    window.location.href = "/cart";
  });

  var cartJSON = localStorage.getItem('cart');
  if (cartJSON == null) {
    localStorage.setItem('cart', JSON.stringify([]));
  }

  loadCartBadge();
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

function loginFormCallback(result) {
  if (result !== false) {
    showModal('commonDialog');

    $('#btnLogin').on('click', function () {
      if (!validateForm('loginForm')) {
        return false;
      }

      callAjax('auth', 'login', {data: fetchForm($('#loginForm'))}, loginCallback);
      return false;
    });
  }

  return false;
}

function registerCallback(result) {
  if (result !== false) {
    showSuccessBubble("Tài khoản của bạn vừa được đăng ký thành công. Vui lòng kiểm tra email để kích hoạt tài khoản.", 7)
    setTimeout(function () {
      window.location.href = "/sign-in";
    }, 7000);
  }
}

function loginCallback(result) {
  $('#btnLogin').prop("disabled", false);
  if (result !== false) {
    if (result.role == 0) {
      window.location.href = "/";
    } else {
      window.location.href = "/admin";
    }
  }
}

function loadCartBadge() {
  if ($('#cart').length > 0) {
    var cartJSON = localStorage.getItem('cart');
    var cartProducts = JSON.parse(cartJSON);
    var cartCount = 0;

    if (cartProducts != null && cartProducts.length > 0) {
      $.each(cartProducts, function (index, product) {
        cartCount += parseInt(product.quantity);
      });
    }

    $('span.badeNum').html(cartCount);
  }
}
