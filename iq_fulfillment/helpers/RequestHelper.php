<?php
/**
 * Copyright (c) 2023 IQ Fulfillment
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/MIT
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@iqintegration.com so we can send you a copy immediately.
 *
 * @author IQ Fulfillment
 * @copyright Since 2023
 * @license https://opensource.org/licenses/MIT
 */

/**
 * Request handler class for IQ fulfillment.
 */
class RequestHelper
{
    /**
     * API base url.
     *
     * @var string
     */
    private const API_BASE_URL = 'https://iqintegrate.com/datahub/v1/prestashop';

    /**
     * Return store url.
     *
     * @return string
     */
    public static function getStoreUrl()
    {
        $context = Context::getContext();

        return (string) $context->shop->getBaseURL(true);
    }

    /**
     * Process the data.
     *
     * @param string $end_point
     * @param mixed  $data
     *
     * @return void
     */
    public static function processRequestData(string $end_point, $data): void
    {
        $payload = [
            'store_url' => self::getStoreUrl(),
            'data'      => $data,
        ];
        if (!WebserviceKey::isKeyActive(Configuration::get('PS_IQ_FULFILLMENT_API_KEY'))) {
            self::processUninstallData();

            return;
        }
        self::sendRequestData($end_point, $payload);
    }

    /**
     * Prepare data for uninstall request.
     *
     * @return void
     */
    public static function processUninstallData(): void
    {
        if (Configuration::get('PS_IQ_FULFILLMENT_IS_ACTIVATE')) {
            self::sendRequestData('/app/uninstall', ['store_url' => self::getStoreUrl()]);
            Configuration::updateValue('PS_IQ_FULFILLMENT_IS_ACTIVATE', 0);
        }
    }

    /**
     * Make the curl request.
     *
     * @param string $end_point
     * @param mixed  $payload
     *
     * @return void
     */
    public static function sendRequestData(string $end_point, $payload): void
    {
        $ch = curl_init(self::API_BASE_URL . $end_point);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
    }
}
