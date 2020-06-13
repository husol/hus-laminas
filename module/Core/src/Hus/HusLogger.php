<?php
/**
 * Created by IntelliJ IDEA.
 * User: khoaht
 */

namespace Core\Hus;

class HusLogger
{
  static public function mlog($content, $override = false, $title = '', $filename = '/tmp/debug')
  {
    $openType = $override ? 'w' : 'a';
    if (!$handle = @fopen($filename, $openType)) {
      $result = 3; //"Cannot open file ($filename)";
    }
    //if(empty($content)) $content = var_export($content, true);
    if (empty($content) || is_array($content) || is_object($content)) {
      $content = print_r($content, true);
    }
    $content = date('Y-m-d H:i:s') . (empty($title) ? '':  " $title \n") ."   $content \n\n";

    // Write $content to our opened file.
    if (@fwrite($handle, $content) === false) {
      $result = 2; //"Cannot write to file ($filename)";
    } else {
      $result = 1; //"Success, wrote ($content) to file ($filename)";
    }
    @fclose($handle);
    return $result;
  }

  static public function error($logs, $name, $title)
  {
    $configHus = \Laminas\Config\Factory::fromFile(ROOT_DIR . '/module/Application/config/config.php');
    $logDir = ROOT_DIR .DIRECTORY_SEPARATOR. 'logs' .DIRECTORY_SEPARATOR. date('Ym');
    if (!file_exists($logDir)) {
      mkdir($logDir, 0777, true);
    }
    $logFile = sprintf('%s_%s.txt', date('d'), $name);
    $logFilePath = $logDir .DIRECTORY_SEPARATOR. $logFile;
    $title = "[{$configHus['LOG_LEVEL']}] $title:";
    return self::mlog($logs, false, $title, $logFilePath);
  }

  static public function debug($logs, $name, $title)
  {
    $configHus = \Laminas\Config\Factory::fromFile(ROOT_DIR . '/module/Application/config/config.php');
    if ($configHus['LOG_LEVEL'] != 'DEBUG') {
      return 0;
    }

    $logDir = ROOT_DIR .DIRECTORY_SEPARATOR. 'logs' .DIRECTORY_SEPARATOR. date('Ym');
    if (!file_exists($logDir)) {
      mkdir($logDir, 0777, true);
    }
    $logFile = sprintf('%s_%s.txt', date('d'), $name);
    $logFilePath = $logDir .DIRECTORY_SEPARATOR. $logFile;
    $title = "[{$configHus['LOG_LEVEL']}] $title:";
    return self::mlog($logs, false, $title, $logFilePath);
  }

  static public function info($logs, $name, $title)
  {
    $configHus = \Laminas\Config\Factory::fromFile(ROOT_DIR . '/module/Application/config/config.php');
    if ($configHus['LOG_LEVEL'] != 'INFO') {
      return 0;
    }

    $logDir = ROOT_DIR .DIRECTORY_SEPARATOR. 'logs' .DIRECTORY_SEPARATOR. date('Ym');
    if (!file_exists($logDir)) {
      mkdir($logDir, 0777, true);
    }
    $logFile = sprintf('%s_%s.txt', date('d'), $name);
    $logFilePath = $logDir .DIRECTORY_SEPARATOR. $logFile;
    $title = "[{$configHus['LOG_LEVEL']}] $title:";
    return self::mlog($logs, false, $title, $logFilePath);
  }
}