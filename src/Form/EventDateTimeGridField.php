<?php

namespace XD\Events\Form;

use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\View\Requirements;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use Symbiote\GridFieldExtensions\GridFieldTitleHeader;

/**
 * Class EventDateTimeGridField
 *
 * @author Bram de Leeuw
 */
class EventDateTimeGridField extends GridFieldConfig
{
    /**
     * The inline delete action ships with a fixed height + negative top margin, so its
     * icon sits above the row's inputs/checkboxes. Centre it to match the rest of the row.
     */
    private const DELETE_ALIGN_CSS = <<<'CSS'
.ss-gridfield-item .grid-field__col-compact { vertical-align: middle; }
.ss-gridfield-item .grid-field__col-compact .action--delete { height: auto; min-height: 0; margin: 0; padding: 0; line-height: 1; align-items: center; }
CSS;

    public function __construct($itemsPerPage = null)
    {
        parent::__construct();

        Requirements::customCSS(self::DELETE_ALIGN_CSS, 'xd-events-inline-delete-align');

        $this->addComponent(new GridFieldButtonRow('before'));
        $this->addComponent(new GridFieldTitleHeader());
        $this->addComponent(new GridFieldEditableColumns());
        $this->addComponent(new GridFieldAddNewInlineButton("buttons-before-left"));
        $this->addComponent(new GridFieldDeleteAction());
//        $this->addComponent($pagination = new GridFieldPaginator($itemsPerPage));
    }
}
