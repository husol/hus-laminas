<?php
/**
 * Created by IntelliJ IDEA.
 * User: khoaht
 */

namespace Core\Hus;

class HusSentry
{
  static public function logError($exception)
  {
    \Sentry\captureException($exception);
  }

  static public function logInfo($message)
  {
    \Sentry\captureMessage($message);
  }
}
