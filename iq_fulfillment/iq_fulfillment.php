<?php

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
            && $this->registerHook('actionValidateOrderAfter')
            && $this->registerHook('actionOrderEdited')
            && $this->registerHook('actionOrderStatusUpdate')
        );

    }

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

    public function getContent()
    {
        if (Tools::isSubmit('submitIqFulfillmentIntegration')) {
            Configuration::updateValue('PS_IQ_FULFILLMENT_IS_ACTIVATE', 1);
            $data = http_build_query([
                "store_url" => IntegrationHelper::getStoreUrl(),
                "api_key" => IntegrationHelper::createAccessTokenWithPermission(),
            ]);
            Tools::redirectAdmin(IntegrationHelper::CALLBACK_URL . "?" . $data, '', false, ['target' => '_blank']);
        }

        $is_active = Configuration::get('PS_IQ_FULFILLMENT_IS_ACTIVATE');
        if ($is_active && WebserviceKey::isKeyActive(Configuration::get('PS_IQ_FULFILLMENT_API_KEY'))) {
            return $this->display(__FILE__, "views/templates/admin/configured.tpl");
        }
        return $this->display(__FILE__, "views/templates/admin/configure.tpl");
    }

    public function hookActionProductAdd($params)
    {
        $product = $params['product'];
        RequestHelper::processRequestData("/skus/create", $product);
    }

    public function hookActionProductUpdate($params)
    {
        $product = $params['product'];
        RequestHelper::processRequestData("/skus/update", $product);
    }

    public function hookActionProductDelete($params)
    {
        $product = $params['product'];
        RequestHelper::processRequestData("/skus/delete", $product);
    }

    public function hookActionValidateOrderAfter($params)
    {
    }

    public function hookActionOrderEdited($params)
    {
    }

    public function hookActionOrderStatusUpdate($params)
    {
    }
}