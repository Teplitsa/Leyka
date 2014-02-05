=== Leyka ===
Contributors: ahaenor, foralien
Author URI: http://te-st.ru
Plugin URI: http://leyka.te-st.ru
Tags: e-donate, donates, charity, wp-donates, crowdfunding, leyka
Requires at least: 3.6.1
Tested up to: 3.8.1
Stable tag: 2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Лейка - система для интеграции, сбора и управления пожертвованиями на вашем сайте.

== Description ==

Плагин «Онлайн-Лейка» — это система, позволяющая добавить на ваш сайт функции сбора пожертвований для вашей организации. 
Пожертвования принимаются в популярных электронных валютах. 
**Следите за разработкой на [GitHub](https://github.com/Teplitsa/Leyka)**

Задача плагина — облегчить и усовершенствовать интеграцию сбора пожертвований на сайты НКО.
* Плагин элементарно устанавливается и требует минимум настроек. 
* Функции сбора пожертвований доступны сразу после установки.
* Многие важные параметры заданы автоматически. Например, юридически корректный текст договора оферты на пожертвование.

Основная аудитория плагина — сотрудники НКО и общественные инициативы.

Внимание: для сбора пожертвований с помощью популярных платёжных систем необходимо иметь договор с этими системами.

**Основные функции**

* Создавайте различные виды пожертвований для различных благотворительных кампаний и проектов.
* Отслеживайте пожертвования с помощью извещений на эл. почту. 
* Следите за статистикой пожертвований.
* Собирайте платежи в разных валютах (поддерживаются рубли, доллары и евро) и с помощью разных платежных операторов.
* Редактируй текст вашей благодарности донорам.
* При необходимости, измените текст договора оферты.

== Installation ==

Процесс инсталляции плагина стандартен для WordPress.

== Frequently Asked Questions ==

Читайте секцию вопросов и ответов на сайте плагина [FAQ](http://leyka.te-st.ru/faq/)

== Screenshots ==

1. Начальная страница плагина (консоль)
2. Страница списка поступивших пожертвований
3. Настройки платёжных систем
4. Настройки email-уведомлений
5. Пример формы пожертвования на сайте
6. Пример квитанции для оплаты через банк

== Changelog ==

= 2.0 =
* New: New major release. Code refactored and data structures changed.
* New: Removed dependency from EasyDigitalDownload.
* New: Payment gateways are now embedded in Leyka.
* New: New design of donation widget.
* New: gateway/payment method API.
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

== Upgrade Notice ==

**ВНИМАНИЕ!**

В версии 2.0 были внесены критические изменения, не совместимые с кодом и данными любой более ранней версии. 
**Если вы активно использовали Лейку версии 1.2 или ранее, не выполняйте это обновление**. 
Разработчики плагина не несут ответственности за результат этого действия. 
Подробнее читайте на [сайте плагина](http://leyka.te-st.ru/old-version/)
