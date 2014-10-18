<?php

namespace Drupal\multiversion\Entity\Index;

use Drupal\Core\Entity\ContentEntityInterface;

interface SequenceIndexInterface {

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param boolean $conflict
   */
  public function add(ContentEntityInterface $entity, $conflict = FALSE);

  /**
   * @param float $start
   * @param float $stop
   *
   * @return array
   */
  public function getRange($start, $stop = NULL);

  public function useWorkspace($name);

  /**
   * @return float
   */
  public function getLastSequenceId();

}
