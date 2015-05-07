<?php

namespace Drupal\multiversion\Entity\Index;

use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\multiversion\Workspace\WorkspaceManagerInterface;

/**
 * @todo Implement caching in a way that avoids stale trees and race conditions.
 */
class RevisionTreeIndex implements RevisionTreeIndexInterface {

  /**
   * @var \Drupal\Core\KeyValueStore\KeyValueFactoryInterface
   */
  protected $keyValueFactory;

  /**
   * @var \Drupal\multiversion\Workspace\WorkspaceManagerInterface
   */
  protected $workspaceManager;

  /**
   * @var \Drupal\multiversion\Entity\Index\RevisionIndexInterface
   */
  protected $revIndex;

  /**
   * @var string
   */
  protected $workspaceId;

  /**
   * @var array
   */
  protected $cache = array();

  /**
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $key_value_factory
   * @param \Drupal\multiversion\Workspace\WorkspaceManagerInterface $workspace_manager
   * @param \Drupal\multiversion\Entity\Index\RevisionIndexInterface $rev_index
   */
  function __construct(KeyValueFactoryInterface $key_value_factory, WorkspaceManagerInterface $workspace_manager, RevisionIndexInterface $rev_index) {
    $this->keyValueFactory = $key_value_factory;
    $this->workspaceManager = $workspace_manager;
    $this->revIndex = $rev_index;
  }

  /**
   * {@inheritdoc}
   */
  public function useWorkspace($id) {
    $this->workspaceId = $id;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function get($uuid) {
    $revs = $this->keyValueStore($uuid)->getAll();
    $revs_info = $this->revIndex->getMultiple(array_keys($revs));

    // Build the tree recursively.
    $tree = self::buildTree($revs, $revs_info);

    return $tree;
  }

  /**
   * {@inheritdoc}
   */
  public function update($uuid, array $branch = array()) {
    $this->keyValueStore($uuid)->setMultiple($branch);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultRevision($uuid) {
    $tree = $this->get($uuid);
    // @todo
  }

  /**
   * {@inheritdoc}
   */
  public function getOpenRevisions($uuid) {
    $tree = $this->get($uuid);
    // @todo
  }

  /**
   * {@inheritdoc}
   */
  public function getConflicts($uuid) {
    $open_revisions = $this->getOpenRevisions($uuid);
    // @todo
  }

  /**
   * @param string $uuid
   * @return \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected function keyValueStore($uuid) {
    $workspace_id = $this->workspaceId ?: $this->workspaceManager->getActiveWorkspace()->id();
    return $this->keyValueFactory->get("entity.index.open_revs.$workspace_id.$uuid");
  }

  /**
   * Helper method to recursively build the tree.
   */
  protected static function buildTree($revs, $revs_info, $parse = 0, &$tree = array(), &$open_revs = array(), &$conflicts = array()) {
    foreach ($revs as $rev => $parent_rev) {
      if ($parent_rev == $parse) {
        $rev_info_defaults = array(
          'entity_type_id' => NULL,
          'entity_id' => NULL,
          'revision_id' => NULL,
          'uuid' => NULL,
          'rev' => $rev,
          'status' => 'missing',
          'open_rev' => FALSE,
          'conflict' => FALSE,
          'default' => FALSE,
        );

        // Build an element structure compatible with Drupal's Render API.
        $i = count($tree);
        $tree[$i] = array(
          '#type' => 'rev',
          '#rev' => $rev,
          '#rev_info' => isset($revs_info[$rev]) ? array_merge($rev_info_defaults, $revs_info[$rev]) : $rev_info_defaults,
          'children' => array(),
        );

        self::buildTree($revs, $revs_info, $rev, $tree[$i]['children'], $open_revs, $conflicts);

        // Sort all tree elements from low to high.
        usort($tree, function($a, $b) {
          return ($a['#rev'] < $b['#rev']) ? -1 : 1;
        });

        if (empty($tree[$i]['children'])) {
          $tree[$i]['#rev_info']['open_rev'] = TRUE;
          $open_revs[$rev] = $tree[$i]['#rev_info']['status'];
          // All open revisions, except deleted and default revisions, are
          // conflicts by definition. We will revert the conflict flag when we
          // find the default revision later on.
          if ($tree[$i]['#rev_info']['status'] != 'deleted') {
            $tree[$i]['#rev_info']['conflict'] = TRUE;
          }
        }
      }
    }

    // Now when the full tree is built we'll find the default revision and
    // revert its conflict flag.
    if ($parse == 0) {
      // Sort from high to low and find the default revision.
      krsort($open_revs);
      foreach ($open_revs as $open_rev => $status) {
        if ($status != 'missing') {
          $default_rev = $open_rev;
          break;
        }
      }
      self::updateDefaultRevision($tree, $default_rev);
    }

    return $tree;
  }

  /**
   * Helper method to update the default revision.
   */
  protected static function updateDefaultRevision(&$tree, $default_rev) {
    // @todo: We can temporarily flip the sort to find the default rev earlier.
    foreach ($tree as &$element) {
      if (isset($element['#rev']) && $element['#rev'] == $default_rev) {
        $element['#rev_info']['default'] = TRUE;
        $element['#rev_info']['conflict'] = FALSE;
        break;
      }
      if (!empty($element['children'])) {
        self::updateDefaultRevision($element['children'], $default_rev);
      }
    }
  }
}
