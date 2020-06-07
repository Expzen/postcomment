<?php

/**
 *
 * @package phpBB Extension - postComment
 * @copyright (c) 2020 Wind
 * @license GNU General Public License v2
 *
 */


namespace furexp\postcomment;

use phpbb\extension\base;

class ext extends base
{
    public function is_enableable()
    {
        $config = $this->container->get('config');
        
        return true;
    }
}

