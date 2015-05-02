<?php

namespace Drupal\multiversion\Entity\Index;

interface RevisionTreeIndexInterface {

  /**
   * @param $workspace_id
   * @return \Drupal\multiversion\Entity\Index\EntityIndexInterface
   */
  public function useWorkspace($workspace_id);

  /**
   * @param string $uuid
   *
   * @return array
   */
  public function get($uuid);

  /**
   * @param array $branch
   */
  public function update($uuid, array $branch = array());

  /**
   * @param string $uuid
   *
   * @return string
   */
  public function getDefaultRevision($uuid);

  /**
   * @param string $uuid
   *
   * @return string[]
   */
  public function getOpenRevisions($uuid);

}
