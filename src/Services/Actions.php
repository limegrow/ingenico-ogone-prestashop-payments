<?php
/**
 * 2007-2021 Ingenico
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

namespace Ingenico\Payment\Services;

use Order;
use IngenicoClient\IngenicoCoreLibrary;
use IngenicoClient\Payment;
use IngenicoClient\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;
use Ingenico\Payment\Connector;

class Actions
{
    const ERROR_ACTION_REQUIRED = 2;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Actions constructor.
     * @param RequestStack $request_stack
     * @param Connector $connector
     */
    public function __construct(
        RequestStack $request_stack,
        Connector $connector
    ) {
        $this->request = $request_stack->getCurrentRequest();
        $this->connector = $connector;
        $this->translator = $connector->translator;
    }

    /**
     * Capture.
     *
     * @return \IngenicoClient\Payment
     * @throws \IngenicoClient\Exception
     */
    public function capture($capture_amount)
    {
        $orderId = $this->request->get('orderId');
        $payId = $this->connector->getIngenicoPayIdByOrderId($orderId);

        return $this->connector->coreLibrary->capture($orderId, $payId, $capture_amount);
    }

    /**
     * Cancel.
     *
     * @return \IngenicoClient\Payment
     * @throws \IngenicoClient\Exception
     */
    public function cancel()
    {
        $orderId = $this->request->get('orderId');
        $payId = $this->connector->getIngenicoPayIdByOrderId($orderId);

        return $this->connector->coreLibrary->cancel($orderId, $payId);
    }

    /**
     * Refund.
     *
     * @return \IngenicoClient\Payment
     * @throws \IngenicoClient\Exception
     */
    public function refund($refund_amount = null)
    {
        $orderId = $this->request->get('orderId');
        $payId = $this->connector->getIngenicoPayIdByOrderId($orderId);

        return $this->connector->coreLibrary->refund($orderId, $payId, $refund_amount);
    }

    /**
     * @return \IngenicoClient\Payment
     * @throws \IngenicoClient\Exception
     */
    public function api()
    {
        $action = $this->request->get('action');
        $orderId = $this->request->get('orderId');
        $paymentId = $this->request->get('paymentId');
        $order = new Order($orderId);

        switch ($action) {
            case 'capture':
                $captureAmount = $this->request->get('captureAmount');
                if ($captureAmount > $this->connector->total->getAvailableCaptureAmount($orderId)) {
                    throw new Exception(
                        $this->translator->trans('order.action.capture_too_much', [], 'messages')
                    );
                }

                if ($captureAmount <= 0) {
                    throw new Exception(
                        $this->translator->trans('order.action.capture_too_little', [], 'messages')
                    );
                }

                if ($captureAmount == $order->total_paid_tax_incl) {
                    /** @var Payment $result */
                    $result = $this->connector->coreLibrary->capture($orderId, $paymentId);
                } else {
                    /** @var Payment $result */
                    $result = $this->connector->coreLibrary->capture($orderId, $paymentId, $captureAmount);
                }

                switch ($result->getPaymentStatus()) {
                    case IngenicoCoreLibrary::STATUS_CAPTURE_PROCESSING:
                        $result->setMessage(
                            $this->translator->trans('order.action.capture_pending', [], 'messages')
                        );
                        break;
                    case IngenicoCoreLibrary::STATUS_CAPTURED:
                        $result->setMessage(
                            $this->translator->trans('order.action.captured', [], 'messages')
                        );
                        break;
                }

                return $result;
            case 'refund':
                $refundAmount = $this->request->get('refundAmount');

                if ($refundAmount > $this->connector->total->getAvailableRefundAmount($orderId)) {
                    throw new Exception(
                        $this->translator->trans('order.action.refund_too_much', [], 'messages')
                    );
                }

                if ($refundAmount <= 0) {
                    throw new Exception(
                        $this->translator->trans('order.action.refund_too_little', [], 'messages')
                    );
                }

                /** @var Payment $result */
                try {
                    $result = $this->connector->coreLibrary->refund($orderId, $paymentId, $refundAmount);
                } catch (Exception $e) {
                    /**
                     * 50001111 - Operation is not allowed : check user privileges
                     * 50001218 - Operation not permitted for the merchant
                     * 50001046 - Operation not permitted for the merchant
                     * 50001186 - Operation not permitted
                     * 50001187 - Operation not permitted
                     */
                    if (in_array((string) $e->getCode(), [
                        '50001111', '50001218', '50001046', '50001186', '50001187'
                    ])) {
                        throw new Exception($e->getMessage(), self::ERROR_ACTION_REQUIRED);
                    } else {
                        throw $e;
                    }
                }

                switch ($result->getPaymentStatus()) {
                    case IngenicoCoreLibrary::STATUS_REFUND_PROCESSING:
                        $result->setMessage(
                            $this->translator->trans('order.action.refund_pending', [], 'messages')
                        );
                        break;
                    case IngenicoCoreLibrary::STATUS_REFUNDED:
                        $result->setMessage(
                            $this->translator->trans('order.action.refunded', [], 'messages')
                        );
                        break;
                }

                return $result;
            case 'cancel':
                /** @var Payment $result */
                $payment = $this->connector->coreLibrary->cancel($orderId, $paymentId);
                $payment->setMessage(
                    $this->translator->trans('order.action.cancelled', [], 'messages')
                );

                return $payment;
            default:
                throw new Exception('Action is unavailable');
        }
    }
}

