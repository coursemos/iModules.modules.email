<?php
/**
 * 이 파일은 아이모듈 이메일모듈 일부입니다. (https://www.imodules.io)
 *
 * 이메일 확인 시간을 업데이트한다.
 *
 * @file /modules/email/apis/checked.get.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 11. 4.
 *
 * @var \modules\email\Email $me
 */
$message_id = $path;
if (
    preg_match(
        '/^([0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12})\.png$/i',
        $path,
        $matched
    ) == true
) {
    $message_id = $matched[1];
    if ($me->getMessage($message_id)?->getCheckedAt() === null) {
        $me->db()
            ->update($me->table('messages'), ['checked_at' => time()])
            ->where('message_id', $message_id)
            ->execute();
    }
}

Header::type('image/png');
readfile($me->getPath() . '/images/t.png');
exit();
