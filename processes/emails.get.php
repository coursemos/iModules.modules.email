<?php
/**
 * 이 파일은 이메일 모듈의 일부입니다. (https://www.coursemos.co.kr)
 *
 * 이메일 발송 내역을 가져온다.
 *
 * @file /modules/email/processes/emails.get.php
 * @author ju318 <ju318@naddle.net>
 * @license MIT License
 * @modified 2024. 10. 14.
 *
 * @var \modules\naddle\desk\Desk $me
 */
if (defined('__IM_PROCESS__') == false) {
    exit();
}

/**
 * 관리자권한이 존재하는지 확인한다.
 */
if ($me->getAdmin()->checkPermission('emails') == false) {
    $results->success = false;
    $results->message = $me->getErrorText('FORBIDDEN');
    return;
}

$results->success = true;
$results->records = $me
    ->db()
    ->select()
    ->from($me->table('messages'))
    ->get();
