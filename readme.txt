=== Leyka ===
Contributors: Ahaenor, teplosup, oleinikv89, foralien, denis.cherniatev, burdianov
Author URI: http://te-st.org
Plugin URI: http://leyka.org
Tags: лейка, crowdfunding, fundraising, donations, recurring donations, charity, leyka, recurring, cloudpayments, webmoney, robokassa, rbk, rbkmoney, rbk-money, yoomoney, chronopay, sms, yookassa, миксплат, mixplat, paypal, paymaster, qiwi, киви, stripe, страйп, gds, google data studio
Requires at least: 3.6.1
Requires PHP: 7.2.5
Tested up to: 6.6
Stable tag: 3.31.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Leyka is a plugin for crowdfunding and donations collection via WordPress website.

== Description ==

Supported payment methods include Visa and MasterCard bank cards payments via **Cloudpayments**, **Yandex.Kassa**, **PayPal**, **Chronopay**, **QIWI Kassa**, **ROBOKASSA** and **RBK Money** systems, mobile and SMS payments via **MIXPLAT**, also **WebMoney**. You can also use a **traditional bank payment orders**.

This plugin developed and supported by [Teplitsa of social technologies](https://te-st.ort/).

The plugin’s task is to ease and improve integrations of donations collecting function on websites of NGOs and any social oriented projects.

- Plugin is very easy to install, and it requires only a minimum of settings.
- You can start to collect donations right after plugin setup.
- Many important settings are setted automatically.

The plugin is designed for any website that wants to collect money online – NGOs, informal unions, individuals.

**Official website:** [leyka.org](https://leyka.org/)

**Warning:** you will need to sign a contract with some payment systems, like Yandex.Money or RBK, to collect donations through them.

**Core features**

- Wide range of payment systems and options
- Suitable for private persons and NGOs
- Automatic e-mails to supporters
- User accounts that help donors to manage recurring donations
- Payment history and statistics on the website
- Integration with Google Analytics out of the box
- Campaign templates and visualization of the progress bars
- Template color customization
- Widgets and shortcodes for WP
- Legally correct templates for personal data usage agreement and oferta text
- Multiple language support
- Partially complies with accessibility standards WCAG 2.0

The plugin manual is avaliable at [official website](https://leyka.org/docs/what-is-leyka/). Intallation and usage are illustrated with [screencasts course](https://te-st.org/2020/04/07/leyka-online-course/). 

PHP at least 7.2.5 is required for plugin to work correctly.

**Help the project**

We will be very grateful if you will help us to make Leyka better.

- You can add a bugreport or a feature request on [GitHub](https://github.com/Teplitsa/Leyka/issues).
- Send us your pull request to share a code impovement.
- You can make a new plugin translation for your language or send us a fixes for an existing translation, if needed.

If you have a questions for the plugin work in any aspect, please address our support service on [GitHub](https://github.com/Teplitsa/Leyka/issues/).

== Installation ==

The plugin manual is avaliable at [official website](https://leyka.org/docs/what-is-leyka/). Intallation and usage are illustrated with screencasts:

* [basic features](//leyka.org/docs/videourok-kak-ustanovit-i-nastroit-plagin-lejka/),
* [extended features](//leyka.org/docs/video-urok-ispolzovanie-novyh-vozmozhnostej-lejki/).

PHP version required: 7.2.5+


== Frequently Asked Questions ==

[FAQ section](https://leyka.org/faq/) can be found at the plugin website. Also, you can address our development and support team by [creating a project issue n Github](//github.com/Teplitsa/Leyka/issues/new/).

For technical support questions, please, use the [plugin support email](maillto:help@te-st.org) or the official [Telegram chat](https://t.me/leykadev).

== Screenshots ==

1. "Campaign card" widget example
2. Donations form example
3. Donors list page example
3. Recurring subscriptions list page example
5. The plugin start page (a console)

== Changelog ==

= 3.31.7 =
* Fix: Security Vulnerability
* Fix: Improve sanitization
* Update: MIXPLAT Gateway


= 3.31.6 =
* Fix: Get donnors shortcode
* Fix: Inputmask attr data
* Fix: Error on page donation failure
* Improve: MIXPLAT Gateway
* Improve: Need Help form template
* Improve: Remove post from Return page selection


= 3.31.5 =
* Add: MIXPLAT Wizard
* Improve: Plugin security

= 3.31.3 =
* Add: New payment system Dolyame
* Improve: Escaping

= 3.31.2 =
* Fix: Broken Access Control vulnerability

= 3.31.1 =
* Improve: Payselection code

= 3.31 =
* Add: Support GA4
* Add: New payment system Payselection
* Update: Sber Gateway
* Improve: CSS and sourceMap

= 3.30.8 =
* Improve PHPCS Security
* Update: MIR Pay method added

= 3.30.7 =
* Fix: Recurring subscriptions filtering bug
* Fix: Fields are missing in the create campaign Wizard.
* Update: Unisender donator name to uppercase

= 3.30.6 =
* Fix: Template Star attr error.

= 3.30.5 =
* Security: Variables and options escaped when echo'd.

= 3.30.4 =
* Security: Fix cross-site scripting vulnerability for terms_text.

= 3.30.3 =
* Fix: the important Google Analytics direct connection bug fixed ("Guzzle bug").
* Fix: one recently found vulnerability fixed.
* Fix: now CloudPayments correctly redirects to the success|faulure page after the Tinkoff Pay payment.

= 3.30.2 =
* Fix: one recently found vulnerability fixed.
* Fix: the CP recurring subscriptions import procedure fixed.
* Fix: different fixes.

= 3.30.1 =
* Fix: different fixes.

= 3.30 =
* New: now YooKassa accepts SBP via "smart payment" payment method.
* New: expanded payment descriptions added for Mixplat.
* New: UTM parameters support added for Mixplat.
* New: SBP recurring mode feature added for Mixplat.
* New: split payments between two campaigns feature added for Mixplat.
* New: the switch between redirect and payment widget feature added for Mixplat.
* Fix: the compatibility bug on WP core ver. lesser than 5.5.0 fixed.
* Fix: the bug of duplication of campaign data in nested posts fixed.
* Fix: the bug of Terms of service & PD usage when displayed on the custom page fixed.
* Fix: the "get_page_by_title() function is deprecated" in WP 6.2 bug fixed.
* Fix: the XSS vulnerability from the recent WordFence report fixed.
* Fix: the CSRF vulnerability from the recent WordFence report fixed.
* Fix: the bug of eng. date format used sometime fixed.
* Fix: new mts icon for Mixplat.
* Fix: Qiwi payments fixed - now callbacks are handled correctly.
* Fix: fix for the manual donor's email sending not working.
* Fix: now manual donor email notifications are sent in accordance with donation status (if donation is failed, then error notification is sent: otherwise, success email is sent).
* Fix: the Person default terms of service text fixed.
* Removed: YooKassa Wizard tmp. removed (it's outdated).

= 3.29.2 =
* Fix: Daily Rouble mode is now working correctly (only with main currency).
* Fix: Merchandise + Multicurrency bug fixed.
* Fix: Donor details admin page bug fixed.
* Fix: "Do not display" checkbox for the Need Help template bug fixed.
* Fix: now finished Campaigns' recurring auto-payments change their status correctly.
* Fix: Payment Methods swiper in the cases when non-main currency is selected - fixed.
* Fix: Multi-currency + Campaign total funded amount recalculation fixed.
* Fix: incorrect amount of the [leyka_amount_collected] shortcode in cases of many Donations currencies fixed.
* Fix: small fixes.

= 3.29.1 =
* Fix: YooKassa compatibility fix for WP 6.1.
* Fix: Demirbank notices bug fixed.
* Fix: the custom admin info pages access error bug fixed.
* Fix: small fixes.

= 3.29 =
* New: Muli-currencies system added.
* New: CloudPayments - subscriptions import support feature added.
* Fix: DemirBank gateway support.
* Fix: small fixes.

= 3.28 =
* New: SBP system for Mixplat added.
* New: cryptocurrencies support added.
* New: return page option for Yookassa gateway added.
* Fix: recurrents health module subscriptions status update fixed.
* Fix: donation hooks now work correctly.
* Fix: admin donations list filters fix.
* Fix: small fixes.

= 3.27 =
* New: new Recurring Health engine (v.1b) is added.
* New: now Additional fields placeholders can be used in the Donations notifications emails.
* New: Redis & other object caching systems compatibility mode added for post-based Donations storage.
* Fix: YooMoney for Physical persons Gateway behavior fixed.
* Fix: Liqpay - fixes.
* Fix: "sticky posts" incompatibility bug fixed.
* Fix: the rare str_contains() error fixed.
* Fix: small fixes.

= 3.26.1 =
* New: Campaigns categories feature added.
* Fix: small Donations handling fixes.
* Fix: conflicts with Mihdan plugins are fixed.

= 3.26 =
* New: now Donations errors full info & recommendations to fix them are displayed completely. YooKassa & CloudPayments gateways errors are added like this.
* New: the new parameter value added for the "leyka_donations_list" shortcode. Now Donors' names may be optionally masked.
* New: new filtering option added for Leyka_Donations::get() methods.
* New: small functional additions & changes in the Donations' admin list table.
* New: now Donor's name & additional fields values are passed to CP via payment widget fields.
* Fix: Additional fields values missing in Donation export results is now fixed.
* Fix: the rare bug when init recurring Donations' Donor thanking emails were sent without Donor account link - is fixed.
* Fix: Donor total funded amount count bug on a new Donation fixed.
* Fix: Liqpay callbacks responses fixed.
* Fix: recurring subscription cancelling via link from non-init recurring Donations fixed.
* Fix: rebills date bug fixed.
* Fix: the "Need Help" form template behavior for smaller containers is greatly improved.
* Fix: small fixes.
~ Tweak: Recurring subscriptions - funded rebills number cache added.
~ Tweak: Recurring subscriptions - funded rebills number cache added.
~ Tweak: small optimizations.

= 3.25 =
* New: now Leyka is fully PHP 8.* compatible.
* New: Dashboard admin page is updated.
* New: cosmetic usability additions to the Donor's Account page.
* New: new filter added for Donations archive page slug.
* Fix: the critical Polylang compatibility bug fixed.
* Fix: the case of very long value of Campaign "payment title" field on YooKassa rebills fixed.
* Fix: the Gateways settings page warning on new installations fixed.
* Fix: the important amounts' error on new installations fixed.
* Fix: the Donations' management page filter fixed.
* Fix: the rare case when YooKassa SDK class is included in some other plugin or an active theme fixed.
* Fix: Donation forms fix for flexible amount mode.
* Fix: the potential error on donors' emails sending fixed.
* Fix: Donation form payment methods icons are fixed.
~ Tweak: small optimizations.

= 3.24 =
* New: Donations amounts descriptions function added.
* New: plugin branding form signatures added.
* New: the cronjob setup info added for all active recurring oriented Gateways.
* Fix: Unisender Extension bug fixed.
* Fix: YooKassa payment tryout step won't get stuck now.
* Fix: small fixes.

= 3.23.1 =
* Fix: important fix for donation forms markup break.
* Fix: changed/renewed donor's data handling for the CP gateway.

= 3.23 =
* New: now there's an option to allow turning off all nonce checks on public requests handling.
* New: the Dashboard banner changed.
* New: now Payment settings admin page has Gateways list sorted.
* Fix: now each CP rebill callback handler automatically fixes the inactive subscription bug for its respective CP subscription.
* Fix: now placeholders in the Terms pages content are properly auto-replaced.
* Fix: small fixes.

= 3.22 =
* New: Kyrgyzstan country support & DemiBank gateway added.
* New: Merchandise/Rewards extension v.2 added.
* New: Donations API improved.
* New: new "Subscription Rebills/Donations list" metabox added for recurring subsctiption Donation info pages.
* New: new Campaign setting added - "display Donations form before/after the content on Campaign page".
* New: Campaign settings page UI - new additions/improvements.
* New: Campaign cards blocks for Gutenberg added.
* Fix: important fix for Polylang incompatibility since v.3.21.
* Fix: failure widget displaying irrelevant of template option value fixed.
* Fix: Small CSS fixes.
~ Tweak: Donation export refactored for better compatibility with MacOS Excel, Google Sheets & other platforms/software.
* Removed: Quittance user manual link removed from the gateway settings page.
* Removed: Extension deletion links are removed.

= 3.21 =
* New: now admins' & donors' email notifications about failed donations may be turned off.
* New: the campaign total funded amount recalculation feature returned.
* New: Unisender extension improvements.
* Fix: Chronopay gateway donations error fixed.
* Fix: double notifications bug fixed.
* Fix: small fixes.

= 3.20.0.1 =
* Fix: recurring subscriptions.
* Fix: different bugfixes.

= 3.20 =
* New: Unisender mailout service integration added as an extension.
* Fix: different bugfixes.

= 3.19.0.2 =
* Fix: the bug of check callbacks for CP gateway recurring subscriptions fixed.
* Fix: critical fix for all plugin updates code.
* Fix: small fixes.

= 3.19.0.1 =
* Fix: the bug of active recurring subscriptions that rebilled everyday fixed.
* Fix: the fatal error for non-existent donations admin pages fixed.
* Fix: the bug of fatal error when donation status is changed on a donation details page fixed.
* Fix: RBK callbacks helper error is fixed.
* Fix: Donor accounts login page bug fixed.
* Fix: the double success emails for YooKassa fixed.
* Fix: small fixes.

= 3.19 =
* New: Core architecture features added for separated donations storage.
* New: Donations, recurring subscriptions & donors admin UI greatly improved.
* New: Stripe gateway support added.
* New: Donations rewards/merchandise extension added.
* New: Donors's account column is added to the GDS-prepared data table in the GDS extension.
* Fix: The important bugfix for donor's account registration & login pages.
* Fix: Lots and lots of smaller bugfixes.
~ Tweak: Lots and lots of refactorings & improvements, both in frontend & backend.

= 3.18 =
* New: Google Data Studio integration extension added.
* Fix: success emails added for Qiwi gateway.
* Fix: small fixes.

= 3.17.1 =
* New: now Extensions settings don't block the main settings areas menu.
* Fix: now YooKassa payment descriptions are forcibly trimmed if they are longer than 128 chars.
* Fix: small fixes.

= 3.17 =
* New: the Additional fields feature added.
* New: Robokassa recurring support added.
* Fix: Tinkoff gateway - fix for recurring rebills.
* Fix: small fixes.

= 3.16 =
* New: Tinkoff gateway added.
* New: additional payment metadata pass to the YooKassa on donation.
* New: new recurring Donation purpose automatically changes to "Charity donation" constant string if it's Campaign is finished.
* New: now Sber Acquiring pass payment description on donation.
* New: now YooKassa gateway handles "canceled" payment status.
* New: now there are links to Donations details pages in Donations list metabox on the Donor details page.
* Change: the main Dashboard banner changed (to the "please grade the plugin" one).
* Fix: Yandex.Money PM label renamed to "YooMoney" on the plugin update to v.3.15+.
* Fix: Engagement Banner extension - now excluding posts/pages by ID works correctly for all post types.
* Fix: Now correctional Donations don't validate a Donor's name field at all. So, any symbols allowed there.
* Fix: Small fixes: l10n, CSS & others.
* Fix for Donors admin list filtering on "single" Donor type.

= 3.15 =
* New: admin menu refactored (shortened).
* New: now Star template text styles are irrelevant of current website theme.
* New: Sber callbacks for recurring transaction errors handling improved.
* New: now Donor's reason to cancel a recurring subscription is saved in the subscription Donation metadata.
* Fix: YooKassa YooMoney payments bug - "yoomoney" error fixed.
* Fix: Polylang compatibility bug fixed.
~ Tweak: CSS for some new admin pages optimized.

= 3.14 =
* New: now Extensions Controller & Render support the case of Extension w/o options.
* New: Yandex.Kassa to YooKassa - gateway renaming & logo changes.
* New: Donation donor comment added as a separate column in admin donations list table.
* New: Organization short name emails placeholder added.
* New: Emails & Terms placeholders display in the options returned.
* Fix: Mixplat options - small additions & wording fixes.
* Fix: Mixplat vulnerability with signature check in callbacks fixed.
* Fix: empty Donors export bug fixed.
* Fix: Extensions engine - small fixes.
* Fix: Polylang compatibility bugfix.
* Fix: text gateway for non-RU int-ns.
* Fix: now all admin SVG icons sources are correct.
* Fix: now YooKassa gives canceled rebills a "failed" status & handles failed donations better.
* Fix: now recurring emails are sent only if active recurring donation is funded.
* Fix: Init Wizard handle for non-ru countries improved.
* Fix: Webpay single donatioons checksums checking fix if recurring is on.
* Fix: CP recurring cancelling callback handling fixed.
* Removed: now Diagnostic data Dashboard block is displayed only if plugin debug mode is on.
* Removed: Cron setup info removed from the Diagnostic data Dashboard block.

= 3.13 =
* New: BY l10n added.
* New: BY WebPay gateway added.
* New: MIXPLAT - API v.3 support added.
* New: many new UA l10n lines.
* New: bank IBAN setting field added for UA l10n.
* Fix: for donations export when PM filter used.
* Fix: callback handling improved for the Paymaster gateway.
* Fix: for donor field notice when saving Donor's admin profile.
* Fix: for org/person terms mixup on the forms if "physical" legal type is selected.
* Fix: small improvements for the Sber gateway callback handling.
* Fix: for UA Liqpay recurring cancelling.
- Removed: bank account setting field removed for UA l10n.

= 3.12 =
* New: internalization code framework added.
* New: UA Liqpay gateway added.
* New: now campaigns settings have a character counter for the payment title setting.
* New: Star template displays PM icons when only one PM available.
* Fix: CP recurring cancelling callback handling fixed.
* Fix: now recurring subscription cancelling hook for CP gateway is triggered at all times.
* Fix: the "notify_tech_support_on_failed_donations" error fixed.
* Fix: Yandex.Kassa get_gateway_response_formatted() method is more error-proof now.
* Fix: Star template controller errors fixed for cases when non-RU l10n is used.
* Fix: small errors in Star & Heed Help templates fixed.
* Removed: the redundant test payment marker removed for Yandex.Kassa gateeway donations.
* Removed: phys. persons support removed for the Quittances & PayPal gateways.

= 3.11.1 =
* Fix: donations & subscriptions export fix.
* Fix: non-workiing active recurring fix.

= 3.11 =
+ New: plugin internationalization framework added.
+ New: SBerbank Acquiring gateway added.
~ Tweak: now options meta is kept in the separate class.
~ Tweak: now options allocation is managed by the Allocators classes family.
* Fix: Star template styling fixes.
* Fix: now gateways commissions are saved correctly.
* Fix: multiple static PMs bug fixed.

* Fix: different CSS, JS & backend fixes.

= 3.10 =
+ New: the "Need Help" template added.
+ New: date parameters added for the leyka_sum shortcode.
+ New: now the form templates may be disabled (via template parameter in comment header).
+ New: now "send tech. support emails on failed donations" option works on all Gateways that use "failed" donations status.
+ New: now the special option added for plugin debug mode.
+ New: IP list entries for CP are stripslashed.
* Fix: a rare bug causing notices on the success page fixed.
* Fix: the default GUA client ID changed to constant value.
* Fix: Donations list filtering bug fixed.
* Fix: RUB & EUR symbols added as default currency labels.
* Fix: improvements of the campaigns target mailout procedure.
* Fix: now Yandex.money for phys. persons has a proper min. commission value.
* Fix: now CloudPayments recurring_change callbacks answer correctly.
* Fix: for the bug of init recurring emails not sending when single donations emails are turned off.
* Fix: admin. settings tabs redesigned.
* Fix: different CSS, JS & backend fixes.

= 3.9 =
+ New: now Smart payment is available for the YK REST API.
+ New: now GA direct integration works with all supported gateways.
* Fix: Support packages campaign check popup width for Safary fixed.
* Fix: Gateways commission values saving fixed.
* Fix: user profile Donor tags list when there are no any tags in DB fixed.
* Fix: oferta & PD popups scrolling fixed.
* Fix: PD text page link fixed.
* Fix: the case when oferta & PD options logically linked together fixed.
* Fix: array_walk() warning while saving Donor's admin profile fixed.
* Fix: GA direct integration - client ID usage fixed.
* Fix: Yandex.Kassa new API donations gateway response metabox warning fixed.

= 3.8.0.1 =
* Fix: "cURL error #28" fixed.
* Fix: CP gateway allowed IPs list updated. Now donations via CP are handled correctly.
* Fix: the gear icon in the Gateways settings list fixed.
* Fix: possible incompatibility with PHP 5.4 fixed.
* Fix: returning Quittance PM fixed.
* Fix: the Support packages in_array() error fixed.

= 3.8 =
* New: recurring subscriptions admin page added.
* New: the Engagement banner extension added.
* New: now Google UA supported directly, without dataLayer & GTA.
* New: the procedure for Donors' notifications on recurring canceling added.
* New: Donors admin list - bulk edit feature added.
* Fix: Donations recurring canceling date bugfix.
* Fix: Donations admin list - footer CSS bug fixed.
* Tweak: admin donation details page - details output improved.
* Tweak: the active recurring procedure improved.
* Tweak: admin styles improved.

= 3.7 =
* New: Extensions engine added.
* New: Support packages Extension added.
* New: Donors' tags bulk edit feature added.
* New: now Gateways & Extensions lists have one markup group - "Modules".
* New: now Gateways Wizards first step allow to enter Gateway parameters at once, without the need to pass the rest of the Wizard.
* New: Donor's comment placeholder added to the emails content.
* Fix: RBK & PayPal gateways bugfixes.
* Fix: Admin footer & helpchat CSS bugfixes & improvements.
* Fix: compatibility with non-standard WP core paths improved.
* Fix: admin small fixes.

= 3.6.1 =
* New: now Gateways cards have an explicit settings link button.
* New: Donors info column added to the admin Users list.
* New: Donors management & Donors' accounts fields logical link added.
* New: API password setting field added to the CP Wizard.
* New: new fields added to the settings.
* Fix: now Donor deletion won't remove a user account if it has more than "Donor" role.
* Fix: Donors logging in & account activation fixes.
* Fix: CP Wizard - small wording & CSS fixes.
* Fix: now Donors are auto-redirected to the respective Account pages on WP login.
* Fix: small fixes.
* Tweak: "Reset filters" for the Donors admin list are auto-submitting filters form now.
* Tweak: now CP Wizard Copy & Paste steps are merged into one.

= 3.6 =
* New: a new group of Star-oriented (more design-flexible) shortcodes added.
* New: amount_formatted property added for Leyka_Donation.
* New: filters for Revo & Star fields labels added.
* New: now plugin ver. is added to the forms templates wrappers as data attribute.
* Fix: on-demand frontend scripts loading fixed.
* Tweak: now Star templates more correctly display PM list in cases of many active payment methods.

= 3.5 =
* New: now RBK Money gateway supports recurring donations.
* New: now old form templates (Revo & earlier) are considered deprecated. They are hidden by default.
* New: Yandex.Kassa new API - errors handling & frontend display improved.
* New: now Star template supports Mixplat mobile PM.
* New: PM special fields engine v.1 added.
* New: Donors list CSV export feature added.
* New: "Misc" admin tab renamed to "for developers".
* Fix: RBK Money gateway fixes & frontend UX changes.
* Fix: now CloudPayments recurring cancelling works correctly.
* Fix: now gateways checkboxes options are saved correctly.
* Fix: muliple shortcodes bugfixes.
* Fix: the legal face RP placeholder removed from the code.
* Fix: small Donor logout callback fix.
* Fix: different small bugfixes.
* Tweak: small refactoring in the Donations export engine.
* Tweak: different refactoring & improvements.

= 3.4.0.1 =
* Fix: bug with spacebar character in the donor names form fields fixed.
* Fix: now Revo forms display correctly via shortcodes.
* Fix: "each() is deprecated" notice fixed.

= 3.4 =
* New: now PayPal supports REST API integration type.
* New: now Star is the default template.
* New: new option added to turn off stats sync attempts.
* New: additions to the Campaign class.
* Fix: now Chronopay supports cyrillic site hostnames.
* Fix: wrong symbols in front-office donation forms "Donor's name" field bug fixed.
* Fix: wrong symbols in Yandex.Kassa shopPassword value generation bug fixed.
* Fix: notice bug for finished campaigns fixed.
* Fix: now finished campaigns forms are displayed by default.
* Fix: admin feedback form error message bug fixed.
* Fix: small bugfixes.
* Tweak: plugin DB tables update moved from procedures to the specific function.

= 3.3.0.1 =
* New: now the Donors management feature is on by default for new installations.
* Fix: now Donors' metadata calculate correctly for all newly added Donors.
* Fix: now Donors' metadata calculation algorithm bases on Donations emails instead of author IDs.
* Fix: admin feedback form submitting JS error fixed.
* Fix: small l10n fixes.
* Removed: unneeded code removed from the plugin core.

= 3.3 =
* New: the Donors management features added.
* New: from now on the plugin activation procedure will run only on plugin activation.
* Fix: the bug of multiple CP recurring donations, presumably, fixed.
* Fix: wizards markup fixes.
* Fix: now gateways commissions are saved correctly for fresh installations.
* Fix: small bugfixes.
* Tweak: admin JS partly refactored, it's volume decreased.

= 3.2.3 =
* New: the plugin Dashboard design renewed.
* New: now Donors accounts are created even on non-initial recurring donations, if needed.
* Fix: admin vulnerability fixes.
* Fix: small bugfixes.

= 3.2.2 =
* New: Now Revo is the default template in the Init Wizard again.
* New: Persistent campaigns - new CSS editor default styles added.
* New: CSS editor features for persistent campaigns CSS field added.
* Fix: Persistent campaign template CSS bugfixes.
* Fix: Persistent campaign CSS editor bufixes.
* Fix: Recurring subscription checkboxes bugfix.
* Fix: Bugfix in Leyka->get_gateways() method.
* Fix: Bugfix for donations comments checkbox field in the plugin settings.

= 3.2.1 =
* New: now [leyka_campaign_form] and [leyka_inline_campaign] shortcodes may be used interchangeably.
* Fix: "submitted" donations status description changed.
* Fix: now Terms agreement checkboxes for Revo & Star templates are independent across different forms on same page.
* Fix: CloudPayments recurring subscription engine is temporarily changed to the default one.
* Fix: Star template markup fixes.
* Fix: now CloudPayments donations work correctly on mobile screens.
* Fix: small bugfixes.

= 3.2 =
* New: Donors personal accounts feature added.
* New: auto-cancelling recurring subscriptions for CloudPayments is possible now.
* New: now it's possible to call procedures as server scripts.
* New: Google UA & GTM integration now supports Enchanced e-commerce.
* Fix: plugin usage statistics synchronization & collection fixed.
* Fix: now active recurring procedure may be called only once per day.
* Fix: recurring support display on the Star template fixed.
* Fix: small bugfixes.

= 3.1 =
* New: Star template added.
* New: Persistent campaigns settings & page template added.
* New: New fields added to the Donations export.
* Fix: Short month active recurring problem fixed.
* Fix: "Donor subscribed" filter added to the Donations list admin page.
* Fix: Revo template markup fixed for some small screen cases.
* Tweak: Plugin frontoffice & backoffice images optimized.

= 3.0.4 =
* New: DataLayer support added for GA e-commerce integration.
* New: now Revo campaigns must be explicitly "finished" to disallow further donations.
* New: the plugin options API improved.
* Fix: the "502 error" bug fixed.
* Fix: a bugfix for non-Revo forms output.
* Fix: now plugin options save correctly.
* Fix: styles for PHP version error message fixed.
* Fix: now Yandex.Kassa Smart payment PM is removed when new YK API is in use.
* Fix: now active recurring scheme for the last days in the short months works correctly.
* Tweak: form templates screenshots tinified.
* Tweak: CSS optimizations.
* Tweak: plugin loading sequence optimizations.
* Tweak: optimizations in the Campaign class for large databases.

= 3.0.3 =
* New: new design of Campaign View settings area added.
* New: now plugin features debug mode switches on/off based on LEYKA_DEBUG instead of WP_DEBUG.
* New: settings render feature - email field rendering function added.
* New: demo mode plugin option added.
* New: donation form templates filter added.
* New: Dashboard commission fields feature added.
* Fix: incorrect symbols in Yandex.Kassa shopPassword value generator removed.
* Fix: Kandinsky theme compatibility fixes.
* Fix: frontend dependencies versions updated.
* Fix: l10n fixes.
* Fix: Wizard settings render - PHP notice fix.
* Fix: small fix in the "campaign target reached" mailout procedure.
* Fix: now PM category on gateway settings page doesn't display when there is only one of them.
* Fix: donor emails sending/not sending checkboxes returned to the Notifications settings area.
* Fix: now thankful emails sended correctly.
* Fix: now donor data fields values on donation forms are trimmed before forms validation.
* Fix: Mixplat SMS PM label & campaign total collected amount auto-refresh bugfixes.
* Fix: CP card PM label display bugfix.
* Fix: plugin options saving bugfix.
* Fix: CP documents links fixed.
* Fix: Revo + CP forms submitting bugfix.
* Fix: small bugfixes.
* Removed: now demo donors on Revo template removed.

= 3.0.2 =
* Fix: important CloudPayments bugfixes.
* Fix: recurring emails titles & texts bugfix.

= 3.0.1 =
* Fix: different bugfixes.

= 3.0 =
* New: plugin settings UI updated.
* New: Setup Wizards added: initial, Yandex.Kassa, CloudPayments.
* New: settings Controllers & Renders framework added.
* New: now PHP v5.6 is the min. supported version.
* Tweak: small optimizations.
* Fix: small bugfixes.

= 2.3.9 =
* New: Paymaster gateway support added.
* Fix: server-side errors handling improved for Revo template.

= 2.3.8 =
* New: Yandex.Kassa new API support added.
* New: Chronopay callbacks handling are more stable now in the cases of DB low performance.
* Fix: PayPal recurring frequency is 1 month now.
* Fix: now commissions apply correctly.
* Fix: Now new Yandex.Kassa branding icons are in use.

= 2.3.7 =
* New: RBK Money new API support added. Now the gateway is operational again.

= 2.3.6.1 =
* New: now Leyka may optionally syncronize outer IP for Yandex.Kassa requests with inner IP.
* Fix: important fix for donation amount passing while using redirects-based gateways with Revo template.

= 2.3.6 =
* New: Paymaster gateway support added.
* New: the "campaign finished" donors mailout feature added.
* New: now all payment methods have both SVG & PNG icons.
* Fix: now CloudPayments recurring works normally.
* Fix: now Quittances work normally on Revo template.
* Fix: different form templates fixes.
* Fix: localization improved.
* Fix: Polylang support module fixed.
* Fix: success subscription widget submitting fixed.
* Fix: now all plugin options values are trimmed before saving.
* Fix: lots of small fixes.
* Tweak: success & failure widgets output checks improved.
* Tweak: now CP IPs list option has empty default value.
* Tweak: plugin JS optimized.
* Tweak: links security improved.
* Tweak: obsolete code removed.

= 2.3.5 =
* New: in the donations export, donations amount and currency are separate columns now.
* New: PayPal recurring donations added.
* Fix: CloudPayments on the Revo template works correctly again.
* Fix: min and max donations amount settings for the Revo template fixed.

= 2.3.4 =
* New: PayPal payments API for Revo template changed to checkout.js.
* Tweak: Revo template CSS compatibility with outside code improved.
* Fix: recurring donations gateways commissions behavior fixed.
* Fix: small fixes.

= 2.3.3 =
* New: gateways commissions function added.
* New: optional donor comment field added.
* Fix: missing emails settings returned.
* Fix: localization improved.
* Fix: lots of small fixes.

= 2.3.2 =
* New: Personal data usage options support added.
* Fix: Recurring email notifications fixed.
* Fix: Yandex.Kassa - recurring bugs fixed.
* Fix: localization improved.

= 2.3.1 =
 * New: Yandex.Kassa recurring subscription cancelling via donor emails added.
 * New: inner API architecture improved.
 * Tweak: wordings improved.
 * Fix: fixes of Revo compatibility with different themes.

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