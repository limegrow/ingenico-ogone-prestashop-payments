# Changelog
## [3.1.0] - 2021-05-18
### Added
- Implemented Oney payment method
- Add `CH_AUTHENTICATION_INFO` parameter

## [3.0.1] - 2021-03-01
### Added
- Add `Device` parameter for Bancontact

### Changed
- Update `owneraddress` length (35)
- Change size validation of `ownertown` field (40)

## [3.0.0] - 2020-11-03
- Use mb_strlen for length checking

## [2.0.2] - 2020-10-26
### Changed
- Remove length limitions of address fields
- Update OwnerAddress field limitions
- Remove Ogone Uris validation

## [2.0.1] - 2020-09-22
### Fixed
- Issue `setOperation`: operation can be null
