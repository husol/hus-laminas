<?php
namespace Core\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class Pagination extends AbstractHelper
{
  private $resultsPerPage;
  private $totalResults;
  private $results;
  private $baseUrl;
  private $paging;
  private $page;

  public function __invoke($pagedResults, $page, $baseUrl, $resultsPerPage=10)
  {
    $this->resultsPerPage = $resultsPerPage;
    $this->totalResults = $pagedResults->count();
    $this->results = $pagedResults;
    $this->baseUrl = $baseUrl;
    $this->page = $page;
    return $this->generatePaging();
  }

  /**
   * Generate paging html
   */
  private function generatePaging()
  {
    // Get total page count
    $pages = ceil($this->totalResults / $this->resultsPerPage);

    // Don't show pagination if there's only one page
    if($pages == 1)
    {
      return;
    }

    // Show back to first page if not first page
    if($this->page != 1)
    {
      $this->paging = '<a href="' . $this->baseUrl . 'page/1"><<</a>';
    }

    // Create a link for each page
    $pageCount = 1;
    while($pageCount <= $pages)
    {
      $this->paging .= '<a href="' . $this->baseUrl . 'page/' . $pageCount . '">' . $pageCount . '</a>';
      $pageCount++;
    }

    // Show go to last page option if not the last page
    if($this->page != $pages)
    {
      $this->paging .= '<a href="'. $this->baseUrl . 'page/' . $pages . '">>></a>';
    }

    return $this->paging;
  }
}
