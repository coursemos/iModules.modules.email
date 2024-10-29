<?php
/**
 * 이 파일은 서비스데스크 모듈의 일부입니다. (https://www.coursemos.co.kr)
 *
 * 이메일 확인 시간을 업데이트한다.
 *
 * @file /modules/email/apis/read.php
 * @author pbj <ju318@ubion.co.kr>
 * @license MIT License
 * @modified 2024. 10. 15.
 *
 * @var \modules\email\Email $me
 */

//@todo 보냄과 동시에 읽어지는지 등등 이슈 확인/처리

$message_id = Request::get('id', true);

if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $message_id)) {
    if ($me->getMessage($message_id)?->getCheckedAt() === null) {
        $me->db()
            ->update($me->table('messages'), ['checked_at' => time()])
            ->where('message_id', $message_id)
            ->execute();
    }
}

exit();
