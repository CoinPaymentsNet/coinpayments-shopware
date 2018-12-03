<?php

namespace CoinPayments\Tests;

use CoinPayments\CoinPayments as Plugin;
use Shopware\Components\Test\Plugin\TestCase;

class PluginTest extends TestCase
{
    protected static $ensureLoadedPlugins = [
        'CoinPayments' => []
    ];

    public function testCanCreateInstance()
    {
        /** @var Plugin $plugin */
        $plugin = Shopware()->Container()->get('kernel')->getPlugins()['CoinPayments'];

        $this->assertInstanceOf(Plugin::class, $plugin);
    }
}
