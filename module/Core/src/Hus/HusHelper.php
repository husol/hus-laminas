<?php
/**
 * Last modifier: khoaht
 * Last modified date: 26/09/18
 * Description: Use this class to implement common functions
 */

namespace Core\Hus;

class HusHelper
{
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

  public static function getTitleList()
  {
    $titleList = ['Dr.', 'Mdm.', 'Mr.', 'Mrs.', 'Ms.', 'Sir.'];

    return $titleList;
  }
}
