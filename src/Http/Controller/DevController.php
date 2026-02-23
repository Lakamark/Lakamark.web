<?php

namespace App\Http\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DevController extends AbstractController
{
    #[Route(path: '/dev', name: 'app_dev')]
    public function index(Request $request): Response
    {
        // check env

        if (!$this->getParameter('kernel.debug')) {
            if ('json' === $request->getPreferredFormat()) {
                return $this->json([
                    'error' => 'You are not allowed to access this endpoint.',
                ], Response::HTTP_FORBIDDEN);
            }
            $this->addFlash('error', 'You are not allowed to access this page.');

            return $this->redirectToRoute('home');
        }

        return $this->render('dev/index.html.twig');
    }
}
