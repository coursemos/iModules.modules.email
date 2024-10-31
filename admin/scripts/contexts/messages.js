/**
 * 이 파일은 아이모듈 이메일모듈 일부입니다. (https://www.imodules.io)
 *
 * 이메일 발송 내역 화면을 구성한다.
 *
 * @file /modules/email/admin/scripts/contexts/messages.ts
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 11. 1.
 */
Admin.ready(async () => {
    const me = Admin.getModule('email');
    return new Aui.Grid.Panel({
        id: 'messages-context',
        title: await me.getText('admin.contexts.messages'),
        iconClass: 'xi xi-letter',
        layout: 'fit',
        border: false,
        selection: { selectable: true },
        topbar: [
            new Aui.Form.Field.Search({
                id: 'keyword',
                width: 200,
                emptyText: await me.getText('admin.keyword'),
                handler: async (keyword) => {
                    const messages = Aui.getComponent('messages-context');
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
        columns: [
            {
                text: await me.getText('admin.messages.columns.to'),
                dataIndex: 'to',
                width: 260,
                renderer: (value) => {
                    return me.getAddress(value);
                },
            },
            {
                text: await me.getText('admin.messages.columns.title'),
                dataIndex: 'title',
                sortable: true,
                minWidth: 300,
                flex: 1,
            },
            {
                text: await me.getText('admin.messages.columns.sended_at'),
                dataIndex: 'sended_at',
                width: 150,
                sortable: true,
                filter: new Aui.Grid.Filter.Date({
                    format: 'timestamp',
                }),
                renderer: (value) => {
                    return Format.date('Y.m.d(D) H:i', value);
                },
            },
            {
                text: await me.getText('admin.messages.columns.checked_at'),
                dataIndex: 'checked_at',
                width: 150,
                sortable: true,
                filter: new Aui.Grid.Filter.Date({
                    format: 'timestamp',
                }),
                renderer: (value) => {
                    if (value != undefined) {
                        return Format.date('Y.m.d(D) H:i', value);
                    }
                    else {
                        return '';
                    }
                },
            },
            {
                text: await me.getText('admin.messages.columns.from'),
                dataIndex: 'from',
                width: 260,
                renderer: (value) => {
                    return me.getAddress(value);
                },
            },
            {
                text: await me.getText('admin.messages.columns.status'),
                dataIndex: 'status',
                width: 140,
                sortable: true,
                filter: new Aui.Grid.Filter.List({
                    dataIndex: 'status',
                    store: new Aui.Store.Local({
                        fields: ['display', 'value'],
                        records: [
                            [await me.getText('admin.status.TRUE'), 'TRUE'],
                            [await me.getText('admin.status.FALSE'), 'FALSE'],
                        ],
                    }),
                }),
                renderer: (value, record, $dom) => {
                    $dom.addClass(value);
                    if (value == 'TRUE') {
                        $dom.addClass('center');
                        return me.printText('admin.status.' + value);
                    }
                    else {
                        return record.get('response') ?? me.printText('admin.status.' + value);
                    }
                },
            },
        ],
        store: new Aui.Store.Remote({
            url: me.getProcessUrl('messages'),
            primaryKeys: ['message_id'],
            limit: 50,
            sorters: { sended_at: 'DESC' },
            remoteSort: true,
            remoteFilter: true,
        }),
        bottombar: new Aui.Grid.Pagination([
            new Aui.Button({
                iconClass: 'mi mi-refresh',
                handler: (button) => {
                    const grid = button.getParent().getParent();
                    grid.getStore().reload();
                },
            }),
        ]),
        listeners: {
            openItem: (record) => {
                me.messages.show(record.get('message_id'), record.get('title'), record.get('to'));
            },
        },
    });
});
