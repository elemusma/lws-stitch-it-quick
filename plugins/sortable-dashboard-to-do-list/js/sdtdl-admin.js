(function ($) {
    sdtdl = {
        ...sdtdl,
        init_sortable_list: function () {
            $("#sdtdl-list").sortable({
                containment: "document",
                items: "> li.sdtdl-item",
                handle: ".dashicons-sort",
                connectWith: "#sdtdl-list",
                opacity: 0.5,
                update: function () {
                    sdtdl.save_items();
                }
            });
        },
        save_settings: function () {
            let settings = {'front': $("#option-show-front").is(":checked")}
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    "action": "sdtdl_settings",
                    "settings": settings,
                    "nonce": sdtdl.strings.nonce,
                },
                dataType: "json",
                success: function () {
                    if (settings.front === false) {
                        $(".show-front-option").hide();
                    } else {
                        $(".show-front-option").show();
                    }
                }
            })
        },
        save_items: function (timestamp = '', type = '', index = '') {
            let toDoList = $("#sdtdl-list li.sdtdl-item"),
                toDoData = [];
            toDoList.each(function (i, li) {
                let itemData = $(li),
                    edited = itemData.data("edited");
                if (edited === undefined) {
                    edited = 0;
                }
                toDoData[i] = {
                    "title": itemData.find(".sdtdl-item-title").text().trim(),
                    "content": itemData.find(".sdtdl-content-text")[0].innerHTML.trim(),
                    "added": itemData.data("added"),
                    "last_edited": edited,
                    "front": itemData.data("front"),
                    'id': itemData.data("id")
                };
            });
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    "action": "sdtdl_update",
                    "data": toDoData,
                    "date_data": {
                        "timestamp": timestamp,
                        "type": type,
                    },
                    "nonce": sdtdl.strings.nonce,
                },
                dataType: "json",
                success: function (response) {
                    if (!response.data) {
                        return;
                    }
                    if (type === 'add') {
                        $(".sdtdl-item[data-key='" + index + "'] .sdtdl-date-added").html(response.data.full);
                        $(".sdtdl-item[data-key='" + index + "'] .sdtdl-item-title").attr("title", response.data.short);
                    } else {
                        $(".sdtdl-item[data-key='" + index + "'] .sdtdl-date-edited").html(response.data.full);
                    }
                }
            })
        },
        init_dialogs: function () {
            $(".sdtdl-dialog-content").dialog({
                autoOpen: false,
                position: {my: "center", at: "center", of: "#sdtdl-list"},
                modal: true,
                closeText: sdtdl.strings.Close
            });
        },
        open_dialog: function (action) {
            let buttons = {},
                classes = {},
                selector = ".sdtdl-" + action + "-item";
            switch (action) {
                case "settings":
                    selector = ".sdtdl-options";
                    buttons = [
                        {
                            text: sdtdl.strings.Save,
                            click: function () {
                                $(this).dialog("close");
                                sdtdl.save_settings();
                            }
                        }
                    ];
                    classes = {
                        "ui-dialog": "sdtdl-dialog sdtdl-settings"
                    };
                    break;
                case "new":
                    buttons = [
                        {
                            text: sdtdl.strings.Save,
                            click: function () {
                                sdtdl.add_item(this);
                            }
                        }
                    ];
                    classes = {
                        "ui-dialog": "sdtdl-dialog sdtdl-add"
                    };
                    break;
                case "edit":
                    buttons = [
                        {
                            text: sdtdl.strings.SaveEdits,
                            click: function () {
                                sdtdl.save_edits(this);
                            }
                        }];
                    classes = {
                        "ui-dialog": "sdtdl-dialog sdtdl-edit"
                    }
                    $(selector).dialog({
                        open: function () {
                            sdtdl.populate_edit_dialog();
                        }
                    });
                    break;
                case "delete":
                    buttons = [
                        {
                            text: sdtdl.strings.Delete,
                            click: function () {
                                sdtdl.delete_item(this);
                            }
                        },
                        {
                            text: sdtdl.strings.Cancel,
                            click: function () {
                                $(this).dialog("close");
                                $(sdtdl.previousDialog).dialog("open");
                            }
                        }];
                    classes = {
                        "ui-dialog": "sdtdl-dialog sdtdl-delete"
                    };
                    break;
                default:
                    buttons = [
                        {
                            text: sdtdl.strings.Edit,
                            click: function () {
                                sdtdl.open_dialog("edit");
                                $(this).dialog("close");
                            }
                        },
                        {
                            text: sdtdl.strings.Delete,
                            click: function () {
                                sdtdl.previousDialog = this;
                                sdtdl.open_dialog("delete");
                                $(this).dialog("close");
                            }
                        }];
                    classes = {
                        "ui-dialog": "sdtdl-dialog sdtdl-view"
                    };
            }
            $(selector).dialog("option", {"buttons": buttons, "classes": classes});
            $(selector).dialog("open");
        },
        save_edits: function (el) {
            let item = $(".sdtdl-item[data-key='" + sdtdl.item_index + "']"),
                editedText = $("#edit-sdtdl-text").val(),
                editedTitleEl = $("#edit-sdtdl-title"),
                editedTitle = editedTitleEl.val(),
                showFrontBox = $("#edit-show-front");
            if (!editedTitle.length) {
                editedTitleEl.addClass("error");
                return;
            }
            let timestamp = Math.floor(Date.now() / 1000);
            editedTitleEl.removeClass("error");
            item.data("front", showFrontBox.is(':checked'));
            item.data("edited", timestamp);
            item.find(".sdtdl-item-title").html(editedTitle);
            item.find(".sdtdl-content-text").html(editedText);
            item.find(".sdtdl-date-edited").html('<span class="dashicons dashicons-edit"></span>' + sdtdl.strings.RecentlyEdited);
            sdtdl.save_items(timestamp, "edit", sdtdl.item_index);
            $(el).dialog("close");
        },
        populate_view_dialog: function (el) {
            let $el = $(el),
                title = $el.find(".sdtdl-item-title").html().trim(),
                content = $el.find(".sdtdl-content-text").html().trim(),
                dates = $el.find(".sdtdl-dates").html().trim(),
                contentContainer = $(".sdtdl-view-item .sdtdl-content-text");
            $(".sdtdl-view-item").closest(".ui-dialog").find(".ui-dialog-title").html(title);
            $(".sdtdl-view-item .sdtdl-no-content").remove();
            $(".sdtdl-view-item .sdtdl-dates").html(dates);
            content=content.replace(/(?:<(ul|ol)>.*?<\/\1>|<\/li>)\s*?(?=<\/\1>|<li>)/gis,'');
            contentContainer.html(content);
            if (content.length === 0) {
                contentContainer.after($(".sdtdl-no-content-container").html());
            }
        },
        populate_edit_dialog: function () {
            let el = $(".sdtdl-item[data-key='" + sdtdl.item_index + "']"),
                title = el.find(".sdtdl-item-title").html().trim(),
                content = el.find(".sdtdl-content-text").html().trim(),
                showFront = el.data("front");
            $("#edit-sdtdl-title").val(title);
            $("#edit-sdtdl-text").val(content.replace(/&amp;/g, '&'));
            $("#edit-show-front").prop("checked", showFront);
        },
        add_item: function (el) {
            let newToDoItem = $("#new-sdtdl-title"),
                newItemTitle = newToDoItem.val(),
                showFrontBox = $("#new-show-front");
            if (!newItemTitle) {
                newToDoItem.addClass("error");
                return;
            }
            newToDoItem.removeClass("error");
            let toDoList = $("#sdtdl-list"),
                newToDoText = $("#new-sdtdl-text"),
                timestamp = Math.floor(Date.now() / 1000),
                showFront = showFrontBox.is(':checked');
            toDoList.append(sdtdl.create_li_item(newItemTitle, newToDoText.val(), showFront, timestamp))
            toDoList.sortable("refresh");
            showFrontBox.prop("checked", true);
            newToDoItem.val("");
            newToDoText.val("");
            sdtdl.save_items(timestamp, "add", sdtdl.count);
            sdtdl.count++;
            $(el).dialog("close");
        },
        create_li_item: function (title, content, showFront, timestamp) {
            return '' +
                '<li class="sdtdl-item" data-key="' + sdtdl.count + '" data-added="' + timestamp + '" data-front=' + showFront + ' data-id=' + sdtdl.unique_id(timestamp) + '>' +
                '<span class="dashicons dashicons-sort"></span>' +
                '<div class="sdtdl-item-title" title="' + sdtdl.strings.RecentlyAdded + '">' + title + '</div>' +
                '<div class="sdtdl-content-container">' +
                '<div class="sdtdl-content-text">' + content + '</div>' +
                '<div class="sdtdl-dates">' +
                '<div class="sdtdl-date-added">' +
                '<span class="dashicons dashicons-plus"></span>' +
                sdtdl.strings.RecentlyAdded +
                '</div>' +
                '<div class="sdtdl-date-edited"></div>' +
                '</div>' +
                '</div>' +
                '</li>'
        },
        unique_id: function (timestamp) {
            return Math.trunc(timestamp + 10000 * Math.random()).toString(36);
        },
        delete_item: function (el) {
            let item = $(".sdtdl-item[data-key='" + sdtdl.item_index + "']");
            $(el).dialog("close");
            item.fadeOut(500, function () {
                    $(this).remove();
                    sdtdl.save_items();
                }
            );
        },
        init: function () {
            $(document)
                .on("click", ".sdtdl-item", function (e) {
                    if (e.target.classList.contains("dashicons")) {
                        return false;
                    }
                    sdtdl.item_index = $(this).data("key");
                    sdtdl.populate_view_dialog(this);
                    sdtdl.open_dialog("view");
                })
                .on("click", ".sdtdl-add-button", function () {
                    sdtdl.open_dialog("new");
                })
                .on("click", ".sdtdl-settings-button", function () {
                    sdtdl.open_dialog("settings");
                })
                .on('click', ".ui-widget-overlay", function () {
                    $(this).siblings('.ui-dialog').find('.ui-dialog-content').dialog('close');
                });
            $(document).ready(function () {
                sdtdl.init_dialogs();
                sdtdl.init_sortable_list();
                //allow clicking only when initialized to prevent jquery dialog errors
                $("#sdtdl-list").css("visibility", "visible");
                sdtdl.count = $("#sdtdl-list .sdtdl-item").length;
            });
        },
    };
    sdtdl.init();
})(jQuery);