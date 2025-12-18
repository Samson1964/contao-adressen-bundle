<?php

namespace Schachbulle\ContaoAdressenBundle\Cron;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;

class ExtrahiereAdressen
{
	private ContaoFramework $framework;

	public function __construct(ContaoFramework $framework)
	{
	}

	public function __invoke(): void
	{

		// Log-Eintrag vornehmen
		\Contao\System::getContainer()->get('monolog.logger.contao.cron')->info('[Adressen-Verwaltung] Adressen wurden eingetragen');
		
	}

}
