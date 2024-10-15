/**
 * 이 파일은 서비스데스크 모듈의 일부입니다. (https://www.coursemos.co.kr)
 *
 * 메일 발송 히스토리를 리스트한다.
 *
 * @file /modules/email/admin/scripts/contexts/messages.ts
 * @author pbj <ju318@ubion.co.kr>
 * @license MIT License
 * @modified 2024. 10. 15.
 *
 * @var \modules\naddle\desk\Desk $me
 */
Admin.ready(async () => {
    const me = Admin.getModule('email');
    return new Aui.Tab.Panel({
        id: 'messages-context',
        iconClass: 'mi mi-message-dots',
        title: '이메일관리',
        border: false,
        layout: 'column',
        disabled: true,
        topbar: [
            new Aui.Form.Field.Search({
                id: 'keyword',
                width: 200,
                emptyText: '수선지,메일주소',
                handler: async (keyword) => {
                    const context = Aui.getComponent('messages-context');
                    const messages = context.getActiveTab().getItemAt(0);
                    if (keyword.length > 0) {
                        messages.getStore().setParam('keyword', keyword);
                    }
                    else {
                        messages.getStore().setParam('keyword', null);
                    }
                    messages.getStore().loadPage(1);
                },
            }),
        ],
        items: [],
        listeners: {
            render: async (tab) => {
                const results = await me.getMomo().viewers.get('messages');
                if (results.success == true) {
                    for (const viewer of results.records) {
                        tab.append(new Aui.Panel({
                            id: viewer.viewer_id,
                            title: viewer.title,
                            iconClass: viewer.icon,
                            layout: 'column',
                            border: false,
                            items: [
                                new Aui.Grid.Panel({
                                    border: false,
                                    flex: 1,
                                    selection: { selectable: true, type: 'column', cancelable: true },
                                    autoLoad: false,
                                    bottombar: new Aui.Grid.Pagination([
                                        new Aui.Button({
                                            iconClass: 'mi mi-refresh',
                                            handler: (button) => {
                                                const grid = button.getParent().getParent();
                                                grid.getStore().reload();
                                            },
                                        }),
                                    ]),
                                    columns: [
                                        {
                                            text: '수신자',
                                            dataIndex: 'name',
                                            width: 160,
                                        },
                                        {
                                            text: '수신자메일주소',
                                            dataIndex: 'email',
                                            width: 160,
                                        },
                                        {
                                            text: '컴포넌트',
                                            dataIndex: 'component_name',
                                            width: 160,
                                            sortable: true,
                                        },
                                        {
                                            text: '제목',
                                            dataIndex: 'title',
                                            selectable: true,
                                            sortable: true,
                                            minWidth: 280,
                                            flex: 1,
                                        },
                                        {
                                            text: '보낸시간',
                                            dataIndex: 'sended_at',
                                            width: 150,
                                            sortable: true,
                                            renderer: (value) => {
                                                return Format.date('Y.m.d(D) H:i', value);
                                            },
                                        },
                                        {
                                            text: '발송상태',
                                            dataIndex: 'status',
                                            width: 100,
                                            sortable: true,
                                            filter: new Aui.Grid.Filter.List({
                                                dataIndex: 'status',
                                                store: new Aui.Store.Local({
                                                    fields: ['display', { name: 'value', type: 'string' }],
                                                    records: [
                                                        ['성공', 'TRUE'],
                                                        ['실패', 'FALSE'],
                                                    ],
                                                }),
                                                displayField: 'display',
                                                valueField: 'value',
                                            }),
                                            renderer: (value) => {
                                                const statuses = {
                                                    'TRUE': '성공',
                                                    'FALSE': '실패',
                                                };
                                                return statuses[value];
                                            },
                                        },
                                    ],
                                    store: new Aui.Store.Remote({
                                        url: me.getProcessUrl('messages'),
                                        primaryKeys: ['message_id'],
                                        filters: viewer.filters,
                                        sorters: viewer.sorters ?? { sended_at: 'DESC' },
                                        limit: 50,
                                        remoteSort: true,
                                        remoteFilter: true,
                                    }),
                                    listeners: {
                                        update: (grid) => {
                                            if (Admin.getContextSubUrl(1) !== null &&
                                                grid.getSelections().length == 0) {
                                                grid.select({ message_id: Admin.getContextSubUrl(1) });
                                            }
                                        },
                                        selectionChange: (selection, grid) => {
                                            //todo view 필요없다면 없애기
                                            Aui.getComponent('messages-context').properties.setUrl();
                                        },
                                    },
                                }),
                            ],
                        }));
                    }
                }
                if (Admin.getContextSubUrl(0) !== null && Aui.getComponent(Admin.getContextSubUrl(0)) !== null) {
                    tab.active(Admin.getContextSubUrl(0));
                }
                else {
                    tab.active(0);
                }
                tab.setDisabled(false);
            },
            active: async (panel, tab) => {
                const grid = panel.getItemAt(0);
                const keyword = Aui.getComponent('keyword');
                keyword.setValue(grid.getStore().getParam('keyword') ?? null);
                Aui.getComponent('messages-context').properties.setUrl();
                const message_id = Admin.getContextSubUrl(1);
                if (message_id !== null) {
                    const results = await Ajax.get(me.getProcessUrl('messages'), {
                        ...(await grid.getStore().getLoaderParams()),
                        message_id: message_id,
                    });
                    if (results.success == true) {
                        if (results.page == -1) {
                            if (panel.getId() != 'all') {
                                Admin.setContextSubUrl('/all/' + message_id);
                                tab.active('all');
                            }
                            else {
                                Admin.setContextSubUrl('/all');
                                grid.getStore().load();
                            }
                        }
                        else {
                            grid.getStore().loadPage(results.page);
                        }
                    }
                }
                else if (grid.getStore().isLoaded() == false) {
                    grid.getStore().load();
                }
                if (grid.getStore().isLoaded() == false) {
                    grid.getStore().load();
                }
            },
        },
        setUrl: () => {
            //todo view 필요없다면 없애기
            const context = Aui.getComponent('messages-context');
            const tab = context.getActiveTab();
            if (Admin.getContextSubUrl(0) !== tab.getId()) {
                Admin.setContextSubUrl('/' + tab.getId());
            }
            const grid = tab.getItemAt(0);
            if (grid.getStore().isLoaded() == true) {
                if (grid.getSelections().length == 0) {
                    Admin.setContextSubUrl('/' + tab.getId());
                }
                else {
                    const record = grid.getSelections()[0];
                    if (Admin.getContextSubUrl(1) !== record.get('message_id')) {
                        Admin.setContextSubUrl('/' + tab.getId() + '/' + record.get('message_id'));
                    }
                }
            }
        },
        reloadAll: async () => {
            const context = Aui.getComponent('messages-context');
            const reloads = [];
            for (const tab of context.getItems()) {
                const grid = tab.getItemAt(0);
                reloads.push(grid.getStore().reload());
            }
            await Promise.all(reloads);
        },
    });
});
