<?php

require_once(_PS_ROOT_DIR_ . '/classes/webservice/WebserviceRequest.php');

class IntegrationHelper
{

    const CALLBACK_URL = "https://www.google.com";
    const PERMISSIONS = [
        'customers' => ['GET' => 1],
        'addresses' => ['GET' => 1],
        'images' => ['GET' => 1],
        'currencies' => ['GET' => 1],
        'products' => ['GET' => 1, 'POST' => 1, 'PUT' => 1, 'HEAD' => 1],
        'product_options' => ['GET' => 1],
        'combinations' => ['GET' => 1, 'POST' => 1, 'PUT' => 1, 'HEAD' => 1],
        'stock_availables' => ['GET' => 1, 'POST' => 1, 'PUT' => 1, 'HEAD' => 1],
        'orders' => ['GET' => 1, 'POST' => 1, 'PUT' => 1, 'HEAD' => 1],
        'order_details' => ['GET' => 1, 'POST' => 1, 'PUT' => 1, 'HEAD' => 1],
        'order_state' => ['GET' => 1, 'POST' => 1, 'PUT' => 1, 'HEAD' => 1],
    ];

    public static function createAccessTokenWithPermission(): string
    {
        if (WebserviceKey::isKeyActive(Configuration::get('PS_IQ_FULFILLMENT_API_KEY'))) {
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

    public static function getStoreUrl(): string
    {
        $context = Context::getContext();
        return (string)$context->shop->getBaseURL(true);
    }


}