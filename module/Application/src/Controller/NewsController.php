<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Application\Controller;

use Laminas\View\Model\ViewModel;

class NewsController extends HusController
{
  public function __construct($container)
  {
    parent::__construct($container);
  }

  public function indexAction()
  {
    $doc = new \DOMDocument();
    $doc->load('http://vnexpress.net/rss/so-hoa.rss');

    $newses = [];
    foreach ($doc->getElementsByTagName('item') as $node) {
      $itemRSS = array (
        'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
        'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
        'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
        'pubDate' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue
      );

      $newses[] = $itemRSS;
    }

    return new ViewModel(['newses' => $newses]);
  }
}
