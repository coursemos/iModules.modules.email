<?php
/**
 * 이 파일은 아이모듈 이메일모듈 일부입니다. (https://www.imodules.io)
 *
 * 이메일 주소 구조체를 정의한다.
 *
 * @file /modules/email/dtos/Address.php
 * @author youlapark <youlapark@naddle.net>
 * @license MIT License
 * @modified 2024. 10. 30.
 */
namespace modules\email\dtos;
class Address
{
    /**
     * @var string $_address 이메일주소
     */
    private string $_address;

    /**
     * @var string $_name 이름
     */
    private ?string $_name;

    /**
     * @var int $_member_id 회원고유값
     */
    private int $_member_id;

    /**
     * @var \modules\member\dtos\Member $_member 회원정보
     */
    private \modules\member\dtos\Member $_member;

    /**
     * 이메일 구조체를 정의한다.
     *
     * @param string $address 이메일주소
     * @param ?string $name 이름
     * @param ?int $member_id 회원고유값
     */
    public function __construct(string $address, ?string $name = null, ?int $member_id = null)
    {
        $this->_address = $address;
        $this->_name = $name;
        $this->_member_id = $member_id ?? 0;
    }

    /**
     * 이메일주소를 가져온다.
     *
     * @param string $address
     */
    public function getAddress(): string
    {
        return $this->_address;
    }

    /**
     * 이름을 가져온다.
     *
     * @param bool $is_encode 이메일표준에 의한 UTF-8 인코딩을 할지 여부
     * @return ?string $name
     */
    public function getName(bool $is_encode = false): ?string
    {
        if ($this->_name === null) {
            return null;
        }

        if ($is_encode == true) {
            return '=?UTF-8?b?' . base64_encode($this->_name) . '?=';
        } else {
            return $this->_name;
        }
    }

    /**
     * 회원고유값을 가져온다.
     *
     * @return ?int $member_id
     */
    public function getMemberId(): ?int
    {
        return $this->_member_id;
    }

    /**
     * 회원정보를 가져온다.
     *
     * @return \modules\member\dtos\Member $member
     */
    public function getMember(): \modules\member\dtos\Member
    {
        if (isset($this->_member) == false) {
            /**
             * @var \modules\member\Member $mMember
             */
            $mMember = \Modules::get('member');
            $this->_member = $mMember->getMember($this->_member_id)->setNicknamePlaceHolder($this->_name);
        }

        return $this->_member;
    }

    /**
     * JSON 으로 변환한다.
     *
     * @return object $json
     */
    public function getJson(): object
    {
        $address = new \stdClass();
        $address->address = $this->_address;
        $address->name = $this->_name;
        $address->member = $this->getMember()->getJson();

        return $address;
    }
}
