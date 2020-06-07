<?php

/**
 *
 * @package phpBB Extension - postComment
 * @copyright (c) 2020 Wind
 * @license GNU General Public License v2
 *
 */


namespace furexp\postcomment\core;

use phpbb\db\driver\driver_interface as db_interface;
use phpbb\user;
use furexp\postcomment\core\settings;

class data
{

    /** @var user */
    protected $user;

    /** @var db_interface */
    protected $db;

    /** @var settings */
    protected $settings;

    /** @var array */
    protected $active_users;

    /**
     * Constructor
     *
     * @param settings              $settings
     * @param db_interface			$db
     * @param user					$user

     */

    public function __construct(
        settings $settings,
        db_interface $db,
        user $user
    ) {
        $this->settings                = $settings;
        $this->db                    = $db;
        $this->user                    = $user;
    }

    public function get_comment($id)
    {
        $sql_array = [
            'SELECT'    => 'c.*',
            'FROM'      => [$this->settings->get_postcomment_table() => 'c'],
            'WHERE' => 'c.comment_id = ' . $this->db->sql_escape($id),
        ];
        $sql = $this->db->sql_build_query('SELECT', $sql_array);
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        return $row;
    }

    public function get_comment_rowset(int $post_id, int $topic_id, int $user_id = -1, int $total = -1)
    {
        
        $sql = 'SELECT c.*,k.like_type, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height '
            . ' FROM ' . $this->settings->get_postcomment_table() . ' AS c '
            . ' LEFT JOIN ' . USERS_TABLE . ' AS u ON u.user_id = c.user_id '
            . ' LEFT JOIN ' . POSTS_TABLE . ' AS p ON p.post_id = c.post_id '
            . ' LEFT JOIN ( SELECT * FROM '. $this->settings->get_like_table()
            . ' WHERE user_id = ' . $user_id . ' ) AS k '
            . ' ON k.comment_id = c.comment_id '
            . ' WHERE c.post_id = ' . $post_id . ' AND c.is_deleted = 0 AND p.topic_id = ' . $topic_id 
            . ' ORDER BY c.comment_id DESC';

        if ($total > 0) {
            $result = $this->db->sql_query_limit($sql, $total);
        } else {
            $result = $this->db->sql_query($sql);
        }
        $rows = $this->db->sql_fetchrowset($result);
        $this->db->sql_freeresult($result);

        return $rows;
    }

    public function get_comment_row(int $comment_id, int $user_id = -1)
    {
        //sql
        $sql = 'SELECT c.*,k.like_type, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height FROM '
            . $this->settings->get_postcomment_table() . ' AS c LEFT JOIN ' .
            USERS_TABLE . ' AS u ON u.user_id = c.user_id LEFT JOIN ( SELECT * FROM '
            . $this->settings->get_like_table()
            . ' WHERE user_id = ' . $user_id . ' ) AS k ON k.comment_id = c.comment_id WHERE c.comment_id = '
            . $comment_id . ' AND is_deleted = 0';


        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        return $row;
    }

    public function add_comment($comment_data)
    {
        $this->db->sql_query('INSERT INTO '
            . $this->settings->get_postcomment_table()
            . ' ' . $this->db->sql_build_array('INSERT', $comment_data));
        return $this->db->sql_nextid();
    }

    public function get_comment_count_of_post(int $post_id)
    {
        $sql_array = [
            'SELECT'    => 'COUNT(*) as comment_count',
            'FROM'      => [$this->settings->get_postcomment_table() => 'c'],
            'WHERE' => 'c.post_id = ' . $this->db->sql_escape($post_id) . ' AND is_deleted = 0',
        ];


        $sql = $this->db->sql_build_query('SELECT', $sql_array);

        $result = $this->db->sql_query($sql);
        $count = (int) $this->db->sql_fetchfield('comment_count');
        return $count;
    }

    public function get_posts_comment_count(array $post_ids)
    {
        $sql_array = [
            'SELECT'    => 'post_id, comment_count',
            'FROM'      => [POSTS_TABLE => 'p'],
            'WHERE' => $this->db->sql_in_set('post_id', $post_ids),
        ];


        $sql = $this->db->sql_build_query('SELECT', $sql_array);

        $result = $this->db->sql_query($sql);
        $rowset = $this->db->sql_fetchrowset($result);
        $this->db->sql_freeresult($result);

        $set = [];
        for ($i=0; $i < count($rowset); $i++) { 
            $set[$rowset[$i]['post_id']] = $rowset[$i]['comment_count'];
        }

        return $set;
    }

    public function edit_comment(int $id, $comment_data)
    {
        $this->db->sql_query('UPDATE ' . $this->settings->get_postcomment_table()
            . ' SET ' . $this->db->sql_build_array('UPDATE', $comment_data)
            . ' WHERE comment_id = ' . (int) $id);
        return $this->db->sql_affectedrows();
    }

    public function del_comment(int $id, bool $hard = false)
    {
        if ($hard) {
            $this->db->sql_query('DELETE FROM ' . $this->settings->get_postcomment_table() . ' WHERE id = ' . (int) $id);
        } else {
            $this->db->sql_query('UPDATE ' . $this->settings->get_postcomment_table()
                . ' SET is_deleted = 1 '
                . ' WHERE comment_id = ' . (int) $id);
        }
        return $this->db->sql_affectedrows();
    }

    public function update_comment_count(int $post_id)
    {
        $this->db->sql_query('UPDATE ' . POSTS_TABLE
            . ' SET comment_count = ( SELECT COUNT(*) FROM ' . $this->settings->get_postcomment_table()
            . ' WHERE is_deleted = 0 AND post_id = ' . $post_id . ') WHERE post_id = ' . $post_id . ';');
        return $this->db->sql_affectedrows();
    }

    public function get_like($like_data)
    {
        $comment_id = $this->db->sql_escape($like_data['comment_id']);
        $user_id = $this->db->sql_escape($like_data['user_id']);

        $result = $this->db->sql_query('SELECT * FROM phpbb_postcomment_likes WHERE comment_id = ' . (int) $comment_id . ' AND user_id = ' . (int) $user_id);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        return $row;
    }

    public function get_likes(int $user_id, array $comment_ids)
    {
        $sql = 'SELECT comment_id, like_type FROM ' . $this->settings->get_like_table()
            . ' WHERE user_id = ' . $user_id . ' AND ' . $this->db->sql_in_set('comment_id', $comment_ids);
        $result = $this->db->sql_query($sql);
        $rows = $this->db->sql_fetchrowset($result);
        $this->db->sql_freeresult($result);
        return $rows;
    }

    public function add_like($like_data)
    {
        $this->db->sql_query('INSERT INTO '
            . $this->settings->get_like_table()
            . ' ' . $this->db->sql_build_array('INSERT', $like_data));
        return $this->db->sql_affectedrows();
    }

    public function update_like($like_data)
    {
        $this->db->sql_query('UPDATE ' . $this->settings->get_like_table()
            . ' SET ' . $this->db->sql_build_array('UPDATE', $like_data)
            . ' WHERE comment_id = ' . (int) $like_data['comment_id'] . ' AND user_id = ' . (int) $like_data['user_id']);
        return $this->db->sql_affectedrows();
    }

    public function delete_like($like_data)
    {
        $this->db->sql_query('DELETE FROM ' . $this->settings->get_like_table()
            . ' WHERE comment_id = ' . (int) $like_data['comment_id'] . ' AND user_id = ' . (int) $like_data['user_id']);
        return $this->db->sql_affectedrows();
    }

    public function get_like_count(int $comment_id)
    {
        $sql = 'SELECT likes,dislikes FROM ' . $this->settings->get_postcomment_table()
            . ' WHERE comment_id = ' . $comment_id;
        $result = $this->db->sql_query($sql);
        $rows = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        return $rows;
    }

    public function update_like_count(int $comment_id)
    {
        $comment_id = $this->db->sql_escape($comment_id);
        $this->db->sql_query('UPDATE ' . $this->settings->get_postcomment_table()
            . ' SET likes = ( SELECT COUNT(*) FROM ' . $this->settings->get_like_table()
            . ' WHERE like_type = 1 AND comment_id = ' . $comment_id . ') WHERE comment_id = ' . $comment_id . ';');
        $this->db->sql_query('UPDATE ' . $this->settings->get_postcomment_table()
            . ' SET dislikes = ( SELECT COUNT(*) FROM ' . $this->settings->get_like_table()
            . ' WHERE like_type = 2 AND comment_id = ' . $comment_id . ') WHERE comment_id = ' . $comment_id . ';');
        return $this->db->sql_affectedrows();
    }

    public function get_user(int $id)
    {
        $sql_array = [
            'SELECT'    => 'u.*',
            'FROM'      => [USERS_TABLE => 'u'],
            'WHERE' => 'u.user_id = ' . $this->db->sql_escape($id),
        ];
        $sql = $this->db->sql_build_query('SELECT', $sql_array);
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        return $row;
    }

    public function get_post(int $post_id)
    {
        $sql_array = [
            'SELECT'    => 'p.post_id, p.topic_id, p.forum_id',
            'FROM'      => [POSTS_TABLE => 'p'],
            'LEFT_JOIN' => [
                [
                    'FROM' => [TOPICS_TABLE => 't'],
                    'ON' => 't.topic_id = p.topic_id'
                ]
            ],
            'WHERE' => 'p.post_id = ' . $post_id .
                ' AND t.topic_visibility = ' . ITEM_APPROVED .
                ' AND p.post_visibility = ' . ITEM_APPROVED,
        ];
        $sql = $this->db->sql_build_query('SELECT', $sql_array);
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        return $row;
    }

    public function get_topic(int $topic_id)
    {
        $sql_array = [
            'SELECT'    => 't.topic_id, t.forum_id',
            'FROM'      => [TOPICS_TABLE => 't'],
            'WHERE' => 't.topic_id = ' . $topic_id .
                'AND t.topic_visibility = ' . ITEM_APPROVED,
        ];
        $sql = $this->db->sql_build_query('SELECT', $sql_array);
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        return $row;
    }
}
