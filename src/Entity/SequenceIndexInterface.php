<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Entity\ContentEntityInterface;

interface SequenceIndexInterface {

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param integer $parent_revision_id
   * @param boolean $conflict
   */
  public function add(ContentEntityInterface $entity, $parent_revision_id, $conflict = FALSE);

  /**
   * @param float $start
   * @param float $stop
   *
   * @return array
   */
  public function getRange($start, $stop = NULL);

  public function useWorkspace($name);

}