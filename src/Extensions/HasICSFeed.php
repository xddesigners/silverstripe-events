<?php

namespace XD\Events\Extensions;

use SilverStripe\Assets\FileNameFilter;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Extension;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\SSViewer;

class HasICSFeed extends Extension
{
    private static $allowed_actions = [
        'ics',
    ];

    public function ics(HTTPRequest $request)
    {
        $body = $this->owner->customise([
            'Host' => Director::absoluteBaseURL()
        ]);

        $fileData = $body->render()->getValue();
        $fileName = FileNameFilter::create()->filter(implode(' ', [
            SiteConfig::current_site_config()->getTitle(),
            $this->owner->Title
        ]));
        return HTTPRequest::send_file($fileData, "{$fileName}.ics", 'text/calendar');
    }
}