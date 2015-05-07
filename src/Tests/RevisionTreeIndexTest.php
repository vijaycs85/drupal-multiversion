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
          'entity_type_id' => 'entity_test',
          'entity_id' => 1,
          'revision_id' => 1,
          'uuid' => $uuid,
          'rev' => $revs[0],
          'status' => 'available',
          'open_rev' => FALSE,
          'conflict' => FALSE,
          'default' => FALSE,
        ),
        'children' => array(
          array(
            '#type' => 'rev',
            '#rev' => $revs[1],
            '#rev_info' => array(
              'entity_type_id' => 'entity_test',
              'entity_id' => 1,
              'revision_id' => 2,
              'uuid' => $uuid,
              'rev' => $revs[1],
              'status' => 'available',
              'open_rev' => FALSE,
              'conflict' => FALSE,
              'default' => FALSE,
            ),
            'children' => array(
              array(
                '#type' => 'rev',
                '#rev' => $revs[2],
                '#rev_info' => array(
                  'entity_type_id' => 'entity_test',
                  'entity_id' => 1,
                  'revision_id' => 3,
                  'uuid' => $uuid,
                  'rev' => $revs[2],
                  'status' => 'available',
                  'open_rev' => FALSE,
                  'conflict' => FALSE,
                  'default' => FALSE,
                ),
                'children' => array(
                  array(
                    '#type' => 'rev',
                    '#rev' => $revs[3],
                    '#rev_info' => array(
                      'entity_type_id' => 'entity_test',
                      'entity_id' => 1,
                      'revision_id' => 4,
                      'uuid' => $uuid,
                      'rev' => $revs[3],
                      'status' => 'available',
                      'open_rev' => TRUE,
                      'conflict' => TRUE,
                      'default' => FALSE,
                    ),
                    'children' => array(),
                  ),
                  array(
                    '#type' => 'rev',
                    '#rev' => $revs[4],
                    '#rev_info' => array(
                      'entity_type_id' => 'entity_test',
                      'entity_id' => 1,
                      'revision_id' => 5,
                      'uuid' => $uuid,
                      'rev' => $revs[4],
                      'status' => 'available',
                      'open_rev' => FALSE,
                      'conflict' => FALSE,
                      'default' => FALSE,
                    ),
                    'children' => array(
                      array(
                        '#type' => 'rev',
                        '#rev' => $revs[5],
                        '#rev_info' => array(
                          'entity_type_id' => 'entity_test',
                          'entity_id' => 1,
                          'revision_id' => 6,
                          'uuid' => $uuid,
                          'rev' => $revs[5],
                          'status' => 'available',
                          'open_rev' => TRUE,
                          'conflict' => FALSE,
                          'default' => TRUE,
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
                  'entity_type_id' => 'entity_test',
                  'entity_id' => 1,
                  'revision_id' => 7,
                  'uuid' => $uuid,
                  'rev' => $revs[6],
                  'status' => 'available',
                  'open_rev' => TRUE,
                  'conflict' => TRUE,
                  'default' => FALSE,
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

    $tree = $this->tree->get($uuid);
    $this->assertEqual($tree, $expected_tree, 'Tree was correctly parsed.');
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
