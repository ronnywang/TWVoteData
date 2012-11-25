<?php


require_once("../PHPExcel/Classes/PHPExcel.php");
require_once("../PHPExcel/Classes/PHPExcel/IOFactory.php");
require_once("../PHPExcel/Classes/PHPExcel/Reader/Excel5.php");


ini_set("memory_limit","1024M");

$objReader = PHPExcel_IOFactory::createReader('Excel5');
$objReader->setReadDataOnly(true);  


$folder = '../../raw/第13任總統副總統得票概況/';

$data = array();
$data['候選人'][] = array('總統'=>'蔡英文','副總統'=>'蘇嘉全');
$data['候選人'][] = array('總統'=>'馬英九','副總統'=>'吳敦義');
$data['候選人'][] = array('總統'=>'宋楚瑜','副總統'=>'林瑞雄');


if($handle = opendir($folder))
{
	while (false !== ($entry = readdir($handle)))
	{
        if ($entry != "." && $entry != "..") 
        {
            
            parserExecl($objReader,$data,$folder,$entry);
            //break;
        }
    }
    closedir($handle);
}
//print_r($data);
//清空記憶體
unset($objReader);
output(&$data);


function parserExecl(&$reader,&$data,$path,$filename)
{
	//initialize
	$county = "";
	$city = "";
	$village = "";
	$vote_no = "";

	$county = getContry($filename);
	//echo "county:".$county."\n";
	$excel = $reader->load($path.$filename);
	//$excel = PHPExcel_IOFactory::load($path.$filename);	
	$currentSheet = $excel->getSheet(0);//讀取第一個工作表(編號從 0 開始)  
	$allLine = $currentSheet->getHighestRow();//取得總列數 
	for($excel_line = 7;$excel_line<=$allLine;$excel_line++) // Ignore the first 6 rows data
	{
		$temp_city = str_replace("　","",$currentSheet->getCell("A{$excel_line}")->getValue()); //去除空白

		if($temp_city!="")
		{
			$city = $temp_city;
			continue;
		}else{
			$village = $currentSheet->getCell("B{$excel_line}")->getValue();
			$vote_no = $currentSheet->getCell("C{$excel_line}")->getValue();
			$vote_num = array();
			$vote_num[] = intval($currentSheet->getCell("D{$excel_line}")->getValue());
			$vote_num[] = intval($currentSheet->getCell("E{$excel_line}")->getValue());
			$vote_num[] = intval($currentSheet->getCell("F{$excel_line}")->getValue());
			$vaild_num =  intval($currentSheet->getCell("G{$excel_line}")->getValue());
			$invaild_num =  intval($currentSheet->getCell("H{$excel_line}")->getValue());
			$total_num =  intval($currentSheet->getCell("I{$excel_line}")->getValue());	    //總投票數
			$vote_rate = array();
			$vote_rate[] = floatval($vote_num[0]/$total_num);
			$vote_rate[] = floatval($vote_num[1]/$total_num);
			$vote_rate[] = floatval($vote_num[2]/$total_num);			
			$no_vote =  intval($currentSheet->getCell("J{$excel_line}")->getValue());		//已領未投票
			$total_people =  intval($currentSheet->getCell("M{$excel_line}")->getValue());	
			$total_rate =  floatval($currentSheet->getCell("N{$excel_line}")->getValue());	
			$data['投票狀況'][$county][$city][$village][] = array(
					'票所'=>$vote_no,
					'得票數'=>$vote_num,
					'得票率'=>$vote_rate,
					'有效票'=>$vaild_num,
					'無效票'=>$invaild_num,
					'投票'=>$total_num,
					'已領未投投票'=>$no_vote,
					'選舉人數'=>$total_people,
					'投票率'=>$total_rate);
			//print_r($data);
			

			}
		
	}

	//清空記憶體
	unset($excel);
	
}

function output(&$data)
{
	$json = json_encode($data);
	$fp = fopen('../../13_president.json','w+');
	fprintf($fp,"%s",$json);
	fclose($fp);
}

function getContry($filename)
{	
	$cols = explode("-",$filename);	
	$cols = explode(")",$cols[3]);	
	return substr($cols[0], 1);
}
/**
$filename = '../../raw/13th_president_vote.csv';
$fp = fopen($filename,'r');


$data = array();
$data['候選人'][] = array('總統'=>'陳水扁','副總統'=>'呂秀蓮');
$data['候選人'][] = array('總統'=>'連戰','副總統'=>'宋楚瑜');




//Jump the first line
fgets($fp);




while(!feof($fp))
{
	$line = fgets($fp);
	$cols = explode(",",$line);
	$size = count($cols);
	

	//echo $line."\n";

	$data['投票狀況'][$cols[0]][$cols[1]][$cols[2]][] = array(
		'票所'=>$cols[3],
		'得票數'=>array(intval($cols[4]),intval($cols[6])),
		'得票率'=>array(floatval($cols[5]),floatval($cols[7])),
		'有效票'=>intval($cols[8]),
		'無效票'=>intval($cols[9]),
		'投票'=>intval($cols[10]),
		'已領未投投票'=>intval($cols[11]),
		'選舉人數'=>intval($cols[12]),
		'投票率'=>floatval($cols[13]));
}

$json = json_encode($data);

fclose($fp);

$fp = fopen('../../13_president.json','w+');
fprintf($fp,"%s",$json);
fclose($fp);

*/



?>