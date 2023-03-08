<?php

class RequestHelper
{

    private const API_BASE_URL = "https://0b4a-103-82-11-225.ap.ngrok.io/iq-fulfillment/iq-integrate/public/datahub/v1/magento-20";

    public static function getStoreUrl(): string
    {
        $context = Context::getContext();
        return (string)$context->shop->getBaseURL(true);
    }

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

    public static function processUninstallData(): void
    {
        if (Configuration::get('PS_IQ_FULFILLMENT_IS_ACTIVATE')) {
            self::sendRequestData("/app/uninstall", ["store_url" => self::getStoreUrl()]);
            Configuration::updateValue('PS_IQ_FULFILLMENT_IS_ACTIVATE', 0);
        }
    }

    public static function sendRequestData($end_point, $payload): void
    {
        $ch = curl_init(self::API_BASE_URL . $end_point);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
    }

}