<?php declare(strict_types=1);

namespace CoinPayments\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Class ConfigService
 *
 * @package CoinPayments\Service
 */
class ConfigService
{

    public const PLUGIN_CONFIG_DOMAIN = 'CoinPayments.config.';


    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * ConfigService constructor.
     *
     * @param SystemConfigService $systemConfigService
     */
    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }


    /**
     * Get config
     *
     * @param string|null $salesChannelId
     * @return array
     */
    public function getConfig(?string $salesChannelId = null): array
    {
        $values = $this->systemConfigService->getDomain(
            self::PLUGIN_CONFIG_DOMAIN,
            $salesChannelId,
            true
        );

        $propertyValuePairs = [];

        /** @var string $key */
        foreach ($values as $key => $value) {
            $property = (string)\mb_substr($key, \mb_strlen(self::PLUGIN_CONFIG_DOMAIN));
            if ($property === '') {
                continue;
            }
            $propertyValuePairs[$property] = $value;
        }

        return $propertyValuePairs;
    }
}
