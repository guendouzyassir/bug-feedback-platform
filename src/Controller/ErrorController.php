<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ErrorController extends AbstractController
{
    public function __invoke(\Throwable $exception): Response
    {
        if ($exception instanceof NotFoundHttpException) {
            return $this->render('@Twig/Exception/error404.html.twig', [
                'status_code' => Response::HTTP_NOT_FOUND,
                'status_text' => 'Not Found',
            ]);
        }

        return $this->render('@Twig/Exception/error.html.twig', [
            'status_code' => 500,
            'status_text' => 'Internal Server Error',
        ]);
    }
}
