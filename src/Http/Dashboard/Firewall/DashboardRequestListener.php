<?php

namespace App\Http\Dashboard\Firewall;

use App\Http\Dashboard\Controller\BaseController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * DashboardRequestListener.
 *
 * This event subscriber protects the dashboard area by restricting access
 * to users having the required role (`CMS_MANAGE`).
 *
 * It provides a double security layer:
 *
 * 1. Request-level protection (onRequest)
 *    - Intercepts every main HTTP request.
 *    - If the request URI starts with the configured dashboard prefix,
 *      the user must be granted `CMS_MANAGE`.
 *
 * 2. Controller-level protection (onController)
 *    - Ensures that any controller extending BaseController
 *      also requires the `CMS_MANAGE` permission.
 *
 * This dual mechanism ensures:
 * - Protection by URL (prefix-based security)
 * - Protection by controller type (defensive security layer)
 *
 * The listener throws an AccessDeniedException if access is denied.
 * Symfony's security system will then convert it into a 403 HTTP response.
 *
 * This class is readonly and depends only on:
 * - The configured dashboard prefix
 * - The AuthorizationCheckerInterface
 */
readonly class DashboardRequestListener implements EventSubscriberInterface
{
    private const string REQUIRED_ROLE = 'CMS_MANAGE';

    public function __construct(
        private string $dashboardPrefix,
        private AuthorizationCheckerInterface $authChecker,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ControllerEvent::class => 'onController',
            RequestEvent::class => 'onRequest',
        ];
    }

    /**
     * Checks access when a controller is resolved.
     *
     * If the controller is an instance of BaseController,
     * the user must have the `CMS_MANAGE` permission.
     *
     * This acts as a defensive layer to prevent access even if
     * the route prefix changes or is misconfigured.
     *
     * @throws AccessDeniedException
     */
    public function onController(ControllerEvent $event): void
    {
        if (false === $event->isMainRequest()) {
            return;
        }

        $controller = $event->getController();
        if (
            is_array($controller)
            && $controller[0] instanceof BaseController
            && !$this->authChecker->isGranted(self::REQUIRED_ROLE)
        ) {
            throw $this->rejetRequest($event->getRequest());
        }
    }

    /**
     * Checks access at the HTTP request level.
     *
     * If the request URI starts with the configured dashboard prefix,
     * the user must have the `CMS_MANAGE` permission.
     *
     * Only the main request is evaluated (sub-requests are ignored).
     *
     * @throws AccessDeniedException
     */
    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        $uri = '/'.trim($request->getRequestUri(), '/').'/';
        $prefix = '/'.trim($this->dashboardPrefix, '/').'/';

        if (
            substr($uri, 0, mb_strlen($prefix)) === $prefix
            && !$this->authChecker->isGranted(self::REQUIRED_ROLE)
        ) {
            throw $this->rejetRequest($event->getRequest());
        }
    }

    /**
     * Creates an AccessDeniedException with the current request as subject.
     */
    private function rejetRequest(Request $request): AccessDeniedException
    {
        $exception = new AccessDeniedException();
        $exception->setSubject($request);

        return $exception;
    }
}
