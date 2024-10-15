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
if ($me->getAdmin()->checkPermission('messages') == false) {
    $results->success = false;
    $results->message = $me->getErrorText('FORBIDDEN');
    return;
}

$sorters = Request::getJson('sorters');
$start = Request::getInt('start') ?? 0;
$limit = Request::getInt('limit') ?? 50;
$filters = Request::getJson('filters');
$keyword = Request::get('keyword');
$results->keyword = $keyword;

$records = $me
    ->db()
    ->select()
    ->from($me->table('messages'));

if ($filters !== null) {
    $records->setFilters($filters, 'AND', [
        'status' => 'status',
    ]);
}

if ($keyword !== null) {
    $records->where('(name like ? or email like ?)', ['%' . $keyword . '%', '%' . $keyword . '%']);
}

$message_id = request::get('message_id');
if ($message_id !== null) {
    //todo view 필요없다면 없애기
    $masasage = $records
        ->copy()
        ->where('message_id', $message_id)
        ->getone();
    if ($masasage === null) {
        $results->success = true;
        $results->page = -1;
        return;
    } else {
        foreach ($sorters as $field => $direction) {
            $records->where($field, $masasage->{$field}, $direction == 'ASC' ? '<=' : '>=');
        }
        $results->success = true;
        $results->page = $limit !== null ? ceil($records->count() / $limit) : 1;
        return;
    }
}

if ($sorters !== null) {
    foreach ($sorters as $field => $direction) {
        $records->orderBy($field, $direction);
    }
    if (isset($sorters->sended_at) == false) {
        $records->orderBy('title', 'ASC');
    }
}

$total = $records->copy()->count();
$records = $records->limit($start, $limit)->get();

if ($records === null) {
    $results->success = true;
    $results->message = $me->getErrorText('NOT_FOUND_DATA');
    return;
}

$results->success = true;
$results->records = $records;
$results->total = $total;
