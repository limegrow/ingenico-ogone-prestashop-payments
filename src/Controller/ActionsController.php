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

namespace Ingenico\Payment\Controller;

use IngenicoClient\Payment;
use IngenicoClient\Exception;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Ingenico\Payment\Connector;
use Ingenico\Payment\Services\Actions;
use IngenicoClient\IngenicoCoreLibrary;
use Symfony\Component\HttpFoundation\Response;

class ActionsController extends FrameworkBundleAdminController
{
    /**
     * Capture
     *
     * @AdminSecurity("is_granted(['read', 'create', 'update', 'delete'], 'ADMINMODULESSF_')")
     *
     * @return Response
     */
    public function captureAction()
    {
        /** @var Connector $connector */
        $connector = $this->get('ingenico.payment.connector');

        /** @var Actions $result */
        $actions = $this->get('ingenico.payment.actions');

        try {
            /** @var \IngenicoClient\Payment $result */
            $result = $actions->capture();

            switch ($result->getPaymentStatus()) {
                case IngenicoCoreLibrary::STATUS_CAPTURE_PROCESSING:
                    $this->addFlash(
                        'success',
                        $this->trans('order.action.capture_pending', 'messages', [])
                    );
                    break;
                case IngenicoCoreLibrary::STATUS_CAPTURED:
                    $this->addFlash(
                        'success',
                        $this->trans('order.action.captured', 'messages', [])
                    );

                    break;
                default:
                    break;
            }
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * Refund
     *
     * @AdminSecurity("is_granted(['read', 'create', 'update', 'delete'], 'ADMINMODULESSF_')")
     *
     * @return Response
     */
    public function refundAction()
    {
        /** @var Connector $connector */
        $connector = $this->get('ingenico.payment.connector');

        /** @var Actions $result */
        $actions = $this->get('ingenico.payment.actions');

        try {
            /** @var \IngenicoClient\Payment $result */
            $result = $actions->refund();

            switch ($result->getPaymentStatus()) {
                case IngenicoCoreLibrary::STATUS_REFUND_PROCESSING:
                    $this->addFlash(
                        'success',
                        $this->trans('order.action.refund_pending', 'messages', [])
                    );

                    break;
                case IngenicoCoreLibrary::STATUS_REFUNDED:
                    $this->addFlash(
                        'success',
                        $this->trans('order.action.refunded', 'messages', [])
                    );
                    break;
            }
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * Cancel
     *
     * @AdminSecurity("is_granted(['read', 'create', 'update', 'delete'], 'ADMINMODULESSF_')")
     *
     * @return Response
     */
    public function cancelAction()
    {
        /** @var Connector $connector */
        $connector = $this->get('ingenico.payment.connector');

        /** @var Actions $result */
        $actions = $this->get('ingenico.payment.actions');

        try {
            $actions->cancel();

            $this->addFlash(
                'success',
                $this->trans('order.action.cancelled', 'messages', [])
            );
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * Api
     *
     * @AdminSecurity("is_granted(['read', 'create', 'update', 'delete'], 'ADMINMODULESSF_')")
     *
     * @return Response
     */
    public function apiAction()
    {
        /** @var Connector $connector */
        $connector = $this->get('ingenico.payment.connector');

        /** @var Actions $result */
        $actions = $this->get('ingenico.payment.actions');

        try {
            /** @var Payment $result */
            $result = $actions->api();

            return $this->json([
                'status' => 'ok',
                'message' => $result->getMessage()
            ]);
        } catch (Exception $e) {
            if ($e->getCode() === $actions::ERROR_ACTION_REQUIRED) {
                return $this->json([
                    'status' => 'action_required',
                    'message' => $e->getMessage()
                ]);
            }

            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
