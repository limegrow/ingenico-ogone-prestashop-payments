<?php
/** @var \IngenicoClient\MailTemplate $view */
/** @var string $contents */
/** @var string $locale */
/** @var string $shop_name */
/** @var string $shop_logo */
/** @var string $shop_url */
/** @var string $customer_name */
/** @var string $ingenico_logo */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="<?php echo substr($locale, 0, 2); ?>">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
    <title>Message from <?php echo $shop_name; ?></title>
    <style>
        a {
            text-decoration: none;
            color: #306ba8
        }
    </style>
</head>
<body style="-webkit-text-size-adjust:none;background-color:#f2f5f7;width:700px;font-family:Open-sans, sans-serif;color:#555454;font-size:15px;line-height:18px;margin:auto;padding: 0 20px 20px">
<a title="<?php echo $shop_name; ?>" href="<?php echo $shop_url; ?>" style="text-align:center;width:100%;float:left;margin:30px 0;">
    <img width="150px" src="<?php echo $ingenico_logo; ?>" alt="<?php echo $shop_name; ?>"/>
</a>
<table class="table table-mail"
       style="width:100%;margin-top:10px;">
    <tr>
        <td align="center" style="padding:7px 0">
            <table class="table" bgcolor="#ffffff" style="width:100%">
                <tr>
                    <td class="box" style="padding:7px 0">
                        <table class="table" style="width:100%">
                            <tr>
                                <td style="padding:30px">
                                    <?php echo $contents; ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>