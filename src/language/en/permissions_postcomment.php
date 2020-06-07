<?php

/**
 *
 * @package phpBB Extension - postComment
 * @copyright (c) 2020 Wind
 * @license GNU General Public License v2
 *
 */

if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = array();
}



//permissions
$lang = array_merge($lang,[
    'ACL_U_POSTCOMMENT_USE' => 'Can user use PostComment.',
    'ACL_U_POSTCOMMENT_FETCH' => 'Can user get comments.',
    'ACL_U_POSTCOMMENT_ADD' => 'Can user submit comment.',
    'ACL_U_POSTCOMMENT_EDIT' => 'Can user edit own comments.',
    'ACL_U_POSTCOMMENT_DELETE' => 'Can user delete own comments.',
    'ACL_U_POSTCOMMENT_LIKE' => 'Can user like or dislike comments.',

    'ACL_F_POSTCOMMENT_ENABLE' => 'Enable PostComment.',
    'ACL_F_POSTCOMMENT_FETCH' => 'Can get comments.',
    'ACL_F_POSTCOMMENT_ADD' => 'Can submit comment.',
    'ACL_F_POSTCOMMENT_EDIT' => 'Can edit own comments.',
    'ACL_F_POSTCOMMENT_DELETE' => 'Can delete own comments.',
    'ACL_F_POSTCOMMENT_LIKE' => 'Can like or dislike comments.',

    'ACL_A_POSTCOMMENT' => 'Can edit PostComment settings.',

    'ACL_M_POSTCOMMENT_EDIT' => "Can edit anyone's comments.",
    'ACL_M_POSTCOMMENT_DELETE' => "Can delete anyone's comments.",
]);





$lang = array_merge($lang,[
    'ACP_CAT_POSTCOMMENT' => 'PostComment'
]);
