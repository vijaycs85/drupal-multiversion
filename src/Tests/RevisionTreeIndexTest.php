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
        '#rev' => $revs[0],
        '#open_rev' => FALSE,
        '#status' => 'available',
        '#default' => FALSE,
        '#conflict' => FALSE,
        'children' => array(
          array(
            '#rev' => $revs[1],
            '#open_rev' => FALSE,
            '#status' => 'available',
            '#default' => FALSE,
            '#conflict' => FALSE,
            'children' => array(
              array(
                '#rev' => $revs[2],
                '#open_rev' => FALSE,
                '#status' => 'available',
                '#default' => FALSE,
                '#conflict' => FALSE,
                'children' => array(
                  array(
                    '#rev' => $revs[3],
                    '#open_rev' => TRUE,
                    '#status' => 'available',
                    '#default' => FALSE,
                    '#conflict' => TRUE,
                    'children' => array(),
                  ),
                  array(
                    '#rev' => $revs[4],
                    '#open_rev' => FALSE,
                    '#status' => 'available',
                    '#default' => FALSE,
                    '#conflict' => FALSE,
                    'children' => array(
                      array(
                        '#rev' => $revs[5],
                        '#open_rev' => TRUE,
                        '#status' => 'available',
                        '#default' => TRUE,
                        '#conflict' => FALSE,
                        'children' => array(),
                      )
                    )
                  )
                )
              ),
              array(
                '#rev' => $revs[6],
                '#open_rev' => TRUE,
                '#status' => 'available',
                '#default' => FALSE,
                '#conflict' => TRUE,
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
