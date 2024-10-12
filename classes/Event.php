<?php
/**
 * 이 파일은 아이모듈 이메일모듈 일부입니다. (https://www.imodules.io)
 *
 * 이메일모듈의 이벤트목록을 정의한다.
 *
 * @file /modules/email/classes/Event.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 10. 11.
 */
namespace modules\email;
class Event extends \Listeners
{
    /**
     * 이메일을 전송할 때 발생한다.
     *
     * @param \modules\email\Sender $sender 이메일을 보내고 있는 전송자 객체
     * @param int $sended_at 전송시각
     * @return bool|string|null $success 발송성공여부 또는 실패메시지 (NULL 인 경우, 다른 이벤트리스너나 이메일 모듈에서 이어서 발송한다.)
     */
    public static function send(\modules\email\Sender $sender, int $sended_at): bool|string|null
    {
        return null;
    }
}
