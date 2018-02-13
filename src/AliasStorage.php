<?php

namespace Drupal\multiversion;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Database\Query\Merge;
use Drupal\Core\Database\SchemaObjectExistsException;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Path\AliasStorage as CoreAliasStorage;
use Drupal\Core\State\StateInterface;
use Drupal\multiversion\Workspace\WorkspaceManagerInterface;


/**
 * Extends the core AliasStore class. We need this to make possible aliases to
 * work with Multiversion and Replication.
 */
class AliasStorage extends CoreAliasStorage {

  /**
   * The workspace manager.
   *
   * @var \Drupal\multiversion\Workspace\WorkspaceManagerInterface
   */
  private $workspaceManager;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $connection, ModuleHandlerInterface $module_handler, WorkspaceManagerInterface $workspace_manager, StateInterface $state) {
    parent::__construct($connection, $module_handler);
    $this->workspaceManager = $workspace_manager;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public function save($source, $alias, $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED, $pid = NULL) {
    if (!$this->state->get('multiversion.migration_done', FALSE)) {
      return parent::save($source, $alias, $langcode, $pid);
    }

    if ($source[0] !== '/') {
      throw new \InvalidArgumentException(sprintf('Source path %s has to start with a slash.', $source));
    }

    if ($alias[0] !== '/') {
      throw new \InvalidArgumentException(sprintf('Alias path %s has to start with a slash.', $alias));
    }

    $fields = [
      'source' => $source,
      'alias' => $alias,
      'langcode' => $langcode,
      'workspace' => $this->workspaceManager->getActiveWorkspace()->id(),
    ];

    // Insert or update the alias.
    if (empty($pid)) {
      $try_again = FALSE;
      try {
        $query = $this->connection->merge(static::TABLE)
          ->condition('workspace', $fields['workspace'])
          ->condition('source', $fields['source'])
          ->condition('langcode', $fields['langcode'])
          ->fields($fields);
        $result = $query->execute();
      }
      catch (\Exception $e) {
        // If there was an exception, try to create the table.
        if (!$try_again = $this->ensureTableExists()) {
          // If the exception happened for other reason than the missing table,
          // propagate the exception.
          throw $e;
        }
      }
      // Now that the table has been created, try again if necessary.
      if ($try_again) {
        $query = $this->connection->merge(static::TABLE)
          ->condition('workspace', $fields['workspace'])
          ->condition('source', $fields['source'])
          ->condition('langcode', $fields['langcode'])
          ->fields($fields);
        $result = $query->execute();
      }

      $pid = $this->connection->select(static::TABLE)
        ->fields(static::TABLE, ['pid'])
        ->condition('workspace', $fields['workspace'])
        ->condition('source', $fields['source'])
        ->condition('langcode', $fields['langcode'])
        ->condition('alias', $fields['alias'])
        ->execute()
        ->fetchField();
      $fields['pid'] = $pid;
      if ($result == Merge::STATUS_INSERT) {
        $operation = 'insert';
      }
      elseif ($result == Merge::STATUS_UPDATE) {
        $operation = 'update';
      }
    }
    else {
      // Fetch the current values so that an update hook can identify what
      // exactly changed.
      try {
        $original = $this->connection->query('SELECT source, alias, langcode FROM {url_alias} WHERE pid = :pid', [':pid' => $pid])
          ->fetchAssoc();
      }
      catch (\Exception $e) {
        $this->catchException($e);
        $original = FALSE;
      }
      $fields['pid'] = $pid;
      $query = $this->connection->update(static::TABLE)
        ->fields($fields)
        ->condition('pid', $pid);
      $pid = $query->execute();
      $fields['original'] = $original;
      $operation = 'update';
    }
    if ($pid) {
      // @todo Switch to using an event for this instead of a hook.
      $this->moduleHandler->invokeAll('path_' . $operation, [$fields]);
      Cache::invalidateTags(['route_match']);
      return $fields;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function lookupPathAlias($path, $langcode) {
    if (!$this->state->get('multiversion.migration_done', FALSE)) {
      return parent::lookupPathAlias($path, $langcode);
    }

    $source = $this->connection->escapeLike($path);
    $langcode_list = [$langcode, LanguageInterface::LANGCODE_NOT_SPECIFIED];

    // See the queries above. Use LIKE for case-insensitive matching.
    $select = $this->connection->select(static::TABLE)
      ->fields(static::TABLE, ['alias'])
      ->condition('source', $source, 'LIKE')
      ->condition('workspace', $this->workspaceManager->getActiveWorkspace()->id(), 'LIKE');
    if ($langcode == LanguageInterface::LANGCODE_NOT_SPECIFIED) {
      array_pop($langcode_list);
    }
    elseif ($langcode > LanguageInterface::LANGCODE_NOT_SPECIFIED) {
      $select->orderBy('langcode', 'DESC');
    }
    else {
      $select->orderBy('langcode', 'ASC');
    }

    $select->orderBy('pid', 'DESC');
    $select->condition('langcode', $langcode_list, 'IN');
    try {
      return $select->execute()->fetchField();
    }
    catch (\Exception $e) {
      $this->catchException($e);
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function lookupPathSource($path, $langcode) {
    if (!$this->state->get('multiversion.migration_done', FALSE)) {
      return parent::lookupPathSource($path, $langcode);
    }

    $alias = $this->connection->escapeLike($path);
    $langcode_list = [$langcode, LanguageInterface::LANGCODE_NOT_SPECIFIED];

    // See the queries above. Use LIKE for case-insensitive matching.
    $select = $this->connection->select(static::TABLE)
      ->fields(static::TABLE, ['source'])
      ->condition('alias', $alias, 'LIKE')
      ->condition('workspace', $this->workspaceManager->getActiveWorkspace()->id(), 'LIKE');
    if ($langcode == LanguageInterface::LANGCODE_NOT_SPECIFIED) {
      array_pop($langcode_list);
    }
    elseif ($langcode > LanguageInterface::LANGCODE_NOT_SPECIFIED) {
      $select->orderBy('langcode', 'DESC');
    }
    else {
      $select->orderBy('langcode', 'ASC');
    }

    $select->orderBy('pid', 'DESC');
    $select->condition('langcode', $langcode_list, 'IN');
    try {
      return $select->execute()->fetchField();
    }
    catch (\Exception $e) {
      $this->catchException($e);
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function aliasExists($alias, $langcode, $source = NULL) {
    if (!$this->state->get('multiversion.migration_done', FALSE)) {
      return parent::aliasExists($alias, $langcode, $source);
    }

    // Use LIKE and NOT LIKE for case-insensitive matching.
    $query = $this->connection->select(static::TABLE)
      ->condition('alias', $this->connection->escapeLike($alias), 'LIKE')
      ->condition('langcode', $langcode)
      ->condition('workspace', $this->workspaceManager->getActiveWorkspace()->id(), 'LIKE');
    if (!empty($source)) {
      $query->condition('source', $this->connection->escapeLike($source), 'NOT LIKE');
    }
    $query->addExpression('1');
    $query->range(0, 1);
    try {
      return (bool) $query->execute()->fetchField();
    }
    catch (\Exception $e) {
      $this->catchException($e);
      return FALSE;
    }
  }

}
