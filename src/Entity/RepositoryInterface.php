<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Entity\EntityInterface;

interface RepositoryInterface extends EntityInterface {

  public function name();

}
