<?php

namespace Application\Repository;

class RoomRepo extends HusRepo
{
  const PAGE_SIZE = 20;

  public function __construct($container)
  {
    parent::__construct($container);
    $this->apiHost = $this->config['SERVICES']['HUS_HOST']. '/pms';
    $this->entity = 'rooms';
  }
}
