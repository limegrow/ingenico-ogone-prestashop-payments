<?php
/** @var \IngenicoClient\MailTemplate $view */
/** @var $shop_name */
/** @var $shop_logo */
/** @var $shop_url */
/** @var $customer_name */
/** @var $order_reference */
/** @var $order_url */
?>
<?php echo $view->__('authorization.dear', ['%name%' => $customer_name], 'email'); ?>,<br/>
<?php echo $view->__('authorization.text1', ['%url%' => $order_url, '%order_id%' => $order_reference], 'email'); ?><br />
<?php echo $view->__('authorization.text2', [], 'email'); ?><br />
<a href="<?php echo $shop_url; ?>"><?php echo $shop_name; ?></a> <?php echo $view->__('authorization.administration', [], 'email'); ?><br />
