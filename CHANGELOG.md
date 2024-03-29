# Changelog
## [5.0.1] - 2021-08-05
### Changed
- Remove the conditional freeze of the iFrame
- Don't empty cart on redirect for payment
- Oney updates

## [5.0.0] - 2021-06-12
### Added
- PrestaShop v1.7.7 support
- UI to manage saved cards
- Added Carte Bancaire method
- Added AirPlus payment method

### Changed
- PSR-4 code improvements
- Changed color of order statuses in the backoffice for PS v1.7.7
- Updated translations
- Fixed image uploading for flex methods
- Excluded some payment methods for Generic method
- Fix klarna parameters
- Substitute street number from address
- Branding fixes
- Disable refunds for Intersolve
- Optimize minified js files

## [4.0.0] - 2021-05-18
### Added
- Implemented Blank payment methods
- Implement Oney payment method
- Klarna: use title and gender fields
- Add Generic Ingenico payment method
- Implemented Order::isVirtual()
- Hide CC iframe if ToS is unchecked

### Changed
- Updated translations
- Move images to /views/img/ directory
- Klarna api updates

### Fixed
- Improved logic of hiding/showing iframe and order submission button
- Remove category title on Klarna intermediate page
- Fixed DOB field
- Bancontact fixes
- Use html code in tpl instead of php files
- Escape the chars in tpl files

## [3.0.0] - 2020-11-03

### Added
- Implement inline methods on the checkout page
- Implemented new Klarna methods
- Implemented "Send an e-mail when order has been paid or reverted from cancelled state." settings
- Implemented "Send an e-mail when refund has been failed."
- Send the order confirmation after an order payment
- Implemented "Partial Refund" order status
- Add disclaimer about PCI compliant
- Add refund/capture "processing" state

### Changed
- Database schema updates (aliases, payments etc)
- Updated translations
- Improved WhiteLabels feature
- Show KLARNA in the payment methods list based on customers location

### Fixed
- Fixed the country configuration of Afterpay payment method
- Errors when cancellation inside inline credit card payment form
- OpenInvoice/Klarna/Afterpay design fixes

## [2.1.1] - 2020-07-23

### Changed
- Add Austria support email
- Improved logger
- Update api urls of WhiteLabels

## [2.0.0] - 2020-05-04
- Various changes and improvements