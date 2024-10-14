/**
 * 이 파일은 서비스데스크 모듈의 일부입니다. (https://www.coursemos.co.kr)
 *
 * 서비스데스크관리 화면을 구성한다.
 *
 * @file /modules/naddle/desk/admin/scripts/contexts/emails.ts
 * @author youlapark <youlapark@naddle.net>
 * @license MIT License
 * @modified 2024. 10. 1.
 */
Admin.ready(async () => {
    const me = Admin.getModule('email');
    return new Aui.Tab.Panel({
        id: 'emails-context',
        iconClass: 'mi mi-message-dots',
        title: '이메일관리',
        border: false,
        layout: 'column',
        disabled: true,
        topbar: [
            new Aui.Form.Field.Search({
                id: 'keyword',
                width: 200,
                emptyText: '키워드',
                handler: async (keyword) => {
                    const context = Aui.getComponent('emails-context');
                    const emails = context.getActiveTab().getItemAt(0);
                    if (keyword.length > 0) {
                        emails.getStore().setParam('keyword', keyword);
                    }
                    else {
                        emails.getStore().setParam('keyword', null);
                    }
                    emails.getStore().loadPage(1);
                },
            }),
        ],
        items: [],
        listeners: {
            render: async (tab) => {
                const results = await me.getMomo().viewers.get('emails');
                console.log(results);
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
                                            dataIndex: 'member_id',
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
                                            text: '발송상태',
                                            dataIndex: 'status',
                                            width: 160,
                                            sortable: true,
                                        },
                                    ],
                                    store: new Aui.Store.Remote({
                                        url: me.getProcessUrl('emails'),
                                        primaryKeys: ['message_id'],
                                        filters: viewer.filters,
                                        sorters: viewer.sorters ?? { title: 'ASC' },
                                        limit: 50,
                                        remoteSort: true,
                                        remoteFilter: true,
                                    }),
                                    listeners: {},
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
                Aui.getComponent('emails-context').properties.setUrl();
                const message_id = Admin.getContextSubUrl(1);
                if (message_id !== null) {
                    const results = await Ajax.get(me.getProcessUrl('emails'), {
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
            const context = Aui.getComponent('emails-context');
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
            const context = Aui.getComponent('emails-context');
            const reloads = [];
            for (const tab of context.getItems()) {
                const grid = tab.getItemAt(0);
                reloads.push(grid.getStore().reload());
            }
            await Promise.all(reloads);
        },
    });
});
