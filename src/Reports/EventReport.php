<?php

namespace XD\Events\Reports;

use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Reports\Report;
use XD\Events\Model\EventDateTime;
use XD\Events\Model\EventPage;

class GuestListReport extends Report
{
    public function title()
    {
        return _t(__CLASS__ . '.Title', 'Events');
    }

    public function sourceRecords($params, $sort, $limit)
    {
        $joinTable = EventPage::singleton()->baseTable();
        $events = EventDateTime::get()
            ->innerJoin($joinTable, "\"$joinTable\".\"ID\" = \"EventDateTime\".\"EventID\"");

        $from = null;
        $till = null;
        if (isset($params['DefinedPeriod'])) {
            $definedPeriod = $params['DefinedPeriod'];
            switch($definedPeriod) {
                case 'Day':
                    $from = date('Y-m-d');
                    $till = date('Y-m-d');
                    break;
                case 'Week':
                    $from = date('Y-m-d', strtotime('-1 week'));
                    $till = date('Y-m-d');
                    break;
                case 'Month':
                    $from = date('Y-m') . '-01';
                    $till = date('Y-m-t');
                    break;
                case 'Year':
                    $from = date('Y') . '-01-01';
                    $till = date('Y') . '-12-31';
                    break;
                default:
                case 'Other':
                    break;
            }
        }

        if (isset($params['CustomPeriodFrom'])) {
            $from = $params['CustomPeriodFrom'];
        }

        if (isset($params['CustomPeriodTill'])) {
            $till = $params['CustomPeriodTill'];
        }

        // start date end date
        if ($from) {
            $events = $events->where("(\"PinnedForever\" = 1) OR (\"StartDate\" >= '$from') OR (\"StartDate\" <= '$from' AND \"EndDate\" >= '$from')");
        }

        if ($till) {
            $events = $events->where("(\"PinnedForever\" = 1) OR (\"StartDate\" <= '$till')");
        }

        if (!$from && !$till) {
            $now = DBDatetime::now()->getValue();
            $events = $events->where("(\"PinnedForever\" = 1) OR (\"StartDate\" >= '$now') OR (\"StartDate\" <= '$now' AND \"EndDate\" >= '$now')");
        }

        if ($sort) {
            $events = $events->sort($sort);
        }

        if ($limit) {
            $events = $events->limit($limit);
        }

        return $events;
    }

    public function columns()
    {
        // TODO: add edit link
        $fields = [
            'Event.Title' => 'Title',
            'StartDate.Nice' => 'Startdatum',
            'EndDate.Nice' => 'Einddatum',
            'StartTime.Nice' => 'Starttijd',
            'EndTime.Nice' => 'Eindtijd',
            // 'CMSEditLink' => ['link' => true]
        ];

        return $fields;
    }
    public function parameterFields()
    {
        $fields = FieldList::create(
            DropdownField::create(
                'DefinedPeriod', 
                _t('XD\Events\Reports.DefinedPeriod', 'Period'), 
                [
                    'Day' => _t('XD\Events\Reports.Day', 'Today'),
                    'Week' => _t('XD\Events\Reports.Week', 'This week'),
                    'Month' => _t('XD\Events\Reports.Month', 'This month'),
                    'Year' => _t('XD\Events\Reports.Year', 'This year'),
                    'Other' => _t('XD\Events\Reports.Other', 'Custom period'),
                ]
            )->setEmptyString(_t('XD\Events\Reports.FilterPeriod', 'Filter on period')),
            FieldGroup::create([
                DateField::create('CustomPeriodFrom',  _t('XD\Events\Reports.CustomPeriodFrom', 'From date')),
                DateField::create('CustomPeriodTill',  _t('XD\Events\Reports.CustomPeriodTill', 'Till date')),
            ])  
        );

        $this->extend('updateParameterFields', $fields);
        return $fields;
    }
}