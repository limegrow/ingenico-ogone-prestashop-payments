<?php

declare(strict_types=1);

use Ingenico\Payment\Alias;
use Ingenico\Payment\Utils;

class Ingenico_EpaymentsAliasesModuleFrontController extends ModuleFrontController
{
    /**
     * @var \Ingenico\Payment\Connector
     */
    public $connector;

    public function initContent()
    {
        parent::initContent();
        $this->connector = $this->module->getModuleService('ingenico.payment.connector');

        // Set up Controller for Connector
        $this->connector->controller = $this;
        $this->dispatch();
    }

    private function dispatch()
    {
        $action = Tools::getValue('action');

        switch ($action) {
            case 'delete':
                $this->processDelete();
                break;

            default:
                $this->connector->showStoredCreditCardTempate();
                break;
        }
    }

    public function processDelete()
    {
        $idAlias = (int) Tools::getValue('id_alias');
        if (!$idAlias) {
            return false;
        }

        $this->connector->processDeleteAlias($idAlias);


        Tools::redirect($this->context->link->getModuleLink($this->connector->name, 'aliases'));
    }
}
