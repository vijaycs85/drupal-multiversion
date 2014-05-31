<?php

namespace Drupal\multiversion\Entity\Sequence;

interface SequenceFactoryInterface {

  public function useRepository($repository_name);

  public function currentRepository();

  public function repository($repository_name = NULL);
}
