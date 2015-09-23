<?php

namespace Koshatul\HAProxyWeb\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use \BigWhoop\HAProxyAPI;

class Stats extends Command {

	protected function configure()
	{
		$this
			->setName('stats')
			->setDescription('Get stats from servers')
			->addArgument(
				'server',
				InputArgument::IS_ARRAY,
				'Specify a server or list of servers to query'
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$stats = new \Koshatul\HAProxyWeb\Helper\GetStats($input->getArgument('server'));
		$stats->process();
	}
}