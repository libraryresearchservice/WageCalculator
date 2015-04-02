<?php namespace Lrs\WageCalculator;

class WageCalculator {

	public $wage = array();
	
	public function __construct($params = array()) {
		$this->params($params);
	}
	
	public function params($params, $overwrite = true) {
		if ( $overwrite ) {
			$this->wage = $params;
		} else {
			$this->wage = array_merge($this->wage, $params);
		}
		$default = array_fill_keys(array(
		  'hours_per_week',
		  'low_hourly',
		  'low_weekly',
		  'low_monthly',
			'low_annually',
			'high_hourly',
			'high_weekly',
			'high_monthly',
			'high_annually',
			'interval',
			'success'
		), false);
		$this->wage = array_merge($default, $this->wage);
		if ( !is_numeric($this->wage['hours_per_week']) || $this->wage['hours_per_week'] <= 0 ) {
			$this->wage['hours_per_week'] = 40;	
		}
		foreach ( $this->wage as $k => &$v ) {
			if ( !is_bool($v) ) {
				$val = preg_replace('/[^0-9\.-]/', '', $v);
				if ( preg_match('/(low_|high_)/', $k) ) {
					if ( !is_numeric($val) || $val <= 0 ) {
						$val = false;	
					}
				}
				$v = $val;
			}
		}
		return $this;
	}
	
	public function calculate($params = array(), $format = true) {
		if ( is_array($params) && sizeof($params) > 0 ) {
			$this->params($params);	
		}
		foreach ( array('low', 'high') as $v ) {
			if ( $this->wage[$v.'_annually'] ) {
				$this->wage[$v.'_hourly'] = $this->wage[$v.'_annually'] / ( $this->wage['hours_per_week'] * 52 );
				$this->wage[$v.'_weekly'] = $this->wage[$v.'_annually'] / 52;
				$this->wage[$v.'_monthly'] = $this->wage[$v.'_annually'] / 12;
				$this->wage['interval'] = 'annually';
			} else if ( $this->wage[$v.'_monthly'] ) {
				$this->wage[$v.'_annually'] = $this->wage[$v.'_monthly'] * 12;
				$this->wage[$v.'_weekly'] = $this->wage[$v.'_annually'] / 52;
				$this->wage[$v.'_hourly'] = $this->wage[$v.'_annually'] / ( $this->wage['hours_per_week'] * 52 );
				$this->wage['interval'] = 'monthly';
			} else if ( $this->wage[$v.'_weekly'] ) {
				$this->wage[$v.'_hourly'] = $this->wage[$v.'_weekly'] / $this->wage['hours_per_week'];
				$this->wage[$v.'_annually'] = $this->wage[$v.'_weekly'] * 52;
				$this->wage[$v.'_monthly'] = $this->wage[$v.'_annually'] / 12;
				$this->wage['interval'] = 'weekly';
			} else if ( $this->wage[$v.'_hourly'] ) {
				$this->wage[$v.'_weekly'] = $this->wage[$v.'_hourly'] * $this->wage['hours_per_week'];
				$this->wage[$v.'_annually'] = $this->wage[$v.'_hourly'] * ( $this->wage['hours_per_week'] * 52 );
				$this->wage[$v.'_monthly'] = $this->wage[$v.'_annually'] / 12;
				$this->wage['interval'] = 'hourly';
			} 
		}
		unset($v);
		if ( $format ) {
			foreach ( $this->wage as $k => &$v ) {
				if ( !is_bool($v) && preg_match('/(low_|high_)/', $k) ) {
					$v = '$'.number_format($v, 2);
					if ( !$this->wage['success'] ) {
						$this->wage['success'] = true;	
					}
				}
			}
		}
		return $this->wage;
	}
	
}
