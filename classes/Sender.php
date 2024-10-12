<?php
/**
 * 이 파일은 아이모듈 이메일모듈 일부입니다. (https://www.imodules.io)
 *
 * 이메일 전송자 클래스를 정의한다.
 *
 * @file /modules/email/classes/Sender.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 10. 11.
 */
namespace modules\email;
class Sender
{
    /**
     * @var \Component $_component 이메일을 전송하는 컴포넌트 객체
     */
    private \Component $_component;

    /**
     * @var \modules\email\dtos\Address $_to 받는사람
     */
    private \modules\email\dtos\Address $_to;

    /**
     * @var ?\modules\email\dtos\Message $_message 전송할 메시지
     */
    private ?\modules\email\dtos\Message $_message = null;

    /**
     * 이메일 전송자 클래스를 정의한다.
     *
     * @param \Component $component 메일을 전송하는 컴포넌트 객체
     */
    public function __construct(\Component $component)
    {
        $this->_component = $component;
    }

    /**
     * 받는 사람을 설정한다.
     *
     * @param \modules\email\dtos\Address $to
     * @return \modules\email\Sender $this
     */
    public function setTo(\modules\email\dtos\Address $to): \modules\email\Sender
    {
        $this->_to = $to;
        return $this;
    }

    /**
     * 받는 사람을 가져온다.
     *
     * @return \modules\email\dtos\Address $to
     */
    public function getTo(): \modules\email\dtos\Address
    {
        return $this->_to;
    }

    /**
     * 이메일 메시지를 설정한다.
     *
     * @param string $content 본문내용
     * @param bool $is_html HTML 여부
     * @return \modules\email\Sender $this
     */
    public function setMessage(\modules\email\dtos\Message $message): \modules\email\Sender
    {
        $this->_message = $message;
        return $this;
    }

    /**
     * 메시지를 가져온다.
     *
     * @return ?\modules\email\dtos\Message $message
     */
    public function getMessage(): ?\modules\email\dtos\Message
    {
        return $this->_message;
    }

    /**
     * 메일을 전송한다.
     *
     * @param ?int $sended_at - 전송시각(NULL 인 경우 현재시각)
     * @return bool $success 성공여부
     */
    public function send(int $sended_at = null): bool
    {
        if (isset($this->_to) == false || $this->_message == null) {
            return false;
        }

        /**
         * @var \modules\email\Email $mEmail
         */
        $mEmail = \Modules::get('email');

        $sended_at ??= time();

        $success = \Event::fireEvent($mEmail, 'send', [$this, $sended_at], 'NOTNULL');

        if ($success === null) {
            require_once $mEmail->getPath() . '/vendor/PHPMailer/src/Exception.php';
            require_once $mEmail->getPath() . '/vendor/PHPMailer/src/PHPMailer.php';
            require_once $mEmail->getPath() . '/vendor/PHPMailer/src/SMTP.php';

            $PHPMailer = new \PHPMailer\PHPMailer\PHPMailer(true);

            try {
                $PHPMailer->isSMTP();
                $PHPMailer->Encoding = 'base64';
                $PHPMailer->CharSet = 'UTF-8';
                $PHPMailer->Host = $mEmail->getConfigs('smtp_host');
                $PHPMailer->SMTPAuth = $mEmail->getConfigs('smtp_id') && $mEmail->getConfigs('smtp_password');
                if ($PHPMailer->SMTPAuth == true) {
                    $PHPMailer->Username = $mEmail->getConfigs('smtp_id');
                    $PHPMailer->Password = $mEmail->getConfigs('smtp_password');
                }

                if ($mEmail->getConfigs('smtp_secure') != 'NONE') {
                    $PHPMailer->SMTPSecure = $mEmail->getConfigs('smtp_secure');
                }
                $PHPMailer->Port = intval($mEmail->getConfigs('smtp_port'), 10);

                $PHPMailer->setFrom(
                    $this->_message->getFrom()->getAddress(),
                    $this->_message->getFrom()->getName() ?? ''
                );

                foreach ($this->_message->getReplyTo() as $address) {
                    $PHPMailer->addReplyTo($address->getAddress(), $address->getName() ?? '');
                }

                $PHPMailer->addAddress($this->_to->getAddress(), $this->_to->getName() ?? '');

                $PHPMailer->isHTML(true);
                $PHPMailer->Subject = $this->_message->getTitle(true);
                $PHPMailer->Body = $this->_message->getContent(true, true);

                $success = $PHPMailer->send();
            } catch (\PHPMailer\PHPMailer\Exception $e) {
                $success = $e->getMessage() . ' ' . $PHPMailer->ErrorInfo;
            }
        }

        if ($this->_message->getId() === null) {
            $message_id = \UUID::v1($this->_message->getTitle());

            $replyTo = [];
            foreach ($this->_message->getReplyTo() as $address) {
                $replyTo = $address->getJson();
            }

            $mEmail
                ->db()
                ->insert($mEmail->table('messages'), [
                    'message_id' => $message_id,
                    'from_address' => $this->_message->getFrom()->getAddress(),
                    'from_name' => $this->_message->getFrom()->getName(),
                    'from_member_id' => $this->_message->getFrom()->getMemberId(),
                    'reply_to' => \Format::toJson($replyTo),
                    'title' => $this->_message->getTitle(),
                    'content' => $this->_message->getContent(),
                    'template' => \Format::toJson($this->_message->getTemplate()),
                ])
                ->execute();
        }

        $mEmail
            ->db()
            ->insert($mEmail->table('emails'), [
                'email_id' => \UUID::v1($this->_to->getAddress()),
                'to_address' => $this->_to->getAddress(),
                'to_name' => $this->_to->getName(),
                'to_member_id' => $this->_to->getMemberId(),
                'message_id' => $message_id,
                'sended_at' => $sended_at,
                'status' => $success === true ? 'TRUE' : 'FALSE',
                'response' => is_bool($success) == false ? \Format::toJson($success) : null,
            ])
            ->execute();

        return $success;
    }
}
