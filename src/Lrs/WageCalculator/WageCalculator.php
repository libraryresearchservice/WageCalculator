<?php 

/*
 * This file is part of the WageCalculator package.
 *
 * (c) Library Research Service / Colorado State Library <LRS@lrs.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lrs\WageCalculator;

class wageCalculator {
	
	protected $low;
	protected $high;
	protected $hours;
	protected $interval;
	protected $intervals = array('annually', 'hourly', 'monthly', 'weekly');
	protected $wage;
	
	public function __construct($low, $high, $hours, $interval) {
		$this->low = $this->filter($low);
		$this->high = $this->filter($high);
		$this->hours = $this->filter($hours);
		$this->interval = in_array($interval, $this->intervals) ? $interval : 'hourly';
		$this->calculate();
	}
	
	public function __invoke() {
		return $this->wage;	
	}
	
	protected function filter($val) {
		return abs(preg_replace('/[^0-9\.]/', '', $val));	
	}
	
	public function calculate() {
		$intervals = array('annually', 'hourly', 'monthly', 'weekly');
		$this->wage = array(
			'low'		=> array_fill_keys($this->intervals, false),
			'high'		=> array_fill_keys($this->intervals, false),
			'hours'		=> $this->hours,
			'interval'	=> $this->interval
		);
		foreach ( array('low', 'high') as $v ) {
			if ( !$this->{$v} ) {
				continue;
			}
			if ( $this->interval == 'annually' ) {
				$annually = $this->{$v};
			} else if ( $this->interval == 'monthly' ) {
				$annually = $this->{$v} * 12;
			} else if ( $this->interval == 'weekly' ) {
				$annually = $this->{$v} * 52;
			} else if ( $this->interval == 'hourly' ) {
				$annually = ( $this->{$v} * $this->hours ) * 52;
			}
			$this->wage[$v]['annually'] = '$'.number_format($annually, 2);
			$this->wage[$v]['hourly'] = '$'.number_format($annually / ( $this->hours * 52 ));
			$this->wage[$v]['monthly'] = '$'.number_format($annually / 12);
			$this->wage[$v]['weekly'] = '$'.number_format($annually / 52);
		}
		return $this->wage;
	}
		
}
