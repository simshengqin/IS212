<?php

class Sort {

####################
## Bootstrap Sort ##
####################

	function comp($a, $b)
	{
		if ($a['file'] == $b['file']) {
			return $a['line'] - $b['line'];
		}
		return strcmp($a['file'], $b['file']);
	}

	function bootstrap($a, $b)
	{
		return strcmp(end($a),end($b));
	}

#####################
## Bid Amount Sort ##
#####################

	function bidAmount ($a, $b)
	{
	return $a['amount'] < $b['amount'] ? 1:-1;
	}

################
### For Dump ###
################

	function course($a, $b)
	{
		$temp_a = preg_split("/((?<=[a-z])(?=\d))/i", $a['course']);
		$temp_b = preg_split("/((?<=[a-z])(?=\d))/i", $b['course']);

		if (sizeof($temp_a) == 2 && sizeof($temp_b) == 2){
			if ($temp_a[0] == $temp_b[0])
				if (is_numeric($temp_a[1]) && is_numeric($temp_b[1]))
					return $temp_a[1] - $temp_b[1];
				else
					return strcmp($temp_a[1], $temp_b[1]);
			return strcmp($temp_a[0], $temp_b[0]);
		}
		else 
			return strcmp($temp_a[0], $temp_b[0]);
		
		// return strcmp($a['course'], $b['course']);
	}

	function student($a, $b)
	{
		return strcmp($a['userid'], $b['userid']);
	}

	function section($a, $b)

	{	$temp_a = preg_split("/((?<=[a-z])(?=\d))/i", $a['section']);
		$temp_b = preg_split("/((?<=[a-z])(?=\d))/i", $b['section']);
		if ($a['course'] == $b['course'])
			return $temp_a[1] - $temp_b[1];
		return strcmp($a['course'], $b['course']);
	}
	
	function prerequisite($a, $b)
	{
		if ($a['course'] == $b['course'])
			return strcmp($a['prerequisite'], $b['prerequisite']);
		return strcmp($a['course'], $b['course']);
	}

	function course_completed($a, $b)
	{
		if ($a['course'] == $b['course'])
			return strcmp($a['userid'], $b['userid']);
		return strcmp($a['course'], $b['course']);
	}

	function bid($a, $b)
	{
		if ($a['course'] == $b['course']){
			if ($a['section'] == $b['section']){
				if ($a['amount'] == $b['amount']){
					return strcmp($a['userid'], $b['userid']);
				}
				return $a['amount'] < $b['amount'] ? 1:-1;
			}
			return strcmp($a['section'], $b['section']);
		}
		return strcmp($a['course'], $b['course']);
	}
	
	function section_student($a, $b)
	{
		if ($a['course'] == $b['course'])
			return strcmp($a['userid'], $b['userid']);
		return strcmp($a['course'], $b['course']);
	}



#####################
### Main Function ###
#####################

	function sort_it($list,$sorttype)
	{
		usort($list,array($this,$sorttype));
		return $list;
	}

}

?>