(function ($) {
    sdtdl = {
        ...sdtdl,
        list:{
            collapse: function () {
                $(".sdtdl-front").fadeOut();
                $(".sdtdl-collapsed").css("display", "flex");
                sdtdl.cookie.set('list_state', 'collapsed');
            },
            restore: function () {
                $(".sdtdl-collapsed").hide();
                $(".sdtdl-front").fadeIn();
                sdtdl.cookie.set('list_state', 'open');
            },
        },
        dialogs:{
            init: function () {
                let close_text = $(".sdtdl-front").data("close");
                $(".sdtdl-dialog-content").dialog({
                    autoOpen: false,
                    position: {my: "left top", at: "right top", of: ".sdtdl-front"},
                    modal: false,
                    buttons: [
                        {
                            text: close_text,
                            click: function (event, ui) {
                                $(this).dialog("close");
                            }
                        },
                    ],
                    classes: {
                        "ui-dialog": "sdtdl-dialog sdtdl-view"
                    },
                    closeText: close_text,
                    dragStop: function (event, ui) {
                        sdtdl.cookie.update(event, ui)
                    },
                    resizeStop: function (event, ui) {
                        sdtdl.cookie.update(event, ui)
                    },
                    open: function (event, ui) {
                        ui = sdtdl.dialogs.check_previous_ui_state(event);
                        sdtdl.cookie.update(event, ui);
                    },
                    beforeClose: function (event, ui) {
                        sdtdl.cookie.update(event, ui, 'close')
                    },
                    create: function (event) {
                        $(event.target).dialog("widget").css({"position": "fixed"})
                    }
                });
                sdtdl.dialogs.populate_previous_states();
            },
            open: function () {
                let selector = ".sdtdl-" + sdtdl.item_id;
                $(selector).dialog("open");
            },
            check_previous_ui_state: function (event) {
                let id = event.target.dataset.id,
                    ui = {},
                    cookie = sdtdl.cookie.get();
                if (cookie.hasOwnProperty(id) && cookie[id].hasOwnProperty("position")) {
                    ui.position = cookie[id].position;
                }
                return ui;
            },
            populate_previous_states: function () {
                sdtdl.cookie.maintenance();
                let cookie = sdtdl.cookie.get();
                $.each(cookie, function (index, value) {
                    if (index === "list_state") {
                        return;
                    }
                    if (value.state === 'close') {
                        return;
                    }
                    let selector = $(".sdtdl-" + index);
                    if (value.hasOwnProperty("position")) {
                        selector.dialog("option", {
                            "position": {
                                my: "left+" + parseInt(value.position.left,10) + " top+" + parseInt(value.position.top,10),
                                at: "left top",
                                of: window
                            }
                        });
                        selector.dialog("option", {"width": parseInt(value.size.width,10), "height": parseInt(value.size.height,10)});
                    }
                    selector.dialog("open");
                })
            },
        },
        cookie:{
            name: 'sdtdl_front_state_' + sdtdl.strings.UserID + '_' + sdtdl.strings.BlogID,
            maintenance: function () {
                let cookie = sdtdl.cookie.get(),
                    item_ids = [],
                    maintain = false;
                $(".sdtdl-item").each(function () {
                    item_ids.push($(this).data("id"));
                });
                $.each(cookie, function (index, value) {
                    if (index === "list_state") {
                        return;
                    }
                    if (!item_ids.includes(index)) {
                        delete cookie[index];
                        maintain = true;
                    }
                });
                if (maintain === true) {
                    sdtdl.cookie.save(cookie);
                }
            },
            update: function (event, ui, type = '') {
                let id = event.target.dataset.id,
                    state = "open",
                    dialog = event.target.offsetParent;
                if (type === 'close') {
                    state = "close";
                }
                sdtdl.cookie.set(id, {
                    "position": ui.position,
                    "size": {"height": dialog.offsetHeight, "width": dialog.offsetWidth},
                    "state": state
                })
            },
            save: function(cookie){
                let expires = new Date();
                expires.setTime(expires.getTime() + (365 * 24 * 60 * 60 * 1000));
                document.cookie = sdtdl.cookie.name + '=' + JSON.stringify(cookie) + ';path=/;expires=' + expires.toUTCString();
            },
            get: function () {
                let regex = new RegExp(`(^| )${sdtdl.cookie.name}=([^;]+)`),
                    match = document.cookie.match(regex)
                if (match) {
                    return JSON.parse(match[2]);
                }
                return {};
            },
            set: function (key, value) {
                let cookie = sdtdl.cookie.get();
                cookie[key] = value;
                sdtdl.cookie.save(cookie);
            }
        },
        init: function () {
            $(document)
                .on("click touchend", ".sdtdl-item", function () {
                    sdtdl.item_id = $(this).data("id");
                    sdtdl.dialogs.open();
                })
                .on('click touchend', ".sdtdl-front .dashicons-arrow-down-alt2", function () {
                    sdtdl.list.collapse();
                })
                .on('click touchend', ".sdtdl-collapsed", function () {
                    sdtdl.list.restore();
                });
            $(document).ready(function () {
                sdtdl.dialogs.init();
                //allow clicking only when initialized to prevent jquery dialog errors
                $("#sdtdl-list").css("visibility", "visible");
            });
        },
    };
    sdtdl.init();
})(jQuery);