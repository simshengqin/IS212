<?php
class Sort {
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

################
### For Dump ###
################

	function course($a, $b)
	{
		return strcmp($a['course'], $b['course']);
	}

	function student($a, $b)
	{
		return strcmp($a['userid'], $b['userid']);
	}

	function section($a, $b)
	{
		if ($a['course'] == $b['course'])
			return strcmp($a['section'], $b['section']);
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