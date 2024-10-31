<?php
/**
 * 이 파일은 아이모듈 이메일모듈 일부입니다. (https://www.imodules.io)
 *
 * 이메일 발송 내역 디테일을 가져온다.
 *
 * @file /modules/email/processes/message.get.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 11. 1.
 *
 * @var \modules\email\Email $me
 */
if (defined('__IM_PROCESS__') == false) {
    exit();
}

/**
 * 관리자권한이 존재하는지 확인한다.
 */
if ($me->getAdmin()->checkPermission('messages') == false) {
    $results->success = false;
    $results->message = $me->getErrorText('FORBIDDEN');
    return;
}

$message_id = Request::get('message_id', true);
$message = $me->getMessage($message_id);
if ($message === null) {
    $results->success = false;
    $results->message = $me->getErrorText('NOT_FOUND_DATA');
    return;
}

$results->success = true;
$results->data = $message->getJson(true);
