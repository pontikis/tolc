$(function () {
    var project_url = $("#project_url").val();
    var btn_do_new_page_value = $("#btn_do_new_page").val();
    var btn_cancel_new_page_value = $("#btn_cancel_new_page").val();

    $("#new_page_form").dialog({
        autoOpen: true,
        show: "blind",
        hide: "explode",
        width: 700,
        height: 600,
        resizable: true,
        open: function () {
            $(this).load(project_url + '/app/admin/new_page/ajax_new_page_form.php', {}, function () {
                $("#page_url").focus();
            });
            $('.ui-dialog-buttonpane').find('button:contains("' + btn_do_new_page_value + '")').button({
                icons: {
                    primary: 'ui-icon-check'
                }
            });
            $('.ui-dialog-buttonpane').find('button:contains("' + btn_cancel_new_page_value + '")').button({
                icons: {
                    primary: 'ui-icon-cancel'
                }
            });
        },
        buttons: [
            {
                text: btn_do_new_page_value,
                click: function () {
                    switch (validate_new_page_form()) {
                        case 1:
                            $("#page_url").focus();
                            update_user_message($("#msg_page_url_required").val());
                            break;
                        case 2:
                            $("#page_title").focus();
                            update_user_message($("#msg_page_title_required").val());
                            break;
                        case 3:
                            $("#www_templates_id").focus();
                            update_user_message($("#msg_template_required").val());
                            break;
                        default:
                            $.ajax({
                                type: 'POST',
                                url: project_url + "/app/admin/new_page/ajax_new_page.php",
                                data: {
                                    page_url: $("#page_url").val(),
                                    page_title: $("#page_title").val(),
                                    www_templates_id: $("#www_templates_id").val(),
                                    parent_id: $("#parent_id").val()
                                },
                                success: function (data) {
                                    if (data == '') {
                                        window.location = $("#page_url").val();
                                    } else {
                                        update_user_message(data);
                                    }
                                }
                            });
                    }
                }
            },
            {
                text: btn_cancel_new_page_value,
                click: function () {
                    $(this).dialog("close");
                }
            }
        ]
    });

    $('#new_page_form').keypress(function (e) {
        if (e.keyCode == $.ui.keyCode.ENTER) {
            $('.ui-button:contains("' + btn_do_new_page_value + '")').click()
        }
    });

});


function validate_new_page_form() {
    if ($("#page_url").val() == '') {
        return 1;
    }
    if ($("#page_title").val() == '') {
        return 2;
    }
    if ($("#www_templates_id").val() == 0) {
        return 3;
    }
    return 0;
}

function update_user_message(t) {
    $("#user_message").text(t)
    $("#user_message").addClass("ui-state-highlight");
    setTimeout(function () {
        $("#user_message").removeClass("ui-state-highlight", 1500);
        //$("#user_message").text('');
    }, 1500);
}