<?php

namespace cgoIT\aeo\ContaoManager;

use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;

/**
 * Plugin for the Contao Manager.
 *
 * @author Carsten Götzinger
 */
class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create('cgoIT\aeo\CgoITAeoBundle')
                ->setLoadAfter(['Contao\CoreBundle\ContaoCoreBundle'])
                ->setLoadAfter(['*'])
                ->setReplace(['aeo']);
        ];
    }
}
