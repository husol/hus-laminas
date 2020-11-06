<?php
/**  * Description of Uploader  *  * @author khoaht */

namespace Core\Hus;

class HusUploader
{
  const ERROR_UPLOAD_OK = 0;
  const ERROR_UPLOAD_UNKNOWN = 1;
  const ERROR_FILESIZE = 2;
  const ERROR_FILETYPE = 4;

  public $endpoint = "";
  public $key = "";
  public $secret = "";
  public $bucket = "";
  public $pathfilename = "";
  public $sourcefile = "";
  public $sourcename = "";
  public $checkimage = false;
  public $maxfilesize = 0;

  public function __construct($s3_config, $new_path_filename, $source, $check_image = false, $max_file_size = 0)
  {
    $this->endpoint = $s3_config['ENDPOINT'];
    $this->key = $s3_config['KEY'];
    $this->secret = $s3_config['SECRET'];
    $this->bucket = $s3_config['BUCKET'];
    $this->pathfilename = $new_path_filename;
    if (is_array($source)) {
      $this->sourcefile = $source['file'];
      $this->sourcename = $source['name'];
    } else {
      $this->sourcefile = $source;
      $this->sourcename = basename($source);
    }

    $this->checkimage = $check_image;
    $this->maxfilesize = intval($max_file_size) > 0
      ? $this->returnByte($max_file_size)
      : $this->returnByte(ini_get('upload_max_filesize'));
  }

  public function upload()
  {
    $error = 0;

    //check file size
    if (filesize($this->sourcefile) > $this->maxfilesize) {
      $error = $error | self::ERROR_FILESIZE;
    }

    //check image file
    $ext = pathinfo($this->sourcename, PATHINFO_EXTENSION);
    if ($this->checkimage && !in_array(strtolower($ext), ['gif', 'jpg', 'jpeg', 'png'])) {
      $error = $error | self::ERROR_FILETYPE;
    }

    if ($error == 0) {
      $s3 = new \Aws\S3\S3Client([
        'version' => 'latest',
        'region' => 'ap-southeast-1',
//        'endpoint' => $this->endpoint,
//        'use_path_style_endpoint' => true,
        'credentials' => [
          'key' => $this->key,
          'secret' => $this->secret,
        ],
      ]);

      try {
        // Upload data.
        $result = $s3->putObject([
          'Bucket' => $this->bucket,
          'Key' => $this->pathfilename,
          'SourceFile' => $this->sourcefile,
          'ACL' => 'public-read',
          'ContentType' => mime_content_type($this->sourcefile)
        ]);

        $arr = parse_url($result['ObjectURL']);

        return [
          'error' => self::ERROR_UPLOAD_OK,
          'path' => $arr['path']
        ];
      } catch (\Aws\S3\Exception\S3Exception $e) {
        return [
          'error' => self::ERROR_UPLOAD_UNKNOWN,
          'info' => $e->getMessage()
        ];
      }
    }

    return [
      'error' => $error,
      'info' => 'Invalid file size or file type.'
    ];
  }

  public function returnByte($val)
  {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch ($last) {
      case 'g':
        $val = intval($val)*1024*1024*1024;
        break;
      case 'm':
        $val = intval($val)*1024*1024;
        break;
      case 'k':
        $val = intval($val)*1024;
        break;
    }

    return $val;
  }
}

