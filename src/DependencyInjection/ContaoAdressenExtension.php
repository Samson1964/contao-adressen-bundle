<?php

declare(strict_types=1);

/*
 * Dieses Bundle stellt eine Adressen-Verwaltung für Contao 4.13 und Contao 5 bereit.
 *
 * @license LGPL-3.0-or-later
 */

namespace Schachbulle\ContaoAdressenBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Lädt die Service-Definitionen des Bundles in den Symfony-Container.
 */
class ContaoAdressenExtension extends Extension
{
	/**
	 * {@inheritdoc}
	 */
	public function load(array $mergedConfig, ContainerBuilder $container): void
	{
		$loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load('services.yaml');
	}

	/**
	 * Alias der Extension (sonst würde Symfony "contao_adressen" aus dem
	 * Klassennamen ableiten – das ist zwar identisch, wird hier aber
	 * ausdrücklich festgeschrieben).
	 */
	public function getAlias(): string
	{
		return 'contao_adressen';
	}
}
