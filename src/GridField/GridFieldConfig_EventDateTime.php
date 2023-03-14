<?php

namespace XD\Events\GridField;

use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use Symbiote\GridFieldExtensions\GridFieldTitleHeader;

/**
 * Class EventDateTimeGridField
 *
 * @author Bram de Leeuw
 */
class GridFieldConfig_EventDateTime extends GridFieldConfig
{
    public function __construct($itemsPerPage = null)
    {
        parent::__construct();
        $this->addComponent(new GridFieldButtonRow('before'));
        $this->addComponent(new GridFieldTitleHeader());
        $this->addComponent(new GridFieldEditableColumns());
        $this->addComponent(new GridFieldAddNewInlineButton("buttons-before-left"));
        $this->addComponent(new GridFieldTitleHeader());

        $this->removeComponentsByType([
            GridField_ActionMenu::class,
            GridFieldFilterHeader::class,
            GridFieldSortableHeader::class,
            GridFieldPageCount::class,
            GridFieldPaginator::class,
            GridFieldDeleteAction::class
        ]);
    }
}
