<?php
/**
 * 이 파일은 아이모듈 이메일모듈 일부입니다. (https://www.imodules.io)
 *
 * 이메일 전송자 클래스를 정의한다.
 *
 * @file /modules/email/classes/Sender.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 11. 7.
 */
namespace modules\email;
class Sender
{
    /**
     * @var \Component $_component 이메일을 전송하는 컴포넌트 객체
     */
    private \Component $_component;

    /**
     * @var \modules\email\dtos\Address $_to 받는사람
     */
    private \modules\email\dtos\Address $_to;

    /**
     * @var ?\modules\email\dtos\Address $_from 보내는사람
     */
    private ?\modules\email\dtos\Address $_from = null;

    /**
     * @var \modules\email\dtos\Address[] $_reply_to 답장받는사람
     */
    private array $_reply_to = [];

    /**
     * @var ?string $_title 메일제목
     */
    private ?string $_title = null;

    /**
     * @var ?string $_content 메일내용
     */
    private ?string $_content = null;

    /**
     * @var string $_content 스타일이 적용된 메일내용
     */
    private string $_contentWithStyle;

    /**
     * @var string $_contentWithTemplet 템플릿이 적용된 메일내용
     */
    private string $_contentWithTemplet;

    /**
     * @var string[] $_styles 스타일시트
     */
    private array $_styles = [];

    /**
     * @var ?object $_template 템플릿설정
     */
    private ?object $_template = null;

    /**
     * @var \Template $_templateClass 템플릿클래스
     */
    private \Template $_templateClass;

    /**
     * 이메일 전송자 클래스를 정의한다.
     *
     * @param \Component $component 메일을 전송하는 컴포넌트 객체
     */
    public function __construct(\Component $component)
    {
        $this->_component = $component;
    }

    /**
     * 받는 사람을 설정한다.
     *
     * @param \modules\email\dtos\Address $to
     * @return \modules\email\Sender $this
     */
    public function setTo(\modules\email\dtos\Address $to): \modules\email\Sender
    {
        $this->_to = $to;
        return $this;
    }

    /**
     * 받는 사람을 가져온다.
     *
     * @return \modules\email\dtos\Address $to
     */
    public function getTo(): \modules\email\dtos\Address
    {
        return $this->_to;
    }

    /**
     * 보내는 사람을 설정한다.
     *
     * @param \modules\email\dtos\Address $from
     * @return \modules\email\Sender $this
     */
    public function setFrom(\modules\email\dtos\Address $from): \modules\email\Sender
    {
        $this->_from = $from;
        return $this;
    }

    /**
     * 보내는 사람을 가져온다.
     *
     * @return \modules\email\dtos\Address $from
     */
    public function getFrom(): \modules\email\dtos\Address
    {
        if ($this->_from === null) {
            /**
             * @var \modules\email\Email $mEmail
             */
            $mEmail = \Modules::get('email');
            $this->_from = $mEmail->getAddress(
                $mEmail->getConfigs('default_from_address'),
                $mEmail->getConfigs('default_from_name')
            );
        }

        return $this->_from;
    }

    /**
     * 답장받는 사람을 추가한다.
     *
     * @param \modules\email\dtos\Address $reply_to
     * @return \modules\email\Sender $this
     */
    public function addReplyTo(\modules\email\dtos\Address $reply_to): \modules\email\Sender
    {
        $this->_reply_to[] = $reply_to;
        return $this;
    }

    /**
     * 답장받는사람을 가져온다.
     *
     * @return \modules\email\dtos\Address[] $reply_to
     */
    public function getReplyTo(): array
    {
        return $this->_reply_to;
    }

    /**
     * 제목을 설정한다.
     *
     * @param string $content 본문내용
     * @param bool $is_html HTML 여부
     * @return \modules\email\Sender $this
     */
    public function setTitle(string $title): \modules\email\Sender
    {
        $this->_title = $title;
        return $this;
    }

    /**
     * 제목을 가져온다.
     *
     * @param bool $is_encode 이메일표준에 의한 UTF-8 인코딩을 할지 여부
     * @return string $title
     */
    public function getTitle(bool $is_encode = false): string
    {
        if ($is_encode == true) {
            return '=?UTF-8?b?' . base64_encode($this->_title) . '?=';
        } else {
            return $this->_title;
        }
    }

    /**
     * 본문내용을 설정한다.
     *
     * @param string $content 본문내용
     * @param bool $is_html HTML 여부
     * @return \modules\email\Sender $this
     */
    public function setContent(string $content, bool $is_html = true): \modules\email\Sender
    {
        if ($is_html == false) {
            $content = nl2br($content);
        }

        $this->_content = $content;
        return $this;
    }

    /**
     * 본문을 가져온다.
     *
     * @param bool $is_template 이메일 발송을 위한 템플릿을 포함한 내용을 가져올지 여부
     * @return string $content
     */
    public function getContent(bool $is_template = false): string
    {
        if (isset($this->_contentWithStyle) == false) {
            if (count($this->_styles) == 0) {
                $this->_contentWithStyle = $this->_content;
            } else {
                $content = $this->_content;
                $html = \Pelago\Emogrifier\CssInliner::fromHtml($this->_content);
                foreach ($this->_styles as $path => &$content) {
                    $scss = new \ScssPhp\ScssPhp\Compiler();
                    $content ??= is_file($path) == true ? file_get_contents($path) : '';
                    $content = $scss->compileString($content)->getCss();
                    $html->inlineCss($content);
                }

                $dom = $html->getDomDocument();
                \Pelago\Emogrifier\HtmlProcessor\HtmlPruner::fromDomDocument($dom)
                    ->removeElementsWithDisplayNone()
                    ->removeRedundantClassesAfterCssInlined($html);

                \Pelago\Emogrifier\HtmlProcessor\CssVariableEvaluator::fromDomDocument($dom)->evaluateVariables();
                $this->_contentWithStyle = \Pelago\Emogrifier\HtmlProcessor\CssToAttributeConverter::fromDomDocument(
                    $dom
                )->renderBodyContent();
            }
        }

        $content = $this->_contentWithStyle;

        if ($is_template == true) {
            if (isset($this->_contentWithTemplet) == false) {
                $template = $this->getTemplate();
                $template->assign('content', '${CONTENT}');
                $contentWithTemplet = $template->getLayout();

                if (count($template->getPackage()->getStyles()) > 0) {
                    $html = \Pelago\Emogrifier\CssInliner::fromHtml($contentWithTemplet);
                    foreach ($template->getPackage()->getStyles() as $path) {
                        if (is_file($template->getPath() . $path) == true) {
                            $html->inlineCss(file_get_contents($template->getPath() . $path));
                        }
                    }

                    $dom = $html->getDomDocument();
                    \Pelago\Emogrifier\HtmlProcessor\HtmlPruner::fromDomDocument($dom)
                        ->removeElementsWithDisplayNone()
                        ->removeRedundantClassesAfterCssInlined($html);

                    \Pelago\Emogrifier\HtmlProcessor\CssVariableEvaluator::fromDomDocument($dom)->evaluateVariables();
                    $contentWithTemplet = \Pelago\Emogrifier\HtmlProcessor\CssToAttributeConverter::fromDomDocument(
                        $dom
                    )->renderBodyContent();
                }

                $this->_contentWithTemplet = str_replace('${CONTENT}', $content, $contentWithTemplet);
            }

            $content = $this->_contentWithTemplet;
        }

        /**
         * 일부 메일클라이언트의 경우 줄바꿈을 강제로 개행함으로 인하여,
         * 줄바꿈을 제거한다.
         */
        $content = preg_replace("(\r\n|\n)", '', $content);

        return $content;
    }

    /**
     * 본문스타일시트를 추가한다.
     *
     * @param string $style 스타일시트파일경로
     * @return \modules\email\Sender $this
     */
    public function addStyle(string $style): \modules\email\Sender
    {
        $this->_styles[$style] = null;
        return $this;
    }

    /**
     * 메일템플릿을 설정한다.
     *
     * @param ?object $template 템플릿설정
     * @return \modules\email\Sender $this
     */
    public function setTemplate(?object $template): \modules\email\Sender
    {
        $this->_template = $template;
        return $this;
    }

    /**
     * 메일템플릿설정을 가져온다.
     *
     * @return \Template $template 템플릿설정
     */
    public function getTemplate(): \Template
    {
        /**
         * @var \modules\email\Email $mEmail
         */
        $mEmail = \Modules::get('email');

        if (isset($this->_templateClass) == false) {
            $this->_templateClass = $mEmail->getTemplate($this->_template ?? $mEmail->getConfigs('template'));
        }

        if (
            $this->_templateClass->getPathName() !==
            ($this->_template?->name ?? $mEmail->getConfigs('template')?->name)
        ) {
            unset($this->_templateClass);
            return $this->getTemplate();
        }

        $site = \Sites::get();
        $this->_templateClass->assign(
            'logo',
            $site->getLogo()?->getUrl('view', true) ?? \Domains::get()->getUrl() . \Configs::dir() . '/images/logo.png'
        );
        $this->_templateClass->assign(
            'emblem',
            $site->getEmblem()?->getUrl('view', true) ??
                \Domains::get()->getUrl() . \Configs::dir() . '/images/emblem.png'
        );
        $this->_templateClass->assign('url', $site->getUrl());

        return $this->_templateClass;
    }

    /**
     * 메일을 전송한다.
     *
     * @param ?int $sended_at - 전송시각(NULL 인 경우 현재시각)
     * @return bool $success 성공여부
     */
    public function send(int $sended_at = null): bool
    {
        if (isset($this->_to) == false || $this->_title == null || $this->_content == null) {
            return false;
        }

        /**
         * @var \modules\email\Email $mEmail
         */
        $mEmail = \Modules::get('email');

        $sended_at ??= time();

        $success = \Events::fireEvent($mEmail, 'send', [$this, $sended_at], 'NOTNULL');

        $message_id = \UUID::v1($this->getTitle());
        if ($success === null) {
            require_once $mEmail->getPath() . '/vendor/PHPMailer/src/Exception.php';
            require_once $mEmail->getPath() . '/vendor/PHPMailer/src/PHPMailer.php';
            require_once $mEmail->getPath() . '/vendor/PHPMailer/src/SMTP.php';

            $PHPMailer = new \PHPMailer\PHPMailer\PHPMailer(true);

            try {
                $PHPMailer->isSMTP();
                $PHPMailer->Encoding = 'base64';
                $PHPMailer->CharSet = 'UTF-8';
                $PHPMailer->Host = $mEmail->getConfigs('smtp_host');
                $PHPMailer->SMTPAuth = $mEmail->getConfigs('smtp_id') && $mEmail->getConfigs('smtp_password');
                if ($PHPMailer->SMTPAuth == true) {
                    $PHPMailer->Username = $mEmail->getConfigs('smtp_id');
                    $PHPMailer->Password = $mEmail->getConfigs('smtp_password');
                }

                if ($mEmail->getConfigs('smtp_secure') != 'NONE') {
                    $PHPMailer->SMTPSecure = $mEmail->getConfigs('smtp_secure');
                }
                $PHPMailer->Port = intval($mEmail->getConfigs('smtp_port'), 10);

                $PHPMailer->setFrom($this->getFrom()->getAddress(), $this->getFrom()->getName() ?? '');

                foreach ($this->getReplyTo() as $address) {
                    $PHPMailer->addReplyTo($address->getAddress(), $address->getName() ?? '');
                }

                $PHPMailer->addAddress($this->_to->getAddress(), $this->_to->getName() ?? '');

                $PHPMailer->isHTML(true);
                $PHPMailer->Subject = $this->getTitle(true);

                $style = file_get_contents($mEmail->getPath() . '/styles/email.css');
                $style = preg_replace('/\/\*(.|\n)*?\*\//', '', $style);
                $style = preg_replace('/(\n|\r\n|    )/', '', $style);

                $domain = \Domains::get();

                $PHPMailer->Body = \Html::tag(
                    '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
                    '<html xmlns="http://www.w3.org/1999/xhtml">',
                    '<head>',
                    '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />',
                    '<meta name="viewport" content="width=device-width, initial-scale=1.0" />',
                    '<style type="text/css">',
                    $style,
                    '</style>',
                    '</head>',
                    '<body style="width: 100% !important; height: 100% !important; margin: 0; padding: 0; font-family: \'Apple SD Gothic Neo\', \'malgun gothic\', Helvetica, Georgia, Arial, sans-serif !important;">',
                    $this->getContent(true),
                    '<img src="' .
                        $domain->getUrl(true) .
                        $mEmail->getApiUrl('checked/' . $message_id . '.png') .
                        '" alt="" style="width:1px; height:1px;">',
                    '</body>',
                    '</html>'
                );

                $success = $PHPMailer->send();
            } catch (\PHPMailer\PHPMailer\Exception $e) {
                $success = $e->getMessage() . ' ' . $PHPMailer->ErrorInfo;
            }
        }

        $replyTo = [];
        foreach ($this->getReplyTo() as $address) {
            $replyTo = $address->getJson();
        }

        $mEmail
            ->db()
            ->insert($mEmail->table('messages'), [
                'message_id' => $message_id,
                'member_id' => $this->getTo()->getMemberId() ?? 0,
                'email' => $this->getTo()->getAddress(),
                'name' => $this->getTo()->getName(),
                'component_type' => $this->_component->getType(),
                'component_name' => $this->_component->getName(),
                'title' => $this->getTitle(),
                'content' => $this->getContent(true),
                'reply_to' => \Format::toJson($replyTo),
                'sended_by' => $this->getFrom()->getMemberId() ?? 0,
                'sended_email' => $this->getFrom()->getAddress(),
                'sended_name' => $this->getFrom()->getName(),
                'sended_at' => $sended_at,
                'status' => $success === true ? 'TRUE' : 'FALSE',
                'response' => is_bool($success) == false ? \Format::toJson($success) : null,
            ])
            ->execute();

        return $success;
    }
}
