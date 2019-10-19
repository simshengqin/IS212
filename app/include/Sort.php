<?php
class Sort {
	function comp($a, $b)
	{
		if ($a['file'] == $b['file']) {
			return $a['line'] - $b['line'];
		}
		return strcmp($a['file'], $b['file']);
	}

	function course($a, $b)
	{
		return strcmp($a['course'], $b['course']);
	}

	function student($a, $b)
	{
		return strcmp($a['userid'], $b['userid']);
	}

	function bootstrap($a, $b)
	{
		return strcmp(end($a),end($b));
	}
	
	function sort_it($list,$sorttype)
	{
		usort($list,array($this,$sorttype));
		return $list;
	}

}

?>