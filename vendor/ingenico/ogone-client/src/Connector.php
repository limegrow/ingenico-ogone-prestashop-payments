<?php

namespace IngenicoClient;

/**
 * Class Connector
 * @package IngenicoClient
 */
abstract class Connector implements ConnectorInterface
{
    const PARAM_NAME_TYPE = 'type';
    const PARAM_NAME_ORDER_ID = 'order_id';
    const PARAM_NAME_ALIAS_ID = 'alias_id';
    const PARAM_CARD_BRAND = 'card_brand';
    const PARAM_CARD_CN = 'card_cn';
    const PARAM_DATA = 'data';
    const PARAM_NAME_URL = 'url';
    const PARAM_NAME_FIELDS = 'fields';
    const PARAM_NAME_CATEGORIES = 'categories';
    const PARAM_NAME_METHODS = 'methods';
    const PARAM_NAME_CC_URL = 'credit_card_url';
    const PARAM_NAME_PAY_ID = 'pay_id';
    const PARAM_NAME_PAYMENT_STATUS = 'payment_status';
    const PARAM_NAME_IS_SHOW_WARNING = 'is_show_warning';
    const PARAM_NAME_MESSAGE = 'message';
    const PARAM_NAME_PAYMENT_ID = 'payment_id';
    const PARAM_NAME_PM = 'pm';
    const PARAM_NAME_BRAND = 'brand';
    const PARAM_NAME_EPLATFORM = 'eCommercePlatform';
    const PARAM_NAME_COMPANY = 'companyName';
    const PARAM_NAME_EMAIL = 'email';
    const PARAM_NAME_COUNTRY = 'country';
    const PARAM_NAME_REQUEST_TIME = 'requestTimeDate';
    const PARAM_NAME_VERSION_NUM = 'versionNumber';
    const PARAM_NAME_SHOP_NAME = 'shop_name';
    const PARAM_NAME_SHOP_LOGO = 'shop_logo';
    const PARAM_NAME_SHOP_URL = 'shop_url';
    const PARAM_NAME_INGENICO_LOGO = 'ingenico_logo';
    const PARAM_NAME_CUSTOMER_NAME = 'customer_name';
    const PARAM_NAME_ORDER_REFERENCE = 'order_reference';
    const PARAM_NAME_ORDER_URL = 'order_url';
    const PARAM_NAME_ORDER_VIEW_URL = 'order_view_url';
    const PARAM_NAME_PRODUCTS = 'products';
    const PARAM_NAME_ORDER_TOTAL = 'order_total';
    const PARAM_NAME_PAYMENT_LINK = 'complete_payment_link';
    const PARAM_NAME_PLATFORM = 'platform';
    const PARAM_NAME_TICKET = 'ticket';
    const PARAM_NAME_DESCRIPTION = 'description';
}
