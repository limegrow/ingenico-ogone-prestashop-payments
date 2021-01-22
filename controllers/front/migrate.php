<?php
/**
 * 2007-2019 Ingenico
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@ingenico.com we can send you a copy immediately.
 *
 * @author    Ingenico <contact@ingenico.com>
 * @copyright Ingenico
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

require_once dirname(__FILE__) . '/../../setup/Migration.php';
require_once dirname(__FILE__) . '/../../model/Alias.php';

use Ingenico\Utils;
use Ingenico\Setup\Migration;
use Ingenico\Model\Alias;

class Ingenico_EpaymentsMigrateModuleFrontController extends ModuleFrontController
{
    /** @var Ingenico_epayments */
    public $module;

    /** @var Migration */
    private $migration;

    /**
     * @var Alias
     */
    private $alias;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->migration = new Migration($this->module->name);
        $this->alias = new Alias($this->module);
    }

    /**
     * Initializes common front page content
     *
     * @throws Exception
     */
    public function initContent()
    {
        parent::initContent();

        $response = [
            'success' => true,
            'data' => []
        ];


        try {
            $action = Tools::getValue('action');

            switch ($action) {
                case 'step1':
                    // Keys of Configuration that must be saved
                    $backupData = [
                        'OGONE_SHA_IN',
                        'OGONE_SHA_OUT',
                        'OGONE_DL_SHA_IN',
                    ];

                    // Backup Configuration
                    $result = [];
                    foreach ($backupData as $key) {
                        $value = Configuration::get($key);
                        $result[$key] = $value;
                    }

                    // Save Configuration
                    Migration::saveConf('INGENICO_MIGRATION_JSON_ROLLBACK', $result);

                    // Load Saved Configuration
                    $savedData = Migration::getConf('INGENICO_MIGRATION_JSON_ROLLBACK');

                    // Verify Saved Configuration
                    foreach ($backupData as $key) {
                        if (empty($savedData[$key])) {
                            throw new Exception('Validation failed: ' . $key . ' is empty' );
                        }

                        $value = $savedData[$key];
                        if (strcmp(Configuration::get($key), $value) !== 0) {
                            throw new Exception('Validation failed: ' . $key );
                        }
                    }

                    $response['data'][] = 'Configuration was saved';
                    break;
                case 'step2':
                    // Generate new parameters
                    $newShaSignature = Migration::generateHash(40);
                    $newWebHook = $this->context->link->getModuleLink($this->module->name, 'webhook');

                    // Save Configuration
                    $newCredentials = [
                        'OGONE_SHA_IN' => $newShaSignature,
                        'OGONE_SHA_OUT' => $newShaSignature,
                        'OGONE_DL_SHA_IN' => $newShaSignature,
                        'WEBHOOK_URL' => $newWebHook
                    ];
                    Migration::saveConf('INGENICO_MIGRATION_JSON_NEW', $newCredentials);

                    // Update PSPID on Ingenico side with the new values
                    $result = $this->migration->updateIngenicoDetails([
                        'SHA_IN' => $newShaSignature,
                        'SHA_OUT' => $newShaSignature,
                        'DIRECTLINK_SHA_IN' => $newShaSignature,
                        'SHA_ALGORITHM' => 'SHA-512',
                        'ENCODING' => 'UTF-8',
                        'WEBHOOK_URL' => $newWebHook
                    ]);

                    if (!$result) {
                        throw new Exception('Failed to update account details.');
                    }

                    // Setup the new plugin
                    $this->module->saveSetting(true, 'connection_live_webhook', $newWebHook);
                    $this->module->saveSetting(true, 'connection_live_signature', $newShaSignature);

                    $response['data'][] = 'New SHA signature was generated';
                    break;
                case 'step3':
                    // Setup the new plugin
                    Utils::updateConfig('connection_mode', 'on');
                    Utils::updateConfig('test_to_live', 1);

                    // @todo Compare credentials
                    $newCredentials = Migration::getConf('INGENICO_MIGRATION_JSON_NEW');
                    $remoteCredentials = $this->migration->getIngenicoDetails(Configuration::get('OGONE_PSPID'));

                    if ($newCredentials !== $remoteCredentials) {
                        //throw new Exception('Verification is failed.');
                    }

                    // Setup the new plugin
                    $this->module->saveSetting(true, 'connection_live_pspid', Configuration::get('OGONE_PSPID'));
                    $this->module->saveSetting(true, 'connection_live_dl_user', Configuration::get('OGONE_DL_USER'));
                    $this->module->saveSetting(true, 'connection_live_dl_password', Configuration::get('OGONE_DL_PASSWORD'));

                    // Mapping plugin logic settings
                    $configuration = [
                        'settings_directsales' => Configuration::get('OGONE_OPERATION') === 'SAL',
                        'settings_tokenisation' => (bool) Configuration::get('OGONE_PROPOSE_ALIAS'),
                        'settings_oneclick' => (bool) Configuration::get('OGONE_PROPOSE_ALIAS'),
                        'paymentpage_type' => (bool) Configuration::get('OGONE_MAKE_IP') ? 'INLINE' : 'REDIRECT',
                        'settings_skipsecuritycheck' => !(bool) Configuration::get('OGONE_USE_D3D')
                    ];

                    foreach ($configuration as $key => $value) {
                        $this->module->saveSetting(true, $key, $value);
                    }

                    if (Configuration::get('OGONE_USE_KLARNA')) {
                        $this->module->saveSetting(true, 'selected_payment_methods', ['klarna']);
                    }

                    $response['data'][] = 'Configuration were migrated';
                    break;
                case 'aliases_info':
                    $response['aliases'] = $this->migration->getAliasesIDs();
                    $response['data'][] = 'Found ' . count($response['aliases']) . ' aliases';

                    break;
                case 'import_alias':
                    $alias_id = Tools::getValue('alias_id');
                    $alias = $this->migration->getAlias($alias_id);

                    \Db::getInstance()->insert(
                        'ingenico_aliases',
                        [
                            'customer_id' => (int) $alias['id_customer'],
                            'ALIAS' => $alias['alias'],
                            'BRAND' => $alias['brand'],
                            'CARDNO' => $alias['cardno'],
                            'BIN' => $alias['cn'],
                            'PM' => 'CreditCard',
                            'ED' => date('my', strtotime($alias['expiry_date'])),
                            'created_at' => $alias['date_add'],
                            'updated_at' => $alias['date_upd'],
                        ],
                        false,
                        false,
                        \Db::INSERT_IGNORE
                    );

                    break;
                case 'finish':
                    $this->migration->setIsMigrationPerformed();
                    break;
                default:
                    //
                    break;
            }
        } catch (Exception $e) {
            $response['success'] = false;
            $response['data'][] = $e->getMessage();
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    /**
     * Get IDs of Aliases
     *
     * @return array
     */
    private function getAliasesIDs()
    {
        return $this->migration->getAliasesIDs();
    }

    /**
     * Get Alias by ID
     *
     * @param $id
     * @return array|false
     */
    private function getAlias($id)
    {
        return $this->migration->getAlias($id);
    }

}
