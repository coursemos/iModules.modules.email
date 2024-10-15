<?php
/**
 * 이 파일은 이메일 모듈의 일부입니다. (https://www.coursemos.co.kr)
 *
 * @todo 삭제 예정
 *
 * @file /modules/email/processes/emailtest.get.php
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

/**
 * @var \modules\push\Push $mPush
 */
$mPush = \Modules::get('push');
$mPush
    ->getSender(Modules::get('naddle/desk'))
    ->setTo(0, '박병주', 'ju318@naddle.net', '01020181315')
    ->setTarget('issue', '21587590-86d3-11ef-9dce-614044d65abd')
    ->setContent('status', ['status' => 'ACCEPT'])
    ->send();

exit();
