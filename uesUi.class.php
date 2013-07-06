<?php
class uesUi{
  protected $ue_configs;
	protected $uedb;
	//protected $ue_stat;
	
	function __construct(PDO $uedb){
	$this->uedb=$uedb;
	//$this->ue_configs = getConfigs();
	}

	function getStat(){
	try{
		$this->uedb->setAttribute(PDO::ATTR_ERRMODE, 
		PDO::ERRMODE_EXCEPTION); 
		$sql="SELECT span,bay,section,MAX(date)FROM `ues_spcounts`";
		$stmt = $this->uedb->prepare($sql);
		$stmt->execute();
		$res=$stmt->fetch(PDO::FETCH_ASSOC);
		//print_r($res); 
}
	catch (PDOException $e){
		print_r($e.'<br>');
	}
	return $res;
}

function setStat($span='1', $bay='0', $section='1'){
	
	try{
		$this->uedb->setAttribute(PDO::ATTR_ERRMODE, 
		PDO::ERRMODE_EXCEPTION); 
$sql = 'INSERT INTO ues_spcounts (span,bay,section) VALUES (:span, :bay, :section)'; 

$stmt = $this->uedb->prepare($sql);
$stmt->bindParam(':span', $span);
$stmt->bindParam(':bay', $bay);
$stmt->bindParam(':section', $section);

$stmt->execute(); 
}
	catch (PDOException $e){
		//print_r($e.'<br>');
	}

}


function getUrl($stat, $home){
	$url =$home.'?span='.$stat['0'].'&bay='.$stat['1'].'&section='.$stat['2'];
	return $url;
}

function preSave($count, $sp){
	//k=sp v=pop
	foreach ($counts as $k => $v) {
    //echo "\$a[$k] => $v.\n";
	if ($k!='submit' && $v!=0 || $v =='more than 20'){
		/*if ($v!=0){*/
			saveCount($count, $sp);	
			/*}*/
		
		}
	}
	
}

function saveCount($stat,$count='0', $sp='0', $crop){
/*echo 'bay '.$row . $col.'sp '.$sp.' count '.$count.'<br>';*/
if($count!='0'){
	
try
    {
     
      $sql = "INSERT INTO ues_spcounts (span, bay, section, species, count, crop) VALUES(:span, :bay, :section, :species, :count, :crop)";  
          
      $stmt = $this->uedb->prepare($sql);
      // bind the user input
      $stmt->bindParam(':span', $stat['0']);
	  $stmt->bindParam(':bay', $stat['1']);
	  $stmt->bindParam(':section', $stat['2']);
      $stmt->bindParam(':species', $sp);
	  $stmt->bindParam(':count', $count);
	  $stmt->bindParam(':crop', $crop);
      $stmt->execute();
     
    }
    catch (PDOException $e)
    {      
		  print_r($e);
    }
	}	
}


function nextBay($span,$bay,$sect){
	//include 'ue_configure.php';
	$rm=$this->getRm();
	$tbays=$rm['totalbay'];
	$tspans=$rm['totalspan'];
	$tsect=$rm['totalsect'];
	/*print_r($rm);*/
	echo 'sp= '.$span.' b = '.$bay.' se= '.$sect;
	
	
	if(($tspans<=$span)&&($sect==$tsect)&& ($bay==$tbays)){	
		echo 'finished';	
		return('complete');
	}
	
	if ($bay>=$tbays&& $sect=='1' ){
		echo 'end of bays, sect '.$sect;
		$sect++;	
		return array($span,$bay,$sect);
	}	
	
	/*if ($bay==1 && $sect>$tsect){
		$span=$span + 1;
		$sect='1';
		echo 'end of span';
		return array($span,$bay,$sect);	
		}*/
	
	if($sect % 2=='0' AND $bay>'1'){
		$bay=$bay-1;
		echo "going back";
		return array($span,$bay,$sect);		
		}
	if($sect % 2!='0' AND $sect < $tsect){
		$bay=$bay + 1;
		echo "going forward";	
		return array($span,$bay,$sect);
		}
	if ($sect % 2 == '0' AND $bay == '1'){
		echo 'back at start';
		$sect++;
		/*$bay++;*/
		return array($span,$bay,$sect);		
	}
	if ($sect == $tsect AND $bay == $tbays){
		$span++;
		$sect == '1';
		return array($span,$bay,$sect);	
	}
	
	else {
		echo 'Out of bounds Error sect = '.$sect;
	}
}

function navForm($stat, $hom){
	require('ue_configure.php');
	
	$url=$this->getUrl($stat, $hom);
	
	$rm=$this->getRm();
	$tbays=$rm['totalbay'];
	$tspans=$rm['totalspan'];
	
	
	$frm=new mForm('navigation','post',$url);
	//for($i=1;$i<$tSpans;$i++)
	$frm->adBtn('submit','goto','Go To');
	$sp=range(1,$tspans);
	$ba=range(1,$tbays);
	$se=range(1,$tSect);
	$frm->adLst('Span', $sp);
	$frm->adLst('Bay', $ba);
	$frm->adLst('Section', $se);
	
	echo $frm->display();
		
	}
	
	
function navButs($bay, $span){
	$rm=$this->getRm();
	print_r($rm);
	$maxBay=$rm['totalbay'];
	$maxSpan=$rm['totalspan'];
	
		if($bay!=1 || $span!=1){
			$clk10=array($bay-1,$span-1);
		}
		else{
			$clk10='--';
		}
		
		if($bay!=1){
			$clk12=array($bay-1,$span);
		}
		else{
			$clk12='--';
		}
		if($bay!=1 && $span!=$maxSpan){
			$clk2=array($bay-1,$span+1);
		}
		else{
			$clk2='--';
		}
		
		
		$nform=new mForm('navbuts', 'POST', $_SERVER['PHP_SELF'].'?bay='.$bay.'&span='.$span );
		$nform->adHtm('<table ><tr><td>');
		
		$nform->adBtn('Submit', '10oclock', 'b'.$clk10["0"].' s'.$clk10["1"]);
		$nform->adHtm('</td><td>');
		$nform->adBtn('Submit', '12oclock', $clk12);
		$nform->adHtm('</td><td>');
		$nform->adBtn('Submit', '2oclock', $clk2);
	
		$nform->adHtm('</td></tr><tr><td>');
		$nform->adBtn('Submit', '9oclock', $clk12);
		$nform->adHtm('</td><td>'.$bay.$span.'</td><td>');
		$nform->adBtn('Submit', '3oclock', $clk12);
		$nform->adHtm('</td)></tr><td>');
		$nform->adBtn('Submit', '7oclock', $clk12);
		$nform->adHtm('</td><td>');
		$nform->adBtn('Submit', '6oclock', $clk12);
		$nform->adHtm('</td><td>');
		$nform->adBtn('Submit', '5oclock', $clk12);

		$nform->adHtm('</td></tr></table>');		
		
		echo $nform->display();
		
}
	
function adSpec($data){
$name=$data['name'];
$comments=$data['comments'];
$ben=$data['ben'];


try{
		$this->uedb->setAttribute(PDO::ATTR_ERRMODE, 
		PDO::ERRMODE_EXCEPTION); 
$sql = 'INSERT INTO ues_sptypes (name,comments, beneficial) VALUES (:name, :comments, :ben)'; 

$stmt = $this->uedb->prepare($sql);
$stmt->bindParam(':name', $name);
$stmt->bindParam(':comments', $comments);
$stmt->bindParam(':ben', $ben);
$stmt->execute(); 
}
	catch (PDOException $e){
		//print_r($e.'<br>');
	}
return('TRUE');	
}

function delSp($dsp){
	try{
		$this->uedb->setAttribute(PDO::ATTR_ERRMODE, 
		PDO::ERRMODE_EXCEPTION); 
	$sql = 'DELETE FROM ues_sptypes WHERE name = "'.$dsp.'"'; 

$stmt = $this->uedb->prepare($sql);
$stmt->execute(); 
}
	catch (PDOException $e){
		//print_r($e.'<br>');
	}
return('TRUE');	
}
	


function getSp(){
$sp=array();
	try 
	{
		$this->uedb->setAttribute(PDO::ATTR_ERRMODE, 
		PDO::ERRMODE_EXCEPTION); 
		$sql = 'Select * from ues_sptypes';
		foreach ($this->uedb->query($sql)as $row) 
		{
			array_push($sp, $row['name']);

		} 

return($sp);
} 
catch (PDOException $e) 
{
echo 'Error: ' . $e->getMessage(); 
} 
}

function adCrop($data){
$name=$data['name'];
$comments=$data['comments'];


try{
		$this->uedb->setAttribute(PDO::ATTR_ERRMODE, 
		PDO::ERRMODE_EXCEPTION); 
$sql = 'INSERT INTO ues_crops (name,comments) VALUES (:name, :comments)'; 

$stmt = $this->uedb->prepare($sql);
$stmt->bindParam(':name', $name);
$stmt->bindParam(':comments', $comments);
$stmt->execute(); 
}
	catch (PDOException $e){
		//print_r($e.'<br>');
	}
return('TRUE');	
}

function delCrp($cr){
	try{
		$this->uedb->setAttribute(PDO::ATTR_ERRMODE, 
		PDO::ERRMODE_EXCEPTION); 
	$sql = 'DELETE FROM ues_crops WHERE name = "'.$cr.'"'; 

$stmt = $this->uedb->prepare($sql);
$stmt->execute(); 
}
	catch (PDOException $e){
		//print_r($e.'<br>');
	}
return('TRUE');	
}


function getCr(){
$cr=array();
	try 
	{
		$this->uedb->setAttribute(PDO::ATTR_ERRMODE, 
		PDO::ERRMODE_EXCEPTION); 
		$sql = 'Select * from ues_crops';
		foreach ($this->uedb->query($sql)as $row) 
		{
			array_push($cr, $row['name']);

		} 

return($cr);
} 
catch (PDOException $e) 
{
echo 'Error: ' . $e->getMessage(); 
} 
}


function edRm($spans, $bays){
/*print_r($spans.$rows."spanrow");*/
try{
		$this->uedb->setAttribute(PDO::ATTR_ERRMODE, 
		PDO::ERRMODE_EXCEPTION); 
	//$sql = 'UPDATE ues_room SET totalspan = :totalspan, totalbay = :totalbay)'; 
	//$sql = 'UPDATE ues_room (totalspan,totalrow) VALUES (:spans, :rows)'; 
	$count = $this->uedb->exec("UPDATE ues_room SET totalspan='".$spans."',totalbay=".$bays);
	echo $count;

	/*$stmt = $this->uedb->prepare($sql);
	$stmt->bindParam(':totalspan', $spans);
	$stmt->bindParam(':totalbay', $rows);
	$stmt->execute(); */
}
	catch (PDOException $e){
		//print_r($e.'<br>');
	}
return('TRUE');		
}

function getRm(){
$sp=array();
	try {
	
		$this->uedb->setAttribute(PDO::ATTR_ERRMODE, 
		PDO::ERRMODE_EXCEPTION); 
		//$sql = 'Select totalspan, totalrow from ues_room';
		$sql="SELECT totalspan,totalbay,totalsect FROM `ues_room`";
		$stmt = $this->uedb->prepare($sql);
		$stmt->execute();
		$res=$stmt->fetch(PDO::FETCH_ASSOC);
		return($res); 
		
} 
catch (PDOException $e) 
{
echo 'Error: ' . $e->getMessage(); 
} 
return($res);
}

function getCounts($stat){
$span=$stat['0'];$bay=$stat['1'];$sect=$stat['2'];
$cs=array();
try {
	
		$this->uedb->setAttribute(PDO::ATTR_ERRMODE, 
		PDO::ERRMODE_EXCEPTION); 
		//$sql = 'Select totalspan, totalrow from ues_room';
		//$sql="SELECT species,count FROM 'ues_spcounts' WHERE span = '".$span."' AND bay = '".$bay."' AND SECTION = '".$sect."'";
		//$sql="SELECT species, count FROM `ues_spcounts` WHERE span=1";
		$sql="SELECT species, count FROM `ues_spcounts` WHERE span=".$span." AND bay=".$bay." AND section=".$sect;
		
		//$sql="SELECT totalspan,totalbay FROM `ues_room`";
		foreach ($this->uedb->query($sql)as $row) 
		{
			//print_r("species ".$row['species']." count ".$row['count']);
			array_push($cs, array($row['species'],$row['count']));

		} 
		
		/*$stmt = $this->uedb->prepare($sql);
		$stmt->execute();
		$res=$stmt->fetch(PDO::FETCH_ASSOC);
		return($res); */
		return($cs);
} 
catch (PDOException $e) 
{
echo 'Error: ' . $e->getMessage(); 
} 

	
}

}



?>
