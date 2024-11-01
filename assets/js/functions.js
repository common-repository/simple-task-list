jQuery(document).ready(function ($) {
    'use strict';
    const l10n = ajax_var.l10n;
// show message sob menu
    $("#tableBox").on('click', '.msgTable', function () {
        var msgData = $(this).data('table-msg');
        var email = msgData.user_email;
        var gravatar = $('<img>').attr({src: 'https://www.gravatar.com/avatar/' + md5(email)});
        $('#avatar-msgTable').html(gravatar);
        $('#name-msgTable').text(msgData.sender_name);
        $('#title-msgTable').text(msgData.title);
        $('#description-msgTable').text(msgData.description);
        $('#time-msgTable').text(ago(new Date(msgData.time_create)));
    })
// save message ajax

    $("#saveMsg").on('click', function () {
        let self = this;
        var user_id = $('#msg_users').val();
        var title = $('#input-title').val();
        var description = $('#description-area').val();
        if (user_id === '' || title === '') {
            alert(l10n.required_user_and_title)
        } else {
            $(self).addClass('event-none')
            //  Save message
            jQuery.ajax({
                type: 'POST',
                data: {
                    action: 'save',
                    security: ajax_var.nonce,
                    user_id: user_id,
                    title: title,
                    description: description
                },
                url: ajax_var.url,
                success: function (resp) {
                    $(self).removeClass('event-none')
                    window.location.reload()
                } ,
                error: function () {
                    alert('Error')
                }
            });
        }

    })

// change status message ajax
    $(".changeStatus").on('click', function () {
        let msg_id = $(this).data('msg-id');
        $("#msg-id-" + msg_id).addClass('on-processed');
        let status = $(this).data('status');
        // Change status
        jQuery.ajax({
            type: 'POST',
            data: {
                action: 'status',
                security: ajax_var.nonce,
                msg_id: $(this).data('msg-id'),
                status: status
            },
            url: ajax_var.url,
            success: function (resp) {
                // if (resp && status === "done") {
                //     $("#msg-id-" + msg_id).hide();
                // }
                window.location.reload()
            }

        });


    })

// dropdown
    $("#dropdown_admin").on('click', function () {
        document.getElementById("myDropdown").classList.toggle("show");
    })

    $(".dropdown-content").on('click', 'li', function () {
        $("#dropbtn").html($(this).text());
        $('#value-hide').val($(this).attr('id'));
    });
    $('#filter').change(function () {
        if ($(this).val() === '' || $(this).val() === 'all') {
            $('#searchByuser').css('display', 'none');
        } else {
            $('#searchByuser').css('display', 'block');
        }
    });


// add data in show message fields

    $(document).ready(function () {
        var modal = document.querySelector(".modal");
        var triggers = document.querySelectorAll(".openModal");
        var closeButton = document.querySelector(".close-button");

        function toggleModal() {
            modal.classList.toggle("show-modal");
            $('body').toggleClass("hidden-overflow");
        }

        function windowOnClick(event) {
            if (event.target === modal) {
                toggleModal();
            }
        }

        for (var i = 0, len = triggers.length; i < len; i++) {
            triggers[i].addEventListener("click", toggleModal);
        }
        closeButton.addEventListener("click", toggleModal);
        window.addEventListener("click", windowOnClick);

        $('.satl-select').each(function () {
            $(this).select2({
                theme: 'bootstrap4',
                width: 'style',
                placeholder: $(this).attr('placeholder'),
                allowClear: Boolean($(this).data('allow-clear')),
            });
        });
        var $tabButtonItem = $('.nav-tabs li'),
            $tabContents = $('.tab-pane'),
            activeClass = 'active';

        $tabButtonItem.first().addClass(activeClass);
        $tabContents.not(':first').hide();

        $tabButtonItem.find('a').on('click', function (e) {
            var target = $(this).attr('href');
            $tabButtonItem.removeClass(activeClass);
            $(this).parent().addClass(activeClass);
            $tabContents.hide();
            $(target).show();
            e.preventDefault();
        });
        $('.openModal').on('click', function (e) {
            var tab = $(this).data('tab');
            $tabButtonItem.removeClass(activeClass);
            $tabContents.hide();
            $('[data-toggle="'+tab+'"]').parent().addClass(activeClass);
            $('#' + tab).show();
            $('.back-tab').show();
            $('.nav-tabs').show();
        });

        $('.msg-item').on('click', function (e) {
            if (e.target.tagName !== "BUTTON") {
                $tabButtonItem.removeClass(activeClass);
                $tabContents.hide();
                show_detail_modal(this)
            }
        })
        $('#create-task').on('click', function (e) {
            toggleModal();
            $tabButtonItem.removeClass(activeClass);
            $tabContents.hide();
            $('#new').addClass(activeClass);
            $('#new').show();
        })

        $('.view-msg').on('click', function (e) {
            toggleModal();
            $tabButtonItem.removeClass(activeClass);
            $tabContents.hide();
            show_detail_modal(this)
            $('.back-tab').hide();
            $('.nav-tabs').hide();

        })
        function show_detail_modal(e){
            $('#tab-hide').addClass(activeClass);
            $('#tab-hide').show();
            let uid = $('#satl-get-uid').val();
            let msgData = $(e).data('msg-detailed');
            let email = msgData.user_email;
            let gravatar = $('<img>').attr({src: 'https://www.gravatar.com/avatar/' + md5(email)});
            $('#avatar-msg').html(gravatar);
            $('#name-msg').text(msgData.sender_name);
            $('#title-msg').text(msgData.title);
            $('#description-msg').text(msgData.description);
            $('#time-msg').text(ago(new Date(msgData.time_create)));
            if (uid === msgData.user_id){
                if (msgData.status !== "done"){
                    $('#btn_submit').show()
                    $('#btn_submit').attr({
                        'data-user-id': msgData.user_id,
                        'data-msg-id': msgData.id,
                        'data-status': 'done'
                    });
                }
            }else {
                $('#btn_submit').hide()
            }


        }
        $('.back-tab').on('click', function (e) {
            $tabButtonItem.removeClass(activeClass);
            $tabContents.hide();
            $('#task-list').show();

        })

    });

    function ago(val) {
        val = 0 | (Date.now() - val) / 1000;
        var unit, length = {
            [l10n.s]: 60, [l10n.i]: 60, [l10n.h]: 24, [l10n.d]: 7, [l10n.w]: 4.35,
            [l10n.m]: 12, [l10n.y]: 10000
        }, result;

        for (unit in length) {
            result = val % length[unit];
            if (!(val = 0 | val / length[unit]))
                return result + ' ' + (result - 1 ? unit : unit) + ' ' + [l10n.ago];
        }
    }
})