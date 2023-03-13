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

require_once(_PS_ROOT_DIR_ . '/classes/webservice/WebserviceRequest.php');

/**
 *  Integration creation helper class that will create necessary permissions
 */
class IntegrationHelper
{

    const CALLBACK_URL = "https://516a-103-82-11-225.ap.ngrok.io/iq-fulfillment/iq-integrate/public/datahub/v1/prestashop/auth/callback";
    const PERMISSIONS = [
        'customers' => ['GET' => 1],
        'addresses' => ['GET' => 1],
        'images' => ['GET' => 1],
        'currencies' => ['GET' => 1],
        'countries' => ['GET' => 1],
        'states' => ['GET' => 1],
        'products' => ['GET' => 1, 'POST' => 1, 'PUT' => 1, 'HEAD' => 1],
        'product_options' => ['GET' => 1],
        'combinations' => ['GET' => 1, 'POST' => 1, 'PUT' => 1, 'HEAD' => 1],
        'stock_availables' => ['GET' => 1, 'POST' => 1, 'PUT' => 1, 'HEAD' => 1],
        'orders' => ['GET' => 1, 'POST' => 1, 'PUT' => 1, 'HEAD' => 1],
        'order_details' => ['GET' => 1, 'POST' => 1, 'PUT' => 1, 'HEAD' => 1],
        'order_state' => ['GET' => 1, 'POST' => 1, 'PUT' => 1, 'HEAD' => 1],
    ];

    /**
     * @return string
     * @throws PrestaShopException
     */
    public static function createAccessTokenWithPermission(): string
    {
        if (WebserviceKey::isKeyActive(Configuration::get('PS_IQ_FULFILLMENT_API_KEY'))) {
            Configuration::updateValue('PS_IQ_FULFILLMENT_IS_ACTIVATE', 1);
            return (string)Configuration::get('PS_IQ_FULFILLMENT_API_KEY');
        }
        $api_access = new WebserviceKey();
        $key = strtoupper(bin2hex(random_bytes(16)));
        $api_access->key = $key;
        $api_access->description = "IQ Fulfillment";
        $api_access->save();
        WebserviceKey::setPermissionForAccount($api_access->id, self::PERMISSIONS);
        Configuration::updateValue('PS_IQ_FULFILLMENT_API_KEY', $key);
        Configuration::updateValue('PS_IQ_FULFILLMENT_API_ACCESS_ID', $api_access->id);
        return $key;
    }

    /**
     * @return string
     */
    public static function getStoreUrl(): string
    {
        $context = Context::getContext();
        return (string)$context->shop->getBaseURL(true);
    }

    /**
     * @return string
     */
    public static function getCurrencyCode(): string
    {
        $default_currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        return $default_currency->iso_code;
    }


}