<?
function block5()
{
	global $smlrt;
	global $K;
	global $k;


	$n = findMaxSmlrt($smlrt);

	$n1 = $n[0];
	$n2 = $n[1]; 
	$cnt = 0;
	while(count($smlrt) > $k)
	{
		$merged = mergeCluster($smlrt[$n1], $n1, $smlrt[$n2], $n2);
	
		#移除合併項目	
		unset($smlrt[$n1]);
		unset($smlrt[$n2]);
		
		
		#移除merge後的欄位
		$dup = $smlrt;
		foreach($dup as $key1 => $c)
		{
			$temp = array();
			foreach($c as $key2 => $v)
			{
				if($key2 == $n1 || $key2 == $n2)
					continue;
				else
					$temp[$key2] = $v;
			}
			$smlrt[$key1] = $temp;
		}

		#補滿相似度矩陣
		foreach($merged as $key => $v)
			$smlrt[$key][$n1 . '/' . $n2] = $v;
		
		$smlrt[$n1 . '/' . $n2] = $merged;
		$smlrt[$n1 . '/' . $n2][$n1 . '/' . $n2] = 0;


		#尋找下組相似度最大的組合
		$temp = findMaxSmlrt($smlrt);
		$n1 = $temp[0];
		$n2 = $temp[1];
		
		$cnt++;
		
		echo '.';
		if($cnt % 100 == 0)
			echo "\r\n<br>";
		flush();
	
	}
}

#return array(key,key)
function findMaxSmlrt($smlrt)
{
	#把所有相似度結果組成陣列
	$a = array();
	foreach($smlrt as $key1 => $c)
		foreach($c as $key2 => $v)
		{
			if($key1 == $key2)
				continue;
			$a[$key1 . '&' . $key2] = $v;
		}
	#傳回最大的那一組
	$k = array_keys($a, max($a));
	$k = explode('&', $k[0]);
	
	#return 2 key
	return array($k[0], $k[1]);
	
}

#合併 cluster
function mergeCluster($A, $nameA, $B, $nameB)
{
	
	$merged = array();

	foreach($A as $key => $c)
	{
		if(($key == $nameA) || ($key == $nameB))
			continue;
		
		#選大的
		if($c < $B[$key])
			$newValue = $B[$key];
		else
			$newValue = $c;
			
		$merged[$key] = $newValue;
		
	}
	return $merged;
}

?>