<?php
class Sort {
	function file($a, $b)
	{
	    return strcmp($a['file'],$b['file']);
	}

	function line($a, $b)
	{
	    return strcmp($a['line'],$b['line']);
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