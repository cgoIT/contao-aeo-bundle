<?php

namespace cgoIT\aeo;

use Contao\CoreBundle\ContaoCoreBundle;
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
            BundleConfig::create(CgoITAeoBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class])
                ->setReplace(['aeo']),
        ];
    }
}
