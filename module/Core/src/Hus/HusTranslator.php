<?php
/**
 * Last modifier: khoaht
 * Last modified date: 23/09/19
 * Description: Use this class to implement language translator functions
 */

namespace Core\Hus;

class HusTranslator
{
  /**
   * @var $_instance the unique instance of cache storage
   */
  private static $__instance = null;
  private static $__module = null;
  protected $translator;

  public function __construct()
  {
    $this->translator = \Laminas\I18n\Translator\Translator::factory([
      'locale' => 'en_US',
      'translation_file_patterns' => [
        [
          'type'     => 'phparray',
          'base_dir' => ROOT_DIR . DS . 'module' . DS . self::$__module . DS . 'language',
          'pattern'  => '%s.php',
        ],
      ]
    ]);

    if (strcmp(self::$__module, 'Application') !== 0) {
      $filename = ROOT_DIR . DS . 'module' . DS . 'Application' . DS . 'language';
      $this->translator->addTranslationFilePattern('phparray', $filename, '%s.php');
    }

    $request = new \Laminas\Http\PhpEnvironment\Request();
    $myCookie = $request->getCookie();
    if (!empty($myCookie) && $myCookie->offsetExists('lang')) {
      $this->translator->setLocale($myCookie->lang);
    }

    return $this;
  }

  public static function getInstance($module = 'Application')
  {
    if (null === self::$__instance || ($module !== self::$__module)) {
      $thisClass = __CLASS__;
      self::$__module = $module;
      self::$__instance = new $thisClass();
    }
    return self::$__instance;
  }

  public function getLocale()
  {
    return $this->translator->getLocale();
  }

  public function getLang($key)
  {
    return $this->translator->translate($key);
  }
}
