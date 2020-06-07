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
use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\user;
use phpbb\template\template;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class acp_listener implements EventSubscriberInterface
{

	/** @var PostComment */
	protected $core;

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
		helper $helper,
		user $user,
		language $lang,
		request_interface $request,
		template $template
	) {
		$this->core					= $core;
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
			'core.permissions' => 'apply_permission'
		];
	}

	public function apply_permission($event)
	{

		$permission_categories = [
			'postcomment' => [
				'u_postcomment_view',
				'u_postcomment_fetch',
				'u_postcomment_add',
				'u_postcomment_edit',
				'u_postcomment_delete',
				'u_postcomment_like',

				'f_postcomment_enable',
				'f_postcomment_peek',
				'f_postcomment_fetch',
				'f_postcomment_add',
				'f_postcomment_edit',
				'f_postcomment_delete',
				'f_postcomment_like',

				'm_postcomment_edit',
				'm_postcomment_delete',
				'a_postcomment'
			]
		];

		$postcomment_permissions = [];

		foreach ($permission_categories as $cat => $permissions) {
			foreach ($permissions as $permission) {
				$postcomment_permissions[$permission] = [
					'lang'	=> 'ACL_' . strtoupper($permission),
					'cat'	=> $cat,
				];
			}
		}

		$event['permissions'] = array_merge($event['permissions'], $postcomment_permissions);

		$event['categories'] = array_merge($event['categories'], [
			'postcomment'				=> 'ACP_CAT_POSTCOMMENT',
		]);
	}
}
