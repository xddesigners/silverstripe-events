<?php

namespace XD\Events\Model;

use PageController;
use SilverStripe\Model\List\PaginatedList;
use XD\Events\Extensions\HasICSFeed;

/**
 * Class EventsPageController
 * @mixin EventsPage
 */
class EventsPageController extends PageController
{
    private static $extensions = [
        HasICSFeed::class
    ];

    public function PaginatedList()
    {
        $events = $this->getUpcomingEvents();
        $paginatedEvents = PaginatedList::create($events, $this->getRequest());
        $pageSize = $this->PostsPerPage > 0
            ? $this->PostsPerPage
            : $events->count();


        $paginatedEvents->setPageLength($pageSize);
        return $paginatedEvents;
    }
}
