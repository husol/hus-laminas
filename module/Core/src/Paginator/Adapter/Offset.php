<?php

namespace Core\Paginator\Adapter;

use Laminas\Paginator\Adapter\AdapterInterface;

class Offset implements AdapterInterface
{
  protected $_count = 0;
  protected $_iterator = null;
  public function __construct($iterator, $count)
  {
    $this->_count = $count;
    $this->_iterator = $iterator;
    
  }

  public function count()
  {
    return $this->_count;
  }

  public function getItems($offset, $itemCountPerPage)
  {
    return array_slice((array)$this->_iterator, $offset, $itemCountPerPage);
  }
}
