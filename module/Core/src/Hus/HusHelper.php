<?php
/**
 * Last modifier: khoaht
 * Last modified date: 26/09/18
 * Description: Use this class to implement common functions
 */

namespace Core\Hus;

class HusHelper
{
  public static function getTitleList()
  {
    $titleList = ['Dr.', 'Mdm.', 'Mr.', 'Mrs.', 'Ms.', 'Sir.'];

    return $titleList;
  }

  public static function getCountries($keyword = '')
  {
    $countriesJson = file_get_contents(APP_DIR. DS .'data'.DS.'countries.json');
    $countries = json_decode($countriesJson);

    if (empty($keyword)) {
      return $countries;
    }

    $result = [];
    foreach ($countries as $country) {
      if (strpos(strtoupper($country->name), strtoupper($keyword)) === false) {
        continue;
      }
      $result[] = $country;
    }

    return $result;
  }

  public static function getCountryByCode($code = '', $field = '')
  {
    $countriesJson = file_get_contents(APP_DIR . DS . 'data' . DS . 'countries.json');
    $countries = json_decode($countriesJson);

    foreach ($countries as $country) {
      if ($country->code == $code) {
        if (empty($field)) {
          return $country;
        }

        return $country->$field;
      }
    }

    return false;
  }

  public static function decToHex($int)
  {
    if ($int == 0) {
      return '';
    }

    $strEncode = dechex(7000000 + $int);
    return strtoupper($strEncode);
  }

  public static function hexToDec($str)
  {
    if (empty($str)) {
      return 0;
    }

    $intDecode = hexdec($str) - 7000000;
    return $intDecode;
  }

  public static function getClientInfoFromRequest($request)
  {
    if (!empty($request->getServer('HTTP_CLIENT_IP'))) {
      $ip = $request->getServer('HTTP_CLIENT_IP');
    } else if (!empty($request->getServer('HTTP_X_FORWARDED_FOR'))) {
      $ip = $request->getServer('HTTP_X_FORWARDED_FOR');
    } else {
      $ip = $request->getServer('REMOTE_ADDR');
    }

    return [
      'ip' => $ip,
      'userAgent' => $request->getServer('HTTP_USER_AGENT')
    ];
  }

  public static function generateRandomString($length = 16, $include_special_char = false) {
    $includeChars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    /* Uncomment below to include symbols */
    if ($include_special_char) {
      $includeChars .= "[{(!@#$%^/&*_+;?\:)}]";
    }
    $charLength = strlen($includeChars);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $includeChars[rand(0, $charLength - 1)];
    }

    return $randomString;
  }
}
