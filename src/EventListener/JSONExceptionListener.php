<?php

namespace App\EventListener;

use App\Response\ApiResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class JSONExceptionListener implements EventSubscriberInterface
{
    private LoggerInterface $_logger;
    public function __construct(
        LoggerInterface $logger
    )
    {
        $this->_logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 200],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $message = $exception->getMessage();
        $this->_logger->critical($exception->getMessage());
        $code = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : ApiResponse::INTERNAL_ERROR_RESPONSE_CODE;
        $apiResponse = new ApiResponse($code,ApiResponse::ERROR_STATUS);
        $apiResponse->setErrors([$message]);
        $event->setResponse($apiResponse->getResponse());
    }
}