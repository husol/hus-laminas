<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
if (!function_exists('mlog')) {
  function mlog($content, $override = false, $filename = '/tmp/debug')
  {
    $openType = $override ? 'w' : 'a';
    if (!$handle = @fopen($filename, $openType)) {
      $result = 3; //"Cannot open file ($filename)";
    }
    //if(empty($content)) $content = var_export($content, true);
    if (empty($content) || is_array($content) || is_object($content)) {
      $content = print_r($content, true);
    }
    $content = date('Y-m-d H:i:s') . "  $content \n\n";

    // Write $somecontent to our opened file.
    if (@fwrite($handle, $content) === false) {
      $result = 2; //"Cannot write to file ($filename)";
    } else {
      $result = 1; //"Success, wrote ($somecontent) to file ($filename)";
    }
    @fclose($handle);
    return $result;
  }
}

///////////////////////////This file is used to format, convert, sort//////////////////////////////////

function escapeValuesSQL($values)
{
  $search = ["\x00", "\n", "\r", "\\", "'", "\"", "\x1a"];
  $replace = ["\\x00", "\\n", "\\r", "\\\\" ,"\'", "\\\"", "\\\x1a"];
  $result = [];
  foreach ($values as $value) {
    if (is_string($value)) {
      $result[] = "'" . str_replace($search, $replace, $value) . "'";
    } elseif (is_bool($value)) {
      $result[] = ($value === false) ? 0 : 1;
    } elseif (is_null($value)) {
      $result[] = 'NULL';
    } else {
      $result[] = $value;
    }
  }
  return implode(',', $result);
}

function buildInsertUpdateSQL($tablename, $dataObject = [], $fields = [], $exclude = false)
{
  if (empty($tablename)) {
    return ['result' => false, 'message' => 'Table name must be inputted.'];
  }
  if (empty($fields)) {
    return ['result' => false, 'message' => 'Fields for updating/not updating must be inputted.'];
  }
  if (empty($dataObject)) {
    return ['result' => false, 'message' => 'No data updated.'];
  }

  if (is_array(reset($dataObject))) {
    $keys = array_keys(reset($dataObject));
    foreach ($dataObject as $obj) {
      $values[] = '(' . escapeValuesSQL(array_values($obj)) . ')';
    }
    $values = implode(',', $values);
  } else {
    $keys = array_keys($dataObject);
    $values = '(' . escapeValuesSQL(array_values($dataObject)) . ')';
  }

  if ($exclude) {
    $fields = array_diff($keys, $fields);
  }

  $keys = implode('`,`', $keys);

  $sql = "INSERT INTO $tablename (`$keys`) VALUES $values ON DUPLICATE KEY UPDATE ";

  $updateFields = [];
  foreach ($fields as $col) {
    $updateFields[] = "`$col` = VALUES(`$col`)";
  }

  $sql .= implode(', ', $updateFields);

  return ['result' => true, 'sql' => $sql];
}

function alphabetonly($string = '')
{
  $output = $string;
  //replace no alphabet character
  $output = preg_replace("/[^a-zA-Z0-9]/", "-", $output);
  $output = preg_replace("/-+/", "-", $output);
  $output = trim($output, '-');

  return $output;
}

/**
 * Ham dung de convert cac ky tu co dau thanh khong dau
 * Dung tot cho cac chuc nang SEO cho browser(vi nhieu engine ko
 * hieu duoc dau tieng viet, nen can phai bo dau tieng viet di)
 *
 * @param mixed $string
 */
function codau2khongdau($string = '', $alphabetOnly = false, $tolower = true)
{

  $output = $string;
  if ($output != '') {
    //Tien hanh xu ly bo dau o day
    $search = [
      '&#225;', '&#224;', '&#7843;', '&#227;', '&#7841;',// a' a` a? a~ a.
      '&#259;', '&#7855;', '&#7857;', '&#7859;', '&#7861;', '&#7863;',// a( a('
      '&#226;', '&#7845;', '&#7847;', '&#7849;', '&#7851;', '&#7853;',// a^ a^'..
      '&#273;',// d-
      '&#233;', '&#232;', '&#7867;', '&#7869;', '&#7865;',// e' e`..
      '&#234;', '&#7871;', '&#7873;', '&#7875;', '&#7877;', '&#7879;',// e^ e^'
      '&#237;', '&#236;', '&#7881;', '&#297;', '&#7883;',// i' i`..
      '&#243;', '&#242;', '&#7887;', '&#245;', '&#7885;',// o' o`..
      '&#244;', '&#7889;', '&#7891;', '&#7893;', '&#7895;', '&#7897;',// o^ o^'..
      '&#417;', '&#7899;', '&#7901;', '&#7903;', '&#7905;', '&#7907;',// o* o*'..
      '&#250;', '&#249;', '&#7911;', '&#361;', '&#7909;',// u'..
      '&#432;', '&#7913;', '&#7915;', '&#7917;', '&#7919;', '&#7921;',// u* u*'..
      '&#253;', '&#7923;', '&#7927;', '&#7929;', '&#7925;',// y' y`..
      '&#193;', '&#192;', '&#7842;', '&#195;', '&#7840;',// A' A` A? A~ A.
      '&#258;', '&#7854;', '&#7856;', '&#7858;', '&#7860;', '&#7862;',// A( A('..
      '&#194;', '&#7844;', '&#7846;', '&#7848;', '&#7850;', '&#7852;',// A^ A^'..
      '&#272;',// D-
      '&#201;', '&#200;', '&#7866;', '&#7868;', '&#7864;',// E' E`..
      '&#202;', '&#7870;', '&#7872;', '&#7874;', '&#7876;', '&#7878;',// E^ E^'..
      '&#205;', '&#204;', '&#7880;', '&#296;', '&#7882;',// I' I`..
      '&#211;', '&#210;', '&#7886;', '&#213;', '&#7884;',// O' O`..
      '&#212;', '&#7888;', '&#7890;', '&#7892;', '&#7894;', '&#7896;',// O^ O^'..
      '&#416;', '&#7898;', '&#7900;', '&#7902;', '&#7904;', '&#7906;',// O* O*'..
      '&#218;', '&#217;', '&#7910;', '&#360;', '&#7908;',// U' U`..
      '&#431;', '&#7912;', '&#7914;', '&#7916;', '&#7918;', '&#7920;',// U* U*'..
      '&#221;', '&#7922;', '&#7926;', '&#7928;', '&#7924;'// Y' Y`..
    ];

    $search2 = [
      'á', 'à', 'ả', 'ã', 'ạ',// a' a` a? a~ a.
      'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ',// a( a('
      'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ',// a^ a^'..
      'đ',// d-
      'é', 'è', 'ẻ', 'ẽ', 'ẹ',// e' e`..
      'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ',// e^ e^'
      'í', 'ì', 'ỉ', 'ĩ', 'ị',// i' i`..
      'ó', 'ò', 'ỏ', 'õ', 'ọ',// o' o`..
      'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ',// o^ o^'..
      'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ',// o* o*'..
      'ú', 'ù', 'ủ', 'ũ', 'ụ',// u'..
      'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự',// u* u*'..
      'ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ',// y' y`..
      'Á', 'À', 'Ả', 'Ã', 'Ạ',// A' A` A? A~ A.
      'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ', 'Ặ',// A( A('..
      'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ',// A^ A^'..
      'Đ',// D-
      'É', 'È', 'Ẻ', 'Ẽ', 'Ẹ',// E' E`..
      'Ê', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ',// E^ E^'..
      'Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị',// I' I`..
      'Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ',// O' O`..
      'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ', 'Ộ',// O^ O^'..
      'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ',// O* O*'..
      'Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ',// U' U`..
      'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự',// U* U*'..
      'Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ'// Y' Y`..
    ];

    $replace = [
      'a', 'a', 'a', 'a', 'a',
      'a', 'a', 'a', 'a', 'a', 'a',
      'a', 'a', 'a', 'a', 'a', 'a',
      'd',
      'e', 'e', 'e', 'e', 'e',
      'e', 'e', 'e', 'e', 'e', 'e',
      'i', 'i', 'i', 'i', 'i',
      'o', 'o', 'o', 'o', 'o',
      'o', 'o', 'o', 'o', 'o', 'o',
      'o', 'o', 'o', 'o', 'o', 'o',
      'u', 'u', 'u', 'u', 'u',
      'u', 'u', 'u', 'u', 'u', 'u',
      'y', 'y', 'y', 'y', 'y',

      'A', 'A', 'A', 'A', 'A',
      'A', 'A', 'A', 'A', 'A', 'A',
      'A', 'A', 'A', 'A', 'A', 'A',
      'D',
      'E', 'E', 'E', 'E', 'E',
      'E', 'E', 'E', 'E', 'E', 'E',
      'I', 'I', 'I', 'I', 'I',
      'O', 'O', 'O', 'O', 'O',
      'O', 'O', 'O', 'O', 'O', 'O',
      'O', 'O', 'O', 'O', 'O', 'O',
      'U', 'U', 'U', 'U', 'U',
      'U', 'U', 'U', 'U', 'U', 'U',
      'Y', 'Y', 'Y', 'Y', 'Y'
    ];

    //print_r($search);
    $output = str_replace($search, $replace, $output);
    $output = str_replace($search2, $replace, $output);

    if ($alphabetOnly) {
      $output = alphabetonly($output);
    }

    if ($tolower) {
      $output = strtolower($output);
    }
  }

  return $output;
}

function getCurrentDateDirName($includeDay = true)
{
  $dateArr = getdate();

  if ($includeDay) {
    $path = $dateArr['year'] . DS . $dateArr['month'] . DS . $dateArr['mday'] . DS;
  } else {
    $path = $dateArr['year'] . DS . $dateArr['month'] . DS;
  }

  return $path;
}

/*
    -- Usage: We can sort an array by many fields we want
        $data: Your multiple array data which needs sorting
        $sortCriteria: Add as many fields as you want.
       Ex: $sortCriteria = ['field1' => [SORT_DESC, SORT_STRING], 'field3' => [SORT_DESC, SORT_NUMERIC]];
        $type: type of elements in $data, default is 'array', else: object
        $keepIndex: Keep index after sorting or not, default is false
    */
function sortArrayByKey($data, $sortCriteria, $keepIndex = false, $type = 'array')
{
  if (empty($data)) {
    return [];
  }

  $argsSort = '';
  $dataSort = [];
  $dataWithIndex = [];
  foreach ($sortCriteria as $field => $sortInfo) {
    foreach ($data as $key => $val) {
      if ($type == 'array') {
        $value = str_replace(["%", ","], ['', ''], $val[$field]);
      } else {
        $value = str_replace(["%", ","], ['', ''], $val->$field);
      }
      //backup index here
      if ($keepIndex) {
        $dataWithIndex["index_$key"] = $val;
      }

      $dataSort[$field][$key] = strtolower($value);
    }
    $argsSort .= sprintf('$dataSort["%s"], %s, %s, ', $field, $sortInfo[0], $sortInfo[1]);
  }
  //Sort data with index or not
  eval('array_multisort(' . $argsSort . ($keepIndex ? '$dataWithIndex' : '$data') . ');');

  //Now we return the result with index or not
  $dataSort = [];
  if ($keepIndex) {
    foreach ($dataWithIndex as $key => $value) {
      $dataSort[substr($key, 6)] = $value;
    }
  } else {
    foreach ($data as $value) {
      $dataSort[] = $value;
    }
  }

  return $dataSort;
}

function divideNumber($a, $b)
{
  if ($a == 0 || $b == 0) {
    return 0;
  }
  return $a / $b;
}

function formatNumber($number, $decimal = 0)
{
  return number_format($number, $decimal, '.', ',');
}

function getDaysBetweenDates($date1, $date2)
{
  $date1 = date_create($date1);
  $date2 = date_create($date2);

  $diff = date_diff($date1, $date2);

  return intval($diff->format('%d'));
}

function trimSurName($name)
{
  $temp = trim(preg_replace('!\s+!', ' ', $name));
  $nameArr = explode(' ', $temp);
  $count = count($nameArr);
  if ($count >= 2) {
    $temp = $nameArr[$count - 2] . $nameArr[$count - 1];
  } else {
    $temp = $nameArr[$count - 1];
  }
  $result = codau2khongdau($temp, true);

  return $result;
}

function trimApiRootUrl($url)
{
  if (empty($url)) {
    return '';
  }

  if (strpos($url, 'api') === false) {
    return $url;
  }

  $prefix = strstr($url, "api.", true);
  $s = in_array($prefix, ['http://', 'https://']) ? '' : '.';
  $suffix = strstr($url, "canavi");

  return $prefix . $s . $suffix;
}

function convertToMySqlDate($date)
{
  // "d/m/Y" or "d-m-Y" or "d/M/Y" or "d-M-Y" --> "Y-m-d"
  if (empty($date)) {
    return null;
  }
  $temp = strpos($date, '/') === false ?
    explode('-', $date) :
    explode('/', $date);

  if (count($temp) == 3) {
    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    $month = is_numeric($temp[1]) ? $temp[1] : array_search($temp[1], $months) + 1;
    $day = $temp[0];
    $year = $temp[2];
    //it's ok if date is already Y-m-d
    if (strlen($temp[0]) == 4) {
      $year = $temp[0];
      $day = $temp[2];
    }

    if (strlen($day) < 2) {
      $day = "0{$day}";
    }
    if (strlen($month) < 2) {
      $month = "0{$month}";
    }

    return "{$year}-{$month}-{$day}";
  } else {
    return '0-1-1';
  }
}

function convertToDateDisplay($dateStr, $detail = false, $timezone = [])
{
  if (empty($dateStr)) {
    return '';
  }

  $defaultTimezone = date_default_timezone_get();
  if (!isset($timezone['from'])) {
    $timezone['from'] = $defaultTimezone;
  }
  if (!isset($timezone['to'])) {
    $timezone['to'] = $defaultTimezone;
  }

  $date = new \DateTime($dateStr, new \DateTimeZone($timezone['from']));
  $date->setTimezone(new \DateTimeZone($timezone['to']));

  if ($detail) {
    return $date->format('d-M-Y H:i');
  }

  return $date->format('d-M-Y');
}

function convertAnyStringToNumber($string, $isThousandComma = true)
{
  //Remove all character except number, dot and commas character.
  $string = preg_replace('/[^\d.,-]+/', '', $string);
  $lastOfDot = strrpos($string, ".");
  $lastOfCommas = strrpos($string, ",");

  if ($lastOfDot === false && $lastOfCommas === false) {
    //123456 >> 123456
    return floatval($string);
  }

  if ($isThousandComma && $lastOfDot === false) {
    $string .= '.00';
    $lastOfDot = strrpos($string, ".");
  }

  if ($lastOfDot >= 0 && $lastOfCommas === false) {
    //123.456 >> 123.456
    return floatval($string);
  }
  if ($lastOfCommas >= 0 && $lastOfDot === false) {
    //123,456 >> 123.456
    return floatval(str_replace(",", ".", $string));
  }
  if ($lastOfCommas > $lastOfDot) {
    //2.345,23 >> 2345.23
    return floatval(str_replace(",", ".", str_replace(".", "", $string)));
  }
  if ($lastOfDot > $lastOfCommas) {
    //234,234.93 >> 234234.93
    return floatval(str_replace(",", "", $string));
  }
}

function convertImageUrlByType($url, $type = "origin")
{
  if ($type == "origin") {
    return $url;
  }
  $arrUrl = explode('/', $url);
  $filename = end($arrUrl);
  $newFileName = preg_replace('/(\.[^.]+)$/', sprintf('_%s$1', $type), $filename);
  array_pop($arrUrl);
  array_push($arrUrl, $newFileName);

  return implode('/', $arrUrl);
}

function stringCatContent($content, $limit = '100')
{
  $string = strip_tags($content);
  if (strlen($string) > $limit) {
    // truncate string
    $stringCut = substr($string, 0, $limit);
    // make sure it ends in a word so assassinate doesn't become ass...
    $string = substr($stringCut, 0, strrpos($stringCut, ' ')) . '...';
  }
  return $string;
}

function callAPI($host, $uri, $params)
{
  $method = isset($params['method']) ? strtoupper($params['method']) : 'POST';
  $headers = isset($params['headers']) ? $params['headers'] : [];
  $token = isset($params['token']) ? $params['token'] : '';

  $data = (isset($params['data']) && !empty($params['data'])) ? $params['data'] : [];

  if ($method == 'GET') {
    unset($headers['Content-Type']);
  } elseif (!isset($headers['Content-Type'])) {
    $headers['Content-Type'] = 'application/json';
  }

  if (!empty($token)) {
    $headers['Authorization'] = $token;
  }

  $handlerStack = \GuzzleHttp\HandlerStack::create();
  $handlerStack->push(\GuzzleHttp\Middleware::retry(function ($retry, $request, $value, $reason) {
    //If we have a value already, we should be able to proceed quickly.
    if (!is_null($value)) {
      return false;
    }

    // Reject after 3 retries.
    return $retry < 3;
  }));

  $client = new \GuzzleHttp\Client(['base_uri' => $host . '/', 'verify' => false]);

  try {
    //Build options
    $options = [
      'headers' => $headers,
      'handler' => $handlerStack
    ];

    if (isset($headers['Content-Type'])) {
      if ($headers['Content-Type'] == 'application/json') {
        $options['json'] = $data;
      } elseif ($headers['Content-Type'] == 'application/x-www-form-urlencoded' && $method == 'POST') {
        $options['form_params'] = $data;
      }
    } else {
      $options['query'] = $data;
    }

    $response = $client->request($method, $uri, $options);
  } catch (\GuzzleHttp\Exception\ClientException $e) {
    $response = $e->getResponse();
  }

  $result['code'] = $response->getStatusCode();
  $result['status'] = 'FAIL';
  $resultContent = json_decode($response->getBody()->getContents());

  if ($result['code'] == 200 && in_array($resultContent->status, ['SUCCESSFUL', 'SUCCESS', 'OK'])) {
    $result['status'] = 'SUCCESS';
    $result['result'] = $resultContent->result;
  } else {
    $result['error'] = isset($resultContent->message)
      ? $resultContent->message
      : $response->getBody()->getContents();
  }

  return $result;
}

function getOverlapDays($dateRange1, $dateRange2)
{
  $dateStart1 = new \DateTime($dateRange1[0]);
  $dateEnd1 = new \DateTime($dateRange1[1]);

  $dateStart2 = new \DateTime($dateRange2[0]);
  $dateEnd2 = new \DateTime($dateRange2[1]);

  if ($dateStart1 <= $dateEnd2 && $dateEnd1 >= $dateStart2) {
    return min($dateEnd1, $dateEnd2)->diff(max($dateStart2, $dateStart1))->days + 1;
  }
  return 0;
}
