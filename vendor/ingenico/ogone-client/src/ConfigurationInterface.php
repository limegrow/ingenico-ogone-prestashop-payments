<?php

namespace IngenicoClient;

/**
 * Interface ConfigurationInterface
 *
 * @package IngenicoClient
 */
interface ConfigurationInterface
{
    /**
     * Mode
     */
    const MODE_PRODUCTION = true;
    const MODE_TEST = false;

    /**
     * Hash Algorithm
     */
    const HASH_SHA1 = 'sha1';
    const HASH_SHA256 = 'sha256';
    const HASH_SHA512 = 'sha512';

    /**
     * Payment Page Types
     */
    const PAYMENT_TYPE_REDIRECT = 'REDIRECT';
    const PAYMENT_TYPE_INLINE = 'INLINE';

    /**
     * Layout of the payment method
     */
    const PMLIST_HORIZONTAL_LEFT = 0;
    const PMLIST_HORIZONTAL = 1;
    const PMLIST_VERTICAL = 2;

    /**
     * Payment Page Templates
     */
    const PAYMENT_PAGE_TEMPLATE_INGENICO = 'INGENICO';
    const PAYMENT_PAGE_TEMPLATE_STORE = 'STORE';
    const PAYMENT_PAGE_TEMPLATE_EXTERNAL = 'EXTERNAL';

    /**
     * Installments Types
     */
    const INSTALMENTS_TYPE_FIXED = 'FIXED';
    const INSTALMENTS_TYPE_FLEXIBLE = 'FLEXIBLE';

    /**
     * Configuration keys
     */
    const CONF_CONNECTION_MODE = 'connection_mode';
    const CONF_CONNECTION_TEST_ALGORITHM = 'connection_test_algorithm';
    const CONF_CONNECTION_TEST_PSPID = 'connection_test_pspid';
    const CONF_CONNECTION_TEST_SIGNATURE = 'connection_test_signature';
    const CONF_CONNECTION_TEST_DL_USER = 'connection_test_dl_user';
    const CONF_CONNECTION_TEST_DL_PASSWORD = 'connection_test_dl_password';
    const CONF_CONNECTION_TEST_WEBHOOK = 'connection_test_webhook';
    const CONF_CONNECTION_LIVE_ALGORITHM  = 'connection_live_algorithm';
    const CONF_CONNECTION_LIVE_PSPID = 'connection_live_pspid';
    const CONF_CONNECTION_LIVE_SIGNATURE = 'connection_live_signature';
    const CONF_CONNECTION_LIVE_DL_USER = 'connection_live_dl_user';
    const CONF_CONNECTION_LIVE_DL_PASSWORD = 'connection_live_dl_password';
    const CONF_CONNECTION_LIVE_WEBHOOK = 'connection_live_webhook';
    const CONF_SETTINGS_ORDERFREEZE_DAYS = 'settings_orderfreeze_days';
    const CONF_SETTINGS_REMINDEREMAIL_DAYS = 'settings_reminderemail_days';
    const CONF_FRAUD_NOTIFICATIONS_EMAIL = 'fraud_notifications_email';
    const CONF_DIRECT_SALE_EMAIL = 'direct_sale_email';
    const CONF_PAYMENTPAGE_TYPE = 'paymentpage_type';
    const CONF_PAYMENTPAGE_TEMPLATE = 'paymentpage_template';
    const CONF_PAYMENTPAGE_TEMPLATE_NAME = 'paymentpage_template_name';
    const CONF_PAYMENTPAGE_TEMPLATE_EXTERNALURL = 'paymentpage_template_externalurl';
    const CONF_PAYMENTPAGE_TEMPLATE_LOCALFILENAME = 'paymentpage_template_localfilename';
    const CONF_PAYMENTPAGE_LIST_TYPE = 'paymentpage_list_type';
    const CONF_INSTALMENTS_TYPE = 'instalments_type';
    const CONF_INSTALMENTS_FIXED_INSTALMENTS = 'instalments_fixed_instalments';
    const CONF_INSTALMENTS_FIXED_PERIOD = 'instalments_fixed_period';
    const CONF_INSTALMENTS_FIXED_FIRSTPAYMENT = 'instalments_fixed_firstpayment';
    const CONF_INSTALMENTS_FIXED_MINPAYMENT = 'instalments_fixed_minpayment';
    const CONF_INSTALMENTS_FLEX_INSTALMENTS_MIN = 'instalments_flex_instalments_min';
    const CONF_INSTALMENTS_FLEX_INSTALMENTS_MAX = 'instalments_flex_instalments_max';
    const CONF_INSTALMENTS_FLEX_PERIOD_MIN = 'instalments_flex_period_min';
    const CONF_INSTALMENTS_FLEX_PERIOD_MAX = 'instalments_flex_period_max';
    const CONF_INSTALMENTS_FLEX_FIRSTPAYMENT_MIN = 'instalments_flex_firstpayment_min';
    const CONF_INSTALMENTS_FLEX_FIRSTPAYMENT_MAX = 'instalments_flex_firstpayment_max';
    const CONF_SETTINGS_ADVANCED = 'settings_advanced';
    const CONF_SETTINGS_TOKENIZATION = 'settings_tokenisation';
    const CONF_SETTINGS_ONECLICK = 'settings_oneclick';
    const CONF_SETTINGS_SKIP3DSCVC = 'settings_skip3dscvc';
    const CONF_SETTINGS_SKIPSECURITYCHECK = 'settings_skipsecuritycheck';
    const CONF_SETTINGS_SECURE = 'secure';
    const CONF_SETTINGS_DIRECTSALES = 'settings_directsales';
    const CONF_SETTINGS_ORDERFREEZE = 'settings_orderfreeze';
    const CONF_SETTINGS_REMINDEREMAIL = 'settings_reminderemail';
    const CONF_FRAUD_NOTIFICATIONS = 'fraud_notifications';
    const CONF_DIRECT_SALE_EMAIL_OPTION = 'direct_sale_email_option';
    const CONF_INSTALMENTS_ENABLED = 'instalments_enabled';
    const CONF_SELECTED_PAYMENT_METHODS = 'selected_payment_methods';
    const CONF_GENERIC_COUNTRY = 'generic_country';
}
