<?php
 
/**
 * Table tl_screencast
 */
$GLOBALS['TL_DCA']['tl_newsticker'] = array
(
 
	// Config
	'config'   => array
	(
		'dataContainer'    => 'Table',
		'enableVersioning' => true,
		'sql'              => array
		(
			'keys' => array
			(
				'id' => 'primary'
			)
		),
	),
	
	// List
	'list'     => array
	(
		'sorting'           => array
		(
			'mode'        => 2,
			'fields'      => array('ticker'),
			'flag'        => 1,
			'panelLayout' => 'filter;sort,search,limit'
		),
	
	// Label
	'label'             => array
		(
			'fields' => array('ticker'),
			'format' => '%s',
		),

	// Global Operations
	'global_operations' => array
		(
			'all' => array
			(
				'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'       => 'act=select',
				'class'      => 'header_edit_all',
				'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
			)
		),

	
	// Operations
	'operations'        => array
		(
			'edit'   => array
			(
				'label' => &$GLOBALS['TL_LANG']['tl_newsticker']['edit'],
				'href'  => 'act=edit',
				'icon'  => 'edit.gif'
			),			
			'delete' => array
			(
				'label'      => &$GLOBALS['TL_LANG']['tl_newsticker']['delete'],
				'href'       => 'act=delete',
				'icon'       => 'delete.gif',
				'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_newsticker']['toggle'],
				'icon'                => 'visible.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
				'button_callback'     => array('tl_newsticker', 'toggleIcon')
			),
			'show'   => array
			(
				'label'      => &$GLOBALS['TL_LANG']['tl_newsticker']['show'],
				'href'       => 'act=show',
				'icon'       => 'show.gif',
				'attributes' => 'style="margin-right:3px"'
			),
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'       => 'ticker,published,start,stop'
	),

	// Fields
	'fields'   => array
	(
		'id'     => array
		(
			'sql' => "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp' => array
		(
			'sql' => "int(10) unsigned NOT NULL default '0'"
		),
		'ticker'  => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_newsticker']['ticker'],
			'inputType' => 'text',
			'exclude'   => true,
			'sorting'   => true,
			'flag'      => 1,
                        'search'    => true,
			'eval'      => array(
								'mandatory'   => true,
                                'unique'      => true,
                                'maxlength'   => 255,
								
 
			),
			'sql'       => "varchar(255) NOT NULL default ''"
		),
		
		'published' => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_newsticker']['published'],
			'exclude'   => true,
			'filter'    => true,
			'inputType' => 'checkbox',
			'tl_class'  => 'm12',
			'sql'       => "char(1) NOT NULL default ''"
		),
        'start' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_newsticker']['start'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                     => "varchar(10) NOT NULL default ''"
        ),
        'stop' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_newsticker']['stop'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
            'sql'                     => "varchar(10) NOT NULL default ''"
        )

    )
);

class tl_newsticker extends Backend
{
    /**
     * Ändert das Aussehen des Toggle-Buttons.
     * @param $row
     * @param $href
     * @param $label
     * @param $title
     * @param $icon
     * @param $attributes
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $this->import('BackendUser', 'User');
 
        if (strlen($this->Input->get('tid')))
        {
            $this->toggleVisibility($this->Input->get('tid'), ($this->Input->get('state') == 0));
            $this->redirect($this->getReferer());
        }
 
        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_newsticker::published', 'alexf'))
        {
            return '';
        }
 
        $href .= '&amp;id='.$this->Input->get('id').'&amp;tid='.$row['id'].'&amp;state='.$row[''];
 
        if (!$row['published'])
        {
            $icon = 'invisible.gif';
        }
 
        return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
    }
	/**
	* Toggle the visibility of an element
	* @param integer
	* @param boolean
	*/
	public function toggleVisibility($intId, $blnPublished)
	{
    // Check permissions to publish
    if (!$this->User->isAdmin && !$this->User->hasAccess('tl_newsticker::published', 'alexf'))
    {
        $this->log('Not enough permissions to show/hide record ID "'.$intId.'"', 'tl_newsticker toggleVisibility', TL_ERROR);
        $this->redirect('contao/main.php?act=error');
    }
 
    $this->createInitialVersion('tl_newsticker', $intId);
 
    // Trigger the save_callback
    if (is_array($GLOBALS['TL_DCA']['tl_newsticker']['fields']['published']['save_callback']))
    {
        foreach ($GLOBALS['TL_DCA']['tl_newsticker']['fields']['published']['save_callback'] as $callback)
        {
            $this->import($callback[0]);
            $blnPublished = $this->$callback[0]->$callback[1]($blnPublished, $this);
        }
    }
 
    // Update the database
    $this->Database->prepare("UPDATE tl_newsticker SET tstamp=". time() .", published='" . ($blnPublished ? '' : '1') . "' WHERE id=?")
        ->execute($intId);
    $this->createNewVersion('tl_newsticker', $intId);
	}	

	
}

