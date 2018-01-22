<?php

class movio_utils_TimelineDates
{
    static function formatDate($date)
    {
		if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(-?\d{2,4})$/', $date, $matches)) {
			return array(
				'year' => $matches[3],
				'month' => $matches[2],
				'day' => $matches[1]
			);
		} elseif (preg_match('/^(\d{1,2})\/(-?\d{2,4})$/', $date, $matches)) {
			return array(
				'year' => $matches[2],
				'month' => $matches[1]
			);
		} elseif (preg_match('/^(-?\d{2,4})$/', $date, $matches)) {
			return array(
				'year' => $matches[1]
			);
		}
    }
}