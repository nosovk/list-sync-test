<?php
/**
 * User: SaWey
 * Date: 16/12/13
 */

namespace phpList\helper;

use phpList\Config;

class DefaultConfig
{
    private static $_instance;
    private $default_config = array();

    /**
     * Get an item from the default configuration
     * @param string $item
     * @return mixed
     */
    public static function get($item)
    {
        if (!DefaultConfig::$_instance instanceof self) {
            DefaultConfig::$_instance = new self();
        }
        if (!empty(DefaultConfig::$_instance->default_config[$item])) {
            return DefaultConfig::$_instance->default_config[$item];
        } else {
            return false;
        }
    }

    private function __construct()
    {
        $defaultheader = '</head><body>';

        $defaultfooter = '</body></html>';
        // TODO: change this to correct uri
		// Notes: This causes memory size error. Needs some discussion since UI is splitted
        // if (is_file(dirname(__FILE__) . '/ui/' . Config::get('ui') . '/frontendheader.php')) {
            // $defaultheader = file_get_contents(dirname(__FILE__) . '/ui/' . Config::get('ui') . '/frontendheader.php');
        // }
        // if (is_file(dirname(__FILE__) . '/ui/' . Config::get('ui') . '/frontendfooter.php')) {
            // $defaultfooter = file_get_contents(dirname(__FILE__) . '/ui/' . Config::get('ui') . '/frontendfooter.php');
        // }

        if (isset ($_SERVER['HTTP_HOST'])) {
            $D_website = $_SERVER['HTTP_HOST'];
        } else {
            $D_website = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
        }
        $D_domain = $D_website;
        if (preg_match('#^www\.(.*)#i', $D_domain, $regs)) {
            $D_domain = $regs[1];
        }

        $this->default_config = array(

            /* any next line has the format
              'name' => array(
                'value',     // default value
                'description',
                'type',      // text, textarea, boolean
                'allow empty', // 0 or 1 (or false/true)
                'category'   // general
              ),
            */

            # what is your website location (url)
            'website' => array(
                'value' => $D_website,
                'description' => s('Website address (without http://)'),
                'type' => 'text',
                'allowempty' => false, ## indication this value cannot be empty (1 being it can be empty)
                'category' => 'general'
            ),
            # what is your domain (for sending emails)
            'domain' => array(
                'value' => $D_domain,
                'description' => s('Domain Name of your server (for email)'),
                'type' => 'text',
                'allowempty' => false,
                'category' => 'general',
            ),
            # admin address is the person who is in charge of this system
            'admin_address' => array(
                'value' => 'webmaster@[DOMAIN]',
                'description' => s('Person in charge of this system (one email address)'),
                'type' => 'email',
                'allowempty' => false,
                'category' => 'general',
            ),
            # name of the organisation
            'organisation_name' => array(
                'value' => '',
                'description' => s('Name of the organisation'),
                'type' => 'text',
                'allowempty' => true,
                'category' => 'general',
            ),
            # how often to check for new versions of PHPlist
            'check_new_version' => array(
                'value' => '7',
                'description' => s('How often do you want to check for a new version of phplist (days)'),
                'type' => 'integer',
                'min' => 1,
                'max' => 180,
                'category' => 'security',
            ),
            # admin addresses are other people who receive copies of subscriptions
            'admin_addresses' => array(
                'value' => '',
                'description' => s('List of email addresses to CC in system messages (separate by commas)'),
                'type' => 'emaillist',
                'allowempty' => true,
                'category' => 'reporting',
            ),
            'campaignfrom_default' => array(
                'value' => '',
                'description' => s('Default for \'From:\' in a campaign'),
                'type' => 'text',
                'allowempty' => true,
                'category' => 'campaign',
            ),
            'notifystart_default' => array(
                'value' => '',
                'description' => s('Default for \'address to alert when sending starts\''),
                'type' => 'email',
                'allowempty' => true,
                'category' => 'campaign',
            ),
            'notifyend_default' => array(
                'value' => '',
                'description' => s('Default for \'address to alert when sending finishes\''),
                'type' => 'email',
                'allowempty' => true,
                'category' => 'campaign',
            ),
            'always_add_googletracking' => array(
                'value' => '0',
                'description' => s('Always add Google tracking code to campaigns'),
                'type' => 'boolean',
                'allowempty' => true,
                'category' => 'campaign',
            ),
            # report address is the person who gets the reports
            'report_address' => array(
                'value' => 'listreports@[DOMAIN]',
                'description' => s('Who gets the reports (email address, separate multiple emails with a comma)'),
                'type' => 'emaillist',
                'allowempty' => true,
                'category' => 'reporting',
            ),
            # where will messages appear to come from
            'message_from_address' => array(
                'value' => 'noreply@[DOMAIN]',
                'description' => s('From email address for system messages'),
                'type' => 'email',
                'allowempty' => 0,
                'category' => 'transactional',
            ),
            'message_from_name' => array(
                'value' => s('Webmaster'),
                'description' => s('Name for system messages'),
                'type' => 'text',
                'allowempty' => 0,
                'category' => 'transactional',
            ),
            # what is the reply-to on messages?
            'message_replyto_address' => array(
                'value' => 'noreply@[DOMAIN]',
                'description' => s('Reply-to email address for system messages'),
                'type' => 'email',
                'allowempty' => 0,
                'category' => 'transactional',
            ),
            # if there is only one visible list, do we hide it and automatically
            # subscribe users who sign up
            ## not sure why you would not want this :-) maybe it should not be an option at all
            'hide_single_list' => array(
                'value' => '1',
                'description' => s(
                    'If there is only one visible list, should it be hidden in the page and automatically subscribe users who sign up'
                ),
                'type' => 'boolean',
                'allowempty' => true,
                'category' => 'subscription-ui',
            ),
            # categories for lists, to organise them a little bit
            # comma separated list of words
            'list_categories' => array(
                'value' => '',
                'description' => s('Categories for lists. Separate with commas.'),
                'type' => 'text',
                'allowempty' => true,
                'category' => 'segmentation',
            ),
            # width of a textline field
            'textline_width' => array(
                'value' => '40',
                'description' => s('Width of a textline field (numerical)'),
                'type' => 'integer',
                'min' => 20,
                'max' => 150,
                'category' => 'subscription-ui',
            ),
            # dimensions of a textarea field
            'textarea_dimensions' => array(
                'value' => '10,40',
                'description' => s('Dimensions of a textarea field (rows,columns)'),
                'type' => 'text',
                'allowempty' => 0,
                'category' => 'subscription-ui',
            ),
            # send copies of subscribe, update unsubscribe messages to the administrator
            'send_admin_copies' => array(
                'value' => '0',
                'description' => s('Send notifications about subscribe, update and unsubscribe'),
                'type' => 'boolean',
                'allowempty' => true,
                'category' => 'reporting',
            ),
            # the main subscribe page, when there are multiple
            'defaultsubscribepage' => array(
                'value' => 1,
                'description' => s('The default subscribe page when there are multiple'),
                'type' => 'integer',
                'min' => 1,
                'max' => 999, // max(id) from subscribepage
                'allowempty' => true,
                'category' => 'subscription',
            ),
            # the default template for sending an html message
            'defaultmessagetemplate' => array(
                'value' => 0,
                'description' => s('The default HTML template to use when sending a message'),
                'type' => 'text',
                'allowempty' => true,
                'category' => 'campaign',
            ),
            # the template for system messages (welcome confirm subscribe etc)
            'systemmessagetemplate' => array(
                'value' => 0,
                'description' => s('The HTML wrapper template for system messages'),
                'type' => 'integer',
                'min' => 0,
                'max' => 999, // or max(id) from template
                'allowempty' => true,
                'category' => 'transactional',
            ),
            ## the location of your subscribe script
            #'public_baseurl' => array("http://[WEBSITE]Config::PAGEROOT/",
            #  'Base URL for public pages',"text"),

            # the location of your subscribe script
            'subscribeurl' => array(
                'value' => Config::get('scheme') . '://[WEBSITE]'.Config::PAGEROOT.'/?p=subscribe',
                'description' => s('URL where subscribers can sign up'),
                'type' => 'url',
                'allowempty' => 0,
                'category' => 'subscription',
            ),
            # the location of your unsubscribe script:
            'unsubscribeurl' => array(
                'value' => Config::get('scheme') . '://[WEBSITE]'.Config::PAGEROOT.'/?p=unsubscribe',
                'description' => s('URL where subscribers can unsubscribe'),
                'type' => 'url',
                'allowempty' => 0,
                'category' => 'subscription',
            ),
            #0013076: Blacklisting posibility for unknown users
            # the location of your blacklist script:
            'blacklisturl' => array(
                'value' => Config::get('scheme') . '://[WEBSITE]'.Config::PAGEROOT.'/?p=donotsend',
                'description' => s('URL where unknown users can unsubscribe (do-not-send-list)'),
                'type' => 'url',
                'allowempty' => 0,
                'category' => 'subscription',
            ),
            # the location of your confirm script:
            'confirmationurl' => array(
                'value' => Config::get('scheme') . '://[WEBSITE]'.Config::PAGEROOT.'/?p=confirm',
                'description' => s('URL where subscribers have to confirm their subscription'),
                'type' => 'text',
                'allowempty' => 0,
                'category' => 'subscription',
            ),
            # url to change their preferences
            'preferencesurl' => array(
                'value' => Config::get('scheme') . '://[WEBSITE]'.Config::PAGEROOT.'/?p=preferences',
                'description' => s('URL where subscribers can update their details'),
                'type' => 'text',
                'allowempty' => 0,
                'category' => 'subscription',
            ),
            # url to change their preferences
            'forwardurl' => array(
                'value' => Config::get('scheme') . '://[WEBSITE]'.Config::PAGEROOT.'/?p=forward',
                'description' => s('URL for forwarding messages'),
                'type' => 'text',
                'allowempty' => 0,
                'category' => 'subscription',
            ),
            'ajax_subscribeconfirmation' => array(
                'value' => s(
                    '<h3>Thanks, you have been added to our newsletter</h3><p>You will receive an email to confirm your subscription. Please click the link in the email to confirm</p>'
                ),
                'description' => s('Text to display when subscription with an AJAX request was successful'),
                'type' => 'textarea',
                'allowempty' => true,
                'category' => 'subscription',
            ),
            # the location of your subscribe script
            #'subscribe_baseurl' => array("http://[WEBSITE]".Config::PAGEROOT."/",
            #  'Base URL for public pages',"text"),

            # the subject of the message
            'subscribesubject' => array(
                'value' => s('Request for confirmation'),
                'description' => s('Subject of the message subscribers receive when they sign up'),
                'type' => 'text',
                'allowempty' => 0,
                'category' => 'transactional',
            ),
            # message that is sent when people sign up to a list
            # [LISTS] will be replaced with the list of lists they have signed up to
            # [CONFIRMATIONURL] will be replaced with the URL where a user has to confirm
            # their subscription
            'subscribemessage' => array(
                'value' => '

  Almost welcome to our newsletter(s) ...

  Someone, hopefully you, has subscribed your email address to the following newsletters:

  [LISTS]

  If this is correct, please click the following link to confirm your subscription.
  Without this confirmation, you will not receive any newsletters.

  [CONFIRMATIONURL]

  If this is not correct, you do not need to do anything, simply delete this message.

  Thank you

    ',
                'description' => s('Message subscribers receive when they sign up'),
                'type' => 'textarea',
                'allowempty' => 0,
                'category' => 'transactional',
            ),
            # subject of the message when they unsubscribe
            'unsubscribesubject' => array(
                'value' => s('Goodbye from our Newsletter'),
                'description' => s('Subject of the message subscribers receive when they unsubscribe'),
                'type' => 'text',
                'allowempty' => 0,
                'category' => 'transactional',
            ),
            # message that is sent when they unsubscribe
            'unsubscribemessage' => array(
                'value' => '

  Goodbye from our Newsletter, sorry to see you go.

  You have been unsubscribed from our newsletters.

  This is the last email you will receive from us. Our newsletter system, phpList,
  will refuse to send you any further messages, without manual intervention by our administrator.

  If there is an error in this information, you can re-subscribe:
  please go to [SUBSCRIBEURL] and follow the steps.

  Thank you

  ',
                'description' => s('Message subscribers receive when they unsubscribe'),
                'type' => 'textarea',
                'allowempty' => 0,
                'category' => 'transactional',
            ),
            # confirmation of subscription
            'confirmationsubject' => array(
                'value' => s('Welcome to our Newsletter'),
                'description' => s('Subject of the message subscribers receive after confirming their email address'),
                'type' => 'text',
                'allowempty' => 0,
                'category' => 'transactional',
            ),
            # message that is sent to confirm subscription
            'confirmationmessage' => array(
                'value' => '

  Welcome to our Newsletter

  Please keep this message for later reference.

  Your email address has been added to the following newsletter(s):
  [LISTS]

  To update your details and preferences please go to [PREFERENCESURL].
  If you do not want to receive any more messages, please go to [UNSUBSCRIBEURL].

  Thank you

  ',
                'description' => s('Message subscribers receive after confirming their email address'),
                'type' => 'textarea',
                'allowempty' => 0,
                'category' => 'transactional',
            ),
            # the subject of the message sent when changing the user details
            'updatesubject' => array(
                'value' => s('[notify] Change of List-Membership details'),
                'description' => s('Subject of the message subscribers receive when they have changed their details'),
                'type' => 'text',
                'allowempty' => 0,
                'category' => 'transactional',
            ),
            # the message that is sent when a user updates their information.
            # just to make sure they approve of it.
            # confirmationinfo is replaced by one of the options below
            # userdata is replaced by the information in the database
            'updatemessage' => array(
                'value' => '

  This message is to inform you of a change of your details on our newsletter database

  You are currently member of the following newsletters:

  [LISTS]

  [CONFIRMATIONINFO]

  The information on our system for you is as follows:

  [USERDATA]

  If this is not correct, please update your information at the following location:

  [PREFERENCESURL]

  Thank you

    ',
                'description' => s('Message subscribers receive when they have changed their details'),
                'type' => 'textarea',
                'allowempty' => 0,
                'category' => 'transactional',
            ),
            # this is the text that is placed in the [!-- confirmation --] location of the above
            # message, in case the email is sent to their new email address and they have changed
            # their email address
            'emailchanged_text' => array(
                'value' => '
  When updating your details, your email address has changed.
  Please confirm your new email address by visiting this webpage:

  [CONFIRMATIONURL]

  ',
                'description' => s(
                    'Part of the message that is sent to their new email address when subscribers change their information, and the email address has changed'
                ),
                'type' => 'textarea',
                'allowempty' => 0,
                'category' => 'transactional',
            ),
            # this is the text that is placed in the [!-- confirmation --] location of the above
            # message, in case the email is sent to their old email address and they have changed
            # their email address
            'emailchanged_text_oldaddress' => array(
                'value' => '
  Please Note: when updating your details, your email address has changed.

  A message has been sent to your new email address with a URL
  to confirm this change. Please visit this website to activate
  your membership.
  ',
                'description' => s(
                    'Part of the message that is sent to their old email address when subscribers change their information, and the email address has changed'
                ),
                'type' => 'textarea',
                'allowempty' => 0,
                'category' => 'transactional',
            ),
            'personallocation_subject' => array(
                'value' => s('Your personal location'),
                'description' => s('Subject of message when subscribers request their personal location'),
                'type' => 'text',
                'allowempty' => 0,
                'category' => 'transactional',
            ),
            'messagefooter' => array(
                'value' => '--

    <div class="footer" style="text-align:left; font-size: 75%;">
      <p>This message was sent to [EMAIL] by [FROMEMAIL]</p>
      <p>To forward this message, please do not use the forward button of your email application, because this message was made specifically for you only. Instead use the <a href="[FORWARDURL]">forward page</a> in our newsletter system.<br/>
      To change your details and to choose which lists to be subscribed to, visit your personal <a href="[PREFERENCESURL]">preferences page</a><br/>
      Or you can <a href="[UNSUBSCRIBEURL]">opt-out completely</a> from all future mailings.</p>
    </div>

  ',
                'description' => s('Default footer for sending a campaign'),
                'type' => 'textarea',
                'allowempty' => 0,
                'category' => 'campaign',
            ),
            'forwardfooter' => array(
                'value' => '
     <div class="footer" style="text-align:left; font-size: 75%;">
      <p>This message has been forwarded to you by [FORWARDEDBY].</p>
      <p>You have not been automatically subscribed to this newsletter.</p>
      <p>If you think this newsletter may interest you, you can <a href="[SUBSCRIBEURL]">Subscribe</a> and you will receive our next newsletter directly to your inbox.</p>
      <p>You can also <a href="[BLACKLISTURL]">opt out completely</a> from receiving any further email from our newsletter application, phpList.</p>
    </div>
  ',
                'description' => s('Footer used when a message has been forwarded'),
                'type' => 'textarea',
                'allowempty' => 0,
                'category' => 'campaign',
            ),
            'pageheader' => array(
                'value' => $defaultheader,
                'description' => s('Header of public pages.'),
                'type' => 'textarea',
                'allowempty' => 0,
                'category' => 'subscription-ui',
            ),
            'pagefooter' => array(
                'value' => $defaultfooter,
                'description' => s('Footer of public pages'),
                'type' => 'textarea',
                'allowempty' => 0,
                'category' => 'subscription-ui',
            ),
//'html_charset' => array (
            //'UTF-8',
            //'Charset for HTML messages',
            //"text"
//),
//'text_charset' => array (
            //'UTF-8',
            //'Charset for Text messages',
            //"text"
//),

            'personallocation_message' => array(
                'value' => '

You have requested your personal location to update your details from our website.
The location is below. Please make sure that you use the full line as mentioned below.
Sometimes email programmes can wrap the line into multiple lines.

Your personal location is:
[PREFERENCESURL]

Thank you.
',
                'description' => s('Message to send when they request their personal location'),
                'type' => 'textarea',
                'allowempty' => 0,
                'category' => 'transactional',
            ),
            'remoteurl_append' => array(
                'value' => '',
                'description' => s('String to always append to remote URL when using send-a-webpage'),
                'type' => 'text',
                'allowempty' => true,
                'category' => 'campaign',
            ),
            'wordwrap' => array(
                'value' => '75',
                'description' => s('Width for Wordwrap of Text messages'),
                'type' => 'text',
                'allowempty' => true,
                'category' => 'campaign',
            ),
            'html_email_style' => array(
                'value' => '',
                'description' => s('CSS for HTML messages without a template'),
                'type' => 'textarea',
                'allowempty' => true,
                'category' => 'campaign',
            ),
            'alwayssendtextto' => array(
                'value' => '',
                'description' => s('Domains that only accept text emails, one per line'),
                'type' => 'textarea',
                'allowempty' => true,
                'category' => 'campaign',
            ),
            'tld_last_sync' => array(
                'value' => '0',
                'description' => s('last time TLDs were fetched'),
                'type' => 'text',
                'allowempty' => true,
                'category' => 'system',
                'hidden' => true,
            ),
            'internet_tlds' => array(
                'value' => '',
                'description' => s('Top level domains'),
                'type' => 'textarea',
                'allowempty' => true,
                'category' => 'system',
                'hidden' => true,
            ),

        );
    }
} 