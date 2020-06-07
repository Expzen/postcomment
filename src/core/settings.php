<?php

/**
 *
 * @package phpBB Extension - postComment
 * @copyright (c) 2020 Wind
 * @license GNU General Public License v2
 *
 */


namespace furexp\postcomment\core;

use phpbb\user;
use phpbb\config\config;
use phpbb\auth\auth;

class settings
{

    /** @var string */
    protected $postcomment_table;
    protected $like_table;

    /** @var user */
    protected $user;

    /** @var config */
    protected $config;

    /** @var auth */
    protected $auth;

    /** @var root_path */
    protected $root_path;

    /** @var php_extension */
    protected $php_extension;

    ///
    /**
     * The configs that are store in th phpbb_config table.
     * @var array 
     * */
    protected $common_settings;

    public function __construct(
        $user,
        $config,
        $auth,
        $postcomment_table,
        $like_table,
        $root_path,
        $php_extension
    ) {
        $this->user = $user;
        $this->config = $config;
        $this->auth = $auth;
        $this->postcomment_table = $postcomment_table;
        $this->like_table = $like_table;
        $this->root_path = $root_path;
        $this->php_extension = $php_extension;
    }

    /**
     * @return string postcomment table name
     */
    public function get_postcomment_table()
    {
        return $this->postcomment_table;
    }

    /**
     * @return string postcomment like table name
     */
    public function get_like_table()
    {
        return $this->like_table;
    }

    public function get_postcomment_settings()
    {
        if(empty($this->common_settings))
        {
            $this->common_settings = [
                'postcomment_len_min' => ['default' => 1, 'validation' => ['num', false, 0, 499]],
                'postcomment_len_max' => ['default' => 200, 'validation' => ['num', false, 1, 500]],
                'postcomment_peek_count' => ['default' => 5, 'validation' => ['num', false, 0, 50]],
                'postcomment_form_timeout' => ['default' => 3600, 'validation' => ['num', false, 0, 6000]],
                'postcomment_like_count_type' => ['default' => 1, 'validation' => ['num', false, 0, 2]],
                'postcomment_dislike_count_type' => ['default' => 0, 'validation' => ['num', false, 0, 2]],
            ];
        }
        return $this->common_settings;
    }

    public function get_config(string $name)
    {
        return $this->config[$name];
    }

    public function set_config(string $name, $value)
    {
        $this->config->set($name,$value);
    }

    /**
     * @param string $path
     * @param bool $absolute_url
     * @return string
     */
    public function url($path, $absolute_url = false)
    {
        if ($absolute_url) {
            $this->board_url = generate_board_url() . '/';
        }

        $url = ($absolute_url ? $this->board_url : $this->root_path) . $path;

        return $url;
    }

    /**
	 * @param string $file
	 * @param string $function
	 */
	public function include_phpbb_function($file, $function)
	{
		if (!function_exists($function))
		{
			include($this->root_path . 'includes/functions_' . $file . '.' . $this->php_extension);
		}
	}
}
