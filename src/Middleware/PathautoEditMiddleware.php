<?php

namespace Drupal\pathauto_edit_links\Middleware;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Middleware to handle pathauto edit URLs without redirects.
 */
class PathautoEditMiddleware implements HttpKernelInterface {

  /**
   * The wrapped kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a PathautoEditMiddleware object.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel
   *   The wrapped kernel.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(HttpKernelInterface $http_kernel, AliasManagerInterface $alias_manager, EntityTypeManagerInterface $entity_type_manager) {
    $this->httpKernel = $http_kernel;
    $this->aliasManager = $alias_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response {
    $path = $request->getPathInfo();

    // Check if this is a pathauto edit URL
    if (preg_match('/^(.+)\/(edit|delete|revisions)$/', $path, $matches)) {
      $alias = $matches[1];
      $action = $matches[2];
      
      // Skip admin paths and system paths
      if (!preg_match('/^\/?(admin|user|batch|system|node\/\d+)/', $alias)) {
        $system_path = $this->aliasManager->getPathByAlias($alias);
        
        // If this resolves to a node, rewrite the request
        if (preg_match('/^\/node\/(\d+)$/', $system_path, $node_matches)) {
          $node_id = $node_matches[1];
          
          // Create a new request with the system path
          $new_path = '/node/' . $node_id . '/' . $action;
          $new_request = $request->duplicate();
          $new_request->server->set('REQUEST_URI', $new_path);
          $new_request->server->set('PATH_INFO', $new_path);
          
          // Handle the request with the system path
          $response = $this->httpKernel->handle($new_request, $type, $catch);
          
          // Modify the response to maintain the original URL in any redirects or forms
          if ($response->headers->has('location')) {
            $location = $response->headers->get('location');
            // If the location is the system path, replace it with the alias
            if (strpos($location, '/node/' . $node_id) !== false) {
              $new_location = str_replace('/node/' . $node_id, $alias, $location);
              $response->headers->set('location', $new_location);
            }
          }
          
          return $response;
        }
      }
    }

    // Default behavior - pass through to the normal kernel
    return $this->httpKernel->handle($request, $type, $catch);
  }

}
