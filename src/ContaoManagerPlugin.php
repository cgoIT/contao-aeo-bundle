<?php

namespace cgoIT\aeo;

use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;

/**
 * Plugin for the Contao Manager.
 *
 * @author Carsten Götzinger
 */
class ContaoManagerPlugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create('CgoITAeoBundle::class')
                ->setLoadAfter(['Contao\CoreBundle\ContaoCoreBundle'])
                ->setLoadAfter(['*'])
                ->setReplace(['aeo']),
        ];
    }
}
