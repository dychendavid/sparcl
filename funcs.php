<?

function distance($x1, $y1, $x2, $y2)
{
	return sqrt(pow($x1 - $x2, 2) + pow($y1 - $y2, 2));
}


#求平均值
function avg($array)
{
	return (array_sum($array) / sizeof($array));
}

#輸入存有距離的陣列
function density($array)
{
	return pow(avg($array), -1);
}

#求中心點
function center($x, $y)
{
	return array(array_sum($x) / sizeof($x), array_sum($y) / sizeof($y)); 
}

#向量的算法
function vector($x1, $y1, $x2, $y2)
{
	return array($x2 - $x1, $y2 - $y1);
}

#求水平距離 $A是向量 $B 是向量 $vectorB 是純量
function horizontalDist($A, $B, $lengthB)
{
	return ($A[0] * $B[0] + $A[1] * $B[1]) / $lengthB;
}

#求垂直距離 $A是向量 $B 是向量 $vectorB 是純量
function verticalDist($A, $B, $lengthB)
{
	return ($A[0] * $B[1] - $A[1] * $B[0]) / $lengthB;
}

#求標準差 $x 是集合
function standardDeviation($x)
{
	#在這個 case, 平均值為0, 故不處理	
	$sum = 0;
	foreach($x as $t)
		#echo 't: ' . $t . ', avg: ' . $avg . "\r\n";
		$sum += $t * $t;

	$sum /= sizeof($x);

	return sqrt($sum);

}




?>