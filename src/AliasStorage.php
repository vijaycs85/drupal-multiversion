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
   * {@inheritdoc}
   */
  public function __construct(Connection $connection, ModuleHandlerInterface $module_handler, WorkspaceManagerInterface $workspace_manager) {
    parent::__construct($connection, $module_handler);
    $this->workspaceManager = $workspace_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function save($source, $alias, $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED, $pid = NULL) {

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
  public function load($conditions) {
    $select = $this->connection->select(static::TABLE);
    foreach ($conditions as $field => $value) {
      if ($field == 'source' || $field == 'alias') {
        // Use LIKE for case-insensitive matching.
        $select->condition($field, $this->connection->escapeLike($value), 'LIKE');
      }
      else {
        $select->condition($field, $value);
      }
    }
    try {
      return $select
        ->fields(static::TABLE)
        ->orderBy('pid', 'DESC')
        ->range(0, 1)
        ->execute()
        ->fetchAssoc();
    }
    catch (\Exception $e) {
      $this->catchException($e);
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function delete($conditions) {
    $path = $this->load($conditions);
    $query = $this->connection->delete(static::TABLE);
    foreach ($conditions as $field => $value) {
      if ($field == 'source' || $field == 'alias') {
        // Use LIKE for case-insensitive matching.
        $query->condition($field, $this->connection->escapeLike($value), 'LIKE');
      }
      else {
        $query->condition($field, $value);
      }
    }
    try {
      $deleted = $query->execute();
    }
    catch (\Exception $e) {
      $this->catchException($e);
      $deleted = FALSE;
    }
    // @todo Switch to using an event for this instead of a hook.
    $this->moduleHandler->invokeAll('path_delete', [$path]);
    Cache::invalidateTags(['route_match']);
    return $deleted;
  }

  /**
   * {@inheritdoc}
   */
  public function preloadPathAlias($preloaded, $langcode) {
    $langcode_list = [$langcode, LanguageInterface::LANGCODE_NOT_SPECIFIED];
    $select = $this->connection->select(static::TABLE)
      ->fields(static::TABLE, ['source', 'alias']);

    if (!empty($preloaded)) {
      $conditions = new Condition('OR');
      foreach ($preloaded as $preloaded_item) {
        $conditions->condition('source', $this->connection->escapeLike($preloaded_item), 'LIKE');
      }
      $select->condition($conditions);
    }

    // Always get the language-specific alias before the language-neutral one.
    // For example 'de' is less than 'und' so the order needs to be ASC, while
    // 'xx-lolspeak' is more than 'und' so the order needs to be DESC. We also
    // order by pid ASC so that fetchAllKeyed() returns the most recently
    // created alias for each source. Subsequent queries using fetchField() must
    // use pid DESC to have the same effect.
    if ($langcode == LanguageInterface::LANGCODE_NOT_SPECIFIED) {
      array_pop($langcode_list);
    }
    elseif ($langcode < LanguageInterface::LANGCODE_NOT_SPECIFIED) {
      $select->orderBy('langcode', 'ASC');
    }
    else {
      $select->orderBy('langcode', 'DESC');
    }

    $select->orderBy('pid', 'ASC');
    $select->condition('langcode', $langcode_list, 'IN');
    try {
      return $select->execute()->fetchAllKeyed();
    }
    catch (\Exception $e) {
      $this->catchException($e);
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function lookupPathAlias($path, $langcode) {
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

  /**
   * {@inheritdoc}
   */
  public function languageAliasExists() {
    try {
      return (bool) $this->connection->queryRange('SELECT 1 FROM {url_alias} WHERE langcode <> :langcode', 0, 1, [':langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])->fetchField();
    }
    catch (\Exception $e) {
      $this->catchException($e);
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getAliasesForAdminListing($header, $keys = NULL) {
    $query = $this->connection->select(static::TABLE)
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender')
      ->extend('Drupal\Core\Database\Query\TableSortExtender');
    if ($keys) {
      // Replace wildcards with PDO wildcards.
      $query->condition('alias', '%' . preg_replace('!\*+!', '%', $keys) . '%', 'LIKE');
    }
    try {
      return $query
        ->fields(static::TABLE)
        ->orderByHeader($header)
        ->limit(50)
        ->execute()
        ->fetchAll();
    }
    catch (\Exception $e) {
      $this->catchException($e);
      return [];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function pathHasMatchingAlias($initial_substring) {
    $query = $this->connection->select(static::TABLE, 'u');
    $query->addExpression(1);
    try {
      return (bool) $query
        ->condition('u.source', $this->connection->escapeLike($initial_substring) . '%', 'LIKE')
        ->range(0, 1)
        ->execute()
        ->fetchField();
    }
    catch (\Exception $e) {
      $this->catchException($e);
      return FALSE;
    }
  }

  /**
   * Check if the table exists and create it if not.
   */
  protected function ensureTableExists() {
    try {
      $database_schema = $this->connection->schema();
      if (!$database_schema->tableExists(static::TABLE)) {
        $schema_definition = $this->schemaDefinition();
        $database_schema->createTable(static::TABLE, $schema_definition);
        return TRUE;
      }
    }
    // If another process has already created the table, attempting to recreate
    // it will throw an exception. In this case just catch the exception and do
    // nothing.
    catch (SchemaObjectExistsException $e) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Act on an exception when url_alias might be stale.
   *
   * If the table does not yet exist, that's fine, but if the table exists and
   * yet the query failed, then the url_alias is stale and the exception needs
   * to propagate.
   *
   * @param $e
   *   The exception.
   *
   * @throws \Exception
   */
  protected function catchException(\Exception $e) {
    if ($this->connection->schema()->tableExists(static::TABLE)) {
      throw $e;
    }
  }

  /**
   * Defines the schema for the {url_alias} table.
   *
   * @internal
   */
  public static function schemaDefinition() {
    return [
      'description' => 'A list of URL aliases for Drupal paths; a user may visit either the source or destination path.',
      'fields' => [
        'pid' => [
          'description' => 'A unique path alias identifier.',
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ],
        'source' => [
          'description' => 'The Drupal path this alias is for; e.g. node/12.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ],
        'alias' => [
          'description' => 'The alias for this path; e.g. title-of-the-story.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ],
        'langcode' => [
          'description' => "The language code this alias is for; if 'und', the alias will be used for unknown languages. Each Drupal path can have an alias for each supported language.",
          'type' => 'varchar_ascii',
          'length' => 12,
          'not null' => TRUE,
          'default' => '',
        ],
      ],
      'primary key' => ['pid'],
      'indexes' => [
        'alias_langcode_pid' => ['alias', 'langcode', 'pid'],
        'source_langcode_pid' => ['source', 'langcode', 'pid'],
      ],
    ];
  }

}
