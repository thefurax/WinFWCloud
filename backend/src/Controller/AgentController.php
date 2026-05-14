<?php

namespace App\Controller;

use App\Entity\Server;
use App\Entity\RegistrationToken;
use App\Service\PKIService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/agent')]
class AgentController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private PKIService $pkiService;

    public function __construct(EntityManagerInterface $entityManager, PKIService $pkiService)
    {
        $this->entityManager = $entityManager;
        $this->pkiService = $pkiService;
    }

    #[Route('/bootstrap', name: 'agent_bootstrap', methods: ['POST'])]
    public function bootstrap(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $tokenStr = $data['token'] ?? null;
        $csrContent = $data['csr'] ?? null;
        $hostname = $data['hostname'] ?? 'unknown';

        if (!$tokenStr || !$csrContent) {
            return new JsonResponse(['error' => 'Token and CSR required'], 400);
        }

        $token = $this->entityManager->getRepository(RegistrationToken::class)->findOneBy(['token' => $tokenStr, 'used' => false]);

        if (!$token || $token->getExpiresAt() < new \DateTimeImmutable()) {
            return new JsonResponse(['error' => 'Invalid or expired token'], 403);
        }

        // 1. Create Server record
        $server = new Server();
        $server->setHostname($hostname);
        $server->setOsFamily($data['os'] ?? 'linux');
        $this->entityManager->persist($server);

        // 2. Sign CSR
        try {
            $certificate = $this->pkiService->signCSR($csrContent, $server->getHostname());
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }

        // 3. Mark token as used
        $token->setUsed(true);
        $this->entityManager->flush();

        return new JsonResponse([
            'status' => 'success',
            'agent_id' => $server->getId(),
            'certificate' => $certificate,
            'ca_cert' => $this->pkiService->getCACert()
        ]);
    }

    #[Route('/register', name: 'agent_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        // Keep for legacy or mTLS authenticated calls
        return new JsonResponse(['message' => 'Use bootstrap for new agents']);
    }
}
