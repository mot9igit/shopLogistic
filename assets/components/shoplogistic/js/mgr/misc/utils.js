shopLogistic.utils.renderBoolean = function (value) {
    return value
        ? String.format('<span class="green">{0}</span>', _('yes'))
        : String.format('<span class="red">{0}</span>', _('no'));
};

shopLogistic.utils.getMenu = function (actions, grid, selected) {
    var menu = [];
    var cls, icon, title, action;

    var has_delete = false;
    for (var i in actions) {
        if (!actions.hasOwnProperty(i)) {
            continue;
        }

        var a = actions[i];
        if (!a['menu']) {
            if (a == '-') {
                menu.push('-');
            }
            continue;
        }
        else if (menu.length > 0 && !has_delete && (/^remove/i.test(a['action']) || /^delete/i.test(a['action']))) {
            menu.push('-');
            has_delete = true;
        }

        if (selected.length > 1) {
            if (!a['multiple']) {
                continue;
            }
            else if (typeof(a['multiple']) == 'string') {
                a['title'] = a['multiple'];
            }
        }

        icon = a['icon'] ? a['icon'] : '';
        if (typeof(a['cls']) == 'object') {
            if (typeof(a['cls']['menu']) != 'undefined') {
                icon += ' ' + a['cls']['menu'];
            }
        }
        else {
            cls = a['cls'] ? a['cls'] : '';
        }
        title = a['title'] ? a['title'] : a['title'];
        action = a['action'] ? grid[a['action']] : '';

        menu.push({
            handler: action,
            text: String.format(
                '<span class="{0}"><i class="x-menu-item-icon {1}"></i>{2}</span>',
                cls, icon, title
            ),
            scope: grid
        });
    }

    return menu;
};

shopLogistic.utils.renderActions = function (value, props, row) {
    var res = [];
    var cls, icon, title, action, item;
    for (var i in row.data.actions) {
        if (!row.data.actions.hasOwnProperty(i)) {
            continue;
        }
        var a = row.data.actions[i];
        if (!a['button']) {
            continue;
        }

        icon = a['icon'] ? a['icon'] : '';
        if (typeof(a['cls']) == 'object') {
            if (typeof(a['cls']['button']) != 'undefined') {
                icon += ' ' + a['cls']['button'];
            }
        }
        else {
            cls = a['cls'] ? a['cls'] : '';
        }
        action = a['action'] ? a['action'] : '';
        title = a['title'] ? a['title'] : '';

        item = String.format(
            '<li class="{0}"><button class="shoplogistic-btn shoplogistic-btn-default {1}" action="{2}" title="{3}"></button></li>',
            cls, icon, action, title
        );

        res.push(item);
    }

    return String.format(
        '<ul class="shoplogistic-row-actions">{0}</ul>',
        res.join('')
    );
};

shopLogistic.utils.userLink = function (value, id, blank) {
    if (!value) {
        return '';
    } else if (!id) {
        return value;
    }

    return String.format(
        '<a href="?a=security/user/update&id={0}" class="sl-link" target="{1}">{2}</a>',
        id,
        (blank ? '_blank' : '_self'),
        value
    );
};

shopLogistic.utils.genRegExpString = function (str) {
    var str_new = str;

    var words = {};
    words['0-9'] = '0123456789';
    words['a-z'] = 'qwertyuiopasdfghjklzxcvbnm';
    words['A-Z'] = 'QWERTYUIOPASDFGHJKLZXCVBNM';

    var match = /\/((\(?\[[^\]]+\](\{[0-9-]+\})*?\)?[^\[\(]*?)+)\//.exec(str);
    if (match != null) {
        str_new = match[1].replace(/\(?(\[[^\]]+\])(\{[0-9-]+\})\)?/g, regexpReplace1);
        str_new = str.replace(match[0], str_new);
    }

    return str_new;

    function rand(min, max) {
        if (max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }
        else {
            return Math.floor(Math.random() * (min + 1));
        }
    }

    function regexpReplace1(match, symbs, count) {
        symbs = (symbs + count).replace(/\[([0-9a-zA-Z-]+)\]\{([0-9]+-[0-9]+|[0-9]+)\}/g, regexpReplace2);

        return symbs;
    }

    function regexpReplace2(match, symbs, count) {
        var r = match;
        var arr_symbs = symbs.match(/[0-9a-zA-Z]-[0-9a-zA-Z]/g);

        if (arr_symbs.length > 0) {
            var maxcount = 1;

            if (typeof count != 'undefined') {
                nums = count.split('-');

                if (typeof nums[1] == 'undefined') {
                    maxcount = +nums[0];
                }
                else {
                    min = +nums[0];
                    max = +nums[1];

                    maxcount = rand(min, max);
                    maxcount = maxcount < min ? min : maxcount;
                }
            }

            for (var i = 0; i < arr_symbs.length; i++) {
                symbs = symbs.replace(arr_symbs[i], words[arr_symbs[i]]);
            }

            var maxpos = symbs.length - 1,
                pos,
                r = '';

            for (var i = 0; i < maxcount; i++) {
                pos = Math.floor(Math.random() * maxpos);
                r += symbs[pos];
            }
        }

        return r;
    }
}