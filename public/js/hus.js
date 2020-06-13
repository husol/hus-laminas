//////////////////// Check file type //////////////////////////////////////////
/**
 * file : int
 * ext : string array()
 * Note: Extension of file with dot in front of ext.
 * Ex: ext = new Array(".png", ".jpeg", ".jpg")
 */
function checkFileExt(file, ext) {
  var pathLength = file.length;
  var lastDot = file.lastIndexOf(".");
  var fileType = file.substring(lastDot, pathLength);

  for (i = 0; i < ext.length; i++) {
    if (fileType == ext[i]) {
      return true;
    }
  }
  return false;
}

//////////////////// Sort alphabet option select list /////////////////////////
function sortAlphabet(id) {
  var prePrepend = "#";
  if (id.match("^#") == "#") prePrepend = "";
  selectedValue = $('#' + id).val();
  $(prePrepend + id).html($(prePrepend + id + " option").sort(function (a, b) {
    if (a.value <= 0) {
      //alert(a.value);
      return -1;
    } else if (b.value <= 0) {
      return 1;
    } else return a.text.toUpperCase() == b.text.toUpperCase() ? 0 : (a.text.toUpperCase() < b.text.toUpperCase() ? -1 : 1);
  }));

  $('#' + id).val(selectedValue);
}

//////////////////////// callAjax jQuery //////////////////////////////////////
/* Usage:

var dataSend = {id:'testID', loading:'loadingID', param_1:'paramValue_1', param_n:'paramValue_n'};
callAjax('Controller_Name', 'Action_Name', dataSend, testCallback);

function testCallback(result) {
	if (result !== false) {
        //Code here
	}
}

in which:
testID : the id of element we want to work.
loadingID : the id of loading icon we want to display.
paramValue_n: all parameters we want to post to server.
*/
function base64_decode(data) {
  return decodeURIComponent(atob(data).split('').map(function (c) {
    return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
  }).join(''));
}

function fetchForm(formObj, getAll, upload) {
  if (upload == undefined) {
    upload = false;
  }

  if (getAll == undefined) {
    getAll = false;
  }

  let disabledFields = formObj.find('[disabled]');
  if (getAll) {
    disabledFields.prop('disabled', false);
  }

  let formData = {};
  if (upload === true) {
    formData = new FormData(formObj[0]);
  } else {
    formData = formObj.serializeJSON();
  }

  if (getAll) {
    disabledFields.prop('disabled', true);
  }

  return formData;
}

function callAjax(controller, method, args, callback) {
  var isLoading = false, dataType = 'json', isAsync = true,
    contentType = 'application/x-www-form-urlencoded; charset=UTF-8', processData = true;

  if (args != null && args['silent'] == null) {
    isLoading = true;
    $("#loading").show();
  }

  if (args != null && args['dataType'] != null) {
    dataType = args['dataType'];
  }

  if (args != null && args['isAsync'] != null) {
    isAsync = args['isAsync'];
  }

  var data = args;
  if (args != null && args['formData'] != null) {
    var formData = args['formData'];
    delete args['formData'];

    if (args['upload'] != null && args['upload']) {
      contentType = false;
      processData = false;
      data = formData;
    } else {
      data = $.extend({}, args, formData);
    }
  }

  var url = root_url + Array(controller, method).join("/");
  var urlArr = controller.split(":/");
  if (urlArr[0] == 'https' || urlArr[0] == 'http') {
    url = Array(controller, method).join("/");
  }

  var objectCall = $.ajax({
    type: "POST",
    timeout: 99999999999999,
    url: url,
    data: data,
    dataType: dataType,
    async: isAsync,
    contentType: contentType,
    processData: processData,
    success: function (msg) {
      if (args['is_append'] == null) {
        $("#" + args['id']).html('');
      }
      $("#loading").hide();

      if (msg == null) {
        if (isLoading) {
          $("#loading").hide();
          if ($("#" + args['id']).length > 0) {
            $("#" + args['id']).hide();
          }
        }
        return;
      }

      if (msg.result == "expired_session") {
        alert("Session expired.");
        location.reload();
        return;
      }

      if (msg.result == false && msg.messages) {
        showErrorBubble("", msg.messages);
      }

      eval(base64_decode(msg.html));

      if (callback != null) {
        if (msg.result == undefined) {
          callback(msg, args['id']);
        } else {
          callback(msg.result, args['id']);
        }
      }
    },
    error: function (request, status, error) {
      if (request.statusText == 'abort' || request.statusText == 'error') return;
      if (error.name == 'NS_ERROR_NOT_AVAILABLE' || request.readyState == 0) {
        if (args['silent'] == null) {
          $('#infoText').html("Request is interrupted unexpectedly");
          setTimeout(function () {
            $('#loading').show();
          }, 3000);

        }
      } else {
        //default is display html return
        $('#' + args['id']).html(request.responseText);
      }
    }
  });
  return objectCall;
}

/////////////////////// End callAjax jQuery ///////////////////////////////////

function $_GET(param) {
  var vars = {};
  window.location.href.replace(location.hash, '').replace(
    /[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
    function (m, key, value) { // callback
      vars[key] = value !== undefined ? value : '';
    }
  );

  if (param) {
    return vars[param] ? vars[param] : null;
  }
  return vars;
}

// Show tooltip when input data is not valid
function showErrorBubble(control, error_msg, seconds) {
  var ctrl = false;
  if ($(control).length > 0) {
    ctrl = $(control);
  }
  var delay = seconds || 5;
  toastr.error(error_msg, '', {
    "closeButton": true,
    "debug": false,
    "newestOnTop": true,
    "progressBar": true,
    "positionClass": "toast-top-full-width",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": 300,
    "hideDuration": 1000,
    "extendedTimeOut": 1000,
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut",
    "timeOut": delay * 1000
  });

  if (ctrl !== false) {
    ctrl.focus();
  }

  return false;
}

// Show tooltip when saving data successfully
function showSuccessBubble(success_msg, seconds) {
  var delay = seconds || 5;
  toastr.success(success_msg, '', {
    "closeButton": true,
    "debug": false,
    "newestOnTop": true,
    "progressBar": true,
    "positionClass": "toast-top-full-width",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": 300,
    "hideDuration": 1000,
    "extendedTimeOut": 1000,
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut",
    "timeOut": delay * 1000
  });
  return false;
};

// Set cookie
function setCookie(c_name, value, exdays) {
  var exdate = new Date();
  exdate.setDate(exdate.getDate() + exdays);
  var c_value = escape(value) + ((exdays == null) ? "" : "; expires=" + exdate.toUTCString());
  document.cookie = c_name + "=" + c_value;
}

/* Auto load Data per x seconds ajax jQuery
* Usage:
  - On page html:
    $(document).ready(function(){
      autoAjaxCall("input_url","input_html_object",time_second_unit);
    });
  - On page input_url php:
    echo "<p>".$row['field']."</p>"
*/
function autoAjaxCall(url, HTMLObject, jumpTime) {
  var callAjax = function () {
    $.ajax({
      method: 'POST', url: url, success: function (data) {
        $(HTMLObject).html(data);
      }
    });
  }
  setInterval(callAjax, jumpTime * 1000);
}

/* Fixed table header
Usage:
 - Add class fixedHeader to table
 - The table must use thead tag
 - Call triggerFixedHeader(); in JS
 */
function triggerFixedHeader() {
  var tableOffset = $("table.fixedHeader").offset().top;
  var $tblFixedHeader = $("#tblFixedHeader").append($("table.fixedHeader > thead").clone());
  $tblFixedHeader.width($("table.fixedHeader").width());

  $(window).bind("scroll", function () {
    var offset = $(this).scrollTop() + 10;
    if (offset >= tableOffset && $tblFixedHeader.is(":hidden")) {
      $tblFixedHeader.show();
    } else if (offset < tableOffset) {
      $tblFixedHeader.hide();
    }
  });
}

function previewImage(input, imgId) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();

    reader.onload = function (e) {
      $('#' + imgId).attr('src', e.target.result);
    }

    reader.readAsDataURL(input.files[0]);
  }
}

function formatNumber(number, decimal) {
  numberStr = number;
  if (typeof decimal != 'undefined') {
    numberStr = parseFloat(Math.round(number * 100) / 100).toFixed(decimal);
  }

  numberStr += '';
  x = numberStr.split('.');
  x1 = x[0];
  x2 = x.length > 1 ? '.' + x[1] : '';
  var rgx = /(\d+)(\d{3})/;
  while (rgx.test(x1)) {
    x1 = x1.replace(rgx, '$1' + ',' + '$2');
  }
  return x1 + x2;
}

var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

function convertToMySqlDate(date) {
  // "d/m/Y" or "d-m-Y" or "d/M/Y" or "d-M-Y" --> "Y-m-d"
  if (date == "") {
    return "";
  }

  temp = (date.indexOf('/') == -1) ? date.split('-') : date.split('/');

  if (temp.length == 3) {
    var month = $.isNumeric(temp[1]) ? temp[1] : (months.indexOf(temp[1]) + 1).toString();
    var day = temp[0];
    var year = temp[2];

    //it's ok if date is already Y-m-d
    if (temp[0].length == 4) {
      year = temp[0];
      day = temp[2];
    }

    if (day.length < 2) {
      day = '0' + day;
    }
    if (month.length < 2) {
      month = '0' + month;
    }

    return year + '-' + month + '-' + day;
  } else {
    return '0-1-1';
  }
}

function convertSecondsToHms(secondsNum) {
  var secondsNum = Number(secondsNum);
  var d = Math.floor(secondsNum / (3600 * 24));
  var h = Math.floor(secondsNum % (3600 * 24) / 3600);
  var m = Math.floor(secondsNum % 3600 / 60);
  var s = Math.floor(secondsNum % 60);

  var dDisplay = d > 0 ? ((d < 10 ? '0' : '') + d + ' d ') : '';
  var hDisplay = h > 0 ? ((h < 10 ? '0' : '') + h + ' h ') : '';
  var mDisplay = m > 0 ? ((m < 10 ? '0' : '') + m + ' min ') : '';
  var sDisplay = s > 0 ? ((s < 10 ? '0' : '') + s + ' s') : '';
  return dDisplay + hDisplay + mDisplay + sDisplay;
}

function getDateTime(date) {
  date = convertToMySqlDate(date);
  var arrDate = date.split('-');
  var year = parseInt(arrDate[0]);
  var month = parseInt(arrDate[1]) - 1;
  var day = parseInt(arrDate[2]);

  return new Date(year, month, day);
}

function checkDate(str, max) {
  if (str.charAt(0) !== '0' || str == '00') {
    var num = parseInt(str);
    if (isNaN(num) || num <= 0 || num > max) num = 1;
    str = num > parseInt(max.toString().charAt(0)) && num.toString().length == 1 ? '0' + num : num.toString();
  }
  return str;
}

function triggerShownModalEvents(element) {
  element.find('[autofocus]').focus();
  element.find('.husSelect').selectpicker({
    actionsBox: true,
    liveSearch: true,
    multipleSeparator: '; ',
    size: 17,
    noneSelectedText: 'Select an item ...'
  });
  element.find('.birthday').datepicker({
    dateFormat: 'dd-M-yy',
    yearRange: 'c-100:c+10',
    changeMonth: true,
    changeYear: true,
    maxDate: '-6M'
  });
  element.find('.husDate').datepicker({
    yearRange: 'c-100:c+10',
    dateFormat: 'dd-M-yy',
    changeMonth: true,
    changeYear: true,
    firstDay: 1
  });
  element.find('.husTime').mask("99:99");
  element.find('.numeric').autoNumeric("init", {mDec: 2});
  element.find('.numericNegative').autoNumeric("init", {vMin: -999999999999, mDec: 2});
  element.find('.txtUppercase').keyup(function () {
    $(this).val($(this).val().toUpperCase());
  });
}

$(document).ready(function () {
  //HusSelect
  $('.husSelect').selectpicker({
    actionsBox: true,
    liveSearch: true,
    multipleSeparator: '; ',
    size: 17,
    noneSelectedText: 'Select an item ...'
  });
  //End HusSelect

  //HusDate
  $(document).on('keyup', '.husDate, .autoDate', function (e) {
    if (e.keyCode == 8 || e.keyCode == 46) {
      return false;
    }

    this.type = 'text';
    var input = this.value;
    if (/\D\-$/.test(input)) {
      input = input.substr(0, input.length - 3);
    }
    var values = input.split('-').map(function (v) {
      if ($.inArray(v, months) !== -1) {
        return v;
      }

      return v.replace(/\D/g, '')
    });

    if (values[0]) values[0] = checkDate(values[0], 31);
    if (values[1] && $.inArray(values[1], months) === -1) values[1] = checkDate(values[1], 12);

    var output = values.map(function (v, i) {
      if (v.length != 2 || i > 1) {
        return v + ($.inArray(v, months) === -1 ? '' : '-');
      }

      var result = (i ? months[v - 1] : v);
      return result + '-';
    });
    this.value = output.join('').substr(0, 11);
  });
  $('.husDate').datepicker({
    yearRange: 'c-100:c+10',
    dateFormat: 'dd-M-yy',
    changeMonth: true,
    changeYear: true,
    firstDay: 1
  });
  $.fn.modal.Constructor.prototype._enforceFocus = function () {};
  //End HusDate

  //HusTime
  $('.husTime').mask("99:99");
  $(document).on('focusout', '.husTime', function () {
    var timeStr = $(this).val();
    if (timeStr.includes(':')) {
      var timeArr = timeStr.split(':');
      if (timeArr.length == 2) {
        var hourStr = timeArr[0];
        if (hourStr.length < 2) {
          hourStr = "0" + hourStr;
        }
        if (hourStr == 0 || hourStr > 23) {
          hourStr = "00";
        }

        var minStr = timeArr[1];
        if (minStr.length < 2) {
          minStr = "0" + minStr;
        }
        if (minStr == 0 || minStr > 59) {
          minStr = "00";
        }

        $(this).val(hourStr + ":" + minStr);
      }
    }
    else {
      switch(timeStr.length) {
        case 0:
          var defaultTime = "00:00";
          if (typeof $(this).data('default') != "undefined") {
            defaultTime = $(this).data('default');
          }

          $(this).val(defaultTime);
          break;
        case 1:
          $(this).val("0" + $(this).val() + ":00");
          break;
        case 2:
          var hNumber = $(this).val();
          if (parseInt(hNumber) > 23)
            hNumber = "00";
          $(this).val(hNumber + ":00");
          break;
      }
    }
  });
  //End HusTime
  //TxtUppercase
  $(document).on('keyup', '.txtUppercase', function () {
    $(this).val($(this).val().toUpperCase());
  });
  //NumberOnly
  $(document).on('keypress', '.numberOnly', function (e) {
    var charCode = (e.which) ? e.which : e.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
      return false;
    }

    return true;
  });
  //Table is sortable by using Ajax
  $(document).on('click', 'table.sortable thead > tr > th.cursor-pointer', function () {
    var sortField = $(this).data('sortfield'), sortType = $(this).data('sorttype');
    var page = parseInt($(this).closest('table').data('page')) || 1;
    var sortFunc = $(this).closest('table').data('sortfunc') + '(' + page + ', "' + sortField + '", "' + sortType + '")';

    eval(sortFunc);
  });
  //Table with rowParent
  $(document).on('click', 'table > tbody > tr.rowParent.cursor-pointer > td:not(.noAction)', function () {
    $(this).parent().nextUntil("tr.rowParent").toggle();
  });
  //Numeric: Only Positive support
  $('.numeric').autoNumeric("init", {mDec: 2});
  //Numeric: Both Negative and Positive support
  $('.numericNegative').autoNumeric("init", {vMin: -999999999999, mDec: 2});
  //ContextMenu
  $(document).contextmenu({
    addClass: "ui-contextmenu",
    delegate: ".contextMenu",
    menu: "#contextMenuOptions",
    blur: function (event, ui) {
    },
    beforeOpen: function (event) {
    },
    open: function (event, ui) {
      var dataObj = $(event.currentTarget).data();
      $.each(dataObj, function (key, value) {
        ui.extraData[key] = value;
      });
    },
    select: function (event, ui) {
      eval(ui.cmd + '(ui.extraData)');
    }
  });
  //Trigger Shown Modal Events
  $(document).on('shown.bs.modal', '.modal', function (e) {
    triggerShownModalEvents($(this));
  });
  //For ENTER key trigger on form
  $(document).on('keypress', 'form.eventEnterKey', function (e) {
    if (e.keyCode == 13) {
      e.preventDefault();
      var idObj = $(this).data('enterkey');
      $('#' + idObj).trigger('click');
    }
  });
});
