<?php
class Sort {
	function file($a, $b)
	{
	    return strcmp($a['file'],$b['file']);
	}

	function line($a, $b)
	{
	    return $a['line'] - $b['line'];
	}

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
	
	function sort_it($list,$sorttype)
	{
		usort($list,array($this,$sorttype));
		return $list;
	}

}

?>