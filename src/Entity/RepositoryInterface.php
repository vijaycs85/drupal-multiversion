<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Entity\EntityInterface;

interface RepositoryInterface extends EntityInterface {

  /**
   * Returns the repository name.
   *
   * @return string
   *   The name of the repository.
   */
  public function name();

}
