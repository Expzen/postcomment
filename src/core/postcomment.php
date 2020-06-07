<?php

/**
 *
 * @package phpBB Extension - postComment
 * @copyright (c) 2020 Wind
 * @license GNU General Public License v2
 *
 */


namespace furexp\postcomment\core;

use phpbb\auth\auth;
use phpbb\exception\http_exception;
use phpbb\language\language;
use phpbb\config\config;
use furexp\postcomment\core\settings;
use phpbb\template\template;
use phpbb\user;

class postcomment
{

    /** @var user */
    protected $user;

    /** @var template */
    protected $template;

    /** @var language */
    protected $lang;

    /** @var auth */
    protected $auth;

    /** @var data */
    protected $data;

    /** @var config */
    protected $config;

    /** @var settings */
    protected $settings;


    /**
     * Constructor
     *
     * @param auth					$auth
     * @param config				$config
     * @param language				$lang
     * @param template				$template
     * @param user					$user
     */
    public function __construct(
        data $data,
        settings $settings,
        auth $auth,
        config $config,
        language $lang,
        template $template,
        user $user
    ) {
        $this->data             = $data;
        $this->settings         = $settings;
        $this->auth             = $auth;
        $this->config           = $config;
        $this->lang             = $lang;
        $this->template            = $template;
        $this->user             = $user;
    }


    //api section

    /*
    * get comments of a specific post
    */
    public function api_fetch($post_id)
    {
        $post = $this->get_post($post_id);
        $forum_id = $post['forum_id'];
        if (!$this->check_fetch_permission($forum_id)) {
            throw new http_exception(403, 'POSTCOMMENT_FORBIDDEN');
        }

        $rows = $this->data->get_comment_rowset($post_id, $this->user->data['user_id']);
        //add row detail
        $avatars = [];
        $rows = $this->format_comment_rows($rows, $forum_id, $avatars);
        return $rows;
    }

    /**
     *  get preview comments of a post
     * @param $post_id
     */

    public function api_peek($post_id)
    {
        $post = $this->get_post($post_id);
        $forum_id = $post['forum_id'];
        if (!$this->check_view_permission($forum_id)) {
            throw new http_exception(403, 'POSTCOMMENT_FORBIDDEN');
        }
        //sql

        $length_of_each = $this->config['postcomment_peek_count'];
        $avatars = [];
        $comments = $this->data->get_comment_rowset($post_id, $this->user->data['user_id'], $length_of_each);
        $comments = $this->format_comment_rows($comments, $forum_id, $avatars);

        return $comments;
    }

    /**
     * get a set of perview comments of selected posts
     * @param $post_id
     */
    public function parital_peek_posts($post_ids)
    {
        if(count($post_ids) == 0)
        {
            return [];
        }
        //sql
        $post = $this->get_post($post_ids[0]);
        $forum_id = $post['forum_id'];
        $topic_id = $post['topic_id'];
        $forum_comment_visible = $this->check_fetch_permission($forum_id);
        $comment_sets = [];

        $length_of_each = $this->config['postcomment_peek_count'];
        $avatars = [];
        $comments = [];
        $comment_counts = $this->data->get_posts_comment_count($post_ids);

        //set comment data into list
        foreach ($post_ids as $post_id) {
            if ($forum_comment_visible) {
                $comments[$post_id] = $this->data->get_comment_rowset($post_id, $topic_id, $this->user->data['user_id'], $length_of_each);
            }
        }

        //et comment list and total count of each post into export rowset.
        foreach ($post_ids as $post_id) {
            if ($forum_comment_visible) {
                $comment_sets[$post_id] = [
                    'list' => $this->format_comment_rows($comments[$post_id], $forum_id, $avatars),
                    'total' => $comment_counts[$post_id]
                ];
            }else{
                $comment_sets[$post_id] = [
                    'list' => [],
                    'total' => 0
                ];
            }
        }

        return $comment_sets;
    }

    /**
     * Submit a comment
     * @param $post_id
     * @param $comment comment string
     */
    public function api_add(int $post_id, string $comment)
    {

        $post = $this->get_post($post_id);
        $forum_id = $post['forum_id'];

        if (!$this->check_add_permission($forum_id)) {
            throw new http_exception(403, 'POSTCOMMENT_FORBIDDEN');
        }

        $commit_data = [
            'comment' => $this->process_comment($comment),
            'user_id' => $this->user->data['user_id'],
            'user_ip' => $this->user->ip,
            'comment_time' => time(),
            'edit_time' => 0,
            'post_id' => $post_id,
            'is_deleted' => false
        ];

        $comment_id = $this->data->add_comment($commit_data);
        $new_comment = $this->data->get_comment_row($comment_id, $this->user->data['user_id']);
        $new_comment = $this->format_row($new_comment);
        $new_comment = $this->set_permission_info($new_comment, $forum_id);
        $this->data->update_comment_count($post_id);
        return $new_comment;
    }

    /**
     * Edit a comment
     * @param $id comment id
     * @param $comment comment string
     */
    public function api_edit(int $id, string $comment)
    {
        $old_comment = $this->get_comment($id, true);
        $post = $this->get_post($old_comment['post_id']);
        $forum_id = $post['forum_id'];
        if (!$this->check_edit_permission($old_comment, $forum_id)) {
            throw new http_exception(403, 'POSTCOMMENT_FORBIDDEN');
        } else {
            $commit_data = [
                'comment' => $this->process_comment($comment),
                'edit_time' => time(),
                'edit_user_id' => $this->user->data['user_id'],
            ];
            $this->data->edit_comment($id, $commit_data);
        }
        $new_comment = $this->data->get_comment_row($id, $this->user->data['user_id']);


        $new_comment = $this->format_row($new_comment);
        $new_comment = $this->set_permission_info($new_comment, $forum_id);
        return $new_comment;
    }

    /**
     * Delete a comment
     * @param $id comment id
     */
    public function api_del(int $id)
    {
        $comment = $this->get_comment($id, true);
        if (!$this->check_delete_permission($comment)) {
            throw new http_exception(403, 'POSTCOMMENT_FORBIDDEN');
        } else {
            $this->data->del_comment($id);
            $this->data->update_comment_count($comment['post_id']);
        }

        return 'OK';
    }

    /**
     * Set comment like
     * @param $id comment_id
     * @param $type comment_type, 1 for like, 2 for dislike, 0 for remove like
     */
    public function api_like(int $id, int $type)
    {
        if (!($type >= 0 && $type <= 2)) {
            throw new http_exception(400, 'POSTCOMMENT_BAD_REQUEST');
        }
        if (!$this->check_like_permission($id)) {
            throw new http_exception(403, 'POSTCOMMENT_FORBIDDEN');
        }


        $like_data = [
            'comment_id' => $id,
            'user_id' => $this->user->data['user_id'],
            'like_type' => $type,
            'like_time' => time()
        ];
        $comment_like = $this->data->get_like($like_data);
        if ($comment_like['like_type'] == $type) {
            //if double like, mean remove like
            $type = 0;
        }

        $this->data->delete_like($like_data);
        if ($type > 0) {
            $this->data->add_like($like_data);
        }
        $this->data->update_like_count($id);

        $result = [];

        switch ($type) {
            case 0:
                $result['status'] = 'REMOVED';
                break;
            case 1:
                $result['status'] =  'LIKED';
                break;
            case 2:
                $result['status'] =  'DISLIKED';
                break;
        }
        $like_counts = $this->data->get_like_count($id);
        $this->format_like_conunt($like_counts);
        $result = array_merge($result, $like_counts);
        return $result;
    }

    /**
     * Get a comment
     * @param $comment_id comment id
     * @param $throw_error Will throw error if true
     */
    protected function get_comment(int $comment_id, bool $throw_error = false)
    {
        if (!$comment_id) {
            if ($throw_error) {
                throw new http_exception(400, 'POSTCOMMENT_BAD_REQUEST');
            }
        }
        $comment = $this->data->get_comment($comment_id);
        if (!$comment) {
            if ($throw_error) {
                throw new http_exception(404, 'POSTCOMMENT_NOT_FOUND');
            }
        }
        return $comment;
    }

    /**
     * Check comment view permission of forum
     * @param $forum_id forum id
     */
    public function check_view_permission(int $forum_id)
    {
        if (
            $this->auth->acl_get('u_postcomment_use') &&
            $this->auth->acl_get('f_postcomment_enable', $forum_id) &&
            $this->auth->acl_get('f_read', $forum_id)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check comment fetch permission of forum
     * @param $forum_id forum id
     */
    public function check_fetch_permission(int $forum_id)
    {
        if (
            $this->check_view_permission($forum_id) &&
            $this->auth->acl_get('f_postcomment_fetch', $forum_id) &&
            $this->auth->acl_get('u_postcomment_fetch')
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check comment add permission of forum
     * @param $forum_id forum id
     */
    public function check_add_permission($forum_id)
    {
        //is board exist and allow posting?
        //valid user?
        if (
            $this->check_view_permission($forum_id) &&
            $this->user->data['is_registered'] &&
            $this->auth->acl_get('u_postcomment_add') &&
            $this->auth->acl_get('f_postcomment_add', $forum_id)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check comment edit permission of forum
     * @param $comment A comment array contains post_id, forum_id and user_id
     */
    protected function check_edit_permission($comment, $forum_id = null)
    {
        if (!isset($forum_id)) {
            $post = $this->get_post($comment['post_id']);
            $forum_id = $post['forum_id'];
        }

        //is moderator?
        //is user is the original commenter?

        if ($this->auth->acl_get('m_postcomment_edit', $forum_id)) {
            //ok for moderator
            return true;
        } else if ($comment['user_id'] == $this->user->data['user_id']) {
            if (
                $this->auth->acl_get('u_postcomment_edit') &&
                $this->auth->acl_get('f_postcomment_edit', $forum_id)
            ) {
                //Is current user's comment and forum is allow to edit comment.
                return true;
            } else {
                //Not allow to edit post
                return false;
            }
        } else {
            //Deny user who want to edit other's comment.
            return false;
        }
    }

    /**
     * Check comment delete permission of forum
     * @param $comment A comment array contains post_id, forum_id and user_id
     */
    protected function check_delete_permission($comment, $forum_id = null)
    {
        if (!isset($forum_id)) {
            $post = $this->get_post($comment['post_id']);
            $forum_id = $post['forum_id'];
        }

        //is moderator?
        //is user is the original commenter?
        if ($this->auth->acl_get('m_postcomment_delete', $forum_id)) {
            //Ok for moderator
            return true;
        } else if ($comment['user_id'] == $this->user->data['user_id']) {
            if (
                $this->auth->acl_get('u_postcomment_delete') &&
                $this->auth->acl_get('f_postcomment_delete', $forum_id)
            ) {
                //Is current user's comment and forum is allow to delete comment.
                return true;
            } else {
                //Not allow to delete post
                return false;
            }
        } else {
            //Deny user who want to delete other's comment.
            return false;
        }
    }

    public function check_forum_like_permission($forum_id)
    {
        return $this->auth->acl_get('f_postcomment_like', $forum_id)
            && $this->auth->acl_get('u_postcomment_like');
    }


    public function check_like_permission($comment_id)
    {
        $comment = $this->get_comment($comment_id);
        $post = $this->get_post($comment['post_id']);
        $forum_id = $post['forum_id'];

        return $this->check_forum_like_permission($forum_id);
    }

    /**
     * Get a post
     * @param $id target post id
     */
    public function get_post($id)
    {
        $post = $this->data->get_post($id);
        if (!$post) {
            throw new http_exception(400, 'POSTCOMMENT_BAD_REQUEST');
        }
        return $post;
    }

    /** helpers */

    protected function format_comment_rows($rows, $forum_id, $avatars = [])
    {
        foreach ($rows as $index => $row) {
            $row = $this->format_row($row, $avatars);
            $row = $this->set_permission_info($row, $forum_id);
            $rows[$index] = $row;
        }
        return $rows;
    }

    /**
     * format avatar, time, like counts
     * @param array $row comment row
     * @param array $avatar
     */
    protected function format_row($row, $avatars = [])
    {
        $new_row = [
            'comment_id' => $row['comment_id'],
            'post_id' => $row['post_id'],
            'user_id' => $row['user_id'],
            'comment' => $row['comment'],
            'comment_raw' => $row['comment'],
            'username' => $row['username'],
            'user_color' => $row['user_colour'],
            'like_type' => $row['like_type'],
            'likes' => $row['likes'],
            'dislikes' => $row['dislikes'],
        ];
        if (isset($row['comment_time'])) {
            $new_row['comment_time'] = $this->user->format_date($row['comment_time']);
        }
        if (!isset($avatars[$row['user_id']])) {
            $avatars[$row['user_id']] = $this->get_avatar($row);
        }

        $this->format_like_conunt($new_row);

        $new_row['avatar'] = $avatars[$row['user_id']];
        return $new_row;
    }

    protected function format_like_conunt(&$row)
    {
        $like = $row['likes'];
        $dislike = $row['dislikes'];
        if (0 == $this->config['postcomment_like_count_type']) {
            $row['likes'] = '';
        } else if (2 == $this->config['postcomment_like_count_type']) {
            $row['likes'] = $this->get_percentage($like,$dislike);
        }

        if (0 == $this->config['postcomment_dislike_count_type']) {
            $row['dislikes'] = '';
        } else if (2 == $this->config['postcomment_dislike_count_type']) {
            $row['dislikes'] = $this->get_percentage($dislike,$like);
        }
    }

    /**
     * Insert permission info into comment row.
     * @param array $row comment row
     * @param int $forum_id
     */
    protected function set_permission_info($comment_row, $forum_id)
    {
        $comment_row = array_merge($comment_row, [
            'able_edit' => $this->check_edit_permission($comment_row, $forum_id),
            'able_del' => $this->check_delete_permission($comment_row, $forum_id),
            'able_like' => $this->check_forum_like_permission($forum_id),
        ]);
        return $comment_row;
    }

    /**
     * Get user avatar by user id.
     */
    public function get_user_avatar($id)
    {
        $user = $this->data->get_user($id);
        $avatar = $this->get_avatar($user);
        return $avatar;
    }

    protected function get_avatar($row)
    {
        $avatar = phpbb_get_user_avatar([
            'avatar'        => $row['user_avatar'],
            'avatar_type'    => $row['user_avatar_type'],
            'avatar_width'    => $row['user_avatar_width'] >= $row['user_avatar_height'] ? 32 : 0,
            'avatar_height'    => $row['user_avatar_width'] >= $row['user_avatar_height'] ? 0 : 32,
        ]);
        if (!$avatar) {
            $root_path = $this->settings->url('styles/' . rawurlencode($this->user->style['style_path']), true);
            $avatar = '<img src="' . $root_path . '/theme/images/no_avatar.gif" class="postcomment-avatar">';
        }

        return $avatar;
    }

    protected function get_percentage(int $target, int $other)
    {
        if ($target + $other > 0) {
            return $target / ($target + $other) * 100 . '%';
        } else {
            return '0%';
        }
    }

    protected function process_comment($comment_text)
    {
        $len_min = $this->config['postcomment_len_min'];
        $len_max = $this->config['postcomment_len_max'];

        if (utf8_strlen($comment_text) < $len_min) {
            throw new http_exception(400, 'TOO_SHORT');
        }
        if (utf8_strlen($comment_text) > $len_max) {
            throw new http_exception(400, 'TOO_LONG');
        }

        // content filter.
        $clear_text = htmlspecialchars_decode($comment_text);
        $clear_text = htmlspecialchars($clear_text);

        //$uid = $bitfield = $options = '';
        //$pc_bbcode = $pc_magic_urls = $pc_smilies = $pc_img = $pc_flash = $pc_quote = $pc_url = false;
        //generate_text_for_storage($clear_text, $uid, $bitfield, $options, $pc_bbcode, $pc_magic_urls, $pc_smilies, $pc_img, $pc_flash, $pc_quote, $pc_url, 'postcomment');


        return $clear_text;
    }
}
