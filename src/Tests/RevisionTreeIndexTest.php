<?php

namespace Drupal\multiversion\Tests;

/**
 * Test the methods on the RevisionTreeIndex class.
 *
 * @group multiversion
 */
class RevisionTreeIndexTest extends MultiversionWebTestBase {

  public static $modules = array('entity_test', 'multiversion');

  /**
   * @var \Drupal\multiversion\Workspace\WorkspaceManagerInterface
   */
  protected $workspaceManager;

  /**
   * @var \Drupal\multiversion\Entity\Index\RevisionTreeIndex
   */
  protected $tree;

  protected function setUp() {
    parent::setUp();

    $this->tree = $this->container->get('entity.index.rev.tree');
  }

  public function testMethods() {
    $entity = entity_create('entity_test');
    $uuid = $entity->uuid();

    // Create a conflict scenario to fully test the parsing.

    // Initial revision.
    $entity->save();
    $revs[] = $entity->_rev->value;

    $entity->save();
    $revs[] = $entity->_rev->value;

    $entity->save();
    $revs[] = $entity->_rev->value;

    $entity->save();
    $revs[] = $entity->_rev->value;

    // Create a new branch from the third revision.
    $entity = entity_revision_load('entity_test', 3);
    $entity->save();
    $revs[] = $entity->_rev->value;

    // Continue the newly created branch.
    $entity->save();
    $revs[] = $entity->_rev->value;

    // Create a new branch based on the second revision.
    $entity = entity_revision_load('entity_test', 2);
    $entity->save();
    $revs[] = $entity->_rev->value;

    $expected_tree = array(
      array(
        '#type' => 'rev',
        '#rev' => $revs[0],
        '#rev_info' => array(
          'status' => 'available',
          'default' => FALSE,
          'open_rev' => FALSE,
          'conflict' => FALSE,
        ),
        'children' => array(
          array(
            '#type' => 'rev',
            '#rev' => $revs[1],
            '#rev_info' => array(
              'status' => 'available',
              'default' => FALSE,
              'open_rev' => FALSE,
              'conflict' => FALSE,
            ),
            'children' => array(
              array(
                '#type' => 'rev',
                '#rev' => $revs[2],
                '#rev_info' => array(
                  'status' => 'available',
                  'default' => FALSE,
                  'open_rev' => FALSE,
                  'conflict' => FALSE,
                ),
                'children' => array(
                  array(
                    '#type' => 'rev',
                    '#rev' => $revs[3],
                    '#rev_info' => array(
                      'status' => 'available',
                      'default' => FALSE,
                      'open_rev' => TRUE,
                      'conflict' => TRUE,
                    ),
                    'children' => array(),
                  ),
                  array(
                    '#type' => 'rev',
                    '#rev' => $revs[4],
                    '#rev_info' => array(
                      'status' => 'available',
                      'default' => FALSE,
                      'open_rev' => FALSE,
                      'conflict' => FALSE,
                    ),
                    'children' => array(
                      array(
                        '#type' => 'rev',
                        '#rev' => $revs[5],
                        '#rev_info' => array(
                          'status' => 'available',
                          'default' => TRUE,
                          'open_rev' => TRUE,
                          'conflict' => FALSE,
                        ),
                        'children' => array(),
                      )
                    )
                  )
                )
              ),
              array(
                '#type' => 'rev',
                '#rev' => $revs[6],
                '#rev_info' => array(
                  'status' => 'available',
                  'default' => FALSE,
                  'open_rev' => TRUE,
                  'conflict' => TRUE,
                ),
                'children' => array(),
              )
            )
          )
        )
      ),
    );
    // Sort the expected tree according to the algorithm.
    self::sortRevisionTree($expected_tree);

    $tree = $this->tree->getTree($uuid);
    $this->assertEqual($tree, $expected_tree, 'Tree was correctly parsed.');

    $default_rev = $this->tree->getDefaultRevision($uuid);
    $this->assertEqual($default_rev, $revs[5], 'Default revision is correct.');

    $expected_default_branch = array(
      $revs[0] => 'available',
      $revs[1] => 'available',
      $revs[2] => 'available',
      $revs[4] => 'available',
      $revs[5] => 'available',
    );
    $default_branch = $this->tree->getDefaultBranch($uuid);
    $this->assertEqual($default_branch, $expected_default_branch, 'Default branch is correct.');

    $expected_open_revision = array(
      $revs[3] => 'available',
      $revs[5] => 'available',
      $revs[6] => 'available',
    );
    $open_revisions = $this->tree->getOpenRevisions($uuid);
    $this->assertEqual($open_revisions, $expected_open_revision, 'Open revisions are correct.');

    $expected_conflicts = array(
      $revs[3] => 'available',
      $revs[6] => 'available',
    );
    $conflicts = $this->tree->getConflicts($uuid);
    $this->assertEqual($conflicts, $expected_conflicts, 'Conflicts are correct');
  }

  /**
   * Helper method that sorts a tree according to the revision tree algorithm.
   */
  protected static function sortRevisionTree(&$tree) {
    usort($tree, function($a, $b) {
      return ($a['#rev'] < $b['#rev']) ? -1 : 1;
    });
    foreach ($tree as &$element) {
      if (!empty($element['children'])) {
        self::sortRevisionTree($element['children']);
      }
    }
  }

}
