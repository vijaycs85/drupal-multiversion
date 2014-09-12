<?php

namespace Drupal\multiversion\Entity\Sequence;

interface SequenceFactoryInterface {

  public function useWorkspace($workspace_name);

  public function currentWorkspace();

  public function workspace($workspace_name = NULL);
}
