<?php

namespace App\Controller;

use App\Entity\Server;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/agent')]
class AgentController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/register', name: 'agent_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['hostname'])) {
            return new JsonResponse(['error' => 'Invalid data'], 400);
        }

        $server = new Server();
        $server->setHostname($data['hostname']);
        $server->setOsFamily($data['os'] ?? 'linux');

        $this->entityManager->persist($server);
        $this->entityManager->flush();

        return new JsonResponse([
            'status' => 'success',
            'agent_id' => $server->getId(),
            'message' => 'Agent registered and persisted successfully'
        ]);
    }
}
