<?php

/**
 *
 * @package phpBB Extension - postComment
 * @copyright (c) 2020 Wind
 * @license GNU General Public License v2
 *
 */


namespace furexp\postcomment\acp;

class acp_postcomment_info
{
    function module()
    {
        return [
            'filename'  => '\furexp\postcomment\acp\acp_postcomment_module',
            'title'     => 'ACP_CAT_POSTCOMMENT',
            'modes'     => array(
                'common_setting'   => array(
                    'title' => 'ACP_POSTCOMMENT_SETTING',
                    'auth'  => 'ext_furexp/postcomment && acl_a_postcomment',
                    'cat'   => array('ACP_POSTCOMMENT_SETTING')
                )
            )
        ];
    }
}
