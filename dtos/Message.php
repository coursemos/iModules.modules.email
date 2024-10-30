<?php
/**
 * 이 파일은 아이모듈 이메일모듈 일부입니다. (https://www.imodules.io)
 *
 * 메시지 구조체를 정의한다.
 *
 * @file /modules/email/dtos/Message.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 10. 30.
 */
namespace modules\email\dtos;
class Message
{
    /**
     * @var string $_id 메시지고유값
     */
    private string $_id;

    /**
     * @var \modules\email\dtos\Address $_to 수신자
     */
    private \modules\email\dtos\Address $_to;

    /**
     * @var \modules\email\dtos\Address $_to 전송자
     */
    private \modules\email\dtos\Address $_from;

    /**
     * @var int $_member_id 수신자회원고유값
     */
    private int $_member_id;

    /**
     * @var string $_email 수신자메일주소
     */
    private string $_email;

    /**
     * @var string $_name 수신자명
     */
    private string $_name;

    /**
     * @var string $_component_type 컴포넌트종류
     */
    private string $_component_type;

    /**
     * @var string $_component_name 컴포넌트명
     */
    private string $_component_name;

    /**
     * @var string $_title 타이틀
     */
    private string $_title;

    /**
     * @var string $_content 본문내용
     */
    private string $_content;

    /**
     * @var object $_template 메일템플릿
     */
    private object $_template;

    /**
     * @var int $_sended_at 발송일시
     */
    private int $_sended_at;

    /**
     * @var ?int $_checked_at 확인일시
     */
    private ?int $_checked_at;

    /**
     * @var string $_status 발송상태
     */
    private string $_status;

    /**
     * @var ?string $_response 발송응답내용
     */
    private ?string $_response;

    /**
     * 메시지 구조체를 정의한다.
     *
     * @param object $message 메시지정보
     */
    public function __construct(object $message)
    {
        /**
         * @var \modules\email\Email $mEmail
         */
        $mEmail = \Modules::get('email');

        $this->_id = $message->message_id;
        $this->_to = $mEmail->getAddress($message->email, $message->name, $message->member_id);
        $this->_from = $mEmail->getAddress($message->sended_email, $message->sended_name, $message->sended_by);
        $this->_component_type = $message->component_type;
        $this->_component_name = $message->component_name;
        $this->_title = $message->title;
        $this->_content = $message->content;
        $this->_template = json_decode($message->template);
        $this->_sended_at = $message->sended_at;
        $this->_checked_at = $message->checked_at;
        $this->_status = $message->status;
        $this->_response = $message->response;
    }

    /**
     * 고유값을 가져온다.
     *
     * @return string $id
     */
    public function getId(): string
    {
        return $this->_id;
    }

    /**
     * 확인일시를 가져온다.
     *
     * @return ?int $checked_at
     */
    public function getCheckedAt(): ?int
    {
        return $this->_checked_at;
    }

    /**
     * 템플릿이 적용된 컨텐츠를 가져온다.
     *
     * @param bool $is_template 템플릿포함여부
     * @return string $content
     */
    public function getContent(bool $is_template = false): string
    {
        if ($is_template == true) {
            /**
             * @var \modules\email\Email $mEmail
             */
            $mEmail = \Modules::get('email');
            $site = \Sites::get();
            $template = $mEmail->getTemplate($this->_template ?? $mEmail->getConfigs('template'));

            // @todo 발송한 사이트를 저장한 뒤 실제로 발송한 사이트 정보로 대치
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
            $template->assign('content', $this->_content);

            return $template->getLayout();
        }

        return $this->_content;
    }

    /**
     * JSON 으로 변환한다.
     *
     * @param bool $is_content 메시지본문 포함여부
     * @return object $message
     */
    public function getJson(bool $is_content = false): object
    {
        $message = new \stdClass();
        $message->message_id = $this->_id;
        $message->to = $this->_to->getJson();
        $message->from = $this->_from->getJson();
        $message->component_type = $this->_component_type;
        $message->component_name = $this->_component_name;
        $message->title = $this->_title;
        if ($is_content === true) {
            $message->content = $this->getContent(true);
        }
        $message->sended_at = $this->_sended_at;
        $message->checked_at = $this->_checked_at;
        $message->status = $this->_status;
        $message->response = $this->_response;

        return $message;
    }
}
