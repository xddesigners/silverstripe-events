<?php

namespace XD\Events\Model;

use PageController;
use SilverStripe\Assets\FileNameFilter;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\DataObject;

/**
 * Class EventPageController
 * @method EventPage data()
 */
class EventPageController extends PageController
{
    private static $allowed_actions = [
        'date',
        'ics'
    ];

    private static $url_handlers = [
        'date/$ID/$StartDate/$EndDate' => 'date'
    ];

    /**
     * @return DataObject|\SilverStripe\ORM\FieldType\DBField|string
     */
    public function getCurrentDate()
    {
        if ($date = DataObject::get_by_id(EventDateTime::class, $this->getRequest()->param('ID'))) {
            return $date;
        } elseif ($date = $this->data()->getUpcomingDate()) {
            return $date;
        } else {
            return EventDateTime::get()->filter([
                'EventID' => $this->ID,
            ])->first();
        }
    }


    public function ics(HTTPRequest $request)
    {
        if (!($id = $request->param('ID'))) {
            return $this->httpError(404, 'not found');
        }
            
        if (!($date = DataObject::get_by_id(EventDateTime::class, $id))) {
            return $this->httpError(404, 'not found');
        }
        
        $filter = FileNameFilter::create();
        $fileName = $filter->filter($date->getTitle());
        $data = $date->ics();
        header("Content-type:text/calendar");
        header('Content-Disposition: attachment; filename="' . $fileName . '.ics"');
        Header('Content-Length: ' . strlen($data));
        Header('Connection: close');
        echo $data;
    }
}
