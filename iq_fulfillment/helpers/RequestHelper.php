<?php
/**
 * Copyright (c) 2023 IQ Fulfillment
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/MIT
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@iqintegration.com so we can send you a copy immediately.
 *
 * @author    IQ Fulfillment
 * @copyright Since 2023 IQ Fulfillment
 * @license   https://opensource.org/licenses/MIT
 */

/**
 *  Request handler class for IQ fulfillment
 */
class RequestHelper
{

    private const API_BASE_URL = "https://516a-103-82-11-225.ap.ngrok.io/iq-fulfillment/iq-integrate/public/datahub/v1/prestashop";

    /**
     * @return string
     */
    public static function getStoreUrl(): string
    {
        $context = Context::getContext();
        return (string)$context->shop->getBaseURL(true);
    }

    /**
     * @param $end_point
     * @param $data
     * @return void
     */
    public static function processRequestData($end_point, $data): void
    {
        $payload = [
            "store_url" => self::getStoreUrl(),
            "data" => $data
        ];
        if (!WebserviceKey::isKeyActive(Configuration::get('PS_IQ_FULFILLMENT_API_KEY'))) {
            self::processUninstallData();
            return;
        }
        self::sendRequestData($end_point, $payload);
    }

    /**
     * @return void
     */
    public static function processUninstallData(): void
    {
        if (Configuration::get('PS_IQ_FULFILLMENT_IS_ACTIVATE')) {
            self::sendRequestData("/app/uninstall", ["store_url" => self::getStoreUrl()]);
            Configuration::updateValue('PS_IQ_FULFILLMENT_IS_ACTIVATE', 0);
        }
    }

    /**
     * @param $end_point
     * @param $payload
     * @return void
     */
    public static function sendRequestData($end_point, $payload): void
    {
        $ch = curl_init(self::API_BASE_URL . $end_point);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        //file_put_contents(dirname(__FILE__)."/log.txt", json_encode($payload, JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);
        curl_close($ch);
    }

}