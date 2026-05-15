<?php

namespace App\Command;

use App\Entity\Server;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:deploy-policy')]
class DeployPolicyCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private string $projectDir;

    public function __construct(EntityManagerInterface $entityManager, string $projectDir)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->projectDir = $projectDir;
    }

    protected function configure(): void
    {
        $this->addArgument('server-id', InputArgument::REQUIRED, 'ID du serveur cible');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $serverId = $input->getArgument('server-id');
        $server = $this->entityManager->getRepository(Server::class)->find($serverId);

        if (!$server || !$server->getPolicy()) {
            $output->writeln("<error>Serveur ou politique non trouvée.</error>");
            return Command::FAILURE;
        }

        $policy = $server->getPolicy();
        $rules = [];
        foreach ($policy->getRules() as $rule) {
            $rules[] = [
                'name' => $rule->getName(),
                'action' => $rule->getAction(),
                'direction' => $rule->getDirection(),
                'protocol' => $rule->getProtocol(),
                'port' => $rule->getDstPort(),
                'src_ip' => $rule->getSrcIp()
            ];
        }

        $command = [
            'msg_id' => uniqid(),
            'agent_id' => $server->getHostname(), // MVP simplification
            'command' => 'APPLY_POLICY',
            'payload' => [
                'policy_name' => $policy->getName(),
                'rules' => $rules
            ]
        ];

        $cmdDir = $this->projectDir . '/var/commands';
        if (!is_dir($cmdDir)) mkdir($cmdDir, 0777, true);

        file_put_contents($cmdDir . '/' . $server->getHostname() . '.json', json_encode($command));

        $output->writeln("<info>Policy '{$policy->getName()}' queued for {$server->getHostname()}</info>");

        return Command::SUCCESS;
    }
}
