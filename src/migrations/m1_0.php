<?php

/**
 *
 * @package phpBB Extension - postComment
 * @copyright (c) 2020 Wind
 * @license GNU General Public License v2
 *
 */


 namespace furexp\postcomment\migrations;

 use phpbb\db\migration\migration;

 class m1_0 extends migration{
    
    static public function depends_on()
    {
        return array();
    }

    public function update_data()
    {
        return array(

            array('config.add', array('postcomment_version', '0.1.0')),

            //set configs
            array('config.add', array('postcomment_len_min', 1)),
            //per char
            array('config.add', array('postcomment_len_max', 200)),
            array('config.add', array('postcomment_peek_count', 5)),
            array('config.add', array('postcomment_form_timeout', 3600)),
            //in minutes
            array('config.add', array('postcomment_like_count_type', 1)),
            array('config.add', array('postcomment_dislike_count_type', 0)),
            //0: dont show, 1: show count, 2 show percentage

            //add permissions
            array('permission.add', array('u_postcomment_use',true)),
            array('permission.add', array('u_postcomment_fetch',true)),
            array('permission.add', array('u_postcomment_add',true)),
            array('permission.add', array('u_postcomment_edit',true)),
            array('permission.add', array('u_postcomment_delete',true)),
            array('permission.add', array('u_postcomment_like',true)),
            
            array('permission.add', array('f_postcomment_enable',false)),
            array('permission.add', array('f_postcomment_fetch',false)),
            array('permission.add', array('f_postcomment_add',false)),
            array('permission.add', array('f_postcomment_edit',false)),
            array('permission.add', array('f_postcomment_delete',false)),
            array('permission.add', array('f_postcomment_like',false)),

            array('permission.add', array('m_postcomment_edit',false)),
            array('permission.add', array('m_postcomment_delete',false)),
            array('permission.add',array('a_postcomment',true)),

            //set permission
            array('permission.permission_set', array('REGISTERED', 'u_postcomment_use', 'group')),
            array('permission.permission_set', array('REGISTERED', 'u_postcomment_fetch', 'group')),
            array('permission.permission_set', array('REGISTERED', 'u_postcomment_add', 'group')),
            array('permission.permission_set', array('REGISTERED', 'u_postcomment_like', 'group')),

            array('permission.permission_set', array('REGISTERED', 'f_postcomment_fetch', 'group', true)),
            array('permission.permission_set', array('REGISTERED', 'f_postcomment_add', 'group', true)),
            array('permission.permission_set', array('REGISTERED', 'f_postcomment_like', 'group', true)),

            array('permission.permission_set', array('GLOBAL_MODERATORS', 'm_postcomment_edit', 'group', true)),
            array('permission.permission_set', array('GLOBAL_MODERATORS', 'm_postcomment_delete', 'group', true)),

            array('permission.permission_set', array('ADMINISTRATORS', 'm_postcomment_edit', 'group', true )),
            array('permission.permission_set', array('ADMINISTRATORS', 'm_postcomment_delete', 'group', true)),
            array('permission.permission_set', array('ADMINISTRATORS', 'a_postcomment', 'group')),
            
            // Add a parent module to the Extensions tab
            array('module.add', array(
                'acp',
                'ACP_CAT_DOT_MODS',
                'ACP_CAT_POSTCOMMENT'
            )),

            // Add module
			array('module.add', array(
				'acp',
				'ACP_CAT_POSTCOMMENT',
				array(
					'module_basename'	=> '\furexp\postcomment\acp\acp_postcomment_module',
					'modes'				=> array(
						'common_setting',
					),
				),
			)),

        );
    }

    public function update_schema()
    {
        return array(
			'add_tables' => array(
				$this->table_prefix . 'postcomments' => array(
					'COLUMNS'		=> array(
                        'comment_id'	        => array('UINT', null, 'auto_increment'),
						'user_id'				=> array('UINT', 0),
						'user_ip'				=> array('VCHAR:40', ''),
						'comment'				=> array('TEXT_UNI', ''),
						'comment_time'			=> array('INT:11', 0),
                        'post_id'				=> array('UINT', 0),
                        'likes'                 => array('UINT', 0),
                        'dislikes'              => array('UINT', 0),
                        'edit_user_id'			=> array('UINT', 0),
                        'edit_time'				=> array('INT:11', 0),
                        'is_deleted'            => array('BOOL',0)
					),
					'PRIMARY_KEY'	=> 'comment_id',
                ),
                $this->table_prefix . 'postcomment_likes' => array(
					'COLUMNS'		=> array(
						'comment_id'			=> array('UINT', null),
						'user_id'				=> array('UINT', 0),
                        'like_type'				=> array('USINT', 0),
						'like_time'			    => array('INT:11', 0),
					),
					'PRIMARY_KEY'	=> array('comment_id','user_id'),
				),
            ),
            'add_columns' => array(
                $this->table_prefix . 'posts' =>array(
                        'comment_count' =>      array('UINT',0)
                )
            )
		);
    }

    public function revert_schema()
    {
        return array(
			'drop_tables' => array(
                $this->table_prefix . 'postcomments',
                $this->table_prefix . 'postcomment_likes'
            ),
            'drop_columns' => array(
                $this->table_prefix . 'posts' =>array(
                    'comment_count'
            )
            )
		);
    }

 }
