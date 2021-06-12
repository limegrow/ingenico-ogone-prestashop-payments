# Changelog
## [5.4.0] - 2021-06-12
### Added
- Add AirPlus payment method
- Substitute street number from address
- Excluded some payment methods for Generic method

### Changed
- Disabled refunds for Intersolve
- Updated WL template urls

## [5.3.1] - 2021-05-27
### Changed
- Fixed: A non well formed numeric value encountered

## [5.3.0] - 2021-05-18
### Added
- Implemented Oney payment method
- Implemented `Order::isVirtual()`
- Add Generic Ingenico payment method
- Rounding issue workaround

## Changed
- Klarna api updates
- Improved order cancellation code
- Rename "Bank transfer" to "Bank Transfer"

## [5.2.1] - 2021-05-03
## Changed
- Trim owneraddress

## [5.2.0] - 2021-04-18
### Added
- Implemented PMLISTTYPE option
- Added ConfigurationInterface
- ConnectorInterface: `getOrderPaymentMethod()`
- ConnectorInterface: `getQuotePaymentMethod()`

## Changed
- Klarna: don't require ECOM_SHIPTO_POSTAL_STATE
- Klarna: changed DoB format
- Carte Bancaire fixes

## [5.1.0] - 2021-03-25
### Added
- Implemented bank selection for iDeal
- Implemented additional order metadata feature
- Allows to use custom PM and BRAND using additional order metadata

### Changed
- Klarna: Fix street field issues

## [5.0.2] - 2020-12-01
### Added
- Added Sofort payment methods

## [5.0.1] - 2020-11-27
### Changed
- Fixed alias saving in the inline payment page mode

## [5.0.0] - 2020-11-03

### Added
- Implemented `isHidden()` for `PaymentMethod` class
- Make KlarnaDirectDebit and KlarnaBankTransfer payment methods to be hidden

### Changed
- Make `getPaymentMethods()` to be non-static

### Fixed
- Fixed the country configuration of Afterpay payment method

## [4.1.0] - 2020-10-26
### Added
- Improve WhiteLabels feature
- Implemented `validateOpenInvoiceCheckoutAdditionalFields()`

### Changed
- Remove address length limitions
- Word-wrap of street address
- Don't use `ECOM_SHIPTO_ONLINE_EMAIL` parameter for Klarna
- Don't use `ORDERSHIPCOST` parameter for Klarna
- Don't use `CUID` parameter for Klarna
- Changed the format of customer date of birth

### Fixed
- Fixed error message on cancellation from FlexCheckout frame

## [4.0.0] - 2020-09-22
### Added
- Klarna Pay Later
- Klarna Pay Now
- Klarna Bank Transfer
- Klarna Direct Debit
- Klarna Financing
- Implemented `processPaymentRedirectSpecified()`
- Implemented `getSpecifiedRedirectPaymentRequest()`
- Implemented `getHostedCheckoutPaymentRequest()`
- Helper functions for Inline CC form
- Use `CN` for `Alias`
- Use `CODE` const for payment method classes

### Changed
- Make `processPaymentRedirect()` and `processPaymentInline()` public
- Condition of Payment::isTransactionSuccessful()

### Fixed
- Issue with `updateOrderStatus()`
- Don't validate radio field if they aren't mandatory
- Issue with partial refund via WebHook
- Solved: Sometimes the gateway just outputs `NCERROR` with NULL

## [3.0.1] - 2020-07-22
### Changed
- When calling `updateOrderStatus()` method on Connector from \IngenicoClient\IngenicoCoreLibrary, now passing \IngenicoClient\Payment object as second parameter, previously it was a string.
- For Banktransfer/Bancontact payment methods status code “41” now sending “STATUS_PENDING” to Connector

## [3.0.0] - 2020-07-13
### Added
- Support for refund processing with status 81
- Support for capture in progress status

### Changed
- Billing address name is not sent to Ingenico as the Cardholder name

### Fixed
- Issue with payment return error status
- Issue with e-mail template giving an error
- Refund amount rounding


## [2.1.0] - 2020-07-06
### Added
- Country support for Austria

## [2.0.0] - 2020-04-24
### Added
- Support for OpenInvoice
- Special flow for Twint

### Changed
- Alias operation

### Fixed
-  Payment page template for HostedCheckout

## [1.0.4] - 2019-11-08
### Fixed
- DirectLink configuration issue

## [1.0.3] - 2019-09-28
### Fixed
- DirectLink issue

## [1.0.2] - 2019-09-28
### Fixed
- Live mode for DirectLink

## [1.0.1] - 2019-09-27
### Fixed
- Live mode

## [1.0.0] - 2019-07-31
### Added
- Initial version
