<?php
/**
 * 이 파일은 아이모듈 이메일모듈 일부입니다. (https://www.imodules.io)
 *
 * 메시지 구조체를 정의한다.
 *
 * @file /modules/email/dtos/Message.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 10. 11.
 */
namespace modules\email\dtos;
class Message
{
    /**
     * @var ?string $_message_id 메시지고유값
     */
    private ?string $_message_id = null;

    /**
     * @var ?\modules\email\dtos\Address $_from 보내는사람
     */
    private ?\modules\email\dtos\Address $_from = null;

    /**
     * @var \modules\email\dtos\Address[] $_reply_to 답장받는사람
     */
    private array $_reply_to = [];

    /**
     * @var ?string $_title 메일제목
     */
    private ?string $_title = null;

    /**
     * @var ?string $_content 메일내용
     */
    private ?string $_content = null;

    /**
     * @var ?object $_template 템플릿설정
     */
    private ?object $_template = null;

    /**
     * 메시지 구조체를 정의한다.
     *
     * @param ?object $message
     */
    public function __construct(?object $message = null)
    {
        if ($message !== null) {
            $this->_message_id = $message->message_id;
            $this->_from = new \modules\email\dtos\Address(
                $message->from_address,
                $message->from_name,
                $message->from_member_id
            );
            $this->_reply_to = json_decode($message->reply_to);
            foreach ($this->_reply_to as &$reply_to) {
                $reply_to = new \modules\email\dtos\Address($reply_to->address, $reply_to->name, $reply_to->member_id);
            }
            $this->_title = $message->title;
            $this->_content = $message->content;
            $this->_template = $message->_template;
        }
    }

    /**
     * 메시지고유값을 가져온다.
     *
     * @return ?string $message_id
     */
    public function getId(): ?string
    {
        return $this->_message_id;
    }

    /**
     * 보내는 사람을 설정한다.
     *
     * @param \modules\email\dtos\Address $from
     * @return \modules\email\dtos\Message $this
     */
    public function setFrom(\modules\email\dtos\Address $from): \modules\email\dtos\Message
    {
        $this->_from = $from;
        return $this;
    }

    /**
     * 보내는 사람을 가져온다.
     *
     * @return \modules\email\dtos\Address $from
     */
    public function getFrom(): \modules\email\dtos\Address
    {
        if ($this->_from === null) {
            /**
             * @var \modules\email\Email $mEmail
             */
            $mEmail = \Modules::get('email');
            $this->_from = $mEmail->getAddress(
                $mEmail->getConfigs('default_from_address'),
                $mEmail->getConfigs('default_from_name')
            );
        }

        return $this->_from;
    }

    /**
     * 답장받는 사람을 추가한다.
     *
     * @param \modules\email\dtos\Address $reply_to
     * @return \modules\email\dtos\Message $this
     */
    public function addReplyTo(\modules\email\dtos\Address $reply_to): \modules\email\dtos\Message
    {
        $this->_reply_to[] = $reply_to;
        return $this;
    }

    /**
     * 답장받는사람을 가져온다.
     *
     * @return \modules\email\dtos\Address[] $reply_to
     */
    public function getReplyTo(): array
    {
        return $this->_reply_to;
    }

    /**
     * 제목을 설정한다.
     *
     * @param string $content 본문내용
     * @param bool $is_html HTML 여부
     * @return \modules\email\dtos\Message $this
     */
    public function setTitle(string $title): \modules\email\dtos\Message
    {
        $this->_title = $title;
        return $this;
    }

    /**
     * 본문내용을 설정한다.
     *
     * @param string $content 본문내용
     * @param bool $is_html HTML 여부
     * @return \modules\email\dtos\Message $this
     */
    public function setContent(string $content, bool $is_html = true): \modules\email\dtos\Message
    {
        if ($is_html == false) {
            $content = nl2br($content);
        }

        $this->_content = $content;
        return $this;
    }

    /**
     * 메일템플릿을 설정한다.
     *
     * @param ?object $template 템플릿설정
     * @return \modules\email\dtos\Message $this
     */
    public function setTemplate(?object $template): \modules\email\dtos\Message
    {
        $this->_template = $template;
        return $this;
    }

    /**
     * 메일템플릿설정을 가져온다.
     *
     * @return object $template 템플릿설정
     */
    public function getTemplate(): object
    {
        /**
         * @var \modules\email\Email $mEmail
         */
        $mEmail = \Modules::get('email');
        return $this->_template ?? $mEmail->getConfigs('template');
    }

    /**
     * 제목을 가져온다.
     *
     * @param bool $is_encode 이메일표준에 의한 UTF-8 인코딩을 할지 여부
     * @return string $title
     */
    public function getTitle(bool $is_encode = false): string
    {
        if ($is_encode == true) {
            return '=?UTF-8?b?' . base64_encode($this->_title) . '?=';
        } else {
            return $this->_title;
        }
    }

    /**
     * 본문을 가져온다.
     *
     * @param bool $is_template 이메일 발송을 위한 템플릿을 포함한 내용을 가져올지 여부
     * @return string $content
     */
    public function getContent(bool $is_template = false): string
    {
        $content = $this->_content;

        if ($is_template == true) {
            /**
             * @var \modules\email\Email $mEmail
             */
            $mEmail = \Modules::get('email');
            $site = \Sites::get();
            $template = $mEmail->getTemplate($this->_template ?? $mEmail->getConfigs('template'));
            $template->assign(
                'logo',
                $site->getLogo()?->getUrl('view', true) ??
                    \Domains::get()->getUrl() . \Configs::dir() . '/images/logo.png'
            );
            $template->assign(
                'emblem',
                $site->getEmblem()?->getUrl('view', true) ??
                    \Domains::get()->getUrl() . \Configs::dir() . '/images/emblem.png'
            );
            $template->assign('url', $site->getUrl());
            $template->assign('content', $content);

            $style = file_get_contents($mEmail->getPath() . '/styles/email.css');
            $style = preg_replace('/\/\*(.|\n)*?\*\//', '', $style);
            $style = preg_replace('/(\n|\r\n|    )/', '', $style);

            $content = \Html::tag(
                '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
                '<html xmlns="http://www.w3.org/1999/xhtml">',
                '<head>',
                '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />',
                '<meta name="viewport" content="width=device-width, initial-scale=1.0" />',
                '<style type="text/css">',
                $style,
                '</style>',
                '</head>',
                '<body style="width: 100% !important; height: 100% !important; margin: 0; padding: 0; background: #f4f4f4; font-family: \'Apple SD Gothic Neo\', \'malgun gothic\', Helvetica, Georgia, Arial, sans-serif !important;">',
                $template->getLayout(),
                '</body>',
                '</html>'
            );
        }

        return $content;
    }
}
