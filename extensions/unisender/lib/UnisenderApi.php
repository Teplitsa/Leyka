<?php

namespace Unisender\ApiWrapper;

/**
 * API UniSender.
 *
 * @link https://www.unisender.com/en/support/category/api/
 * @link https://www.unisender.com/ru/support/category/api/
 *
 * @method string sendSms(array $params) It is a method for easy sending the one SMS to one or several recipients.
 * @method string sendEmail(array $params) It is a method to send a single individual email without personalization and
 * with limited possibilities to obtain statistics. To send transactional letters, use the
 * UniOne — the transactional letter service from UniSender. https://www.unisender.com/en/features/unione/
 * @method string getLists() It is a method to get the list of all available campaign lists.
 * @method string createList(array $params) It is a method to create a new contact list.
 * @method string updateList(array $params) It is a method to change campaign list properties.
 * @method string deleteList(array $params) It is a method to delete a list.
 * @method string exclude(array $params) The method excludes the contact’s email or phone number from one or several lists.
 * @method string unsubscribe(array $params) The method unsubscribes the contact email or phone number from one or several
 * lists.
 * @method string importContacts(array $params) It is a method of bulk import of contacts.
 * @method string getTotalContactsCount(array $params) The method returns the contacts database size by the user login.
 * @method string getContactCount(array $params) Get contact count in list.
 * @method string createEmailMessage(array $params) It is a method to create an email without sending it.
 * @method string createSmsMessage(array $params) It is a method to create SMS messages without sending them.
 * @method string createCampaign(array $params) This method is used to schedule or immediately start sending email
 * or SMS messages.
 * @method string getActualMessageVersion(array $params) The method returns the id of the relevant version of
 * the specified letter.
 * @method string checkSms(array $params) It returns a string — the SMS sending status.
 * @method string sendTestEmail(array $params) It is a method to send a test email message.
 * @method string checkEmail(array $params) The method allows you to check the delivery status of emails sent
 * using the sendEmail method.
 * @method string updateOptInEmail(array $params) Each campaign list has the attached text of the invitation
 * to subscribe and confirm the email that is sent to the contact to confirm the campaign. The text of the letter
 * can be changed using the updateOptInEmail method.
 * @method string getWebVersion(array $params) It is a method to get the link to the web version of the letter.
 * @method string deleteMessage(array $params) It is a method to delete a message.
 * @method string createEmailTemplate(array $params) It is a method to create an email template for a mass campaign.
 * @method string updateEmailTemplate(array $params) It is a method to edit email templates for a mass campaign.
 * @method string deleteTemplate(array $params) It is a method to delete a template.
 * @method string getTemplate(array $params) The method returns information about the specified template.
 * @method string getTemplates(array $params = []) This method is used to get the list of templates created
 * both through the UniSender personal account and through the API.
 * @method string listTemplates(array $params = []) This method is used to get the list of templates created both
 * through the UniSender personal account and through the API.
 * @method string getCampaignCommonStats(array $params) The method returns statistics similar to «Campaigns».
 * @method string getVisitedLinks(array $params) Get a report on the links visited by users in the specified email campaign.
 * @method string getCampaigns(array $params = array()) It is a method to get the list of all available campaigns.
 * @method string getCampaignStatus(array $params) Find out the status of the campaign created using the createCampaign method.
 * @method string getMessages(array $params = []) This method is used to get the list of letters created both
 * through the UniSender personal account and through the API.
 * @method string getMessage(array $params) It is a method to get information about SMS or email message.
 * @method string listMessages(array $params) This method is used to get the list of messages created both through
 * the UniSender personal account and through the API. The method works like getMessages, the difference of
 * listMessages is that the letter body and attachments are not returned, while the user login is returned. To get the
 * body and attachments, use the getMessage method.
 * @method string getFields() It is a method to get the list of user fields.
 * @method string createField(array $params) It is a method to create a new user field, the value of which can be set for
 * each recipient, and then it can be substituted in the letter.
 * @method string updateField(array $params) It is a method to change user field parameters.
 * @method string deleteField(array $params) It is a method to delete a user field.
 * @method string getTags() It is a method to get list of all tags.
 * @method string deleteTag(array $params) It is a method to delete a user tag.
 * @method string isContactInLists(array $params) Checks whether contact is in list.
 * @method string getContactFieldValues(array $params) Get addinitioan fields values for a contact.
 */
class UnisenderApi
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * @var int
     */
    protected $retryCount = 0;

    /**
     * @var int
     */
    protected $timeout;

    /**
     * @var bool
     */
    protected $compression = false;

    /**
     * Not required argument. Its like UserAgent from browsers. For example, put here: My E-commerce v1.0.
     * @var string
     */
    protected $platform = '';

    /**
     * @var string
     */
    protected $lang = 'en';

    /**
     * Allowed languages for api request.
     * @var array
     */
    protected static $languages = ['en', 'ru', 'ua'];

    /**
     * UniSender Api constructor
     *
     * For example:
     *
     * <pre>
     *
     * $platform = 'My E-commerce product v1.0';
     *
     * $UnisenderApi = new UnisenderApi('api key here', 'UTF-8', 4, null, false, $platform);
     * $UnisenderApi->sendSms(
     *      ['phone' => 380971112233, 'sender' => 'SenderName', 'text' => 'Hello World!']
     * );
     *
     * </pre>
     *
     * @param string $apiKey        Provide your api key here.
     * @param string $encoding      If your current encoding is different from UTF-8, specify it here.
     * @param int    $retryCount
     * @param int    $timeout
     * @param bool   $compression
     * @param string $platform      Specify your product name, example - My E-commerce v1.0.
     *
     */
    public function __construct($apiKey, $encoding = 'UTF-8', $retryCount = 4, $timeout = null, $compression = false, $platform = null)
    {
        $this->apiKey = $apiKey;
        $platform = trim((string) $platform);

        if (!empty($encoding)) {
            $this->encoding = $encoding;
        }

        if (0 < $retryCount) {
            $this->retryCount = $retryCount;
        }

        if (null !== $timeout) {
            $this->timeout = $timeout;
        }

        if ($compression) {
            $this->compression = $compression;
        }

        if ($platform !== '') {
            $this->platform = $platform;
        }
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return string
     */
    public function __call($name, $arguments)
    {
        if (!is_array($arguments) || 0 === count($arguments)) {
            $params = [];
        } else {
            $params = $arguments[0];
        }

        return $this->callMethod($name, $params);
    }

    /**
     * Set desired language for api request.
     *
     * @param string $language
     *
     * @return $this|bool
     */
    public function setApiHostLanguage($language = '')
    {
        if (in_array($language, static::$languages, true))
        {
            $this->lang = $language;
            return $this;
        }

        return false;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function subscribe($params)
    {
        $params = (array) $params;

        if (empty($params['request_ip'])) {
            $params['request_ip'] = $this->getClientIp();
        }

        return $this->callMethod('subscribe', $params);
    }

    /**
     * Export of contact data from UniSender.
     * Depending on the number of contacts to export, the file may take some time to prepare.
     * After the export is ready, it will be sent to the URL specified in the notify_url parameter of
     * the async/exportContacts method.
     *
     * You can also request task status.
     *
     * @see https://www.unisender.com/en/support/api/contacts/exportcontacts/
     *
     * @param array $params
     *
     * @return false|string
     */
    public function taskExportContacts(array $params)
    {
        return $this->callMethod('async/exportContacts', $params);
    }

    /**
     * Get a results report of the delivery of messages in the given campaign.
     * Depending on the number of recipients in the list, a report on it may be prepared for some time.
     * After the report is ready, it will be sent to the URL specified in the notify_url parameter of
     * the async/getCampaignDeliveryStats method.
     *
     * You can also request task status.
     *
     * @see https://www.unisender.com/en/support/api/statistics/getcampaigndeliverystats/
     *
     * @param array $params
     *
     * @return false|string
     */
    public function taskGetCampaignDeliveryStats(array $params)
    {
        return $this->callMethod('async/getCampaignDeliveryStats', $params);
    }

    /**
     * Get task status
     *
     * @param array $params
     *
     * @return false|string
     */
    public function getTaskResult(array $params)
    {
        return $this->callMethod('async/getTaskResult', $params);
    }

    /**
     * The getCurrencyRates method allows you to get a list of all currencies in the UniSender system.
     *
     * @see https://www.unisender.com/en/support/api/common/getcurrencyrates/
     *
     * @return false|string
     */
    public function getCurrencyRates()
    {
        return $this->callMethod('getCurrencyRates');
    }

    /**
     * The method sends a message to the email address with a link to confirm the address as the return address.
     * After clicking on this link, you can send messages on behalf of this email address.
     *
     * @param array $params
     *
     * @see https://www.unisender.com/en/support/api/messages/validatesender/
     *
     * @return false|string
     */
    public function validateSender(array $params)
    {
        return $this->callMethod('validateSender', $params);
    }

    /**
     * The system will register the domain in the list for authentication and generate a dkim key for it.
     * Confirm the address on the domain to add the domain to the list.
     *
     * @see https://www.unisender.com/en/support/api/messages/setsenderdomain/
     *
     * @param array $params
     *
     * @return false|string
     */
    public function setSenderDomain(array $params)
    {
        return $this->callMethod('setSenderDomain', $params);
    }

    /**
     * Get domains list registrated by setSenderDomain api method.
     *
     * @see https://www.unisender.com/en/support/api/messages/getsenderdomainlist/
     *
     * @param array $params
     *
     * @return false|string
     */
    public function getSenderDomainList(array $params)
    {
        return $this->callMethod('getSenderDomainList', $params);
    }

    /**
     * The method returns an object with confirmed and unconfirmed sender’s addresses. Unconfirmed sender’s address
     * is the address to which the message was sent with a link to confirm the return address,
     * but the confirmation link wasn’t clicked.
     * To verify the return address, you can use the validateSender method.
     *
     * @see https://www.unisender.com/en/support/api/messages/getcheckedemail/
     *
     * @param array $params
     *
     * @return false|string
     */
    public function getCheckedEmail(array $params)
    {
        return $this->callMethod('getCheckedEmail', $params);
    }

    /**
     * This method return information about contact.
     *
     * @param array $params Array: email, api_key
     *
     * @return false|string
     */
    public function getContact(array $params)
    {
        return $this->callMethod('getContact', $params);
    }

    /**
     * @param string $json
     *
     * @return mixed
     */
    protected function decodeJSON($json)
    {
        return json_decode($json);
    }

    /**
     * @return string
     */
    protected function getClientIp()
    {
        $result = '';

        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $result = $_SERVER['REMOTE_ADDR'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $result = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $result = $_SERVER['HTTP_CLIENT_IP'];
        }

        if (preg_match('/([0-9]|[0-9][0-9]|[01][0-9][0-9]|2[0-4][0-9]|25[0-5])(\.' .
            '([0-9]|[0-9][0-9]|[01][0-9][0-9]|2[0-4][0-9]|25[0-5])){3}/', $result, $match)) {
            return $match[0];
        }

        return $result;
    }

    /**
     * @param string $value
     * @param string $key
     */
    protected function iconv(&$value, $key)
    {
        $value = iconv($this->encoding, 'UTF-8//IGNORE', $value);
    }

    /**
     * @param string $value
     * @param string $key
     */
    protected function mb_convert_encoding(&$value, $key)
    {
        $value = mb_convert_encoding($value, 'UTF-8', $this->encoding);
    }

    /**
     * @param       $methodName
     * @param array $params
     *
     * @return false|string
     */
    protected function callMethod($methodName, $params = [])
    {
        if ($this->platform !== '') {
            $params['platform'] = $this->platform;
        }

        if (strtoupper($this->encoding) !== 'UTF-8') {
            if (function_exists('iconv')) {
                array_walk_recursive($params, [$this, 'iconv']);
            } elseif (function_exists('mb_convert_encoding')) {
                array_walk_recursive($params, [$this, 'mb_convert_encoding']);
            }
        }

        $url = $methodName.'?format=json';

        if ($this->compression) {
            $url .= '&api_key='.$this->apiKey.'&request_compression=bzip2';
            $content = bzcompress(http_build_query($params));
        } else {
            $params = array_merge((array) $params, ['api_key' => $this->apiKey]);
            $content = http_build_query($params);
        }

        $contextOptions = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $content,
            ],
            'ssl' => [
                'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
            ]
        ];

        if ($this->timeout) {
            $contextOptions['http']['timeout'] = $this->timeout;
        }

        $retryCount = 0;
        $context = stream_context_create($contextOptions);

        do {
            $host = $this->getApiHost();
            $result = @file_get_contents($host.$url, false, $context);
            ++$retryCount;
        } while ($result === false && $retryCount < $this->retryCount);

        return $result;
    }

    /**
     * @return string
     */
    protected function getApiHost()
    {
        return sprintf('https://api.unisender.com/%s/api/', $this->lang);
    }
}
