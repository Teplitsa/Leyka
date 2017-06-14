=== Leyka ===
Contributors: Ahaenor, foralien, denis.cherniatev
Author URI: http://te-st.ru
Plugin URI: http://leyka.te-st.ru
Tags: e-donate, donates, donations, charity, wp-donates, crowdfunding, leyka, fundraising, recurring, payment, charity, cloudpayments, webmoney, robokassa, rbk, rbkmoney, visa, mastercard, yandexmoney, chronopay, rbkmoney, sms, яндекс.касса,яндекс.деньги, миксплат, paypal
Requires at least: 3.6.1
Tested up to: 4.8
Stable tag: 2.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Leyka is a plugin for crowdfunding and donations collection via WordPress website.

== Description ==

Supported payment methods includes bank cards Visa and MasterCard through **Cloudpayments, PayPal, Chronopay and RBK Money** systems, mobile and SMS payments via MIXPLAT, also **WebMoney, ROBOKASSA and Yandex.Money** are supported. You can also use a traditional bank payment orders (quittances).

This plugin developed and supported by [Teplitsa of social technologies](//te-st.ru/).

The plugin's task is to ease and improve integrations of donations collecting function on websites of NGOs and any social oriented projects.

* Plugin is very easy to install, and it requires only a minimum of settings.
* You can start to collect donations right after plugin setup.
* Many important settings are setted automatically.

The plugin is designed for any website that wants to collect money online - NGOs, informal unions, individuals.

**Official website:** [leyka.te-st.ru](//leyka.te-st.ru/)

**Warning:** you will need to sign a contract with some payment systems, like Yandex.Money or RBK, to collect donations through them.

**Core features**

* Wide range of payment systems and options
* Suitable for private persons and NGOs
* Automatic e-mails to supporters
* Multiple language support
* Support for multiple currencies
* Campaign templates and visualization of the progress bars
* Widgets and shortcodes for WP
* Legally correct templates and oferta text
* Ability to embed campaigns via iframe
* Payment history and statistics on the website
* Integration with Google Analytics out of the box
* Partially complies with accessibility standards WCAG 2.0

The plugin manual is avaliable at [official website](//leyka.te-st.ru/instruction/). Intallation and usage are illustrated with screencasts:

* [basic features](//leyka.te-st.ru/docs/videourok-kak-ustanovit-i-nastroit-plagin-lejka/)
* [extended features](//leyka.te-st.ru/docs/video-urok-ispolzovanie-novyh-vozmozhnostej-lejki/)

PHP at least 5.3 is required for plugin to work correctly.

**Help the project**

We will be very grateful if you will help us to make Leyka better.

* You can add a bugreport or a feature request on [GitHub](https://github.com/Teplitsa/Leyka/issues).
* Send us your pull request to share a code impovement.
* You can make a new plugin translation for your language or send us a fixes for an existing translation, if needed.

If you have a questions for the plugin work in any aspect, please address our support service on [GitHub](https://github.com/Teplitsa/Leyka/issues/).

== Installation ==

The plugin manual is avaliable at [official website](//leyka.te-st.ru/instruction/). Intallation and usage are illustrated with screencasts:

* [basic features](//leyka.te-st.ru/docs/videourok-kak-ustanovit-i-nastroit-plagin-lejka/),
* [extended features](//leyka.te-st.ru/docs/video-urok-ispolzovanie-novyh-vozmozhnostej-lejki/).

PHP at least 5.3 is required.


== Frequently Asked Questions ==

[FAQ section](//leyka.te-st.ru/faq/) can be found at the plugin website. Also you can address our development and support team by [creating a project issue n Github](//github.com/Teplitsa/Leyka/issues/new/).

== Screenshots ==

1.  "Campaign card" widget example
2.  "Donations list" widget example
3.  Campaign target indicator example
4.  The plugin start page (a console)
5.  Incoming donations list page
6.  Payment systems settings
7.  Email notifications settings
8.  Frontend donation form example
9.  Bank payment order example
10. Google Analytics events

== Changelog ==
= 2.3 =
 * New: Revo template added.
 * New: inner API architecture evolved. Template controllers class tree added.
 * Tweak: localization files detached from the plugin bundle and provided via WP language packs.
 * Tweak: many wordings improved.
 * Fix: lots of small fixes.

= 2.2.14 =
 * New: plugin options and data clearup function added.
 * New: fields with donation form shortcodes added in campaigns admin area.
 * Fix: problem with inactive donation amount on Radio template solved.
 * Fix: now shortcodes in Terms of Service text work correctly.
 * Fix: support system contacts update.
 * Fix: Terms of Service text markup improved.

= 2.2.13 =
 * New: "Neo" donation form template added.
 * Fix: markup for the Radios and Toggles template fixed.

= 2.2.12.2 =
 * Fix: now single bank card payments via PayPal in real mode work correctly.
 * Fix: bug with some obsolete and untranslated language lines fixed.

= 2.2.12.1 =
 * Fix: missing adminbar now returned.
 * Fix: now donation forms can include custom select fields, and amount fields will not be disabled.

= 2.2.12 =
 * New: alhpa-version of PayPal gateway support added (Express Checkout, single payments).
 * New: now gateway redirection page can be customized by client code.
 * New: now Chronopay payments can pass uniqueness checks.
 * New: Yandex Smart payments added.
 * New: now Yandex.Kassa gateway can use shopPassword parameter to enforce payments security.
 * Fix: localization fixes.
 * Fix: small fixes.

= 2.2.11 =
 * New: MIXPLAT SMS payments support added.
 * New: now CloudPayments outputs errors to a donor in more frienly way.
 * Fix: localization fixes.
 * Fix: small fixes.

= 2.2.10 =
 * New: MIXPLAT support added. Mobile payments and dedicated text box supported.
 * Tweak: now plugin uses EasyModal to work with modal popups.
 * Fix: russian naming and localization fixes.
 * Fix: lots of small fixes.

= 2.2.9.3 =
 * New: server-side data validation is enforced.
 * Fix: flexible and mixed sum field behavior is fixed.
 * Fix: fix of the session_start() bug on PHP 7.
 * Fix: fix for the Chronopay recurring.
 * Fix: now CloudPayments donations are not doubling on "over-submit".

= 2.2.9 =
 * New: donations' status names changed. Now there are comment about each status near it.
 * New: Mixed donation amount field type added.
 * New: Yandex.money has 3 additional internet banking PMs now: Sberbank online, Apfa click, Promsvyazbank.
 * New: now there are archive pages for donations, optionally filtered by campaign.
 * New: now donor name field won't take an email as a value (to protect donors' personal data from being accidentally displayed).
 * New: active recurring engine added. It's supported by Yandex.money gateway (bank card PM).
 * New: lots of new core hooks.
 * New: now donation forms submits through Yandex.money include hidden field to indicate Leyka as a source.
 * Tweak: Radio template refactored. Server loading from it's use strongly optimized.*
 * Fix: important Polylang compatibility fix.
 * Fix: lots of small core and gateways fixes.

= 2.2.8 =
 * New: now correctional donation may be added from the plugin's main menu.
 * Tweak: options engine is refactored. Large queries number improvement.
 * Tweak: added new caching system for campaigns' total collected amounts.
 * Tweak: added a service CC to the feedback form processing.
 * Tweak: the donations export engine is refactored to work more sustainably with large amounts of data. The dependency on Excel Writer is removed.
 * Fix: plugin frontend's compatibility with some another visual frameworks improved.
 * Fix: now pressing enter key while editing PM's custom label won't submit the whole Payment settings form.
 * Fix: serious bug when correctional donations led to the incorrect total funded amounts' calculations is fixed.
 * Fix: the behavior of donation-campaign link when donation form is inserted somewhere via shortcode is fixed.
 * Fix: donations export function is returned to it's rightful place.
 * Fix: donations dataTable bug on campaign editing page is fixed.
 * Fix: campaign views counting is improved to be more accurate and logical.
 * Fix: small code improvements and fixes. Oh come on, you knew that we won't miss this line.

= 2.2.7.2 =
 * New: full support for WP 4.3 is achieved.
 * Fix: storing of total funded amount for each campaign is greatly optimized.
 * Fix: Donations export algorythm is optimised to require much less of the memory to work.

= 2.2.7.1 =
 * Fix: fixed the bug with gateways & PMs list in Payment Settings page.
 * Fix: Chronopay test mode option is removed now. By the words of Chronopay support, test mode using is very rare.

= 2.2.7 =
 * New: added the new CloudPayments gateway. Single and recurring bank card payments supported.
 * New: added a simple campaign statistics function.
 * Fix: more sweet refactioring for the gateways API. Now Chronopay (and all other gateways) are compatible with gateway-specific data fields.
 * Fix: payment settings page UI improved. Known bugs fixed.
 * Fix: campaign selection field in the Donations widget is a dropdown list now. No more pain with copy-pasting IDs.
 * Fix: small fixes... we'll never tired to polish this child of ours.

= 2.2.6 =
 * New: payment settings page has a new UI.
 * New: now PMs on the donation forms can be reordered.
 * New: now text for the donation submits can be changed.
 * New: gateways API is slightly refactored.
 * New: now donation ID adds to the bank order payment title.
 * New: small UI fixes for the bank order.
 * Fix: small, but important fixes in Chronopay and Yandex.Money. Other gateways also has their share of a refactoring.
 * Fix: as always, small fixes.

= 2.2.5 =
 * New: "First steps" metabox is added to the plugin desktop.
 * New: first level of improvement of options validation system.
 * New: technical export function is added.
 * New: now embed campaign card also can be acquired from donation forms.
 * New: added a "leyka_form_pm_order" filter to allow PM list reordering in donation forms.
 * Fix: excerpt metabox is renamed to the "annotation" for the campaigns.
 * Fix: embed campaign cards.
 * Fix: donations export problem for PHP 5.3 is fixed.
 * Fix: Chronopay callbacks fixed.
 * Fix: many small fixes.

= 2.2.4 =
 * New: Yandex.Money Gateway support widened.
 * New: entered plugin's specific user capabilities and roles system.
 * New: added a general user feedback page in the plugin admin menu.
 * New: Leyka Desktop page is slightly improved.
 * New: new plugin hooks (to add new items in the plugin's admin menu, to reorder them, etc.).
 * Fix: presumably, fixed the bug that endlessly doubled plugin's grateful and sorrowful pages.
 * Fix: many fixes in Robokassa, Yandex.money and another gateways.
 * Fix: small core refactoring and fixes.

= 2.2.3 =
 * New: Robokassa gateway support.
 * New: the currencies rates manual editing and auto-refresh option.
 * New: embed campaigns feature (campaign cards).
 * New: Google Analytics events binded to the donation workflow, to better track down donors activity via GA.
 * Fix: small fixes.

= 2.2.2 =
 * Fix: notice on Posts quick edit.
 * Fix: warnings when wp-admin is accessed by user with Subscriber compatibility.
 * Fix: small fixes in code and markup.

= 2.2.1 =
 * New: added RBK Money gateway support.
 * New: added WebMoney support in Yandex.money gateway.
 * New: added new Leyka_Payment_Method class attribute. Now PM labels on frontend and backend can be different.
 * New: added shortcode for Terms Of Service text output.
 * Fix: small fixes.

= 2.2 =
 * New: campaigns now has optional target sum parameters.
 * New: campaigns and donations list tables now has lots of new filters and columns. They mostly are relevant to a new target function.
 * New: now site administrator can manually add a "correctional" donations. They can have positive or negative amount.
 * New: donations now has explicit "date" field, so donation date is separated from it's status history.
 * New: additions to a campaign editing screen. For ex., donations history metabox added.
 * New: Plugin options structure were a little refactored. New "view" option tab added.
 * New: added several shortcodes and widgets (target reaching level, campaigns list, donations list, etc.).
 * Fix: some small bugs, known from previous release and noted by plugin users.
 * Fix: bug with infinite creation of thank-you- and fail-pages, presumably, fixed.

= 2.1.4 =
 * Fix: compatibility with Polylang plugin.
 * Fix: behavior of turning-off when plugin is activated on PHP 5.2 and less.
 * Fix: gateways and payment methods API behavior.
 * Fix: minor bugs.
 * New: new hooks to allow better code customization.
 * New: Chronopay gateway's recurrent donations.
 * New: donations history export in MS Excel format.
 * New: donation form redirect timing now is longer when debug mode is on.

= 2.1.3 =
 * Fix: notices when plugin is activating on new installation or update (PHP strict standards based included).
 * Fix: minor bugs.
 * New: stable and correct turn-off behavior when plugin is activated on PHP 5.2 and less.
 * New: compatibility with Polylang plugin.
 * New: improved code security.
 * New: lots of new hooks to allow better code customization.

= 2.1.2 =
* Fix: Fixed warning message on new installas

= 2.1.1 =
* Fix: Permalink problem after activation on some installs

= 2.1 =
* New: Added support for Static text as a payment method.
* New: Added support for Yandex.money for personal accounts. It presents 2 new payment methods: Yandex.money or Bank card payment to the personal account.
* New: Minor improvements in plugin's inner API.
* Fix: The options caching system completely removed to improve admin area usability.
* Fix: Various bugfixes in plugin options handling

= 2.0.1 =
* New: Added pot file for translation
* Fix: Bugfixes in core and gateways
* Tweak: UI improvements in templates of donation form

= 2.0 =
* New: **WARNING: no further compatibility with previous versions.**
* New: New major release. Code refactored and data structures changed.
* New: Removed dependency from EasyDigitalDownload.
* New: Payment gateways are now embedded in Leyka.
* New: New design of donation widget.
* New: Gateway/payment method API.
* New: Min PHP ver: 5.3.

= 1.2.1 =
* New: Updated Leyka to support EDD 1.7.2
* Fix: Donation panel was not working with some themes
* Tweak: Email settings section in admin panel was slightly updated

= 1.2 =
* New: Improved design of donation panel
* New: Updated Leyka to support EDD 1.7.1
* Fix: Short code for total payment counter is now displayed
* Tweak: Localization improvements

= 1.1 =
* New: Design of donation panel
* New: Donation logging (data is used both for counter and statitics)
* New: Leyka now controls when EDD can upgrade
* New: Updated Leyka to support EDD 1.5.2
* New: Counter shows sum of approved donations and distinquish them by Payment Gateways
* New: Wizard for legal entity and individuals
* New: Standard contract offer for making donations
* Fix: Fixed Checkout page donation mode
* Fix: Made clear for users that no personal data is collected
* Fix: Wrong link for RBK Money context description
* Fix: Unable to delete user comments from trash in admin panel
* Fix: Bulk activation/deactivation of user comments in admin panel
* Fix: Localization issues
* Tweak: Option Accept Donation is now a link without additional static text
* Tweak: Code Refactoring

= 1.0 =
* First official release!
