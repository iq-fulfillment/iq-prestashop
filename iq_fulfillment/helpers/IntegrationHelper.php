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
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@iqintegration.com so we can send you a copy immediately.
 *
 * @author IQ Fulfillment
 * @copyright Since 2023
 * @license https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_ROOT_DIR_ . '/classes/webservice/WebserviceRequest.php';

/**
 * Integration creation helper class that will create necessary permissions.
 */
class IntegrationHelper
{
    const CALLBACK_URL = 'https://iqintegrate.com/datahub/v1/prestashop/auth/callback';
    const PERMISSIONS = [
        'customers'         => ['GET' => 1],
        'addresses'         => ['GET' => 1],
        'images'            => ['GET' => 1],
        'currencies'        => ['GET' => 1],
        'countries'         => ['GET' => 1],
        'states'            => ['GET' => 1],
        'products'          => ['GET' => 1, 'POST' => 1, 'PUT' => 1, 'HEAD' => 1],
        'product_options'   => ['GET' => 1],
        'combinations'      => ['GET' => 1, 'POST' => 1, 'PUT' => 1, 'HEAD' => 1],
        'stock_availables'  => ['GET' => 1, 'POST' => 1, 'PUT' => 1, 'HEAD' => 1],
        'orders'            => ['GET' => 1, 'POST' => 1, 'PUT' => 1, 'HEAD' => 1],
        'order_details'     => ['GET' => 1, 'POST' => 1, 'PUT' => 1, 'HEAD' => 1],
        'order_state'       => ['GET' => 1, 'POST' => 1, 'PUT' => 1, 'HEAD' => 1],
    ];

    /**
     * Create access token with permission.
     *
     * @throws PrestaShopException
     *
     * @return string
     */
    public static function createAccessTokenWithPermission()
    {
        if (WebserviceKey::isKeyActive(Configuration::get('PS_IQ_FULFILLMENT_API_KEY'))) {
            Configuration::updateValue('PS_IQ_FULFILLMENT_IS_ACTIVATE', 1);

            return (string) Configuration::get('PS_IQ_FULFILLMENT_API_KEY');
        }

        $api_access = new WebserviceKey();
        $key = strtoupper(bin2hex(random_bytes(16)));
        $api_access->key = $key;
        $api_access->description = 'IQ Fulfillment';
        $api_access->save();
        WebserviceKey::setPermissionForAccount($api_access->id, self::PERMISSIONS);
        Configuration::updateValue('PS_IQ_FULFILLMENT_API_KEY', $key);
        Configuration::updateValue('PS_IQ_FULFILLMENT_API_ACCESS_ID', $api_access->id);
        return $key;
    }

    /**
     * Retrieves the store URL.
     *
     * @return string
     */
    public static function getStoreUrl()
    {
        $context = Context::getContext();
        return (string) $context->shop->getBaseURL(true);
    }

    /**
     * Retrieves the currency code.
     *
     * @return string
     */
    public static function getCurrencyCode()
    {
        $default_currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        return $default_currency->iso_code;
    }
}