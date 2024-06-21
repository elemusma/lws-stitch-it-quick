=== WooCommerce Product Options ===
Contributors: barn2media
Tags: woocommerce, product, options, addons
Requires at least: 6.0
Tested up to: 6.5.2
Requires PHP: 7.4
Stable tag: 1.6.9
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Add extra options to your WooCommerce products, with over 14 option types, optional fees for each option, min/max quantities, and conditional logic.

== Description ==

Add extra options to your WooCommerce products, with over 14 option types, optional fees for each option, min/max quantities, and conditional logic.

== Installation ==

1. Go to Plugins -> Add New -> Upload and select the plugin ZIP file (see link in Purchase Confirmation Email).
2. Activate the plugin.
3. Follow the setup wizard.

== Frequently Asked Questions ==

Please refer to [our support page](https://barn2.com/our-wordpress-plugins/woocommerce-product-options-documentation-support/).

== Changelog ==

= 1.6.9 =
Release date 16 April 2024

 * New: Added compatibility with WooCommerce Discount Manager
 * Fix: Text and Number fields not being output in cart when their value is falsey
 * Fix: Missing sign function for price formulas on the server side
 * Fix: Dropdown options are duplicated when displayed inside a WooCommerce Quick View Pro modal dialog
 * Fix: Date Picker incorrectly disabling "Today"
 * Fix: Products options not being added to cart when the option is displayed as a dropdown
 * Fix: Removed PHP warnings from Date Picker when adding to cart
 * Tweak: Increased the number of products in product-selection component
 * Tweak: Updated back-end app component to latest React updates
 * Tweak: Fixed Visual Editor auto-focus behavior
 * Tweak: Improved price display of Product option type
 * Tweak: Improved and optimized Date Picker localization
 * Tweak: Changed required attribute of Products option type when displayed as a list of products
 * Tweak: Improved behavior of remove manual product choice
 * Tweak: Extended radio-like behavior to Products option type
 * Tweak: Fixed extra margin in cart message
 * Tweak: Other minor adjustments

= 1.6.8 =
Release date 20 February 2024

 * Fix: File upload options not honoring conditional logic under certain conditions
 * Fix: Time formatted incorrectly in Date options
 * Fix: Dynamic selection of Products option not honoring certain sorting criteria
 * Fix: Visibility display in list of option groups is incorrect
 * Fix: Field names in formulas are case sensitive
 * Fix: Products option "Required" validation not working when "Display choices as" is "Products"
 * Fix: Conditional logic doesn't support Products option type
 * Fix: Wholesale price is not saved in the option
 * Fix: Character validation is not accurate when using unicode characters
 * Fix: A digit in the Number option causes Formula not to work
 * Fix: Date option not working inside modal of WooCommerce Restaurant Ordering
 * Fix: Integration issues with WooCommerce Product Table
 * Tweak: Make the delete button always visible in the choice/conditional logic repeaters
 * Tweak: Show HTML and shortcodes in visual editor field
 * Tweak: Other minor adjustments to styling and usability
 * Dev: Tested up to WordPress 6.4.3 and WooCommerce 8.6.0

= 1.6.7 =
Release date 25 January 2024

 * New: Added conditional logic to price formulas
 * Fix: Conditional logic not working under certain conditions
 * Fix: Required setting and quantity limits not working under certain conditions
 * Fix: It is not possible to remove all the options from a group
 * Fix: When nothing is selected in the inclusion/exclusion lists, the option group is not applied to any product
 * Fix: Visibility column is incorrectly updated when reordering option groups
 * Fix: Group visibility doesn't take into account parent categories
 * Fix: Advanced settings toggle not saving the correct state
 * Fix: Disabled dates not being set correctly when comma-separated list also contains spaces
 * Fix: Conditionally hidden text fields being incorrectly validated
 * Fix: Price inputs parsing numbers incorrectly with non-standard thousand separators
 * Dev: Tested up to WordPress 6.4.2 and WooCommerce 8.5.1

= 1.6.6 =
Release date 13 December 2023

 * Fix: Fatal error is triggered if the image used by an image button is deleted from the Media library
 * Fix: Border style for selected option is not showing in Firefox
 * Fix: Prices and labels of image buttons are displayed incorrectly in WooCommerce Restaurant Ordering
 * Fix: Min/max limits are not validated correctly when HTML code is manipulated in the browser
 * Fix: Currently opened option in editor copies its settings to another option when reordering
 * Fix: Error being triggered in Javascript when thousand separator is empty
 * Dev: Updated internal libraries
 * Dev: Tested up to WordPress 6.4.1 and WooCommerce 8.3.1

= 1.6.5 =
Release date 20 October 2023

 * Fix: Fatal error being triggered upon activation
 * Fix: Product option type not working as expected under certain conditions
 * Fix: Product variations cannot be selected in Products type if variable product has only one attribute
 * Fix: In the Products type, once a variation is selected, then removed, it is not possible to select it again
 * Fix: Color swatches not honoring the "Display label" setting
 * Fix: Quantity limits not working for Products type
 * Tweak: Other minor adjustments
 * Dev: Added promo banner to settings page
 * Dev: Added filter hooks to alter the value of each option setting
 * Dev: Added action hooks firing before and after each field
 * Dev: Tested up to WordPress 6.3.2 and WooCommerce 8.2.1

= 1.6.4 =
Release date 10 October 2023

 * New: Added new setting for the position of image buttons labels
 * New: Added a new option to customize the size of image buttons
 * Tweak: Optimized responsiveness of image buttons
 * Tweak: Improved accessibility and color contrast of image buttons
 * Tweak: Improved interaction between button images and product image gallery
 * Fix: "Default value" and "Number limits" not accepting decimal numbers
 * Fix: Other minor details
 * Dev: Tested up to WordPress 6.3.1 and WooCommerce 8.1.1

= 1.6.3 =
Release date 29 August 2023

 * Fix: Product price in cart includes hidden options
 * Fix: Product price in DIVIcart modules does not include options
 * Fix: Radio buttons malfunction when used for product options

= 1.6.2 =
Release date 17 August 2023

 * Fix: Price is not correct when the decimal separator is not a dot.

= 1.6.1 =
Release date 16 August 2023

 * Fix: Dropdown options malfunction

= 1.6.0 =
Release date 9 August 2023

 * Dev: Enabled HPOS compatibility
 * Dev: Updated internal libraries
 * Dev: Tested up to WP 6.3 and WooCommerce 8.0

<!--more-->

= 1.5.5 =
Release date 2 August 2023

 * New: Price formulas are now transliterated internally to work in the major non-latin languages
 * Fix: WooCommerce Restaurant Ordering showing wrong item prices
 * Fix: Conditionally hidden number options being added to total

= 1.5.4 =
Release date 27 July 2023

 * Fix: Date pickers not being initialized inside product tables
 * Fix: PHP warning being displayed with Products option type under certain circumstances
 * Fix: Wrong product price being displayed when currency symbol includes the same character used for the decimal separator
 * Fix: Conditional logic not working with number and comparison set to "greater than" or "less than"
 * Fix: Integration with WooCommerce Product Table not working in every configuration of the product table
 * Tweak: Minor typographic adjustments in Flatsome

= 1.5.3 =
Release date 20 July 2023

 * Fix: Options do not work properly in WooCommerce Product Table when AJAX loading is active
 * Fix: Minimum quantity of zero for number fields gets disregarded
 * Dev: Tested up to WooCommerce 7.9.0

= 1.5.2 =
Release date 12 July 2023

 * Fix: Option total does not reflect symbol position setting
 * Fix: Product fields does not output image buttons under certain conditions
 * Fix: Checkbox validation fails when field is required
 * Tweak: Improved product image retrieval in the Products field

= 1.5.1 =
Release date 7 July 2023

 * Fix: Adjusted final version of Product option type
 * Tweak: Additional improvements to class and utility methods
 * Tweak: Other minor adjustments to the user interface

= 1.5.0 =
Release date 6 July 2023

 * New: Added new Products option type
 * Fix: An error prevents WP-CLI from running
 * Fix: Min and max values trigger validation errors
 * Fix: When displaying multiple product tables on a page, dropdown options are duplicated
 * Fix: Variation forms in a product table lead to incorrect total calculations
 * Fix: Totals do not follow the default price format
 * Fix: Min and max limits are being validated even option is empty and not required
 * Fix: Conditional logic rules are not duplicated when duplicating an option group
 * Dev: Tested up to WordPress 6.2.2 and WooCommerce 7.8.2

= 1.4.2 =
Release date 22 May 2023

 * Fix: PHP warning for an undefined array is being triggered by image buttons
 * Fix: Non unique IDs are used for the choices of several multiselect options
 * Fix: Error in the javascript console when clicking on an image button of a product with no thumbnail
 * Fix: The Setup Wizard is launched every time the plugin is activated
 * Fix: Formula is mistakenly reported as invalid when an option has 2 or more spaces in its name
 * Fix: Some strings used by the scripts are not translatable
 * Tweak: Improved removal of unused uploaded files and enclosing folders
 * Tweak: Improved spacing for dropdown options
 * Tweak: The progress bar of uploaded files now becomes green upon completion
 * Dev: Improved compatibility with PHP 8.1
 * Dev: Tested up to WooCommerce 7.7

= 1.4.1 =
Release date 26 April 2023

* Fix: Missing strings added to POT translation template file
* Tweak: All the strings of the Dropzone UI can now be translated
* Dev: Added hook to filter the Dropzone markup template for file uploads
* Dev: Added hook to filter whether image previews for file uploads or automatically generated or not
* Dev: Tested up to WordPress 6.2 and WooCommerce 7.6

= 1.4 =
Release date 17 April 2023

* New: Groups, options and choices can now be duplicated to speed up the configuration process
* Fix: Editing a group leads to a blank editor in the back end
* Fix: Dropdown placeholders cannot be translated

= 1.3.1 =
Release date 27 March 2023

* Fix: Some strings cannot be translated.
* Fix: Total does not update when options are initially hidden in a child row of WooCommerce Product Table

= 1.3 =
Release date 24 February 2023

* New: Date option.
* New: Add images using the Visual editor option.
* New: The images used in the Image buttons option can be displayed in the product gallery.
* New: Add negative flat fees or quantity based fees.
* Tweak: The totals container will only display if the selected options affect the total price.
* Fix: The 'Any' condition in the conditional logic settings was not working correctly for checkbox like options.
* Fix: Single product layout issue with the Avada theme.
* Dev: Updated Barn2 libraries and dependencies.

= 1.2.5 =
Release date 21 February 2023

* Fix: Hidden options that had user input in nested conditional logic structures could be passed through to the cart.
* Dev: Tested up to WooCommerce 7.4.0.
* Dev: Updated Barn2 libraries and dependencies.

= 1.2.4 =
Release date 16 February 2023

* Fix: Price formula would not correctly account for zero values.
* Fix: Cart item data was being passed for unselected values in the WooCommerce Product Table multi-cart integration.

= 1.2.3 =
Release date 8 February 2023

* Fix: WooCommerce Restauarant Ordering modal button would not show price decimals.
* Dev: Updated Barn2 libraries and dependencies.

= 1.2.2 =
Release date 27 January 2023

* Fix: WooCommerce Product Tables with multi add-to-cart enabled would not work on single product pages.
* Fix: Price formula field would cause a fatal error on sites with PHP below 8.0.
* Dev: Updated Barn2 libraries and dependencies.

= 1.2.1 =
Release date 17 January 2023

* Tweak: Added srcset to image buttons.
* Fix: Price display suffix would disappear on pageload when product price is excluded in the Price formula option.
* Dev: Tested up to WooCommerce 7.3.0.

= 1.2 =
Release date 11 January 2023

* New: Price formula option type for handling measurement or other calculation based products.
* New: Number option type.
* New: Set specific wholesale prices on option choices for your WooCommerce Wholesale Pro roles.
* Fix: Clicking on the color swatches button would not close the color picker.
* Fix: Dragging the color picker setting would drag the choices row.
* Dev: Updated Barn2 libraries and dependencies.
* Dev: Tested up to WooCommerce 7.2.3.

= 1.1 =
Release date 2 December 2022

* New: The main product price automatically updates to include the selected options.
* New: Compatibility with the WooCommerce 'Order Again' functionality.
* New: Compatibility with WooCommerce Subscriptions.
* New: Compatibility with Aelia Currency Switcher and WPML WooCommerce Multilingual.
* Tweak: WooCommerce Product Table products are automatically selected after adding an option in multi-cart mode.
* Tweak: Updated the design of the allowed file types dropdown.
* Fix: The total price display on single product pages was inaccurate for percentage increases/decreases and quantity changes.
* Fix: Only the first 10 saved products or categories were displayed under visibility in wp-admin.
* Fix: WooCommerce Bulk Variations integration could produce an add to cart error in combination with a non-required file upload option.
* Fix: Removed unneccesary arguments for the file upload REST endpoint.
* Fix: The checkbox for displaying the group or option name would not correctly reflect the saved value.
* Dev: Updated Barn2 libraries and dependencies.
* Dev: Tested up to WordPress 6.1.1 and WooCommerce 7.1.0.

= 1.0 =
Release date 28 September 2022

 * New: Initial release.