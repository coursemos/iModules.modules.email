<?php
/**
 * 이 파일은 아이모듈 이메일모듈 일부입니다. (https://www.imodules.io)
 *
 * 이메일 구조체를 정의한다.
 *
 * @file /modules/email/dto/Email.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2023. 6. 10.
 */
namespace modules\email\dtos;
class Email
{
    /**
     * @var string $_uuid 이메일고유값
     */
    private string $_uuid;

    /**
     * @var string $_title 메일제목
     */
    private string $_title;

    /**
     * @var string $_content 메일내용
     */
    private string $_content;

    /**
     * @var string $_is_html 본문 HTML 여부
     */
    private bool $_is_html;

    /**
     * private ?\modules\email\dtos\Address $_from 보내는사람
     */
    private ?\modules\email\dtos\Address $_from;

    /**
     * private \modules\email\dtos\Address[] $_reply_to 답장받는사람
     */
    private array $_reply_to;

    /**
     * private \modules\email\dtos\Address[] $_address 받는사람
     */
    private array $_address;

    /**
     * private \modules\email\dtos\Address[] $_cc 참조
     */
    private array $_cc;

    /**
     * private \modules\email\dtos\Address[] $_bcc 숨은참조
     */
    private array $_bcc;

    /**
     * 이메일 구조체를 정의한다.
     *
     * @param string|object $email string 인 경우 신규메일의 제목, object 인 경우 기존 발송된 메일내역
     */
    public function __construct(string|object $email)
    {
        if (is_string($email) == true) {
            $this->_uuid = \UUID::v1($email);
            $this->_title = $email;
            $this->_content = '';
            $this->_is_html = false;
            $this->_from = null;
            $this->_reply_to = [];
            $this->_address = [];
            $this->_cc = [];
            $this->_bcc = [];
        }
    }

    /**
     * 보내는 사람을 설정한다.
     *
     * @param \modules\email\dtos\Address $from
     * @return $this
     */
    public function setFrom(\modules\email\dtos\Address $from): self
    {
        $this->_from = $from;
        return $this;
    }

    /**
     * 답장받는 사람을 추가한다.
     *
     * @param \modules\email\dtos\Address $reply_to
     * @return $this
     */
    public function addReplyTo(\modules\email\dtos\Address $reply_to): self
    {
        $this->_reply_to[] = $reply_to;
        return $this;
    }

    /**
     * 받는 사람을 추가한다.
     *
     * @param \modules\email\dtos\Address $address
     * @return $this
     */
    public function addAddress(\modules\email\dtos\Address $address): self
    {
        $this->_address[] = $address;
        return $this;
    }

    /**
     * 참조를 추가한다.
     *
     * @param \modules\email\dtos\Address $to
     * @return $this
     */
    public function addCC(\modules\email\dtos\Address $cc): self
    {
        $this->_cc[] = $cc;
        return $this;
    }

    /**
     * 숨은 참조를 추가한다.
     *
     * @param \modules\email\dtos\Address $bcc
     * @return $this
     */
    public function addBCC(\modules\email\dtos\Address $bcc): self
    {
        $this->_bcc[] = $bcc;
        return $this;
    }

    /**
     * 본문내용을 설정한다.
     *
     * @param string $content 본문내용
     * @param bool $is_html HTML 여부
     * @return $this
     */
    public function setContent(string $content, bool $is_html = false): self
    {
        $this->_content = $content;
        $this->_is_html = $is_html;
        return $this;
    }

    /**
     * 발송자를 가져온다.
     *
     * @return ?\modules\email\dtos\Address $from
     */
    public function getFrom(): ?\modules\email\dtos\Address
    {
        return $this->_from;
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
     * 수신자목록을 가져온다.
     *
     * @return \modules\email\dtos\Address[] $address
     */
    public function getAddress(): array
    {
        return $this->_address;
    }

    /**
     * 참조목록을 가져온다.
     *
     * @return \modules\email\dtos\Address[] $cc
     */
    public function getCC(): array
    {
        return $this->_cc;
    }

    /**
     * 숨은참조목록을 가져온다.
     *
     * @return \modules\email\dtos\Address[] $bcc
     */
    public function getBCC(): array
    {
        return $this->_bcc;
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
     * @param bool $is_encode 이메일표준에 의한 UTF-8 인코딩을 할지 여부
     * @return string $content
     */
    public function getContent(bool $is_encode = false): string
    {
        if ($this->_is_html == true) {
            $content = $this->_content;
        } else {
            $content = nl2br($this->_content);
        }

        if ($is_encode == true) {
            return '=?UTF-8?b?' . $content . '?=';
        } else {
            return $content;
        }
    }
}
