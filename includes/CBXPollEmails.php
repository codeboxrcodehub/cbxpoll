<?php
// If this file is called directly, abort.
if ( ! defined('WPINC')) {
	die;
}
class CBXPollEmails
{
    /**
     * The single instance of the class
     *
     * @var CBXPollEmails
     */
    private static $_instance = null;

    /**
     * Array of email notification classes
     *
     * @var CBXPollEmails[]
     */
    public $emails = [];

    //public $mail_format;

    /**
     * Main CBXPollEmails Instance.
     *
     * Ensures only one instance of CBXPollEmails is loaded or can be loaded.
     *
     * @return CBXPollEmails Main instance
     * @since 2.1
     * @static
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }//end method instance

    /**
     * Cloning is forbidden.
     *
     * @since 2.0.0
     */
    public function __clone()
    {
        cbxpoll_doing_it_wrong(__FUNCTION__, __('Cloning is forbidden.', 'cbxpoll'), '2.0.0');
    }//end method clone

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 2.0.0
     */
    public function __wakeup()
    {
        cbxpoll_doing_it_wrong(__FUNCTION__,
            __('Unserializing instances of this class is forbidden.', 'cbxpoll'), '2.0.0');
    }//end method wakeup

    public function __construct()
    {
        $this->init();

        // Email Header, Footer and content hooks.
        add_action('cbxpoll_email_header', [$this, 'email_header']);
        add_action('cbxpoll_email_footer', [$this, 'email_footer']);

        // Let 3rd parties unhook the above via this hook.
        do_action('cbxpoll_email', $this);
    }//end constructor

    /**
     * Init email classes.
     */
    public function init()
    {
        // Include email classes.
        include_once __DIR__.'/Emails/CBXPollEmail.php';

        $this->emails = apply_filters('cbxpoll_email_classes', $this->emails);
    }//end method init

    /**
     * Get the email header.
     *
     * @param  mixed  $email_heading  Heading for the email.
     */
    public function email_header($email_heading)
    {
        $template_settings = get_option('cbxpoll_email_tpl', []);

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo cbxpoll_get_template_html('emails/email-header.php', [
            'email_heading'     => $email_heading,
            'template_settings' => $template_settings
        ]);
    }//end method email_header

    /**
     * Get the email footer.
     */
    public function email_footer()
    {
        $template_settings = get_option('cbxpoll_email_tpl', []);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo cbxpoll_get_template_html('emails/email-footer.php', ['template_settings' => $template_settings]);
    }//end method email_footer

    /**
     * Send the email.
     *
     * @param  mixed  $to  Receiver.
     * @param  mixed  $subject  Email subject.
     * @param  mixed  $message  Message.
     * @param  string  $headers  Email headers (default: "Content-Type: text/html\r\n").
     * @param  string  $attachments  Attachments (default: "").
     *
     * @return bool
     */
    public function send($to, $subject, $message, $headers = "Content-Type: text/html\r\n", $attachments = '')
    {
        // Send.
        $email = new CBXPollEmail();

        return $email->send($to, $subject, $message, $headers, $attachments);
    }//end method send

    /**
     * Wraps a message in the cbxpoll mail template.
     *
     * @param  string  $email_heading  Heading text.
     * @param  string  $message  Email message.
     * @param  bool  $plain_text  Set true to send as plain text. Default to false.
     *
     * @return string
     */
    public function wrap_message($email_heading, $message, $plain_text = false)
    {
        // Buffer.
        ob_start();

        do_action('cbxpoll_email_header', $email_heading, null);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo wpautop(wptexturize($message)); // WPCS: XSS ok.

        do_action('cbxpoll_email_footer', null);

        // Get contents.
        return ob_get_clean();
    }//end method wrap_message


    /**
     * Get blog name formatted for emails.
     *
     * @return string
     */
    private function get_blogname()
    {
        return wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
    }//end method get_blogname

    public function is_user_email()
    {
        return $this->user_email;
    }//end method is_user_email

    /**
     * Recipient notifiation email trigger manually
     *
     * @param $poll_id
     * @param $to
     * @param $user_name
     * @param $attachments
     *
     * @return void
     */
    /*public function recipient_notification_email($poll_id, $to, $user_name, $attachments)
    {
        $email = $this->emails['recipient_notification_email'];
        $email->trigger($poll_id, $to, $user_name, $attachments);
    }//end method recipient_notification_email*/
}//end class CBXPollEmails