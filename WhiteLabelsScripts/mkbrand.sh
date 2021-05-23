#!/bin/bash

BRAND_ID=$1
CURRENT_DIR=$(pwd)
TMPDIR="/tmp"
SOURCE_DIR=$2
BUILD_DIR=$3

case $1 in
     barclays)
          echo "Selected: barclays."
          MODULE_NAME="barclays_payments";
          MODULE_FILE="barclays_payments.php";
          MODULE_CLASS="Barclays_Payments";
          MODULE_BRAND="Barclays";
          MODULE_DESC="Barclays Payments";
          MODULE_AUTHOR="Barclays";
          PLATFORM_ID="PLATFORM_BARCLAYS"

          COLOR_MEDIUM_BLUE="#085DA9";
          COLOR_MID_BLUE_TWO="#018FD0";
          COLOR_PINKISH_RED="#E03030";
          COLOR_WHITE_TWO="#F1F1F1";
          COLOR_WHITE_FIVE="#DDDDDD";
          COLOR_WHITE_GREY="#848789";
          ;;
     postfinance)
          echo "Selected: Postfinance."
          MODULE_NAME="postfinance";
          MODULE_FILE="postfinance.php";
          MODULE_CLASS="Postfinance";
          MODULE_BRAND="Postfinance";
          MODULE_DESC="PostFinance";
          MODULE_AUTHOR="PostFinance";
          PLATFORM_ID="PLATFORM_POSTFINANCE"

          COLOR_MEDIUM_BLUE="#FFCC00";
          COLOR_MID_BLUE_TWO="#2A6BAA";
          COLOR_PINKISH_RED="#FF0000";
          COLOR_WHITE_TWO="#F7F7F7";
          COLOR_WHITE_FIVE="#E6E6E6";
          COLOR_WHITE_GREY="#999999";
          ;;
     kbc)
          echo "Selected: KBC."
          MODULE_NAME="kbc";
          MODULE_FILE="kbc.php";
          MODULE_CLASS="Kbc";
          MODULE_BRAND="Kbc";
          MODULE_DESC="KBC";
          MODULE_AUTHOR="KBC";
          PLATFORM_ID="PLATFORM_KBC"

          COLOR_MEDIUM_BLUE="#00ADEE";
          COLOR_MID_BLUE_TWO="#003768";
          COLOR_PINKISH_RED="#EB222E";
          COLOR_WHITE_TWO="#F6F6F6";
          COLOR_WHITE_FIVE="#DDDDDD";
          COLOR_WHITE_GREY="#AFAFAF";
          ;;
     concardis)
          echo "Selected: Concardis."
          MODULE_NAME="concardis";
          MODULE_FILE="concardis.php";
          MODULE_CLASS="Concardis";
          MODULE_BRAND="Concardis";
          MODULE_DESC="Concardis";
          MODULE_AUTHOR="ConCardis GmbH";
          PLATFORM_ID="PLATFORM_CONCARDIS"

          COLOR_MEDIUM_BLUE="#DC4405";
          COLOR_MID_BLUE_TWO="#DC4405";
          COLOR_PINKISH_RED="#EB222E";
          COLOR_WHITE_TWO="#F6F6F6";
          COLOR_WHITE_FIVE="#DDDDDD";
          COLOR_WHITE_GREY="#AFAFAF";
          ;;
     viveum)
          echo "Selected: Viveum."
          MODULE_NAME="viveum";
          MODULE_FILE="viveum.php";
          MODULE_CLASS="Viveum";
          MODULE_BRAND="Viveum";
          MODULE_DESC="Viveum";
          MODULE_AUTHOR="VIVEUM";
          PLATFORM_ID="PLATFORM_VIVEUM"

          COLOR_MEDIUM_BLUE="#020D5C";
          COLOR_MID_BLUE_TWO="#353d7d";
          COLOR_PINKISH_RED="#EB222E";
          COLOR_WHITE_TWO="#F6F6F6";
          COLOR_WHITE_FIVE="#DDDDDD";
          COLOR_WHITE_GREY="#AFAFAF";
          ;;
     payglobe)
          echo "Selected: Payglobe."
          MODULE_NAME="payglobe";
          MODULE_FILE="payglobe.php";
          MODULE_CLASS="Payglobe";
          MODULE_BRAND="Payglobe";
          MODULE_DESC="Payglobe";
          MODULE_AUTHOR="Payglobe";
          PLATFORM_ID="PLATFORM_PAYGLOBE"

          COLOR_MEDIUM_BLUE="#173A7E";
          COLOR_MID_BLUE_TWO="#e1a449";
          COLOR_PINKISH_RED="#EB222E";
          COLOR_WHITE_TWO="#F6F6F6";
          COLOR_WHITE_FIVE="#DDDDDD";
          COLOR_WHITE_GREY="#AFAFAF";
          ;;
     santander)
          echo "Selected: Santander."
          MODULE_NAME="santander";
          MODULE_FILE="santander.php";
          MODULE_CLASS="Santander";
          MODULE_BRAND="Santander";
          MODULE_DESC="Santander";
          MODULE_AUTHOR="Santander";
          PLATFORM_ID="PLATFORM_SANTANDER"

          COLOR_MEDIUM_BLUE="#E82729";
          COLOR_MID_BLUE_TWO="#E82729";
          COLOR_PINKISH_RED="#EB222E";
          COLOR_WHITE_TWO="#F6F6F6";
          COLOR_WHITE_FIVE="#DDDDDD";
          COLOR_WHITE_GREY="#AFAFAF";
          ;;
     *)
          echo "Please select template."
          exit
          ;;
esac


# See https://limegrow.atlassian.net/wiki/spaces/ING/pages/694157313/White+labels+brands+details
# See https://limegrow.atlassian.net/wiki/spaces/ING/pages/249135126/White+labels

# Temporary module dir
MODULE_DIR="$TMPDIR/$MODULE_NAME"

# Copy original module to directory of plugin
cp -r "$SOURCE_DIR/" "$MODULE_DIR/"
cd "$MODULE_DIR/"

# Rename plugin file
mv "$MODULE_DIR/ingenico_epayments.php" "$MODULE_DIR/$MODULE_FILE"

# Change class name of plugin file
sed -i -e "s/class Ingenico_Epayments/class $MODULE_CLASS/g" "$MODULE_DIR/$MODULE_FILE"

# Change branding in plugin file
sed -i -e "s/ingenico_epayments/$MODULE_NAME/g" "$MODULE_DIR/$MODULE_FILE"
sed -i -e "s/Ingenico ePayments/$MODULE_DESC/g" "$MODULE_DIR/$MODULE_FILE"
sed -i -e "s/Ingenico Group/$MODULE_AUTHOR/g" "$MODULE_DIR/$MODULE_FILE"

# Change class name of controllers
sed -i -e "s/Ingenico_Epayments/$MODULE_CLASS/g" $MODULE_DIR/controllers/front/ajax.php
sed -i -e "s/Ingenico_Epayments/$MODULE_CLASS/g" $MODULE_DIR/controllers/front/canceled.php
sed -i -e "s/Ingenico_Epayments/$MODULE_CLASS/g" $MODULE_DIR/controllers/front/cron.php
sed -i -e "s/Ingenico_Epayments/$MODULE_CLASS/g" $MODULE_DIR/controllers/front/pay.php
sed -i -e "s/Ingenico_Epayments/$MODULE_CLASS/g" $MODULE_DIR/controllers/front/payment.php
sed -i -e "s/Ingenico_Epayments/$MODULE_CLASS/g" $MODULE_DIR/controllers/front/payment_list.php
sed -i -e "s/Ingenico_Epayments/$MODULE_CLASS/g" $MODULE_DIR/controllers/front/success.php
sed -i -e "s/Ingenico_Epayments/$MODULE_CLASS/g" $MODULE_DIR/controllers/front/webhook.php
sed -i -e "s/Ingenico_Epayments/$MODULE_CLASS/g" $MODULE_DIR/controllers/front/open_invoice.php

# Change branding in PrestaShopConnector.php file
sed -i -e "s/PLATFORM_INGENICO/$PLATFORM_ID/g" "$MODULE_DIR/PrestaShopConnector.php"

# Change branding in Order Statuses
sed -i -e "s/Ingenico ePayment/$MODULE_DESC/g" $MODULE_DIR/setup/Install.php
sed -i -e "s/ingenico_epayments/$MODULE_NAME/g" $MODULE_DIR/setup/Install.php

# Change namespace of connector
sed -i -e "s/namespace Ingenico/namespace $MODULE_CLASS/g" "$MODULE_DIR/PrestaShopConnector.php"
sed -i -e 's@use Ingenico\\PrestaShopConnector@use '$MODULE_CLASS'\\PrestaShopConnector@g' "$MODULE_DIR/$MODULE_FILE"

# Change namespace of Install script
sed -i -e "s/namespace Ingenico/namespace $MODULE_CLASS/g" "$MODULE_DIR/setup/Install.php"
sed -i -e 's@use Ingenico\\PrestaShopConnector@use '$MODULE_CLASS'\\PrestaShopConnector@g' "$MODULE_DIR/setup/Install.php"
sed -i -e 's@use Ingenico\\Setup@use '$MODULE_CLASS'\\Setup@g' "$MODULE_DIR/$MODULE_FILE"

# Change namespace for Utils
sed -i -e "s/namespace Ingenico/namespace $MODULE_CLASS/g" $MODULE_DIR/utils/Utils.php
sed -i -e 's@use Ingenico\\Utils@use '$MODULE_CLASS'\\Utils@g' "$MODULE_DIR/$MODULE_FILE"

# Change namepace for Models
find $MODULE_DIR/model/ -name '*.php' -type f|while read fname; do
  sed -i -e "s/namespace Ingenico/namespace $MODULE_CLASS/g" "$fname"
done

# Change names
find $MODULE_DIR/ -name '*.php' -type f|while read fname; do
  sed -i -e 's@Ingenico\\Utils@'"$MODULE_CLASS"'\\Utils@g' "$fname"
  sed -i -e 's@Ingenico\\Model@'"$MODULE_CLASS"'\\Model@g' "$fname"
done

# Change pluginname in templates
find $MODULE_DIR/views/templates/ -iname *.tpl -type f|while read fname; do
  sed -i -e "s/mod=\'ingenico_epayments\'/mod=\'$MODULE_NAME\'/g" "$fname"
done

# Change branding in translation files
find $MODULE_DIR/translations/ -iname *.po -type f|while read fname; do
  sed -i -e "s/Ingenico ePayments/$MODULE_DESC/g" "$fname"
  sed -i -e "s/Ingenico/$MODULE_BRAND/g" "$fname"
done

# Replace logo
cp -f "$SOURCE_DIR/WhiteLabelsScripts/resources/$BRAND_ID/logo.png" $MODULE_DIR/views/img/logo.png

# Replace icons
cp -f "$SOURCE_DIR/WhiteLabelsScripts/resources/$BRAND_ID/icon.png" $MODULE_DIR/logo.png
convert $MODULE_DIR/logo.png $MODULE_DIR/logo.gif

# Replace images
cp -f "$SOURCE_DIR/WhiteLabelsScripts/resources/$BRAND_ID/inline_off.png" $MODULE_DIR/views/img/inline_off.png

# Replace colors
sed -i -e "s/#306ba8/$COLOR_MEDIUM_BLUE/g" $MODULE_DIR/views/css/backoffice.scss
sed -i -e "s/#2a6baa/$COLOR_MID_BLUE_TWO/g" $MODULE_DIR/views/css/backoffice.scss
sed -i -e "s/#eb222e/$COLOR_PINKISH_RED/g" $MODULE_DIR/views/css/backoffice.scss
sed -i -e "s/#f6f6f6/$COLOR_WHITE_TWO/g" $MODULE_DIR/views/css/backoffice.scss
sed -i -e "s/#dddddd/$COLOR_WHITE_FIVE/g" $MODULE_DIR/views/css/backoffice.scss
sed -i -e "s/#afafaf/$COLOR_WHITE_GREY/g" $MODULE_DIR/views/css/backoffice.scss

sed -i -e "s/#306ba8/$COLOR_MEDIUM_BLUE/g" $MODULE_DIR/views/css/front.scss
sed -i -e "s/#2a6baa/$COLOR_MID_BLUE_TWO/g" $MODULE_DIR/views/css/front.scss
sed -i -e "s/#eb222e/$COLOR_PINKISH_RED/g" $MODULE_DIR/views/css/front.scss
sed -i -e "s/#f6f6f6/$COLOR_WHITE_TWO/g" $MODULE_DIR/views/css/front.scss
sed -i -e "s/#dddddd/$COLOR_WHITE_FIVE/g" $MODULE_DIR/views/css/front.scss
sed -i -e "s/#afafaf/$COLOR_WHITE_GREY/g" $MODULE_DIR/views/css/front.scss

# Build static content
gulp css:build
gulp js:build

# Remove temporary files
rm -rf "$MODULE_DIR/node_modules"
rm -rf "$MODULE_DIR/WhiteLabelsScripts"

# Make package
cd $TMPDIR
zip -r "$MODULE_NAME.zip" "./$MODULE_NAME/"
mv "$TMPDIR/$MODULE_NAME.zip" "$BUILD_DIR/"
rm -rf "$MODULE_DIR/"
