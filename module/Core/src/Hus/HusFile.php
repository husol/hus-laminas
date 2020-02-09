<?php
/**
 * Last modifier: khoaht
 * Last modified date: 26/09/18
 * Description: Use this class to implement file functions
 */

namespace Core\Hus;

class HusFile
{
  protected $file = [];

  public function __construct($file)
  {
    $this->file = $file;
  }

  public function upload($destination, $id = 0)
  {
    try {
      if (!is_dir(ROOT_DIR.DS.'uploads'.DS.$destination)) {
        mkdir(ROOT_DIR.DS.'uploads'.DS.$destination, 0755, true);
      }
      $fileName = (($id > 0) ? "{$id}_" : '') . trim(preg_replace('/\s+/', '_', codau2khongdau($this->file['name'])));
      $pathDest = DS.$destination.$fileName;
      move_uploaded_file($this->file['tmp_name'], ROOT_DIR.DS.'uploads'.$pathDest);

      return ['status' => true, 'path' => $pathDest, 'pathUrl' => str_replace('\\', '/', $pathDest)];
    }
    catch (\Exception $e) {
      return ['status' => false, 'message' => $e->getMessage()];
    }
  }
}
