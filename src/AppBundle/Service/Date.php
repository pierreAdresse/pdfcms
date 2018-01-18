<?php

namespace AppBundle\Service;

class Date
{
    public function getSeasonYear()
    {
        $today = new \Datetime('now');
        $month = $today->format('m');
        $year  = $today->format('Y');

        if ($month > 9) {
            $year++; 
        }

        return $year;
    }

    public function getSeasonDate()
    {
        $year = $this->getSeasonYear();
        $date = new \DateTime($year.'-01-01 00:00:01');

        return $date;
    }
}