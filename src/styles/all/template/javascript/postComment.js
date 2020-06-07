"use strict"

/// this is for comment box in each post
function PostComment() {

    var apiBaseUrl = './app.php/postcomment/';
    var _template = {
        commentrow:
            `<li class="postcomment-row" data-comment-id="{comment_id}">
                <div class="postcomment-avatar" data-comment-userid="{user_id}">
                    <a>    
                        {avatar}
                    </a>
                </div>
                <div class="postcomment-content">
                    <div class="postcomment-content-header">
                        <div class="postcomment-content-buttons">
                            {r_btn_edit}
                            {r_btn_del}
                            <span class="postcomment-time">{comment_time}</span>
                        </div>
                        <a href="./memberlist.php?mode=viewprofile&u={user_id}" class="postcomment-username" style="color:#{user_color};">
                            {username}
                        </a>
                        {r_div_like}
                    </div>
                    <div class="postcomment-text" data-comment="{comment}">
                        {comment}
                    </div>
                </div>
            </li>`,
        editform: `
            <div id="postcomment-edit" class="postcomment-form-edit">
                <h3>{edit_title}</h3>
                <fieldset class="postcomment-fields">
                    <div>{edit_confirm_text}</div>
                    <input type="text" class="postcomment-input" value="" />
                </fieldset>
                <fieldset class="submit-buttons">
                <input type="button" class="button2" name="confirm" value="{btn_ok}"></button>
                <input type="button" class="button2" name="cancel" value="{btn_cancel}"></button>
                </fieldset>
            </div>
        `,
        delform: `
            <div id="postcomment-delete" class="postcomment-form-delete">
                <h3>{edit_title}</h3>
                <fieldset class="postcomment-fields">
                    <div>{delete_confirm_text}</div>
                </fieldset>
                <fieldset class="submit-buttons">
                <input type="button" class="button2" name="confirm" value="{btn_ok}"></button>
                <input type="button" class="button2" name="cancel" value="{btn_cancel}"></button>
                </fieldset>
            </div>
        `
    }

    const _meta = {
        form_token: null,
        creation_time: null,
    };


    function init() {
        var $meta = $('#postcomment-meta');
        _meta.form_token = $meta.find('[name=form_token]').val();
        _meta.creation_time = $meta.find('[name=creation_time]').val();
        var $commentboxes = $('.postcomment');
        for (let i = 0; i < $commentboxes.length; i++) {
            const box = $commentboxes[i];
            setCommentBoxes(box);
        }
    }

    function setCommentBoxes(box) {
        var $box = $(box);
        var postid = $box.data('post-id');
        var $sendForm = $box.find('.postcomment-form');
        $box.on('click', '.postcomment-more', { id: postid, $box: $box }, more);
        $sendForm.on('submit', { id: postid, $box: $box }, add);
        bindCommentBoxEvent($box);

    }

    function bindCommentBoxEvent($box) {
        var $rows = $box.find('.postcomment-row');
        for (let i = 0; i < $rows.length; i++) {
            const row = $rows[i];
            bindCommentRowEvent(row);
        }
    }

    function bindCommentRowEvent(row) {
        var $row = $(row);
        var pl ={$row: $row};
        $row.on('click', '.btn-edit', pl, edit);
        $row.on('click', '.btn-delete', pl, del);
        $row.on('click', '.btn-like', pl, like);
        $row.on('click', '.btn-dislike', pl, dislike);
    }

    function more(e) {
        var postid = e.data.id;
        var $box = e.data.$box;
        var isMore = !!$(this).data('more');
        $(this).data('more', !isMore);
        if (!isMore) {
            getComments(postid).then(function (data) {
                onFetchSucess(data, $box);
            });
            $(this).text(postcommentTemplate.btn_less);
        }
        else {
            $box.find('.postcomment-row').slice(postcommentTemplate.preview_count).remove();
            var btnText = postcommentTemplate.btn_more.replace('%d', $box.find('.postcomment-more').data('count'));
            $box.find('.postcomment-more').text(btnText);
        }

    }

    function add(e) {
        e.preventDefault();
        var postid = e.data.id;
        var $box = e.data.$box;
        var $form = $(this);
        var text = $form.find('input.postcomment-input').val();
        var data = {
            comment: text
        };
        sendComment(postid, data).then(function (result) {
            $form.find('.postcomment-input').val('');
            var $newrow = createCommentRow(result.data);
            $box.find('.postcomment-list').prepend($newrow);
            var $moreBtn = $box.find('.postcomment-more');
            var count = Number($moreBtn.data('count')) + 1;
            if (Number.isFinite(count)) {
                $moreBtn.data('count', count);
                $moreBtn.text(postcommentTemplate.btn_more.replace('%d', count));
            }
            bindCommentRowEvent($newrow);
        }, function (e) {
            error(e);
        });
    }


    function edit(e) {
        var id = e.data.$row.data('comment-id');
        var $row = e.data.$row;
        var text = $row.find('.postcomment-text').data('comment');
        var $form = renderEditForm(text);
        phpbb.confirm($form, function (success) {
            var newText = $form.find('.postcomment-input').val();
            if (success) {
                var data = {
                    comment: newText
                };
                editComment(id, data).then(function (result) {
                    var $newrow = createCommentRow(result.data);
                    $row.replaceWith($newrow);
                    bindCommentRowEvent($newrow);
                })
            }
        });
    }
    function del(e) {
        var id = e.data.$row.data('comment-id');
        var $form = renderDeleteForm();
        phpbb.confirm($form, function (success) {
            if (success) {
                var data = {
                    form_token: _meta.form_token,
                    creation_time: _meta.creation_time,
                };
                delComment(id).then(function (result) {
                    if (result.result == "DELETED") {
                        e.data.$row.remove();
                    }
                })
            }
        });
    }

    function like(e) {
        var id = e.data.$row.data('comment-id');
        var data = {
            type: 1
        };
        likeComment(id, data).then(function (result) {
            onLikeSucess(result, e.data.$row);

        });
    }

    function dislike(e) {
        var id = e.data.$row.data('comment-id');
        var data = {
            type: 2
        };
        likeComment(id, data).then(function (result) {
            onLikeSucess(result, e.data.$row);
        });
    }



    function createCommentRow(row) {
        var templateRow;
        row['comment'] = row['comment'];
        row['btn_like_active'] = row['like_type'] == 1 ? 'active' : '';
        row['btn_dislike_active'] = row['like_type'] == 2 ? 'active' : '';
        templateRow = _template.commentrow.replace(/{(\w+)}/g, function (key, value) {
            if (value == 'r_btn_edit') {
                return row['able_edit'] ? '<a class="btn-edit"><i class="fa fa-pencil" /></a>' : '';

            }
            else if (value == 'r_btn_del') {
                return row['able_del'] ? '<a class="btn-delete"><i class="fa fa-trash-o" /></a>' : '';
            }
            else if (value == 'r_div_like') {
                return row['able_like'] ? `<div class="postcomment-like">
                    <span class="postcomment-like-count">` + row.likes + `</span>
                    <a class="btn-like {btn_like_active}"><i class="fa fa-thumbs-up"></i></a>
                    <span class="postcomment-dislike-count">` + row.dislikes + `</span>
                    <a class="btn-dislike {btn_dislike_active}"><i class="fa fa-thumbs-down"></i></a>
                    </div>` : '';
            }
            return row[value] || key;
        })
        var $row = $(templateRow);
        return $row;
    }



    function renderEditForm(comment) {
        var keys = postcommentTemplate;
        var t = _template.editform.replace(/{(\w+)}/g, function (key, val) {
            return keys[val] || key;
        })
        t = $(t);
        t.find('.postcomment-input').val(comment);
        return t;
    }

    function renderDeleteForm() {
        var keys = postcommentTemplate;
        var t = _template.delform.replace(/{(\w+)}/g, function (key, val) {
            return keys[val] || key;
        })
        t = $(t);
        return t;
    }

    function getComments(id) {
        return new Promise(function (res, rej) {
            $.ajax({
                url: apiBaseUrl + 'fetch/' + id,
                type: 'get',
                contentType: 'application/json',
                dataType: 'json',
                success(data) {
                    if (data.result == 'OK') {
                        res(data.data);
                    }
                },
                error: error
            })
        });
    }

    function sendComment(id, data) {
        return new Promise(function (res, rej) {
            $.ajax({
                url: apiBaseUrl + 'add/' + id,
                type: 'post',
                data: $.extend(data,_meta),
                success(data) {
                    res(data);
                },
                error: error
            })
        });
    }

    function editComment(id, data) {
        return new Promise(function (res, rej) {
            $.ajax({
                url: apiBaseUrl + 'edit/' + id,
                type: 'post',
                data: $.extend(data,_meta),
                success(data) {
                    res(data);
                },
                error: error
            })
        });
    }

    function delComment(id) {
        return new Promise(function (res, rej) {
            $.ajax({
                url: apiBaseUrl + 'del/' + id,
                type: 'post',
                data: $.extend({},_meta),
                success(data) {
                    res(data);
                },
                error: error
            })
        });
    }

    function likeComment(id, data) {
        return new Promise(function (res, rej) {
            $.ajax({
                url: apiBaseUrl + 'like/' + id,
                type: 'post',
                data: $.extend(data,_meta),
                success(data) {
                    res(data);
                },
                error: error
            })
        });
    }

    function onFetchSucess(data, $box) {
        var $commentlist = $box.find('.postcomment-list');
        $commentlist.empty();
        for (let i = 0; i < data.length; i++) {
            const row = data[i];
            let $row = createCommentRow(row);
            $commentlist.append($row);
        }
        $box.find('.postcomment-more').data('count', data.length);
        setBoxRowEvent($box);
        return $commentlist;
    }

    function onLikeSucess(result, $row) {
        const flag = result.data.status;

        if (flag == 'LIKED') {
            $row.find('.btn-like').addClass('active')
            $row.find('.btn-dislike').removeClass('active')
        }
        else if (flag == 'DISLIKED') {
            $row.find('.btn-like').removeClass('active')
            $row.find('.btn-dislike').addClass('active')
        }
        else {
            $row.find('.btn-like').removeClass('active')
            $row.find('.btn-dislike').removeClass('active')
        }

        $row.find('.postcomment-like-count').text(result.data.likes);
        $row.find('.postcomment-dislike-count').text(result.data.dislikes);

    }

    function error(e) {
        if (e.responseJSON) {
            alert(e.responseJSON.message || 'Unknown error.')
        }
    }

    function htmlEncode(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    return {
        init
    };

}

(function () {
    var _isLoaded = false;
    var _postcomment;

    $(function () {
        if (!_isLoaded) {
            _postcomment = new PostComment();
            _postcomment.init();
        }
    })

})();

