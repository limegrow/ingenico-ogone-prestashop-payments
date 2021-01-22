<?php
/** @var \IngenicoClient\MailTemplate $view */
/** @var $shop_name */
/** @var $shop_logo */
/** @var $shop_url */
/** @var $customer_name */
/** @var $order_reference */
?>
<?php echo $view->__('refund_failed.dear', ['%name%' => '<span style="color: #306ba8">' . $customer_name . '</span>'], 'email'); ?>,
<br/><br />
<?php echo $view->__('refund_failed.text1', ['%order_id%' => '<a href="' . $order_url . '" style="text-decoration: none; color: #306ba8">' . $order_reference . '</a>'], 'email'); ?>
<br /><br />
<?php echo $view->__('refund_failed.text2', [], 'email'); ?>
<br /><br />
<?php echo $view->__('refund_failed.text3', [], 'email'); ?>
<br /><br />
<a style="text-decoration: none; color: #306ba8" href="<?php echo $shop_url; ?>"><?php echo $shop_name; ?> <?php echo $view->__('refund_failed.administration', [], 'email'); ?></a>