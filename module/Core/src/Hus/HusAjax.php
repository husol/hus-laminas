<?php
/**
 * Last modifier: khoaht
 * Last modified date: 26/09/18
 * Description: Use this class to deal with ajax request
 */

namespace Core\Hus;

class AjaxElement
{
    protected $id;
    public $innerHTML;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function process()
    {
        $html = str_replace(["\n","\r","\t","/","\""], ["\\n","\\r","\\t","\/",'\"'], $this->innerHTML);
        $result =  "__{$this->id} = document.getElementById(\"{$this->id}\");__{$this->id}.innerHTML=\"".$html."\";";
        return $result;
    }
}

class HusAjax
{
    protected static $messages;

    public static function &getElementById($id)
    {
        $element = new AjaxElement($id);
        $GLOBALS['_HUS_HTML_DATA'][] =& $element;
        return $element;
    }

    public static function setHtml($id, $content)
    {
        self::getElementById($id)->innerHTML = $content;
    }

    public static function setMessage($content)
    {
        $messages = self::$messages . "<span>$content</span><br/>";
        self::$messages = $messages;
    }

    public static function outData($resultData = true)
    {
        ob_start("ob_gzhandler");
        header('Content-Type: application/json; charset=utf-8');

        $outPut = new \stdClass;

        $outPut->result = is_string($resultData) ? self::dataConvert($resultData) : $resultData;

        //get innerHTML
        $htm = array();
        if (isset($GLOBALS['_HUS_HTML_DATA'])) {
            $htmls =  $GLOBALS['_HUS_HTML_DATA'];
            foreach ($htmls as $html) {
                $htm[] = $html->process();
            }
            unset($GLOBALS['_HUS_HTML_DATA']);
        }

        $outPut->html = base64_encode(self::dataConvert(implode('', $htm)));
        $outPut->messages = self::$messages;

        echo json_encode($outPut);
        ob_end_flush();
        die();
    }

    //UTF-8 encoding:
    public static function dataConvert($var)
    {
        $ascii = '';
        $length = strlen($var);
        for ($iterator = 0; $iterator < $length; $iterator ++) {
            $char = $var[$iterator];
            $charCode = ord($char);
            if ($charCode < 128) {
                $ascii .= $char;
            } elseif ($charCode >> 5 == 6) {
                $byteOne = ($charCode & 31);
                $iterator ++;
                $char = $var[$iterator];
                $charCode = ord($char);
                $byteTwo = ($charCode & 63);
                $charCode = ($byteOne * 64) + $byteTwo;
                $ascii .= sprintf('\u%04s', dechex($charCode));
            } elseif ($charCode >> 4 == 14) {
                $byteOne = ($charCode & 31);
                $iterator ++;
                $char = $var[$iterator];
                $charCode = ord($char);
                $byteTwo = ($charCode & 63);
                $iterator ++;
                $char = $var[$iterator];
                $charCode = ord($char);
                $byteThree = ($charCode & 63);
                $charCode = ((($byteOne * 64) + $byteTwo) * 64) + $byteThree;
                $ascii .= sprintf('\u%04s', dechex($charCode));
            } elseif ($charCode >> 3 == 30) {
                $byteOne = ($charCode & 31);
                $iterator ++;
                $char = $var[$iterator];
                $charCode = ord($char);
                $byteTwo = ($charCode & 63);
                $iterator ++;
                $char = $var[$iterator];
                $charCode = ord($char);
                $byteThree = ($charCode & 63);
                $iterator ++;
                $char = $var[$iterator];
                $charCode = ord($char);
                $byteFour = ($charCode & 63);
                $charCode = ((((($byteOne * 64) + $byteTwo) * 64) + $byteThree) * 64) + $byteFour;
                $ascii .= sprintf('\u%04s', dechex($charCode));
            }
        }
        return $ascii;
    }
}
