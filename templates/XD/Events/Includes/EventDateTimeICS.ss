BEGIN:VEVENT
UID:$ID
DTSTART;TZID=$TimeZone:$StartDateTime.Format("yyyyMMdd'T'HHmmss")
DTEND;TZID=$TimeZone:$EndDateTime.Format("yyyyMMdd'T'HHmmss")
DTSTAMP:$Created.Format("yyyyMMdd'T'HHmmss'Z'")
URL:$AbsoluteLink
SUMMARY:$Event.Title
DESCRIPTION:$Event.Content.FirstSentence
END:VEVENT