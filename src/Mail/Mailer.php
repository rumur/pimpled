<?php

namespace Rumur\Pimpled\Mail;

use Closure;
use Exception;
use WP_Error;
use InvalidArgumentException;

/**
 * Class Mailer
 *
 * @author rumur
 */
class Mailer
{
    /** @var string Where to sent to */
    protected $to;

    /** @var string Where to sent to as a hidden copy */
    protected $cc;

    /** @var string Where to sent to as a hidden copy */
    protected $bcc;

    /** @var string Email Charset */
    protected $charset = 'UTF-8';

    /** @var array Email Headers */
    protected $headers = [];

    /** @var string Subject of the email */
    protected $subject;

    /** @var string */
    protected $from_name;

    /** @var string */
    protected $from_email;

    /** @var string */
    protected static $always_from_email;

    /** @var string */
    protected static $always_from_name;

    /** @var string */
    protected $reply_to;

    /**
     * Message of the email
     *
     * @var string
     */
    protected $body;

    /**
     * Attachments that will be passed to the wp_mail
     *
     * @var array
     */
    protected $attachments = [];

    /**
     * Info if the email has been sent.
     *
     * @var bool
     */
    protected $is_sent = false;

    /**
     * Info if the email has been sent and it's failed.
     *
     * @var bool
     */
    protected $is_failed = false;

    /**
     * Params that will be passed
     * to a subject and message of the email
     *
     * @var array
     */
    protected $placeholders = [];

    /** @var string */
    protected $stored_php_mailer_charset;

    /** @var array */
    protected $before_send_listeners = [];

    /** @var array */
    protected $after_sent_listeners = [];

    /** @var array  */
    protected $failed_listeners = [];

    /**
     * EP\Email constructor.
     *
     * @param string $to Comma Separated Email where to sent to
     * @param string $subject Subject of the email
     * @param string $body An Email message
     * @param array $headers Optional. Headers for email.
     * @param array $attachments Optional. Files to attach.
     * @param array $placeholders Optional. Key value pair that will be performed in the subject and Message.
     *
     * @throws Exception
     */
    public function __construct($to, $subject, $body, array $headers = [], array $attachments = [], array $placeholders = [])
    {
        $this->setTo($to);
        $this->setSubject($subject);
        $this->setBody($body);
        $this->setHeaders($headers);
        $this->setAttachments($attachments);
        $this->setPlaceholders($placeholders);
    }

    /**
     * Factory method for chaining.
     *
     * Example:
     *      EP\Email::make('johndou@examle.com', 'Activation for <{first_name} {last_name}>', 'Hello {first_name} {last_name}')
     *          ->setPlaceholders([
     *              '{first_name}' => 'John',
     *              '{last_name}' => 'Dou',
     *              '{activation_link}' => 'http://example.com/?action=activate&key=qweqwioeasdh23{2fsd3fdsf42)^&3&user=john',
     *          ])
     *          ->setReplyTo('replyto@example.com')
     *          ->setFromEmail('admin@example.com')
     *          ->setFromName('Administration')
     *          ->sendWhen(is_user_logged_in())
     *          ->onSuccess(function (EP\Email $email) {
     *              EP\Email::make('admin@examle.com', 'Activation link sent to <{first_name} {last_name}>', 'User: {first_name} {last_name} received the email.')
     *                  ->setPlaceholders($email->placeholders())->send();
     *          })->onFailure(function (EP\Email $email, WP_Error $reason) {
     *              // log error here
     *          });
     *
     * @param string $to Email where to sent to
     * @param string $subject Subject of the email
     * @param string $body An Email message
     * @param array $headers Optional. Headers for the email.
     * @param array $attachments Optional. Files to attach.
     * @param array $placeholders Optional. Key value pair that will be performed in the subject and Message.
     *
     * @return static
     * @throws Exception
     *
     */
    public static function make($to, $subject, $body, array $headers = [], array $attachments = [], array $placeholders = [])
    {
        return new static($to, $subject, $body, $headers, $attachments, $placeholders);
    }

    /**
     * @param array $attachments Optional. Files to attach.
     *
     * @return $this
     */
    public function setAttachments(array $attachments)
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * @param string $to Comma separated list of emails
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setTo($to)
    {
        $to = $this->sanitizeEmail($to);

        if (!$to) {
            throw new InvalidArgumentException('`$to` should contain an email address');
        }

        $this->to = apply_filters('pmld.email_to', $to, $this);

        return $this;
    }

    /**
     * @param string $cc Comma separated list of emails
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setCc($cc)
    {
        $cc = $this->sanitizeEmail($cc);

        if (!$cc) {
            throw new InvalidArgumentException('`$cc` should contain an email address');
        }

        $this->cc = apply_filters('pmld.email_cc', $cc, $this);

        return $this->addHeaders('cc: ' . $cc);
    }

    /**
     * @param string $bcc Comma separated list of emails
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setBcc($bcc)
    {
        $bcc = $this->sanitizeEmail($bcc);

        if (!$bcc) {
            throw new InvalidArgumentException('`$bcc` should contain an email address');
        }

        $this->bcc = apply_filters('pmld.email_bcc', $bcc, $this);

        return $this->addHeaders('bcc: ' . $bcc);
    }

    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Sets From Email.
     *
     * @param string $from_email
     *
     * @return $this
     */
    public function setFromEmail($from_email)
    {
        $from_email = $this->sanitizeEmail($from_email);

        if (!$from_email) {
            throw new InvalidArgumentException('`$from_email` should contain an email address');
        }

        $this->from_email = apply_filters('pmld.email_from_email', $from_email, $this);

        return $this;
    }

    /**
     * Gets From Email.
     *
     * @return string
     */
    public function fromEmail()
    {
        return $this->from_email ?: static::$always_from_email;
    }

    /**
     * Set the "From email", that would apply for all emails.
     *
     * @note be aware that the "$from_email" has a higher priority.
     *
     * @param string $email
     */
    public static function useAlwaysFromEmail($email)
    {
        static::$always_from_email = $email;
    }

    /**
     * Sets From Name.
     *
     * @param string $from_name
     *
     * @return $this
     */
    public function setFromName($from_name)
    {
        $from_name = sanitize_text_field($from_name);

        if (!$from_name) {
            throw new InvalidArgumentException('`$from_name` should contain a string');
        }

        $this->from_name = apply_filters('pmld.email_from_name', $from_name, $this);

        return $this;
    }

    /**
     * Set the "From name", that would apply for all emails.
     *
     * @note be aware that the "$from_name" has a higher priority.
     *
     * @param string $name
     */
    public static function useAlwaysFromName($name)
    {
        static::$always_from_name = $name;
    }

    /**
     * Gets From Name.
     *
     * @return string
     */
    public function fromName()
    {
        return $this->from_name ?: static::$always_from_name;
    }

    /**
     * Sets an filter that helps to change the `from_email` or `from_name`
     */
    public function setFilterFromForMailer()
    {
        if ($this->fromEmail()) {
            add_filter('wp_mail_from', [$this, 'fromEmail'], 500);
        }

        if ($this->fromName()) {
            add_filter('wp_mail_from_name', [$this, 'fromName'], 500);
        }
    }

    /**
     * Removes a filter that was meant to change `from_email` or `from_name`
     */
    protected function removeFilterFromForMailer()
    {
        if ($this->fromEmail()) {
            remove_filter('wp_mail_from', [$this, 'fromEmail'], 500);
        }

        if ($this->fromName()) {
            remove_filter('wp_mail_from_name', [$this, 'fromName'], 500);
        }
    }

    /**
     * @param string $reply_to Comma separated list of emails
     * @return $this
     */
    public function setReplyTo($reply_to)
    {
        $reply_to = $this->sanitizeEmail($reply_to);

        if (!$reply_to) {
            throw new InvalidArgumentException('`$reply_to` should contain an email address');
        }

        $this->reply_to = apply_filters('pmld.email_reply_to', $reply_to, $this);

        return $this->addHeaders('reply-to: ' . $this->reply_to);
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param string $charset
     * @return $this
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Adds the headers
     *
     * @param $header
     * @return $this
     */
    public function addHeaders($header)
    {
        $this->headers[] = $header;

        return $this;
    }

    /**
     * @param array $placeholders The list of placeholders with key value pair
     *
     * Example:
     *      [
     *          '{{first_name}}' => 'John',
     *          '{{last_name}}' => 'Dou',
     *          // ... etc
     *      ];
     *
     * @return $this
     */
    public function setPlaceholders(array $placeholders)
    {
        $this->placeholders = $placeholders;

        return $this;
    }

    /**
     * @return array
     */
    public function placeholders()
    {
        return apply_filters('pmld.email_placeholders', $this->placeholders, $this);
    }

    /**
     * Allow's to send letters such as ÖÕЁЙ etc.
     *
     * @hooked add_filter('wp_mail_charset')
     */
    protected function setCharsetForMailer()
    {
        mb_internal_encoding($this->charset);

        add_filter('wp_mail_charset', [$this, 'encodingHelperForMailer']);
    }

    /**
     * Helps to restore the charset for PHPMailer;
     *
     * @hooked remove_filter('wp_mail_charset')
     */
    protected function restoreCharsetForMailer()
    {
        global $phpmailer;

        $phpmailer->Encoding = $this->stored_php_mailer_charset;

        remove_filter('wp_mail_charset', [$this, 'encodingHelperForMailer']);
    }

    /**
     * @param WP_Error $error
     */
    public function performFailedAction(WP_Error $error)
    {
        $this->is_failed = true;

        foreach ($this->failed_listeners as $listener) {
            $listener($this, $error);
        }
    }

    /**
     * @return $this
     */
    protected function setFailedListeners()
    {
        add_action('wp_mail_failed', [$this, 'performFailedAction']);

        return $this;
    }

    /**
     * @return $this
     */
    protected function removeFailedListeners()
    {
        remove_action('wp_mail_failed', [$this, 'performFailedAction']);

        return $this;
    }

    /**
     * Gather together all filters and actions that need to be performed due to send process.
     *
     * @return void
     */
    protected function setHelperActionsBeforeSend()
    {
        $this->setFailedListeners();
        $this->setCharsetForMailer();
        $this->setFilterFromForMailer();

        if (!$this->is_sent) {
            foreach ($this->before_send_listeners as $listener) {
                $listener($this);
            }
        }

        do_action('pmld.email_before_send', $this);
    }

    /**
     * Remove all filters and actions after a mail being sent.
     *
     * @return void
     */
    protected function removeHelperActionsAfterSend()
    {
        $this->removeFailedListeners();
        $this->restoreCharsetForMailer();
        $this->removeFilterFromForMailer();

        if ($this->is_sent) {
            foreach ($this->after_sent_listeners as $listener) {
                $listener($this);
            }
        }

        do_action('pmld.email_after_send', $this);
    }

    /**
     * @param string $blog_charset
     * @return string
     */
    public function encodingHelperForMailer($blog_charset = 'UTF-8')
    {
        global $phpmailer;

        $charset = empty($this->charset) ? $blog_charset : $this->charset;

        $this->stored_php_mailer_charset = $phpmailer->Encoding;

        $phpmailer->Encoding = (!strcasecmp($charset, 'UTF-8') ? 'base64' : '8bit');

        return $charset;
    }

    /**
     * Load an email $to.
     *
     * @return string
     */
    public function to()
    {
        return apply_filters('pmld.email_to', $this->to, $this);
    }

    /**
     * Load an email template or return a default message.
     *
     * @return bool|false|string
     */
    public function body()
    {
        $body = nl2br(make_clickable($this->performPlaceholders($this->placeholders, $this->body)));

        $body = $this->charset
            ? mb_convert_encoding($body, $this->charset, 'auto')
            : $body;

        return apply_filters('pmld.email_body', $body, $this);
    }

    /**
     * Process the Email subject
     *
     * @return string
     */
    public function subject(): string
    {
        return apply_filters('pmld.email_subject', !empty($this->placeholders)
            ? $this->performPlaceholders($this->placeholders, $this->subject)
            : $this->subject, $this);
    }

    /**
     * @return array
     */
    public function attachments(): array
    {
        return apply_filters('pmld.email_attachments', array_unique($this->attachments), $this);
    }

    /**
     * @param array $placeholders
     * @param string $text
     *
     * @return string
     */
    protected function performPlaceholders(array $placeholders, $text): string
    {
        return str_replace(array_keys($placeholders), array_values($placeholders), $text);
    }

    /**
     * @return array
     */
    protected function headers(): array
    {
        if ($this->charset) {
            $this->addHeaders("Content-Type: text/html; charset={$this->charset}");
        }

        return apply_filters('pmld.email_headers', array_unique($this->headers), $this);
    }

    /**
     * @param string|array $emails Comma separated email or just an array of emails.
     *
     * @return string
     */
    public function sanitizeEmail($emails): string
    {
        return implode(
            ',',
            array_filter(
                array_map(
                    'trim',
                    is_array($emails) ? $emails : explode(',', $emails)
                ),
                'is_email'
            )
        );
    }

    /**
     * Sends an email
     *
     * @return $this
     */
    public function send()
    {
        $this->setHelperActionsBeforeSend();

        $email_args = [
            $this->to(),
            $this->subject(),
            $this->body(),
            $this->headers(),
            $this->attachments(),
        ];

        $this->is_sent = wp_mail(... $email_args);

        $this->removeHelperActionsAfterSend();

        return $this;
    }

    /**
     * @return bool
     */
    public function isSent()
    {
        return $this->is_sent;
    }

    /**
     * @return bool
     */
    public function isFailed()
    {
        return $this->is_failed;
    }

    /**
     * Sends an email when a specific action is fired.
     *
     * @param string $action
     * @param int $priority
     *
     * @return $this
     */
    public function sendWhenAction($action, $priority = 10)
    {
        if (!did_action($action)) {
            add_action($action, [$this, 'send'], $priority);
        }

        return $this;
    }

    /**
     * Sends an email when a specific action is fired.
     *
     * @param boolean | Closure $condition
     *
     * @return $this
     */
    public function sendWhen($condition)
    {
        if ($condition instanceof Closure) {
            $condition = $condition();
        }

        if ($condition) {
            $this->send();
        }

        return $this;
    }

    /**
     * @param Closure $cb
     * @return $this
     */
    public function beforeSend(Closure $cb)
    {
        $this->before_send_listeners[] = $cb;

        return $this;
    }

    /**
     * @param Closure $cb
     * @return $this
     */
    public function onSuccess(Closure $cb)
    {
        $this->after_sent_listeners[] = $cb;

        return $this;
    }

    /**
     * @param Closure $cb
     * @return $this
     */
    public function onFailure(Closure $cb)
    {
        $this->failed_listeners[] = $cb;

        return $this;
    }

    /**
     * Renders the Body of the email.
     */
    public function render()
    {
        echo $this->body();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->body();
    }
}
