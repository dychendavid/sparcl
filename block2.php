<?
#block 2
function block2()
{
	global $psC;
	global $sC;
	global $Xs;
	
	global $K;
	
	$i=0;
	$cnt = sizeof($Xs);
	
	echo "determine cluster: " . date('H:i:s') . "\r\n<br>";
	flush();

	
	do{
		#歸類 每個點
		determineCluster();


		#找出中心點, 並算出新的K
		newK();

		$i++;

		#如前次的Cluster 分布有紀錄 且和這次的一樣, 則離開迴圈
		if(isset($psC) && $psC == $sC)
			break;
		echo '.';
		if($i % 100 == 0)
			echo "\r\n<br>";
		flush();
	}while($i < $cnt);
	echo '<br>Repeats:' . $i . "<br>\r\n<br>\r\n";
	flush();
	
}


#歸類
function determineCluster()
{
	global $sK;

	global $sC;
	global $psC;

	global $Xs;
	global $Ys;
	
	#如果sC是空的 表示是第一次歸類
	if(isset($sC))
	{
		$psC = $sC;
		$sC = array();
	}
	
	$cnt = count($Xs);
	for($i=0; $i < $cnt; $i++)
	{
		#把每個點到各 K 的距離存成陣列
		$t = array();
		foreach($sK as $s)
		{
			if($i != $s)
				$t[$s] = sqrt(pow($Xs[$i] - $Xs[$s], 2) + pow($Ys[$i] - $Ys[$s], 2));
			else
				$t[$s] = 0;
		}
			
		#紀錄各 K 裡有誰
		$cluster = array_keys($t, min($t));
		$sC[$cluster[0]][] = $i;

	}

	#洗掉各 cluster 的 index 
	sort($sC);	

}

#找出 K 的中心並選新的 K
function newK()
{
	global $sC;
	global $sK;
	global $center;
	
	global $Xs;
	global $Ys;
		
	$sK = array();
	
	foreach($sC as $key => $c)
	{
		$x = array();
		$y = array();
		$c2k = array();
		
		foreach($c as $a)
		{
			$x[] = $Xs[$a];
			$y[] = $Ys[$a];
		}
		
		#cluster 的中心
		$tc = center($x, $y);

		#紀錄每個點離中心的位置
		foreach($c as $a)
			$c2k[$a] = sqrt(pow($Xs[$a] - $tc[0], 2) + pow($Ys[$a] - $tc[1], 2));
		
		
		#新的K
		$t = array_keys($c2k, min($c2k));
		$sK[] = $t[0];

		#紀錄該 cluster 的中心
		$center[$key] = array($tc[0], $tc[1]);

		
	}

}
?>