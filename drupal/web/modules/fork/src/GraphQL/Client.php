<?php

namespace Drupal\migrate_source_graphql\GraphQL;

use GraphQL\Client as GraphQLClient;
use GraphQL\Exception\QueryError;
use GraphQL\Query;
use GraphQL\QueryBuilder;
use GraphQL\RawObject;
use GraphQL\QueryBuilder\QueryBuilderInterface;
use GraphQL\Results;
use InvalidArgumentException;

/**
 * Class Client that implements some useful methods to interact with GraphQL.
 */
class Client
{

  /**
   * The client.
   *
   * @var GraphQLClient
   */
  protected $client;

  /**
   * The query builder.
   *
   * @var QueryBuilder
   */
  private $queryBuilder;

  /**
   * Client constructor.
   *
   * @param string $apiEndpoint
   *   API Endpoint.
   * @param array $extraHeader
   *   Extra headers.
   *
   * @return GraphQLClient
   */
  public function __construct(string $apiEndpoint, array $extraHeader)
  {
    $this->client = new GraphQLClient(
      $apiEndpoint,
      $extraHeader
    );

    return $this->client;
  }

  /**
   * Builds a Query object from a query array using recursion.
   *
   * @param string $queryName
   *   The query's name.
   * @param array $query
   *   The query array to build the query object from.
   */
  public function buildQueryRecursive(string $queryName, array $query_array): Query
  {
    $arguments = NULL;
    $keys = array_keys($query_array);

    if (in_array('fields', array_keys($query_array))) {
      // Create a Query object
      $query = new Query($queryName);

      // Initialize the selection set as an empty array
      $selectionSet = [];

      // Process arguments
      if (isset($query_array['arguments'])) {
        $argumentsTemp = [];
        // Iterate over all arguments
        foreach ($query_array['arguments'] as $argumentsKey => $argument) {
          $argumentsTemp[$argumentsKey] = new RawObject(sprintf('{ %s }', $this->arrayToGraphQLString($argument)));
        }
        $query->setArguments($argumentsTemp);
      }

      if (isset($query_array['alias'])) {
        $query->setAlias($query_array['alias']);
      }

      // Process fields recursively and add them to the selection set
      foreach ($query_array['fields'] as $field) {
        if (is_array($field)) {
          // If the field is an array, create a nested query recursively
          $nestedQuery = $this->buildQueryRecursive(array_key_first($field), $field[array_key_first($field)]);
          $selectionSet[] = $nestedQuery;
        } else {
          // If the field is a string, add it directly to the selection set
          $selectionSet[] = $field;
        }
      }


      // Set the selection set for the main query
      $query->setSelectionSet($selectionSet);

      return $query;
    }
    throw new InvalidArgumentException('fields must be defined');
  }

  /**
   * Converts an array to a RAW GraphQL Object String
   *
   * @param $array
   * @return string
   */
  function arrayToGraphQLString($array): string
  {
    $result = '';
    foreach ($array as $key => $value) {
      $result .= $key . ': ';

      if (is_array($value)) {
        $result .= '{' . $this->arrayToGraphQLString($value) . '}';
      } else {
        // Check if the value contains invalid characters and wrap it in quotes if necessary
        $value = is_numeric($value) ? $value : '"' . $value . '"';
        $result .= $value;
      }

      $result .= ', ';
    }

    // Remove the trailing comma and space
    return rtrim($result, ', ');
  }

  /**
   * Run query.
   *
   * @param Query $query
   *   Created query to use.
   * @return Results
   */
  public function runQuery(Query $query)
  {
    return $this->client->runQuery($query);
  }

  /**
   * Recursive array to Query object transform to build a right selectionSet.
   *
   * @param array $array
   *   Recursive array to Query transform to build a right selectionSet.
   */
  private function arrayToQuery(array &$array): void
  {
    $non_array_fields = [];
    if (array_key_exists('fields', $array)) {
      foreach ($array['fields'] as &$item) {

      }
    }
  }

}
