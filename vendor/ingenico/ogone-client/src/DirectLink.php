<?php

namespace IngenicoClient;

use Psr\Log\LoggerInterface;
use Ogone\DirectLink\DirectLinkQueryRequest;
use Ogone\DirectLink\DirectLinkQueryResponse;
use Ogone\DirectLink\DirectLinkMaintenanceRequest;
use Ogone\DirectLink\DirectLinkMaintenanceResponse;
use Ogone\DirectLink\MaintenanceOperation;

/**
 * Class DirectLink
 */
class DirectLink
{
    const ITEM_ID = 'itemid';
    const ITEM_NAME = 'itemname';
    const ITEM_PRICE = 'itemprice';
    const ITEM_VATCODE = 'itemvatcode';

    /** @var LoggerInterface|null */
    private $logger;

    /**
     * Set Logger.
     *
     * @param LoggerInterface|null $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Get Logger.
     *
     * @return LoggerInterface|null
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Create Refund Request.
     *
     * @param Configuration $configuration
     * @param string        $orderId
     * @param string        $payId
     * @param int           $amount
     * @param bool          $isPartially
     *
     * @return Payment
     */
    public function createRefund(
        Configuration $configuration,
        $orderId,
        $payId,
        $amount,
        $isPartially
    ) {
        $operation = $isPartially ?
            MaintenanceOperation::OPERATION_REFUND_PARTIAL : MaintenanceOperation::OPERATION_REFUND_LAST_OR_FULL;

        return $this->createMaintenanceRequest(
            $configuration,
            $orderId,
            $payId,
            $amount,
            [],
            new MaintenanceOperation($operation)
        );
    }

    /**
     * Create Capture Request.
     *
     * @param Configuration $configuration
     * @param string        $orderId
     * @param string        $payId
     * @param int           $amount
     * @param bool          $isPartially
     *
     * @return Payment
     */
    public function createCapture(
        Configuration $configuration,
        $orderId,
        $payId,
        $amount,
        $isPartially
    ) {
        $operation = $isPartially ?
            MaintenanceOperation::OPERATION_CAPTURE_PARTIAL : MaintenanceOperation::OPERATION_CAPTURE_LAST_OR_FULL;

        return $this->createMaintenanceRequest(
            $configuration,
            $orderId,
            $payId,
            $amount,
            [],
            new MaintenanceOperation($operation)
        );
    }

    /**
     * Create Void Request.
     *
     * @param Configuration $configuration
     * @param string        $orderId
     * @param string        $payId
     * @param int           $amount
     * @param bool          $isPartially
     *
     * @return Payment
     */
    public function createVoid(
        Configuration $configuration,
        $orderId,
        $payId,
        $amount,
        $isPartially
    ) {
        $operation = $isPartially ?
            MaintenanceOperation::OPERATION_AUTHORISATION_DELETE :
            MaintenanceOperation::OPERATION_AUTHORISATION_DELETE_AND_CLOSE;

        return $this->createMaintenanceRequest(
            $configuration,
            $orderId,
            $payId,
            $amount,
            [],
            new MaintenanceOperation($operation)
        );
    }

    /**
     * Create Maintenance Request.
     *
     * Items array should contain item with keys like:
     * ['itemid', 'itemname', 'itemprice', 'itemquant', 'itemvatcode', 'taxincluded']
     *
     * @param Configuration        $configuration
     * @param string               $orderId
     * @param string               $payId
     * @param int                  $amount
     * @param array                $items
     * @param MaintenanceOperation $operation
     *
     * @return Payment
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings("cognitive-complexity")
     */
    public function createMaintenanceRequest(
        Configuration $configuration,
        $orderId,
        $payId,
        $amount,
        array $items,
        MaintenanceOperation $operation
    ) {
        $maintenanceRequest = new DirectLinkMaintenanceRequest($configuration->getShaComposer());
        $maintenanceRequest->setOgoneUri($configuration->getApiMaintenancedirect());

        $maintenanceRequest->setPspId($configuration->getPspid());
        $maintenanceRequest->setUserId($configuration->getUserId());
        $maintenanceRequest->setPassword($configuration->getPassword());
        $maintenanceRequest->setOperation($operation);

        if ($orderId) {
            $maintenanceRequest->setOrderId($orderId);
        }

        if ($payId) {
            $maintenanceRequest->setPayId($payId);
        }

        if ($amount > 0) {
            $maintenanceRequest->setAmount((int) bcmul(100, $amount));
        }

        if (count($items) > 0) {
            foreach ($items as &$item) {
                if (isset($item[self::ITEM_ID])) {
                    $item[self::ITEM_ID] = mb_strimwidth($item[self::ITEM_ID], 0, 15);
                }

                if (isset($item[self::ITEM_NAME])) {
                    $item[self::ITEM_NAME] = mb_strimwidth($item[self::ITEM_NAME], 0, 30);
                }

                if (isset($item[self::ITEM_PRICE])) {
                    $item[self::ITEM_PRICE] = (int) bcmul(100, $item[self::ITEM_PRICE]);
                }

                if (isset($item[self::ITEM_VATCODE])) {
                    $item[self::ITEM_VATCODE] = $item[self::ITEM_VATCODE] . '%';
                }
            }

            $maintenanceRequest->setItems($items);
        }

        $params = $maintenanceRequest->toArray();
        $url = $maintenanceRequest->getOgoneUri();
        $shaSign = $maintenanceRequest->getShaSign();

        $client = new Client($this->logger);
        $response = $client->post($params, $url, $shaSign);

        return new Payment((new DirectLinkMaintenanceResponse($response))->toArray());
    }

    /**
     * Create payment status request.
     *
     * @param Configuration $configuration
     * @param $orderId
     * @param $payId
     *
     * @return Payment
     */
    public function createStatusRequest(
        Configuration $configuration,
        $orderId,
        $payId
    ) {
        $queryRequest = new DirectLinkQueryRequest($configuration->getShaComposer());
        $queryRequest->setOgoneUri($configuration->getApiQuerydirect());

        $queryRequest->setPspId($configuration->getPspid());
        $queryRequest->setUserId($configuration->getUserId());
        $queryRequest->setPassword($configuration->getPassword());

        if ($orderId) {
            $queryRequest->setOrderId($orderId);
        }

        if ($payId) {
            $queryRequest->setPayId($payId);
        }

        $params = $queryRequest->toArray();
        $url = $queryRequest->getOgoneUri();
        $shaSign = $queryRequest->getShaSign();

        $client = new Client($this->logger);
        $response = $client->post($params, $url, $shaSign);
        return new Payment((new DirectLinkQueryResponse($response))->toArray());
    }
}
