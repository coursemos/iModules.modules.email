<?php
/**
 * 이 파일은 아이모듈 이메일모듈의 일부입니다. (https://www.imodules.io)
 *
 * 모듈관리자 클래스를 정의한다.
 *
 * @file /modules/email/admin/Email.php
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 11. 1.
 */
namespace modules\email\admin;
class Email extends \modules\admin\admin\Component
{
    /**
     * 관리자 컨텍스트 목록을 가져온다.
     *
     * @return \modules\admin\dtos\Context[] $contexts
     */
    public function getContexts(): array
    {
        $contexts = [];

        if ($this->hasPermission('email') == true) {
            $contexts[] = \modules\admin\dtos\Context::init($this)
                ->setContext('messages')
                ->setTitle($this->getText('admin.contexts.messages'), 'xi xi-letter');
        }

        return $contexts;
    }

    /**
     * 현재 모듈의 관리자 컨텍스트를 가져온다.
     *
     * @param string $path 컨텍스트 경로
     * @return string $html
     */
    public function getContext(string $path): string
    {
        switch ($path) {
            case 'messages':
                \Html::script($this->getBase() . '/scripts/contexts/messages.js');
                break;
        }

        return '';
    }

    /**
     * 현재 컴포넌트의 관리자 권한범위를 가져온다.
     *
     * @return \modules\admin\dtos\Scope[] $scopes
     */
    public function getScopes(): array
    {
        $scopes = [];

        $scopes[] = \modules\admin\dtos\Scope::init($this)->setScope(
            'messages',
            $this->getText('admin.scopes.messages.title')
        );

        return $this->setScopes($scopes);
    }
}
