<?php
/**
 * 이 파일은 아이모듈 이메일모듈 일부입니다. (https://www.imodules.io)
 *
 * 이메일모듈 클래스 정의한다.
 *
 * @file /modules/email/Email.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2023. 6. 10.
 */
namespace modules\email;

use Template;

class Email extends \Module
{
    /**
     * 이메일 구조체를 생성한다.
     *
     * @param string $title 이메일제목
     * @return \modules\email\dto\Email $email
     */
    public function createEmail(string $title): \modules\email\dto\Email
    {
        return new \modules\email\dto\Email($title);
    }

    /**
     * 이메일주소 구조체를 가져온다.
     *
     * @param string $address 이메일주소
     * @param ?string $name 이름
     * @return \modules\email\dto\Address $address 이메일주소 구조체
     */
    public function getAddress(string $address, ?string $name = null): \modules\email\dto\Address
    {
        return new \modules\email\dto\Address($address, $name);
    }

    /**
     * 회원정보를 통해 이메일주소 구조체를 가져온다.
     *
     * @param int $member_id 회원고유값
     * @return \modules\email\dto\Address $address 이메일주소 구조체
     */
    public function getAddressFromMember(int $member_id): \modules\email\dto\Address
    {
        /**
         * @var \modules\member\Member $mMember
         */
        $mMember = \Modules::get('member');
        $member = $mMember->getMember($member_id);
        if ($member->getId() === 0) {
            \ErrorHandler::print('NOT_FOUND_MEMBER');
        }

        return new \modules\email\dto\Address($member->getEmail(), $member->getDisplayName(false), $member->getId());
    }

    /**
     * 메일을 전송한다.
     *
     * @param \modules\email\dto\Email $email 전송할 메일객체
     * @param Template $template 본문템플릿 (NULL 인 경우 모듈 기본 템플릿 사용)
     * @return bool $success 성공여부
     */
    public function send(\modules\email\dto\Email $email, ?Template $template = null): bool
    {
        require_once $this->getPath() . '/vendor/PHPMailer/src/Exception.php';
        require_once $this->getPath() . '/vendor/PHPMailer/src/PHPMailer.php';
        require_once $this->getPath() . '/vendor/PHPMailer/src/SMTP.php';

        $PHPMailer = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $PHPMailer->isSMTP();
            $PHPMailer->Encoding = 'base64';
            $PHPMailer->CharSet = 'UTF-8';
            $PHPMailer->Host = $this->getConfigs('smtp_host');
            $PHPMailer->SMTPAuth = $this->getConfigs('smtp_id') && $this->getConfigs('smtp_password');
            if ($PHPMailer->SMTPAuth == true) {
                $PHPMailer->Username = $this->getConfigs('smtp_id');
                $PHPMailer->Password = $this->getConfigs('smtp_password');
            }

            if ($this->getConfigs('smtp_secure') != 'NONE') {
                $PHPMailer->SMTPSecure = $this->getConfigs('smtp_secure');
            }
            $PHPMailer->Port = intval($this->getConfigs('smtp_port'), 10);

            $from =
                $email->getFrom() ??
                $this->getAddress($this->getConfigs('default_from_address'), $this->getConfigs('default_from_name'));
            $PHPMailer->setFrom($from->getAddress(), $from->getName() ?? '');

            $replyTo = [];
            foreach ($email->getReplyTo() as $address) {
                if (isset($replyTo[$address->getAddress()]) == false) {
                    $PHPMailer->addReplyTo($address->getAddress(), $address->getName() ?? '');
                    $replyTo[$address->getAddress()] = [
                        'address' => $address->getAddress(),
                        'name' => $address->getName(),
                        'member_id' => $address->getMemberId(),
                    ];
                }
            }

            $receivers = [];
            foreach ($email->getAddress() as $address) {
                if (isset($receivers[$address->getAddress()]) == false) {
                    $PHPMailer->addAddress($address->getAddress(), $address->getName() ?? '');
                    $receivers[$address->getAddress()] = [$address, 'TO'];
                }
            }

            foreach ($email->getCC() as $address) {
                if (isset($receivers[$address->getAddress()]) == false) {
                    $PHPMailer->addCC($address->getAddress(), $address->getName() ?? '');
                    $receivers[$address->getAddress()] = [$address, 'CC'];
                }
            }

            foreach ($email->getBCC() as $address) {
                if (isset($receivers[$address->getAddress()]) == false) {
                    $PHPMailer->addBCC($address->getAddress(), $address->getName() ?? '');
                    $receivers[$address->getAddress()] = [$address, 'BCC'];
                }
            }

            $PHPMailer->isHTML(true);
            $PHPMailer->Subject = $email->getTitle();

            $site = \Sites::get();
            $template = $template ?? $this->getTemplate($this->getConfigs('template'));
            $template->assign(
                'logo',
                $site->getLogo()?->getFullUrl('view') ??
                    \Domains::get()->getUrl() . \Configs::dir() . '/images/logo.png'
            );
            $template->assign(
                'emblem',
                $site->getEmblem()?->getFullUrl('view') ??
                    \Domains::get()->getUrl() . \Configs::dir() . '/images/emblem.png'
            );
            $template->assign('url', $site->getUrl());
            $template->assign('content', $email->getContent());

            $style = file_get_contents($this->getPath() . '/styles/email.css');
            $style = preg_replace('/\/\*(.|\n)*?\*\//', '', $style);
            $style = preg_replace('/(\n|\r\n|    )/', '', $style);
            $body = \Html::tag(
                '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
                '<html xmlns="http://www.w3.org/1999/xhtml">',
                '<head>',
                '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />',
                '<meta name="viewport" content="width=device-width, initial-scale=1.0" />',
                '<style type="text/css">',
                $style,
                '</style>',
                '</head>',
                '<body style="width: 100% !important; height: 100% !important; margin: 0; padding: 0; background: #f4f4f4; font-family: \'Apple SD Gothic Neo\', \'malgun gothic\', Helvetica, Georgia, Arial, sans-serif !important;">',
                $template->getLayout(),
                '</body>',
                '</html>'
            );

            $PHPMailer->Body = $body;
            $success = $PHPMailer->send();
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            $success = $e->getMessage() . ' ' . $PHPMailer->ErrorInfo;
        }

        $email_id = \UUID::v1($email->getTitle());
        $this->db()
            ->insert($this->table('emails'), [
                'email_id' => $email_id,
                'from_address' => $from->getAddress(),
                'from_name' => $from->getName(),
                'from_member_id' => $from->getMemberId(),
                'reply_to' => \Format::toJson(array_values($replyTo)),
                'title' => $email->getTitle(),
                'content' => $email->getContent(),
                'template' => \Format::toJson([
                    'name' => $template->getPathName(),
                    'configs' => $template->getConfigs(),
                ]),
                'delivered_at' => time(),
                'status' => $success == true ? 'SUCCESS' : $success,
            ])
            ->execute();

        foreach ($receivers as $receiver) {
            $this->db()
                ->insert($this->table('receivers'), [
                    'email_id' => $email_id,
                    'to_address' => $receiver[0]->getAddress(),
                    'to_name' => $receiver[0]->getName(),
                    'to_member_id' => $receiver[0]->getMemberId(),
                    'type' => $receiver[1],
                ])
                ->execute();
        }

        return $success;
    }
}
