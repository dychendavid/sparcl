<?
function block3()
{
	global $sC;
	global $center;
	global $smlrt;


	#產生相似度矩陣(紀錄用)
	$sCDup = $sC;
	foreach($sC as $key1 => $c)
		foreach($sCDup as $key2 => $c2)
			$smlrt[$key1][$key2] = 0;

	$cnt = 0;
	
	#$key1 = cluster X, $key2 = cluster Y
	$smlrtDup = $smlrt;
	foreach($smlrtDup as $key1 => $c)
	{
		foreach($c as $key2 => $v)
		{
			
			#Step 1 ~ 2
			#自己和自己的相似度是0, 前面已設過
			if($key1 == $key2)
				continue;

			#X cluster 到 Y cluster 的向量
			$vectorB = vector($center[$key1][0], $center[$key1][1], $center[$key2][0], $center[$key2][1]);
			#向量B的純量
			$lengthB = distance($center[$key1][0], $center[$key1][1], $center[$key2][0], $center[$key2][1]);
			
			
	
			#X cluster 的中心到每個點的距離
			#紀錄 Hi, Vi
			$t = saveHiVi($key1, $vectorB, $lengthB);
			$Hi1 = $t[0];
			$Vi1 = $t[1];

			#Y cluster 到 X cluster 的向量
			$vectorB = vector($center[$key2][0], $center[$key2][1], $center[$key1][0], $center[$key1][1]);

			$t = saveHiVi($key2, $vectorB, $lengthB);
			$Hi2 = $t[0];
			$Vi2 = $t[1];
			


			#算標準差
			$SH1 = standardDeviation($Hi1);
			$SV1 = standardDeviation($Vi1);
			$SH2 = standardDeviation($Hi2);
			$SV2 = standardDeviation($Vi2);

			if($SH1 == 0 || $SH2 == 0)
				continue;


			#紀錄 Vi > 2 * S 的 i, 將略過不處理
			$ii1 = ignoreI($Vi1, $Hi1, $SV1); 
			$ii2 = ignoreI($Vi2, $Hi2, $SV2); 

			#拿掉 Hi 中被過慮的點
			#因為後面只會用到Hi, 所以Vi不重算
			$nHi1 = newHi($Hi1, $ii1);
			$nHi2 = newHi($Hi2, $ii2);

			#Step 3
			#算組距
			$boxSize1 = $SH1 / 2;
			$boxSize2 = $SH2 / 2;

			

			#Step 4
			$farest1 = max($nHi1);
			$farest2 = max($nHi2);

			#將點放到各組距裡
			$box1 = putInBox($nHi1, $farest1, $boxSize1);
			$box2 = putInBox($nHi2, $farest2, $boxSize2);
			
			
			#Step 5
			$sz_ratio1 = saveSz_ratio($box1);
			$sz_ratio2 = saveSz_ratio($box2);

		
			#Block4
			#Step 6
			#end = 格子數量
			if( sizeof($sz_ratio1) <= sizeof($sz_ratio2))
				$end = sizeof($sz_ratio1);
			else
				$end = sizeof($sz_ratio2);
			
			
			for($i=0; $i < $end; $i++)
				$size_sim[$i] = $sz_ratio1[$i] * $sz_ratio2[$i];
			

			
			#Step 7
			$space = $lengthB - $farest1 - $farest2;
			$a1 = $boxSize1 / 2;
			$a2 = $boxSize2 / 2;

			for($i=0; $i < $end; $i++)
				$dist_sim[$i] = 2*(($a1 + $a2) * ($i * 2) + $a1 + $a2 + $space) / ($SH1 + $SH2);
			
			
			#Step 8
			$Sxy = 0;
			for($i=0; $i < $end; $i++)
				$Sxy += $size_sim[$i] * exp(-$dist_sim[$i]);

			$smlrt[$key1][$key2] = $Sxy;
			$smlrt[$key2][$key1] = $Sxy;

		}
		
		$cnt++;
		
		echo '.';
		if($cnt % 100 == 0)
			echo "\r\n<br>";
		flush();
		
	}
	echo "<br>\r\n<br>\r\n";
	flush();

}



#key1 cluster 的中心到每個點的向量
#紀錄 Hi, Vi
function saveHiVi($key, $vectorB, $lengthB)
{
	global $sC;
	global $center;
	
	global $Xs;
	global $Ys;
	
	$Hi = array();
	$Vi = array();
	
	
	foreach($sC[$key] as $i)
	{
		$vectorA = vector($center[$key][0], $center[$key][1], $Xs[$i], $Ys[$i]);
		$lengthA = distance($center[$key][0], $center[$key][1], $Xs[$i], $Ys[$i]);
	
		$Hi[$i] = horizontalDist($vectorA, $vectorB, $lengthB);
		$Vi[$i] = verticalDist($vectorA, $vectorB, $lengthB);
	}
	return array($Hi, $Vi);
}

#紀錄Vi > 2SV的點 or Hi 為負的排除
function ignoreI($Vi, $Hi, $SV)
{
	$ii = array();
	
	#Vi > 2 * SV 的	排除
	foreach($Vi as $key => $i)
		if($i > (2 * $SV))
			$ii[] = $key; 

	#Hi < 0 的	排除
	foreach($Hi as $key => $i)
		if($i < 0)
			$ii[] = $key; 

	
	return $ii;

}

#重組 Hi
function newHi($Hi, $ii)
{
	$nHi = array();
	foreach($Hi as $key => $h)
	{
		if(in_array($key, $ii))
			continue;
		$nHi[$key] = $h;
	}
	return $nHi;
}


#將點放到各組距裡
function putInBox($Hi, $farest, $boxSize)
{
	foreach($Hi as $key => $h)
		$box[floor(($farest - $h) / $boxSize)][] = $key;

	return $box;
}


#Step 5
function saveSz_ratio($box)
{
	#紀錄每一格裡點的數量
	$num_ratio = array();
	foreach($box as $key => $b)
		$num_ratio[$key] = sizeof($b);

	$xmax = max($num_ratio);

	
	#紀錄sz_ratio
	$sz_ratio = array();
	for($i=0; $i < sizeof($box); $i++)
		$sz_ratio[$i] = $num_ratio[$i] / $xmax;
	
	return $sz_ratio;
}

?>
