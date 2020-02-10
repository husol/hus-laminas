$(document).ready(function () {

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