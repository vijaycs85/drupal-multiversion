<?php

namespace Drupal\multiversion\Entity\Compaction;

interface CompactionManagerInterface {

  /**
   * Purge revisions that are marked as deleted.
   *
   * @return integer
   */
  public function compact();

}
