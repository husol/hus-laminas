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

  public function upload($dirName, $id = 0)
  {
    try {
      $uploadsDir = ROOT_DIR.DS.'public'.DS.'uploads';
      if (!is_dir($uploadsDir.DS.$dirName)) {
        mkdir($uploadsDir.DS.$dirName, 0755, true);
      }
      $fileName = (($id > 0) ? "{$id}_" : '') . trim(preg_replace('/\s+/', '_', codau2khongdau($this->file['name'])));
      $pathDest = DS.$dirName.DS.$fileName;
      move_uploaded_file($this->file['tmp_name'], $uploadsDir.$pathDest);

      return ['status' => true, 'path' => $pathDest, 'pathUrl' => str_replace('\\', '/', $pathDest)];
    }
    catch (\Exception $e) {
      return ['status' => false, 'message' => $e->getMessage()];
    }
  }

  public function uploadToS3($type = 'images', $pathDir, $name, $prefix = '', $suffix = '')
  {
    $configHus = \Laminas\Config\Factory::fromFile(ROOT_DIR . '/module/Application/config/config.php');
    //Upload file
    //$curDateDir = getCurrentDateDirName();  //path format: ../2009/September/
    $pathDir = "{$pathDir}/";
    $extPart = substr(strrchr($this->file['name'], '.'), 1);

    $namePart = empty($name) ? time() : $name;
    if ($prefix != '') {
      $namePart = $prefix . "_" . $namePart;
    }
    if ($suffix != '') {
      $namePart = $namePart . "_" . $suffix;
    }
    $name = $namePart . '.' . $extPart;
    $pathConf = $type == 'images' ? $configHus['PATH_CONFIG'][$type]['imageDirectory'] : $configHus['PATH_CONFIG'][$type]['fileDirectory'];
    $file = $pathConf. $pathDir . $name;

    $uploader = new HusUploader($configHus['S3_CONFIG'], $file, ['file' => $this->file['tmp_name'], 'name' => $this->file['name']], true);
    $result = $uploader->upload();

    if ($result['error'] == 0 && $type == 'images') {
      //Create thumb image
      $nameThumbPart = substr($name, 0, strrpos($name, '.'));
      $nameThumb = $nameThumbPart . '_thumb.' . $extPart;
      $thumbImage = $configHus['ASSET_CONFIG']['tmpDir'] . $nameThumb;

      $this->resizeImageThumbnail(
        $this->file['tmp_name'],
        $thumbImage,
        $configHus['PATH_CONFIG'][$type]['imageThumbWidth'],
        $configHus['PATH_CONFIG'][$type]['imageThumbHeight']
      );
      $imageThumb = $configHus['PATH_CONFIG'][$type]['imageDirectory'] . $pathDir . $nameThumb;
      $uploader = new HusUploader($configHus['S3_CONFIG'], $imageThumb, $thumbImage);
      $uploader->upload();

      //Create small image
      $nameSmallPart = substr($name, 0, strrpos($name, '.'));
      $nameSmall = $nameSmallPart . '_small.' . $extPart;
      $smallImage = $configHus['ASSET_CONFIG']['tmpDir'] . $nameSmall;

      $this->resizeImageThumbnail(
        $this->file['tmp_name'],
        $smallImage,
        $configHus['PATH_CONFIG'][$type]['imageSmallWidth'],
        $configHus['PATH_CONFIG'][$type]['imageSmallHeight']
      );
      $imageSmall = $configHus['PATH_CONFIG'][$type]['imageDirectory'] . $pathDir . $nameSmall;
      $uploader = new HusUploader($configHus['S3_CONFIG'], $imageSmall, $smallImage);
      $uploader->upload();

      //Create medium image
      $nameMediumPart = substr($name, 0, strrpos($name, '.'));
      $nameMedium = $nameMediumPart . '_medium.' . $extPart;
      $mediumImage = $configHus['ASSET_CONFIG']['tmpDir'] . $nameMedium;

      $this->resizeImageThumbnail(
        $this->file['tmp_name'],
        $mediumImage,
        $configHus['PATH_CONFIG'][$type]['imageMediumWidth'],
        $configHus['PATH_CONFIG'][$type]['imageMediumHeight']
      );
      $imageMedium = $configHus['PATH_CONFIG'][$type]['imageDirectory'] . $pathDir . $nameMedium;
      $uploader = new HusUploader($configHus['S3_CONFIG'], $imageMedium, $mediumImage);
      $uploader->upload();

      //Create full image
      $nameFullPart = substr($name, 0, strrpos($name, '.'));
      $nameFull = $nameFullPart . '_full.' . $extPart;
      $fullImage = $configHus['ASSET_CONFIG']['tmpDir'] . $nameFull;

      $this->resizeImageThumbnail(
        $this->file['tmp_name'],
        $fullImage,
        $configHus['PATH_CONFIG'][$type]['imageMaxWidth'],
        $configHus['PATH_CONFIG'][$type]['imageMaxHeight']
      );
      $imageFull = $configHus['PATH_CONFIG'][$type]['imageDirectory'] . $pathDir . $nameFull;
      $uploader = new HusUploader($configHus['S3_CONFIG'], $imageFull, $fullImage);
      $uploader->upload();
    }

    return $result;
  }

  public function resizeImageThumbnail($source, $destination, $thumbWidth, $thumbHeight)
  {
    $size = getimagesize($source);
    $imageWidth = $newWidth = $size[0];
    $imageHeight = $newHeight = $size[1];
    $extension = image_type_to_extension($size[2]);

    if ($imageWidth > $thumbWidth || $imageHeight > $thumbHeight) {
      // Calculate the ratio
      $xscale = ($imageWidth / $thumbWidth);
      $yscale = ($imageHeight / $thumbHeight);
      $newWidth = ($yscale > $xscale) ? round($imageWidth * (1 / $yscale)) : round($imageWidth * (1 / $xscale));
      $newHeight = ($yscale > $xscale) ? round($imageHeight * (1 / $yscale)) : round($imageHeight * (1 / $xscale));
    }

    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    switch ($extension) {
      case '.jpeg':
      case '.jpg':
        $imageCreateFrom = 'imagecreatefromjpeg';
        $store = 'imagejpeg';
        break;

      case '.png':
        $imageCreateFrom = 'imagecreatefrompng';
        $store = 'imagepng';
        //Removing the black from the placeholder
        $background = imagecolorallocate($newImage, 0, 0, 0);
        imagecolortransparent($newImage, $background);
        break;

      case '.gif':
        $imageCreateFrom = 'imagecreatefromgif';
        $store = 'imagegif';
        break;

      default:
        return false;
    }

    $container = $imageCreateFrom($source);

    imagecopyresampled($newImage, $container, 0, 0, 0, 0, $newWidth, $newHeight, $imageWidth, $imageHeight);

    // Fix Orientation
    $exif = @exif_read_data($source);

    if (isset($exif['Orientation'])) {
      $orientation = $exif['Orientation'];

      switch ($orientation) {
        case 3:
          $newImage = imagerotate($newImage, 180, 0);
          break;
        case 6:
          $newImage = imagerotate($newImage, -90, 0);
          break;
        case 8:
          $newImage = imagerotate($newImage, 90, 0);
          break;
      }
    }

    return $store($newImage, $destination);
  }
}
