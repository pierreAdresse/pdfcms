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

    public function transformDatetimeToStringFr($date)
    {
        $day    = $this->getStringDayFr($date);
        $month  = $this->getStringMonthFr($date);
        $dayNum = $date->format('j');
        $year   = $date->format('Y');

        return "$day $dayNum $month $year";
    }

    public function transformDatetimeToStringFrWithoutYear($date)
    {
        $day    = $this->getStringDayFr($date);
        $month  = $this->getStringMonthFr($date);
        $dayNum = $date->format('j');

        return "$day $dayNum $month";
    }

    private function getStringDayFr($date)
    {
        switch ($date->format('w')) {
            case 0:
                return 'Dimanche';
                break;
            case 1:
                return 'Lundi';
                break;
            case 2:
                return 'Mardi';
                break;
            case 3:
                return 'Mercredi';
                break;
            case 4:
                return 'Jeudi';
                break;
            case 5:
                return 'Vendredi';
                break;
            case 6:
                return 'Samedi';
                break;
        }
    }

    private function getStringMonthFr($date)
    {
        switch ($date->format('n')) {
            case 1:
                return 'janvier';
                break;
            case 2:
                return 'février';
                break;
            case 3:
                return 'mars';
                break;
            case 4:
                return 'avril';
                break;
            case 5:
                return 'mai';
                break;
            case 6:
                return 'juin';
                break;
            case 7:
                return 'juillet';
                break;
            case 8:
                return 'août';
                break;
            case 9:
                return 'septembre';
                break;
            case 10:
                return 'octobre';
                break;
            case 11:
                return 'novembre';
                break;
            case 12:
                return 'décembre';
                break;   
        }
    }
}