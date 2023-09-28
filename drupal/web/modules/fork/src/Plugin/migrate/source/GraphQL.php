<?php

namespace Drupal\migrate_source_graphql\Plugin\migrate\source;

use Drupal;
use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\migrate\Row;
use Drupal\migrate_source_graphql\GraphQL\Client;
use GraphQL\Exception\QueryError;
use GraphQL\Query;
use InvalidArgumentException;

/**
 * Class GraphQL migrate source.
 *
 * @MigrateSource(
 *   id = "graphql",
 *   source_module = "migrate_source_graphql"
 * )
 */
class GraphQL extends SourcePluginBase implements ConfigurableInterface
{
  /**
   * The graphql client.
   *
   * @var \GraphQL\Client
   */
  protected $client;
  private $fields;

  /**
   * {@inheritdoc}
   *
   * @throws InvalidArgumentException
   * @throws MigrateException
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration)
  {
    $configuration['data_key'] = isset($configuration['data_key']) ? $configuration['data_key'] : 'data';

    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    // Endpoint is required.
    if (empty($this->configuration['endpoint'])) {
      throw new InvalidArgumentException('You must declare the "endpoint" to the GraphQL API service in your settings.');
    }

    // Queries are required.
    if (empty($this->configuration['query'])) {
      throw new InvalidArgumentException('You must declare the "query" parameter in your settings to get expected data from GraphQL API service.');
    } else {
      $headers = [];
      if (isset($this->configuration['auth_scheme']) && !empty($this->configuration['auth_scheme'])) {
        $headers['Authorization'] = $this->configuration['auth_scheme'] . ' ' . ($this->configuration['auth_parameters'] ?? '');
      }
      $this->client = new Client($this->configuration['endpoint'], $headers);
    }

    $this->fields = $this->fields();
  }

  /**
   * {@inheritdoc}
   */
  public function fields(): array
  {
    $fields = [];
    $query = $this->configuration['query'];
    foreach ($query as $queryName => $query) {
      $results = is_array($query['fields'][0]) ? array_keys($query['fields'][0]) : $query['fields'];
      foreach ($results as $resultKey) {
        if (isset($query['fields'][0][$resultKey])) {
          foreach ($query['fields'][0][$resultKey] as $field) {
            if (!is_array($field)) {
              $fields[$field] = $field;
            }
          }
        } else {
          if (!is_array($resultKey)) {
            $fields[$resultKey] = $resultKey;
          } else {
            $firstKey = array_key_first($resultKey);
            $fields[$firstKey] = $resultKey[$firstKey] ?? $resultKey;
          }
        }
      }
    }
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration()
  {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration)
  {
    // We must preserve integer keys for column_name mapping.
    $this->configuration = NestedArray::mergeDeepArray([
      $this->defaultConfiguration(),
      $configuration,
    ], TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration()
  {
    return [
      'endpoint' => 'localhost',
    ];
  }

  /**
   * Return a string representing the GraphQL API endpoint.
   *
   * @return string
   *   The GraphQL API endpoint.
   */
  public function __toString()
  {
    return $this->configuration['endpoint'];
  }

  /**
   * {@inheritdoc}
   *
   * @throws MigrateException
   */
  public function initializeIterator()
  {
    return $this->getGenerator();
  }

  /**
   * Return the generator using yield.
   */
  private function getGenerator()
  {
    $query = $this->configuration['query'];
    $queryName = array_key_first($query);
    $qlQuery = $this->client->buildQueryRecursive($queryName, $query[$queryName]);
    try {
      $results = $this->client->runQuery($qlQuery);
      if ($this->configuration['data_key']) {
        $propertyPath = explode(Row::PROPERTY_SEPARATOR, $this->configuration['data_key']);
        foreach ($propertyPath as $path) {
          $results = $results->getData()->{$path} ?? [];
        }
      } else {
        $results = $results->getData()->$queryName ?? [];
      }
      foreach ($results as $result) {
        yield json_decode(json_encode($result), TRUE);
      }
    } catch (QueryError $exception) {
      Drupal::messenger()->addError($exception->getMessage());
    }
  }

  public function getIds()
  {
    return $this->configuration['ids'];
  }
}
