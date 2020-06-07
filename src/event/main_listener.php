<?php

/**
 *
 * @package phpBB Extension - postComment
 * @copyright (c) 2020 Wind
 * @license GNU General Public License v2
 *
 */


namespace furexp\postcomment\event;

use furexp\postcomment\core\PostComment;
use phpbb\controller\helper;
use phpbb\event\data;
use phpbb\language\language;
use phpbb\config\config;
use phpbb\request\request_interface;
use phpbb\user;
use phpbb\template\template;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class main_listener implements EventSubscriberInterface
{

	/** @var PostComment */
	protected $core;

	/** @var config */
	protected $config;

	/** @var helper */
	protected $helper;

	/** @var user */
	protected $user;

	/** @var language */
	protected $lang;

	/** @var request_interface */
	protected $request;

	/** @var template */
	protected $template;


	/** @var array */
	protected $comments;

	/** @var bool */
	protected $is_enabled;


	/**
	 * Constructor
	 *
	 * @param PostComment		$postComment
	 * @param helper			$helper
	 * @param user				$user
	 * @param language			$lang
	 * @param request_interface	$request
	 * @param template			$template
	 */
	public function __construct(
		PostComment $core,
		config $config,
		helper $helper,
		user $user,
		language $lang,
		request_interface $request,
		template $template
	) {
		$this->core					= $core;
		$this->config 				= $config;
		$this->helper				= $helper;
		$this->user					= $user;
		$this->lang					= $lang;
		$this->request				= $request;
		$this->template				= $template;
	}

	/**
	 * Assign functions defined in this class to event listeners in the core
	 *
	 * @return array
	 */
	static public function getSubscribedEvents()
	{
		return [
			'core.user_setup'							=> 'set_lang',
			'core.viewtopic_assign_template_vars_before' => 'assign_topic_variable',
			'core.viewtopic_get_post_data' => 'viewtopic_get_post_data',
			'core.viewtopic_modify_post_row' => 'viewtopic_modify_post_row',
			'furexp.postcomment.api_before' => 'set_lang'
		];
	}

	public function set_lang(data $event)
	{
		//entrance of topic
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'furexp/postcomment',
			'lang_set' => ['common'],
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function assign_topic_variable(data $event)
	{
		$user_id = $this->user->data['user_id'];
		$username = $this->user->data['username'];
		$avatar = $this->core->get_user_avatar($user_id);
		$forum_id = $event['forum_id'];
		$this->is_enabled = $this->core->check_view_permission($forum_id);
		$this->template->assign_var('POSTCOMMENT_ENABLED', $this->is_enabled);
		if (!$this->is_enabled) {
			return;
		}
		
		add_form_key('postcomment');
		$append_data = [
			'POSTCOMMENT_USER_AVATAR' 	=>    $avatar,
			'POSTCOMMENT_USER_NAME' 	=>    $username,
			'POSTCOMMENT_USER_ID' 		=>    $user_id,
			'POSTCOMMENT_ALLOW_ADD' 	=>    $this->core->check_add_permission($forum_id),
			'POSTCOMMENT_ALLOW_FETCH' 	=>    $this->core->check_fetch_permission($forum_id),
			'POSTCOMMENT_LIKE_ENABLED'	=>	  $this->core->check_forum_like_permission($forum_id),
			'POSTCOMMENT_PREVIEW_COUNT' =>    $this->config['postcomment_peek_count']
		];
		$this->template->assign_vars($append_data);
	}

	/**
	 * Before query posts.
	 */
	public function viewtopic_get_post_data(data $event)
	{

		//query comments
		//Array with original post and user data
		$rows = $event['post_list'];
		$this->comments = $this->core->parital_peek_posts($rows);
	}

	public function viewtopic_modify_post_row(data $event)
	{

		if (!$this->is_enabled) {
			return;
		}

		$post_row = $event['post_row'];
		$post_id = $event['row']['post_id'];
		$comments = $this->comments[$post_id];

		$comment_template = array();

		foreach ($comments['list'] as $comment) {
			$comment_template[] = array(
				'AVATAR' => $comment['avatar'],
				'USERNAME' => $comment['username'],
				'USER_COLOR' => '#' . $comment['user_color'],
				'USER_ID' => $comment['user_id'],
				'TIME' => $comment['comment_time'],
				'COMMENT' => $comment['comment'],
				'COMMENT_RAW' => $comment['comment_raw'],
				'COMMENT_ID' => $comment['comment_id'],
				'BTN_LIKE_ACTIVE' => $comment['like_type'] == 1,
				'BTN_DISLIKE_ACTIVE' => $comment['like_type'] == 2,
				'LIKES' => $comment['likes'],
				'DISLIKES' => $comment['dislikes'],
				'ABLE_EDIT' => $comment['able_edit'],
				'ABLE_DELETE' => $comment['able_del'],
			);
		}

		$post_row['POSTCOMMENT_COMMENTS'] = $comment_template;
		$post_row['POSTCOMMENT_TOTAL'] = $comments['total'];
		$post_row['POSTCOMMENT_TOTAL_TEXT'] = $this->lang->lang('POSTCOMMENT_TOTAL', $comments['total']);

		$event['post_row'] = $post_row;

	}
}
