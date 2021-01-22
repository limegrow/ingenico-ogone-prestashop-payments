#!/bin/bash
CURRENT_DIR=$(pwd)
TMPDIR="/tmp"

mkdir $TMPDIR/ingenico_epayments
cp -R -f $CURRENT_DIR/* $TMPDIR/ingenico_epayments/

cd $TMPDIR/ingenico_epayments/
npm install
gulp js:build
gulp css:build

git clone https://github.com/jmcollin/autoindex

read -r -d '' AUTOINDEX_INDEX <<"EOF"
<?php
/**
 * 2007-2020 Ingenico
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

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header("Location: ../");
exit;
EOF

mkdir -p ./autoindex/sources
echo AUTOINDEX_INDEX > ./autoindex/sources/index.php
cd ./autoindex/
php ./index.php ../
cd ../

rm -rf ./autoindex
rm -rf ./vendor/ingenico/ogone-client/src/PaymentMethod/index.php
rm -rf ./gulpfile.js
rm -rf ./vendor/ingenico/ogone-client/vendor/sebastian/diff/tests/Output/UnifiedDiffOutputBuilderDataProvider.php
rm -rf ./vendor/sebastian/diff/tests/Output/UnifiedDiffOutputBuilderDataProvider.php
rm -rf ./.git
rm -rf ./vendor/ingenico/ogone-sdk-php/.git
rm -rf ./vendor/ingenico/ogone-client/.git
rm -rf ./node_modules/

cd $TMPDIR
zip -r ingenico_epayments.zip ./ingenico_epayments/
mv $TMPDIR/ingenico_epayments.zip $CURRENT_DIR/ingenico_epayments.zip
rm -rf $TMPDIR/ingenico_epayments

echo "Finished. Package located in $CURRENT_DIR/ingenico_epayments.zip"

