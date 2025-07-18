# CHANGELOG.md

## [8.2.2] - 2024-10-15

### Fixed
- Resolve double notification issue.

### Refactor
- Updated `product_name` column in `revo_mobile_slider` and `revo_list_mini_banner` tables.

### Chore
- Updated version plugin to 8.2.2

## [8.2.1] - 2024-10-15

### Chore
- Updated version plugin to 8.2.1

## [8.2.0] - 2024-08-30

### Fixed

- Fix search API
- Fix videos checkout
- Fix status checkout native in home API
- Fix stuck top up webview

## [8.1.1] - 2024-08-08

### Added

- New admin dashboard style
- Integration WPML and Polylang to Slider Banner and Additional Banner

### Changed

- Change minimum PHP version to 8.0

### Fixed

- Fix bugs

## [8.0.5] - 2024-07-08

### Fixed

- Fix Video Service
- Adjust rebuild cache function

## [8.0.4] - 2024-06-24

### Added

- Add Video Shopping page (web frontend)

### Fixed

- Fix Bug

## [8.0.2-3] - 2024-06-10

### Added

- Add custom column on order list

## [8.0.2] - 2024-05-08

### Added

- Video Shopping (New Feature)
- Smart search⁠

### Fixed

- Fix Bug

## [7.5.7] - 2024-04-19

### Added

- Coupon for mobile app only (New Feature)

### Changed

- Social and biometric login flow

### Fixed

- Woongkir name function
- Checkout webview

## [7.5.6] - 2024-04-02

### Added

- Add order attribution when checkout from native checkout feature (optional)

### Fixed

- Fixed apple login
- Fix bugs

## [7.5.2-3] - 2024-03-13

### Fixed

- Checkout Datas
- Fix search product by SKU

## [7.5.2-2] - 2024-03-13

### Added

- New hook for trigger child plugin
- Add ability to extend app settings

### Removed

- Remove game function from base plugin

### Fixed

- Fix Bugs

## [7.5.2] - 2024-03-01

### Added

- New filter hooks `revo_woo_register_submenus`, `revo_woo_register_additional_banner_submenus`

### Fixed

- Coupon parameter for list and apply coupon api
- Override style

### Removed

- Remove api apply coupon v2

## [7.5.0-2] - 2024-02-23

### Fixed

- Fix save post cache
- Improve legacy function api
- Improve function for get products and reformat product result

### Removed

- Dump unapplicable function on `Revo_Woo_Flutter_Mobile_App`

## [7.5.0] - 2024-02-22

### Added

- Recently view products section on home page
- Select2 product search with ajax
- New filter hooks `revo_woo_get_products_args`, `rw_register_submenus`

### Fixed

- Redeem point on checkout native page
- Null check read notif api
- Add `revo_woo` prefix to some internal functions
- Home api cache
- Notif order by woocommerce
- Dump unapplicable function on `Revo_Woo_Flutter_Mobile_App`

### Changed

- Optimize get_products function on `Revo_Woo_Flutter_Mobile_App`
- Multilang flow
- Adjust checkout native
- Adjust plugin dashboard

## [7.3.10] - 2024-01-23

### Added

- Header design on customize page
- New filter hooks `rw_response_product_data`, `rw_format_content_html`

### Fixed

- Adjust and fixed bug on plugin dashboard
- Fixed spacing on product description
- Fixed home api
- Dump unapplicable function

### Changed

- `$` Changed to `jQuery` [jQuery]
- User can manage section best deals on customize page
- Change function name get_logo -> revo_woo_get_logo [functions.php]
- Change function name get_categorys -> revo_woo_get_categories [functions.php]
- Load plugin functions on gateway plugin. Remove it from scooper-autoload.php [functions.php]

## [7.3.9] - 2023-12-23

### Fixed

- Home api with lang

## [7.3.8-2] - 2023-12-16

### Fixed

- Fixed missing popup promotional banner
- List Order Api
- Checkout Webview Flow

## [7.3.8] - 2023-12-06

### Added

- Integration with FOX Currency Switcher Professional for WooCommerce plugin
- Affiliate Referral link product

### Changed

- Change method visibility from protected to public - [abstract] Revo_Woo_Integration

## [7.3.3] - 2023-10-23

- hook order & order status changed function
- Remove RevoPOS Order notification
- Deprecated Sync Cart function
- Fix Wordpress with Composer issue

## [7.3.2-2] - 2023-10-09

### Fixed

- JS and CSS on dashboard page
- Fix limitation of product on home api
- Single Banner missing from home api response

### Changed

- Flow for intregration plugin

## [7.3.2] - 2023-09-21

### Fixed

- PHP 8.1 Warning
- Dashboard syling

### Changed

- rename class Revo_Woo_Base_Vendor to Revo_Woo_Base_Integration
- change name directory from `vendor` to `integration`

## [7.2.3] - 2023-07-20

### Fixed

- Social Login Token
- Fix bugs

## [7.2.1] - 2023-07-04

### Changed

- Update web push notification
- Migration Cloud Messaging API (Legacy) -> Firebase Cloud Messaging API (HTTP v1)

### Fixed

- Fix Bugs

## [7.1.0] - 2023-06-22

### Changed

- Implementation cache on home api

### Fixed

- Fix Bug

## [7.0.65] - 2023-06-10

### Added

- Integration with photoreview premium plugin

## [7.0.64] - 2023-05-29

### Added

- Add condition for popup biometric feature -> apps setting menu

### Changed

- Optimize general settings api
- Optimize settings page

### Fixed

- Fix plugin dashboard styling
- Fix overriden css style

## [7.0.62] - 2023-05-24

### Added

- Customize home page
- Integration with themehigh-multiple-addresses plugin (multiple address) By ThemeHigh

### Changed

- Optimize home api & dashboard plugin

### Fixed

- Fixing bug checkout-api class

## [7.0.5] - 2023-05-05

### Fixed

- Fix Envato License

## [7.0.3] - 2023-04-01

### Changed

- Font awesome v4 -> v5.15
- Restructure folder plugin
- Icon & Logo on dashboard page

### Fixed

- PHP notice
- Security wp plugin

## [6.5.8] - 2023-03-01

### Changed

- Optimize Home Api

### Fixed

- Fix required input on main mini banner page
- Fix check authentication

## [6.5.7] - 2023-02-15

### Changed

- Major update with new features and bug fixes
- Minimum version of PHP 7.4

## [5.0.0]

### Added

- Chat integration between revowoo x revopos
