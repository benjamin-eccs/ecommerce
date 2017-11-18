<?php

namespace Hcode;

use Hcode\Page;

/**
 *
 */
class PageAdmin extends Page {

	function __construct($opts = [], $tpl_dir = "/views/admin/") {
		parent::__construct($opts, $tpl_dir);
	}
}

?>