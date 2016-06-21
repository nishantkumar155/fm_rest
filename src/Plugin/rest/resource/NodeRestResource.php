<?php

namespace Drupal\fm_rest\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "node_rest_resource",
 *   label = @Translation("Node rest resource"),
 *   serialization_class = "Drupal\node\Entity\Node",
 *   uri_paths = {
 *     "canonical" = "/api/pppppppppp/node/{entity}",
 *     "https://www.drupal.org/link-relations/create" = "/api/pppppppppp/node/{bundle}"
 *   }
 * )
 */
class NodeRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('fm_rest'),
      $container->get('current_user')
    );
  }

  /**
   * Responds to POST requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @param $bundle
   * @param $data
   * @return \Drupal\rest\ResourceResponse Throws exception expected.
   * Throws exception expected.
   */
  public function post($bundle, $data) {
    //@todo Validate the arguments.

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $node = Node::create(
      array(
        'type' => $bundle,
        'title' => $data->title->value,
        'body' => [
          'summary' => '',
          'value' => html_entity_decode($data->body->value),
          'format' => 'full_html',
        ],
      )
    );
    $node->save();
    return new ResourceResponse($node->id(), 201);
  }

  /**
   * Responds to Patch requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @param $nid
   * @param $data
   * @return \Drupal\rest\ResourceResponse Throws exception expected.
   * Throws exception expected.
   */
  public function patch($nid, $data) {

    $node = Node::load($nid);
    $node->setTitle($data->title->value);
    $node->set("body", html_entity_decode($data->body->value));
    $node->save();
    return new ResourceResponse($node->id(), 200);
  }

  /**
   * Responds to Delete requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @param $nid
   * @param $data
   * @return \Drupal\rest\ResourceResponse Throws exception expected.
   * Throws exception expected.
   */
  public function delete($nid, $data) {
    Node::load($nid)->delete();
    return new ResourceResponse('ok', 204);
  }
}
