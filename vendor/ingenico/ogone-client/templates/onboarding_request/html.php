<?php
/** @phpcs:ignore Generic.Files.LineLength.TooLong */
/** @var \IngenicoClient\MailTemplate $view */
/** @var $eCommercePlatform */
/** @var $companyName */
/** @var $email */
/** @var $country */
/** @var DateTime $requestTimeDate*/
/** @var $versionNumber */
/** @var $platform_name */
?>
<?php echo $view->__('onboarding_request.dear', [], 'email'); ?>,<br />
<?php echo $view->__('onboarding_request.text1', ['%platform_name%' => $platform_name], 'email'); ?> <?php echo $eCommercePlatform; ?>.<br /><br />

<strong><?php echo $view->__('onboarding_request.text2', [], 'email'); ?>:</strong><br />
<?php echo $view->__('onboarding_request.text3', [], 'email'); ?>: <?php echo $companyName; ?><br />
<?php echo $view->__('onboarding_request.text4', [], 'email'); ?>: <?php echo $email; ?><br />
<?php echo $view->__('onboarding_request.text5', [], 'email'); ?>: <?php echo $country; ?><br />
<?php echo $view->__('onboarding_request.text6', [], 'email'); ?>: <?php echo $requestTimeDate->format('r'); ?><br /><br />

<strong><?php echo $view->__('onboarding_request.text7', [], 'email'); ?>:</strong><br />
<?php echo $view->__('onboarding_request.text8', [], 'email'); ?>: <?php echo $versionNumber; ?><br /><br />

<?php echo $view->__('onboarding_request.text9', [], 'email'); ?><br />
<?php echo $platform_name; ?>,<br />
<?php echo $view->__('onboarding_request.text11', [], 'email'); ?><br />
