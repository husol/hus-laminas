<?php
/**
 * Last modifier: khoaht
 * Last modified date: 26/09/18
 * Description: Use this class to deal with email
 */

namespace Core\Hus;

use Laminas\Mail\Message;
use Laminas\Mail\Transport\Smtp;
use Laminas\Mail\Transport\SmtpOptions;

class HusEmail
{
  private $transport = null;
  private $message = null;

  public function __construct($smtpOptions)
  {
    $this->transport = new Smtp();
    $options = new SmtpOptions($smtpOptions);
    $this->transport->setOptions($options);

    $this->message = new Message();
    $this->message->setEncoding('UTF-8');
  }

  public function setFrom($from = [])
  {
    $key = key($from);
    $alias = is_string($key) ? $key : null;
    $this->message->addFrom($from[$key], $alias);
    $this->message->getFrom();
  }

  public function setTo($to = [])
  {
    foreach ($to as $key => $email) {
      $alias = is_string($key) ? $key : null;
      $this->message->addTo($email, $alias);
    }
  }

  public function setSubject($subject)
  {
    $this->message->setSubject($subject);
  }

  public function setCc($cc = [])
  {
    foreach ($cc as $key => $email) {
      $alias = null;
      if (is_string($key)) {
        $alias = $key;
      }
      $this->message->addCc($email, $alias);
    }
  }

  public function setBcc($bcc = [])
  {
    foreach ($bcc as $key => $email) {
      $alias = null;
      if (is_string($key)) {
        $alias = $key;
      }
      $this->message->addBcc($email, $alias);
    }
  }

  public function setReplyTo($replyTo)
  {
    $key = key($replyTo);
    $alias = is_string($key) ? $key : null;
    $this->message->addFrom($replyTo[$key], $alias);
  }

  public function send($content)
  {
    if (empty($this->message->getFrom())) {
      return [
        'status' => false,
        'message' => 'From Email is required.'
      ];
    }
    if (empty($this->message->getTo())) {
      return [
        'status' => false,
        'message' => 'To Emails is required.'
      ];
    }
    if (empty($this->message->getSubject())) {
      return [
        'status' => false,
        'message' => 'Subject Email is required.'
      ];
    }

    $this->message->setBody($content);
    $this->transport->send($this->message);

    return [
      'status' => true,
      'result' => 'SUCCESS'
    ];
  }
}
