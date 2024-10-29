<?php
/**
 * 이 파일은 아이모듈 이메일모듈 일부입니다. (https://www.imodules.io)
 *
 * 이메일모듈 클래스 정의한다.
 *
 * @file /modules/email/Email.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 10. 11.
 */
namespace modules\email;
class Email extends \Module
{
    /**
     * @var \modules\email\dtos\Message[] $_messages 이슈
     */
    private static array $_messages = [];

    /**
     * 이메일을 전송하기 위한 전송자 클래스를 가져온다.
     *
     * @param \Component $component 이메일을 전송하는 컴포넌트 객체
     * @return \modules\email\Sender $sender
     */
    public function getSender(\Component $component): \modules\email\Sender
    {
        return new \modules\email\Sender($component);
    }

    /**
     * 이메일주소 구조체를 가져온다.
     *
     * @param string $address 이메일주소
     * @param ?string $name 이름
     * @param ?int $member_id 회원고유값
     * @return \modules\email\dtos\Address $address 이메일주소 구조체
     */
    public function getAddress(
        string $address,
        ?string $name = null,
        ?int $member_id = null
    ): \modules\email\dtos\Address {
        return new \modules\email\dtos\Address($address, $name, $member_id);
    }

    /**
     * 회원정보를 통해 이메일주소 구조체를 가져온다.
     *
     * @param int $member_id 회원고유값
     * @return \modules\email\dtos\Address $address 이메일주소 구조체
     */
    public function getAddressFromMember(int $member_id): \modules\email\dtos\Address
    {
        /**
         * @var \modules\member\Member $mMember
         */
        $mMember = \Modules::get('member');
        $member = $mMember->getMember($member_id);
        if ($member->getId() === 0) {
            \ErrorHandler::print($this->error('NOT_FOUND_MEMBER'));
        }

        return new \modules\email\dtos\Address($member->getEmail(), $member->getDisplayName(false), $member->getId());
    }

    /**
     * 메시지를 가져온다.
     *
     * @param string $message_id 메시지아이디
     * @return ?\modules\email\dtos\Message $message
     */
    public function getMessage(string $message_id): ?\modules\email\dtos\Message
    {
        $message = $this->db()
            ->select()
            ->from($this->table('messages'))
            ->where('message_id', $message_id)
            ->getOne();

        if ($message == null) {
            self::$_messages[$message_id] = null;
        } else {
            self::$_messages[$message_id] = new \modules\email\dtos\Message($message);
        }

        return self::$_messages[$message_id];
    }

    /**
     * 특수한 에러코드의 경우 에러데이터를 현재 클래스에서 처리하여 에러클래스로 전달한다.
     *
     * @param string $code 에러코드
     * @param ?string $message 에러메시지
     * @param ?object $details 에러와 관련된 추가정보
     * @return \ErrorData $error
     */
    public function error(string $code, ?string $message = null, ?object $details = null): \ErrorData
    {
        switch ($code) {
            case 'NOT_FOUND_MEMBER':
                $error = \ErrorHandler::data($code, $this);
                $error->message = $this->getErrorText('NOT_FOUND_MEMBER');
                return $error;

            default:
                return parent::error($code, $message, $details);
        }
    }
}
