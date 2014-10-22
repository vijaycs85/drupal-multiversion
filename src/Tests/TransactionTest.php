<?php

namespace Drupal\multiversion\Tests;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\multiversion\Entity\Transaction\AllOrNothingTransaction;

/**
 * Test the transaction functionality.
 *
 * @group multiversion
 */
class TransactionTest extends MultiversionWebTestBase {

  public static $modules = array('multiversion');

  public function testTransactionDecorator() {
    $storage = $this->entityManager->getStorage('entity_test');
    // Create a transaction decorator.
    $trx = AllOrNothingTransaction::createInstance($this->container, $storage);
    $entity = $storage->create();
    $trx->save($entity);
    $entity_id = $entity->id();
    $this->assertTrue(!empty($entity_id), 'The transaction decorator saved an entity successfully.');

    $return = entity_load('entity_test', $entity_id);
    $this->assertTrue(empty($return), 'Loading an entity before commit returns nothing.');

    // Commit the transaction.
    $trx->commit();

    $return = entity_load('entity_test', $entity_id);
    $this->assertTrue(!empty($return), 'Loading an entity after commit returns the entity.');

    // Create a new transaction decorator.
    $trx = AllOrNothingTransaction::createInstance($this->container, $storage);

    // Create multiple entities that will be saved in one single transaction.
    // The last entity will throw an error, and demonstrate that none of the
    // entities become available since the transaction failed.
    $entities = array();
    $entities[] = $trx->create();
    $entities[] = $trx->create();
    // Set the ID to an existing entity and enforce the isNew flag. This should
    // throw an exception.
    $entities[1]->id->value = 1;
    $entities[1]->enforceIsNew();

    try {
      foreach ($entities as $entity) {
        $trx->save($entity);
      }
      $this->fail('EntityStorageException was thrown and transaction did not get committed.');
      // We expect an exception to be thrown above. So this commit will in
      // theory never get executed.
      $trx->commit();
    }
    catch (EntityStorageException $e) {
      $this->pass('EntityStorageException was thrown and transaction did not get committed.');
    }

    // Load all entities. This should only return ONE entity, i.e. the one
    // created in the first successful transaction at the beginning).
    $entities = $storage->loadMultiple(NULL);
    $this->assertEqual(count($entities), 1, 'The entities saved during the failed transaction was not made available.');
  }

}
