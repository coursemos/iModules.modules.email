/**
 * 이 파일은 아이모듈 이메일모듈 일부입니다. (https://www.imodules.io)
 *
 * 관리자 UI 이벤트를 관리하는 클래스를 정의한다.
 *
 * @file /modules/email/admin/scripts/Email.ts
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 2. 19.
 */
var modules;
(function (modules) {
    let email;
    (function (email) {
        let admin;
        (function (admin) {
            class Email extends modules.admin.admin.Component {
                /**
                 * 모듈 환경설정 폼을 가져온다.
                 *
                 * @return {Promise<Aui.Form.Panel>} configs
                 */
                async getConfigsForm() {
                    return new Aui.Form.Panel({
                        scrollable: true,
                        items: [
                            new Aui.Form.FieldSet({
                                title: (await this.getText('admin.configs.default')),
                                items: [
                                    new AdminUi.Form.Field.Template({
                                        label: (await this.getText('admin.configs.template')),
                                        name: 'template',
                                        allowBlank: false,
                                        componentType: this.getType(),
                                        componentName: this.getName(),
                                    }),
                                    new Aui.Form.Field.Container({
                                        items: [
                                            new Aui.Form.Field.Text({
                                                label: (await this.getText('admin.configs.default_from_address')),
                                                name: 'default_from_address',
                                                allowBlank: false,
                                                flex: 1,
                                            }),
                                            new Aui.Form.Field.Text({
                                                label: (await this.getText('admin.configs.default_from_name')),
                                                name: 'default_from_name',
                                                allowBlank: false,
                                                flex: 1,
                                            }),
                                        ],
                                    }),
                                ],
                            }),
                            new Aui.Form.FieldSet({
                                title: (await this.getText('admin.configs.smtp')),
                                items: [
                                    new Aui.Form.Field.Container({
                                        items: [
                                            new Aui.Form.Field.Text({
                                                label: (await this.getText('admin.configs.smtp_host')),
                                                name: 'smtp_host',
                                                allowBlank: false,
                                                flex: 1,
                                            }),
                                            new Aui.Form.Field.Text({
                                                label: (await this.getText('admin.configs.smtp_port')),
                                                name: 'smtp_port',
                                                labelWidth: 60,
                                                width: 120,
                                                allowBlank: false,
                                            }),
                                            new Aui.Form.Field.Select({
                                                label: (await this.getText('admin.configs.smtp_secure')),
                                                name: 'smtp_secure',
                                                store: new Aui.Store.Local({
                                                    fields: ['value'],
                                                    records: [['NONE'], ['TLS'], ['SSL']],
                                                }),
                                                displayField: 'value',
                                                valueField: 'value',
                                                allowBlank: false,
                                                width: 200,
                                            }),
                                        ],
                                    }),
                                    new Aui.Form.Field.Container({
                                        label: (await this.getText('admin.configs.smtp_auth')),
                                        direction: 'column',
                                        items: [
                                            new Aui.Form.Field.Check({
                                                name: 'smtp_auth',
                                                boxLabel: (await this.getText('admin.configs.smtp_auth_help')),
                                                listeners: {
                                                    change: (field, value) => {
                                                        const form = field.getForm();
                                                        form.getField('smtp_id').setDisabled(!value);
                                                        form.getField('smtp_password').setDisabled(!value);
                                                    },
                                                },
                                            }),
                                            new Aui.Form.FieldSet({
                                                title: (await this.getText('admin.configs.smtp_auth_info')),
                                                items: [
                                                    new Aui.Form.Field.Container({
                                                        items: [
                                                            new Aui.Form.Field.Text({
                                                                label: (await this.getText('admin.configs.smtp_id')),
                                                                name: 'smtp_id',
                                                                allowBlank: false,
                                                                flex: 1,
                                                                disabled: true,
                                                            }),
                                                            new Aui.Form.Field.Text({
                                                                label: (await this.getText('admin.configs.smtp_password')),
                                                                name: 'smtp_password',
                                                                allowBlank: false,
                                                                flex: 1,
                                                                disabled: true,
                                                            }),
                                                        ],
                                                    }),
                                                ],
                                            }),
                                        ],
                                    }),
                                ],
                            }),
                        ],
                        listeners: {
                            load: (form, response) => {
                                if ((response?.data?.smtp_id ?? null) !== null &&
                                    (response?.data?.smtp_password ?? null) !== null) {
                                    form.getField('smtp_auth').setValue(true);
                                }
                            },
                        },
                    });
                }
            }
            admin.Email = Email;
        })(admin = email.admin || (email.admin = {}));
    })(email = modules.email || (modules.email = {}));
})(modules || (modules = {}));
