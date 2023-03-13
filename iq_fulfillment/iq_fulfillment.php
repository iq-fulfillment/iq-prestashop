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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/helpers/IntegrationHelper.php');
require_once(dirname(__FILE__) . '/helpers/RequestHelper.php');

class Iq_fulfillment extends Module
{
    public function __construct()
    {
        $this->name = 'iq_fulfillment';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'IQ Fulfillment';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.0',
            'max' => '1.7.9',
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('IQ Fulfillment');
        $this->description = $this->l('IQ Fulfillment integrates seamlessly within your operations, taking the burden of fulfillment.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('IQ_FULFILLMENT')) {
            $this->warning = $this->l('No name provided');
        }
    }

    /**
     * @return bool
     */
    public function install()
    {
        return (
            parent::install()
            && Configuration::updateValue('PS_IQ_FULFILLMENT_IS_ACTIVATE', 0)
            && Configuration::updateValue('PS_IQ_FULFILLMENT_API_KEY', null)
            && Configuration::updateValue('PS_IQ_FULFILLMENT_API_ACCESS_ID', 0)
            && $this->registerHook('actionProductAdd')
            && $this->registerHook('actionProductUpdate')
            && $this->registerHook('actionProductDelete')
            && $this->registerHook('actionValidateOrder')
            && $this->registerHook('actionOrderEdited')
            && $this->registerHook('actionOrderStatusUpdate')
        );

    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        RequestHelper::processUninstallData();
        return (
            parent::uninstall()
            && Configuration::updateValue('PS_IQ_FULFILLMENT_IS_ACTIVATE', 0)
            && Configuration::deleteByName('PS_IQ_FULFILLMENT_API_KEY')
            && Configuration::deleteByName('PS_IQ_FULFILLMENT_API_ACCESS_ID')
            && Configuration::deleteByName('IQ_FULFILLMENT')
        );
    }

    /**
     * @return false|string
     * @throws PrestaShopException
     */
    public function getContent()
    {
        if (Tools::isSubmit('submitIqFulfillmentIntegration')) {
            $data = http_build_query([
                "store_url" => IntegrationHelper::getStoreUrl(),
                "currency_code" => IntegrationHelper::getCurrencyCode(),
                "api_key" => IntegrationHelper::createAccessTokenWithPermission(),
            ]);
            Configuration::updateValue('PS_IQ_FULFILLMENT_IS_ACTIVATE', 1);
            Tools::redirectAdmin(IntegrationHelper::CALLBACK_URL . "?" . $data, '');
        }

        $is_active = Configuration::get('PS_IQ_FULFILLMENT_IS_ACTIVATE');
        if ($is_active && WebserviceKey::isKeyActive(Configuration::get('PS_IQ_FULFILLMENT_API_KEY'))) {
            return $this->display(__FILE__, "views/templates/admin/configured.tpl");
        }
        return $this->display(__FILE__, "views/templates/admin/configure.tpl");
    }

    /**
     * @param $params
     * @return void
     */
    public function hookActionProductAdd($params)
    {
        $product = $params['product'];
        RequestHelper::processRequestData("/skus/create", $product);
    }

    /**
     * @param $params
     * @return void
     */
    public function hookActionProductUpdate($params)
    {
        $product = $params['product'];
        RequestHelper::processRequestData("/skus/create", $product);
    }

    /**
     * @param $params
     * @return void
     */
    public function hookActionProductDelete($params)
    {
        $product = $params['product'];
        RequestHelper::processRequestData("/skus/delete", $product);
    }

    /**
     * @param $params
     * @return void
     */
    public function hookActionValidateOrder($params)
    {
        RequestHelper::processRequestData("/orders/create", $params);
    }

    /**
     * @param $params
     * @return void
     */
    public function hookActionOrderEdited($params)
    {
        $order = $params;
        RequestHelper::processRequestData("/orders/update", $order);
    }

    /**
     * @param $params
     * @return void
     */
    public function hookActionOrderStatusUpdate($params)
    {
        $order_status = $params["newOrderStatus"];
        if ($order_status->name != "Canceled") {
            return;
        }
        RequestHelper::processRequestData("/orders/cancel", $params);
    }
}