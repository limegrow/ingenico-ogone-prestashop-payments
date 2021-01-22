<?php
/** @var \IngenicoClient\MailTemplate $view */
/** @var $eCommercePlatform */
/** @var $companyName */
/** @var $email */
/** @var $country */
/** @var DateTime $requestTimeDate*/
/** @var $versionNumber */
/** @var $shop_name */
/** @var $shop_logo */
/** @var $shop_url */
/** @var $ingenico_logo */
/** @var $platform_name */
?>
<?php echo $view->__('onboarding_request.dear', [], 'email'); ?>,
<?php echo $view->__('onboarding_request.text1', ['%platform_name%' => $platform_name], 'email'); ?> <?php echo $eCommercePlatform; ?>.


<?php echo $view->__('onboarding_request.text2', [], 'email'); ?>:
<?php echo $view->__('onboarding_request.text3', [], 'email'); ?>: <?php echo $companyName; ?>
<?php echo $view->__('onboarding_request.text4', [], 'email'); ?>: <?php echo $email; ?>
<?php echo $view->__('onboarding_request.text5', [], 'email'); ?>: <?php echo $country; ?>
<?php echo $view->__('onboarding_request.text6', [], 'email'); ?>: <?php echo $requestTimeDate->format('r'); ?>


<?php echo $view->__('onboarding_request.text7', [], 'email'); ?>:
<?php echo $view->__('onboarding_request.text8', [], 'email'); ?>: <?php echo $versionNumber; ?>


<?php echo $view->__('onboarding_request.text9', [], 'email'); ?>
<?php echo $platform_name; ?>,
<?php echo $view->__('onboarding_request.text11', [], 'email'); ?>>
