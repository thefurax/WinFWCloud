<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:deploy-rule')]
class DeployRuleCommand extends Command
{
    private string $projectDir;

    public function __construct(string $projectDir)
    {
        parent::__construct();
        $this->projectDir = $projectDir;
    }

    protected function configure(): void
    {
        $this->addArgument('agent-id', InputArgument::REQUIRED, 'ID de l\'agent cible')
             ->addArgument('port', InputArgument::REQUIRED, 'Port à ouvrir');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $agentId = $input->getArgument('agent-id');
        $port = $input->getArgument('port');
        $cmdDir = $this->projectDir . '/var/commands';

        if (!is_dir($cmdDir)) {
            mkdir($cmdDir, 0777, true);
        }

        $command = [
            'msg_id' => uniqid(),
            'agent_id' => $agentId,
            'command' => 'ADD_RULE',
            'payload' => ['port' => $port]
        ];

        $filename = $cmdDir . '/' . $agentId . '_' . time() . '.json';
        file_put_contents($filename, json_encode($command));

        $output->writeln("<info>Command queued for $agentId (Port $port)</info>");

        return Command::SUCCESS;
    }
}
