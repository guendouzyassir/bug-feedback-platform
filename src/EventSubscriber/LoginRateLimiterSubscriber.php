<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class LoginRateLimiterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RateLimiterFactory $loginLimiter,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', 0],
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        if ($request->getPathInfo() !== '/login' || $request->getMethod() !== 'POST') {
            return;
        }

        if ($response->getStatusCode() === Response::HTTP_FOUND) {
            return;
        }

        $limiter = $this->loginLimiter->create($request->getClientIp());
        $limiter->consume(1);

        if (!$limiter->tryConsume(0)) {
            $event->setResponse(new Response(
                'Too many failed login attempts. Please try again in 5 minutes.',
                Response::HTTP_TOO_MANY_REQUESTS
            ));
        }
    }
}
