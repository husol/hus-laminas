<?php

namespace Application\Repository;

class RoomRepo extends VlsRepo
{
  const PAGE_SIZE = 20;

  public function __construct($container)
  {
    parent::__construct($container);
    $this->apiHost = $this->config['SERVICES']['HUS_HOST']. DS. 'pms';
    $this->entity = 'rooms';
  }
}
