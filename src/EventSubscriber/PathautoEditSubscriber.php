<?php

namespace Drupal\pathauto_edit_links\EventSubscriber;

use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber for pathauto edit URLs.
 */
class PathautoEditSubscriber implements EventSubscriberInterface {

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Constructs a PathautoEditSubscriber object.
   *
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The path alias manager.
   */
  public function __construct(AliasManagerInterface $alias_manager) {
    $this->aliasManager = $alias_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onRequest', 100];
    return $events;
  }

  /**
   * Handles the request event.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The request event.
   */
  public function onRequest(RequestEvent $event) {
    // Disable the redirect functionality - we'll handle this via routing instead
    return;
  }

}
