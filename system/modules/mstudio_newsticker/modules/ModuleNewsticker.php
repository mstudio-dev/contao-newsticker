<?php

$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/mstudio/assets/js/jquery.marquee.js';
$GLOBALS['TL_JQUERY'][] = '<script>$(\'.marquee\').marquee({duration: 12000});</script>';
 
class ModuleNewsticker extends Module
{
	/**
	 * Template
	 * @var string
	 */

	protected $strTemplate = 'mod_newsticker';
 
	/**
	 * Compile the current element
	 */
	protected function compile()
	{
        $time = time();

		/** @var \Contao\Database\Result $rs */
		$rs = Database::getInstance()
//			->query('SELECT * FROM tl_newsticker WHERE published=1 ORDER BY ticker');
            ->query('SELECT * FROM tl_newsticker WHERE published=1 AND (start = "" OR start <= '.$time.') AND (stop="" OR stop >= '.$time.') ORDER BY ticker');

		$this->Template->newstickers = $rs->fetchAllAssoc();
	}
}
