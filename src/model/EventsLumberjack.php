<?php

namespace XD\Events\Model;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\Tab;
use SilverStripe\Lumberjack\Model\Lumberjack;

/**
 * This class is responsible for filtering the SiteTree when necessary and also overlaps into
 * filtering only published posts.
 */
class EventsLumberjack extends Lumberjack
{
    public function updateCMSFields(FieldList $fields)
    {
        // todo get by filter upcoming
        $pages = EventPage::get()->filter([
            'ParentID' => $this->owner->ID
        ]);

        $gridField = GridField::create(
            'ChildPages',
            $this->getLumberjackTitle(),
            $pages,
            $this->getLumberjackGridFieldConfig()
        );

        $tab = Tab::create('ChildPages', $this->getLumberjackTitle(), $gridField);

        $fields->insertBefore('Main', $tab);
    }

    protected function getLumberjackTitle()
    {
        return _t(self::class . '.TabTitle', 'Events');
    }
}
