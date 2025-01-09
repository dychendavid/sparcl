<?
function drawNcluster($nc, $drawCenter = true, $drawNO = false, $filename = 'output/draw.png')
{
	global $center;
	global $Xs;
	global $Ys;
	
	#draw config
	$width = 1000;
	$height = 1000;
	
	$im = imagecreatetruecolor($width, $height);
	imagefilledrectangle($im, 0, 0, $width, $height, imagecolorexact($im, 255, 255, 255));

	$pointSize = 5;
	$fontSize = 2;
	$centerSize = $pointSize * 1.5;
	$textcolor = imagecolorexact($im, 0, 0, 0);
	$centercolor = imagecolorexact($im, 255, 0, 0);

	
	#產生顏色群組
	$colors = makeColors(sizeof($nc));
	
	#顏色計數器
	$color = 0;
	
	foreach($nc as $key => $c)
	{
		#紀錄各 cluster 的點
		$nx[$key] = array();
		$ny[$key] = array();
	
		for($i=0; $i < sizeof($c); $i++)
		{
			$nx[$key][$i] = $Xs[$c[$i]];
			$ny[$key][$i] = $Ys[$c[$i]];
		}
		
		
		#draw cluster
		for($i=0; $i < sizeof($nx[$key]); $i++)
		{
			#畫圈
			imageellipse($im, $nx[$key][$i], $ny[$key][$i], $pointSize, $pointSize, $colors[$color]);
			#填滿
			imagefilltoborder($im, $nx[$key][$i], $ny[$key][$i], $colors[$color], $colors[$color]);
			
			#寫字
			if($drawNO)
				imagestring($im, $fontSize, $nx[$key][$i], $ny[$key][$i], $i, $textcolor);	
		}
		
		
		#draw center
		if($drawCenter)
		{	
			#畫圈
			imageellipse($im, $center[$key][0], $center[$key][1], $centerSize, $centerSize, $centercolor);
			#填滿
			imagefilltoborder($im, $center[$key][0], $center[$key][1], $centercolor, $centercolor);
			#寫字
			imagestring($im, $fontSize, $center[$key][0], $center[$key][1], $key, $textcolor);	
		}
		
		$color++;
	}

	// Output and free memory
	imagepng($im, $filename);
	imagedestroy($im);
	
	#重導
	echo "<script language='javascript'>window.open('$filename');</script>";	
}


function makeColors($K)
{
	$im = imagecreatetruecolor(1000, 1000);
	
	$colors = array();
	for($i=0; $i < $K; $i++)
	{
		$current = imagecolorexact($im, rand(0, 255), rand(0, 255), rand(0, 255));
		
		if(in_array($current, $colors))
		{
			$i--;
			continue;
		}
		else
			$colors[] = $current;
	}
	
	return $colors;
}

?>