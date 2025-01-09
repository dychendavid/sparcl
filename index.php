<?
#save new source
if($_REQUEST['new'] == 'y')
{	
	file_put_contents('LastFileName.txt', $_FILES['file']['name']);
	
	#save old input
	$date = date('YmdHis_');
	@rename('input/input.txt', 'input/' . $date . 'input.txt');
	
	$updateChk = @rename($_FILES['file']['tmp_name'], 'input/input.txt');
}
$filemtime = @filemtime('input/input.txt');
if($filemtime)
	$lastUpdate = date('Y-m-d H:i:s', @filemtime('input/input.txt'));

?>
<form enctype='multipart/form-data' method='post'>
	<table border='1'>
		<tr>
			<td>起始叢集數 K:</td>
			<td><input type='text' name='K' value='<?=$_REQUEST['K']?>'/></td>
		</tr>
		<tr>
			<td>最終叢集數 k:</td>
			<td><input type='text' name='k' value='<?=$_REQUEST['k']?>' /></td>
		</tr>
		<tr>
			<td>縮放倍率 S:</td>
			<td><input type='text' name='scale' value='<?=$_REQUEST['scale']?>' /></td>
		</tr>
		<tr>
			<td>New Source: <input type='checkbox' value='y' name='new'/	></td><td><input type='file' name='file' /></td>
		</tr>
		<tr>
			<td colspan='2'>Last update:<?=$lastUpdate?></td>
		</tr>
		<tr>
			<td colspan='2'><input type='hidden' name='draw' value='y' /><input type='submit' value='Draw!'/></td>
		</tr>
	</table>
</form>
<?
require_once 'funcs.php';
require_once 'draw.php';
#require_once 'block1.php';
require_once 'block2.php';
require_once 'block3.php';
require_once 'block5.php';

#environment
echo 'Start: ' . date('H:i:s') . '<br>';
flush();
set_time_limit(30000);
ini_set('memory_limit', '2048M');
date_default_timezone_set('ASIA/TAIPEI');
srand((double)microtime() * 1000000);

$K = $_REQUEST['K'];
$k = $_REQUEST['k'];
$mp = $_REQUEST['mp'];
$d = $_REQUEST['d'];
$scale = $_REQUEST['scale'];
$draw = $_REQUEST['draw'];



if($draw != 'y')
	exit;

#scale default 1
if($scale == '' || $scale < 1)
	$scale = 1;
	
#check K & k
if(!preg_match('/[0-9]+/', $K) || !preg_match('/[0-9]+/', $k))
{
	echo 'Please enter number!';
	exit;
}

#check input
if(!file_exists('input/input.txt'))
{
	echo 'No input file!';
	exit;
}


#display setting
echo "K: $K, k: $k, mp:$mp, d:$d, S:$scale<br>\r\n";
echo "New Source: " . $_FILES['file']['name'] . "\r\n<br>";
flush();



#check update
if($updateChk === false)
{
	echo "Upload faild, retry it!";
	exit;
}



#read input
readTxt();
echo 'Point num: ' . count($Xs) . "\r\n<br>";
flush();


#Block1
echo "Block1 doing: " . date('H:i:s') . "\r\n<br>";
flush();
block1();
echo "\r\n<br>";
flush();


/*
for($i=0; $i < count($Xs); $i++)
	$s[0][] = $i;
$s[1] = $sK;
drawNcluster($s);
exit;
*/
#Block2
echo "Block2 doing: " . date('H:i:s') . "\r\n<br>";
flush();
block2();


#debug
#save block status;
file_put_contents('debug/sC.txt', serialize($sC));
file_put_contents('debug/center.txt', serialize($center));
file_put_contents('debug/Xs.txt', serialize($Xs));
file_put_contents('debug/Ys.txt', serialize($Ys));

$sC = unserialize(file_get_contents('debug/sC.txt'));
$center = unserialize(file_get_contents('debug/center.txt'));
$Xs = unserialize(file_get_contents('debug/Xs.txt'));
$Ys = unserialize(file_get_contents('debug/Ys.txt'));


drawNcluster($sC, false, false, 'output/start.png');


echo "Block3 doing: " . date('H:i:s') . "\r\n<br>";
flush();
block3();
	
file_put_contents('debug/smlrt.txt', serialize($smlrt));

$smlrt = unserialize(file_get_contents('debug/smlrt.txt'));



echo "Block5 doing: " . date('H:i:s') . "\r\n<br>";
flush();
block5();
echo "\r\n<br>\r\n<br>";

echo "Making File: " . date('H:i:s') . "\r\n<br>";
flush();
output();
echo "All process done: " . date('H:i:s') . "\r\n<br>";
flush();


#Choose random point for K
function block1()
{
	global $sK;
	global $K;
	global $Xs;
	
	$cnt = count($Xs);
	$sK = array();
	
	while(sizeof($sK) < $K)
	{
		$n = rand(0, $cnt -1);
		if(!in_array($n, $sK))
			$sK[] = $n;
	}
}



#read input.txt
function readTxt($name = 'input/input.txt')
{
	global $m;
	global $Xs;
	global $Ys;
	global $scale;
	
	$file = file_get_contents($name);
	if(substr($file, -2) == "\r\n")
		$file = substr($file, 0, -2);
	
	$m = explode("\r\n", $file);
	$cnt = count($m);
	for($i=0; $i < $cnt; $i++)
	{
		$m[$i] = explode(' ', $m[$i]);
		
		$Xs[$i] = $m[$i][1] * $scale;
		$Ys[$i] = $m[$i][2] * $scale;
		
		if($Xs[$i] >= 1000 || $Ys[$i] >= 1000)
		{
			echo '倍率太大, 請重試!';
			exit;
		}
		
	}	
}

#draw the result of block2 and result of block 5 and echo txt
function output($name = 'output/output.txt', $picname = 'output/result.png')
{
	global $sC;
	global $m;
	global $smlrt;
	
	
	$keys = array_keys($smlrt);
	
	$merge = array();
	foreach($keys as $c)
	{
		if( count($temp = explode('/', $c)) > 1)
		{
			for($i=1; $i < count($temp); $i++)
			{
				$sC[$temp[0]] = array_merge($sC[$temp[0]], $sC[$temp[$i]]);
				unset($sC[$temp[$i]]);
			}
		}	
	}

	$cnt = count($m);
	
	#洗掉原始叢集
	sort($sC);
			
	#產生圖檔
	drawNcluster($sC, false, false, $picname);

	#變更	
	for($i=0; $i < $cnt; $i++)
		foreach($sC as $key => $c)
			if(in_array($i, $c))
				$m[$i][0] = $key;

	#輸出
	$file = '';
	for($i=0; $i < $cnt; $i++)
		$file .= implode(' ', $m[$i]) . "\r\n";
	
	file_put_contents($name, $file);
	

	echo "<script language='javascript'>window.open('download.php');</script>";
	
}


?>
