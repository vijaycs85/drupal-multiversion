<?php

namespace Drupal\multiversion\Entity\Query\Sql;

use Drupal\Core\Entity\Query\Sql\Query as CoreQuery;
use Drupal\multiversion\Entity\Query\QueryInterface;
use Drupal\multiversion\Entity\Query\QueryTrait;

class Query extends CoreQuery implements QueryInterface {

  use QueryTrait;

}
