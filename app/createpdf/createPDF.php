<?php
//error_reporting(E_ALL ^ E_NOTICE);
require ('fpdf.php');
require ('../php/dataServer.php');
require ('../datasources/parsecsv/parsecsv.lib.php');

global $sql, $pageSize;

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////

# create new parseCSV object.
$csv     = new parseCSV();

$inData	= $server->getCleanPost(0); //turn off html entities
$server->fetchDocumentData($inData, 1);	
$upload_dir = "../user_files/";


function pixelsTomm($pixels = 0) {

	$divisor = 4.7609; 
	
	//100DPI = 4.7609
	//150DPI = 5.9057
	
	return $pixels / $divisor;
}


class FPDF2File extends FPDF
{
	var $f;
	var $extgstates; //Transparency
	
	function FPDF2File($orientation='P', $unit='mm', $format='A4')
    {
        parent::FPDF($orientation, $unit, $format);
        $this->extgstates = array();
    }
	
	function Footer()
		{
			global $server, $inData, $pageSize;
			
			
			// Arial bold 15
			$this->SetFont('Arial','B',42);
			// Move to the right
			//$this->Cell(95);
			
			// Title
			if(isset($inData['preview'])) {
				// set alpha to semi-transparency
				$this->SetAlpha(0.3, 'Normal');
				
				for($x = 28; $x < $pageSize['width']; $x += 90) {
					for($y = 5; $y < $pageSize['height']; $y += 50) {
						
						$this->setXY($x,$y);
						$this->SetTextColor(255,0,0); 
						$this->Cell(10,10,'Preview',0,0,'C');
				
					}
					
				}
				
				
				
				$this->SetAlpha(1, 'Normal');
			}
			// Line break
			$this->Ln(20);
		}
		
		function AcceptPageBreak()
		{
			return false;
		}

	function Open($file='doc.pdf')
	{
		if(FPDF_VERSION<'1.7')
			$this->Error('Version 1.7 or above is required by this extension');
		$this->f=fopen($file,'wb');
		if(!$this->f)
			$this->Error('Unable to create output file: '.$file);
		parent::Open();
		$this->_putheader();
	}

	function Image($file, $x=null, $y=null, $w=0, $h=0, $type='', $link='')
	{
		if(!isset($this->images[$file]))
		{
			//Retrieve only meta-information
			$a=getimagesize($file);
			if($a===false)
				$this->Error('Missing or incorrect image file: '.$file);
			$this->images[$file]=array('w'=>$a[0],'h'=>$a[1],'type'=>$a[2],'i'=>count($this->images)+1);
		}
		parent::Image($file,$x,$y,$w,$h,$type,$link);
	}

	function Output($name=null, $dest=null)
	{
		if($this->state<3)
			$this->Close();
	}

	function _endpage()
	{
		parent::_endpage();
		//Write page to file
		$filter=($this->compress) ? '/Filter /FlateDecode ' : '';
		$p=($this->compress) ? gzcompress($this->buffer) : $this->buffer;
		$this->_newobj();
		$this->_out('<<'.$filter.'/Length '.strlen($p).'>>');
		$this->_putstream($p);
		$this->_out('endobj');
		$this->buffer='';
	}

	function _newobj()
	{
		$this->n++;
		$this->offsets[$this->n]=ftell($this->f);
		$this->_out($this->n.' 0 obj');
	}

	function _out($s)
	{
		if($this->state==2)
			$this->buffer.=$s."\n";
		else
			fwrite($this->f,$s."\n",strlen($s)+1);
	}

	function _putimages()
	{
		foreach(array_keys($this->images) as $file)
		{
			$type=$this->images[$file]['type'];
			if($type==1)
				$info=$this->_parsegif($file);
			elseif($type==2)
				$info=$this->_parsejpg($file);
			elseif($type==3)
				$info=$this->_parsepng($file);
			else
				$this->Error('Unsupported image type: '.$file);
			$this->_putimage($info);
			$this->images[$file]['n']=$info['n'];
			unset($info);
		}
	}

	function _putpages()
	{
		$nb=$this->page;
		if($this->DefOrientation=='P')
		{
			$wPt=$this->DefPageSize[0]*$this->k;
			$hPt=$this->DefPageSize[1]*$this->k;
		}
		else
		{
			$wPt=$this->DefPageSize[1]*$this->k;
			$hPt=$this->DefPageSize[0]*$this->k;
		}
		//Page objects
		for($n=1;$n<=$nb;$n++)
		{
			$this->_newobj();
			$this->_out('<</Type /Page');
			$this->_out('/Parent 1 0 R');
			if(isset($this->PageSizes[$n]))
				$this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->PageSizes[$n][0],$this->PageSizes[$n][1]));
			$this->_out('/Resources 2 0 R');
			if(isset($this->PageLinks[$n]))
			{
				//Links
				$annots='/Annots [';
				foreach($this->PageLinks[$n] as $pl)
				{
					$rect=sprintf('%.2F %.2F %.2F %.2F',$pl[0],$pl[1],$pl[0]+$pl[2],$pl[1]-$pl[3]);
					$annots.='<</Type /Annot /Subtype /Link /Rect ['.$rect.'] /Border [0 0 0] ';
					if(is_string($pl[4]))
						$annots.='/A <</S /URI /URI '.$this->_textstring($pl[4]).'>>>>';
					else
					{
						$l=$this->links[$pl[4]];
						$h=isset($this->PageSizes[$l[0]]) ? $this->PageSizes[$l[0]][1] : $hPt;
						$annots.=sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>',2+$nb+$l[0],$h-$l[1]*$this->k);
					}
				}
				$this->_out($annots.']');
			}
			$this->_out('/Contents '.(2+$n).' 0 R>>');
			$this->_out('endobj');
		}
		//Pages root
		$this->offsets[1]=ftell($this->f);
		$this->_out('1 0 obj');
		$this->_out('<</Type /Pages');
		$kids='/Kids [';
		for($n=1;$n<=$nb;$n++)
			$kids.=(2+$nb+$n).' 0 R ';
		$this->_out($kids.']');
		$this->_out('/Count '.$nb);
		$this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]',$wPt,$hPt));
		$this->_out('>>');
		$this->_out('endobj');
	}

	function _putresources()
	{
		$this->_putextgstates();
		$this->_putfonts();
		$this->_putimages();
		//Resource dictionary
		$this->offsets[2]=ftell($this->f);
		$this->_out('2 0 obj');
		$this->_out('<<');
		$this->_putresourcedict();
		$this->_out('>>');
		$this->_out('endobj');
	}

	function _putcatalog()
	{
		$this->_out('/Type /Catalog');
		$this->_out('/Pages 1 0 R');
		$n=3+$this->page;
		if($this->ZoomMode=='fullpage')
			$this->_out('/OpenAction ['.$n.' 0 R /Fit]');
		elseif($this->ZoomMode=='fullwidth')
			$this->_out('/OpenAction ['.$n.' 0 R /FitH null]');
		elseif($this->ZoomMode=='real')
			$this->_out('/OpenAction ['.$n.' 0 R /XYZ null null 1]');
		elseif(!is_string($this->ZoomMode))
			$this->_out('/OpenAction ['.$n.' 0 R /XYZ null null '.($this->ZoomMode/100).']');
		if($this->LayoutMode=='single')
			$this->_out('/PageLayout /SinglePage');
		elseif($this->LayoutMode=='continuous')
			$this->_out('/PageLayout /OneColumn');
		elseif($this->LayoutMode=='two')
			$this->_out('/PageLayout /TwoColumnLeft');
	}

	function _enddoc()
	{
		$this->_putpages();
		$this->_putresources();
		
		if(!empty($this->extgstates) && $this->PDFVersion<'1.4')
            $this->PDFVersion='1.4';
			
		//Info
		$this->_newobj();
		$this->_out('<<');
		$this->_putinfo();
		$this->_out('>>');
		$this->_out('endobj');
		//Catalog
		$this->_newobj();
		$this->_out('<<');
		$this->_putcatalog();
		$this->_out('>>');
		$this->_out('endobj');
		//Cross-ref
		$o=ftell($this->f);
		$this->_out('xref');
		$this->_out('0 '.($this->n+1));
		$this->_out('0000000000 65535 f ');
		for($i=1;$i<=$this->n;$i++)
			$this->_out(sprintf('%010d 00000 n ',$this->offsets[$i]));
		//Trailer
		$this->_out('trailer');
		$this->_out('<<');
		$this->_puttrailer();
		$this->_out('>>');
		$this->_out('startxref');
		$this->_out($o);
		$this->_out('%%EOF');
		$this->state=3;
		fclose($this->f);
	}
	
	
	//FPDF Transparency - http://www.fpdf.de/downloads/addons/74/
	
	// alpha: real value from 0 (transparent) to 1 (opaque)
    // bm:    blend mode, one of the following:
    //          Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn, 
    //          HardLight, SoftLight, Difference, Exclusion, Hue, Saturation, Color, Luminosity
    function SetAlpha($alpha, $bm='Normal')
    {
        // set alpha for stroking (CA) and non-stroking (ca) operations
        $gs = $this->AddExtGState(array('ca'=>$alpha, 'CA'=>$alpha, 'BM'=>'/'.$bm));
        $this->SetExtGState($gs);
    }
	
	function AddExtGState($parms)
    {
        $n = count($this->extgstates)+1;
        $this->extgstates[$n]['parms'] = $parms;
        return $n;
    }
	
	function SetExtGState($gs)
    {
        $this->_out(sprintf('/GS%d gs', $gs));
    }
	
	function _putresourcedict()
    {
        parent::_putresourcedict();
        $this->_out('/ExtGState <<');
        foreach($this->extgstates as $k=>$extgstate)
            $this->_out('/GS'.$k.' '.$extgstate['n'].' 0 R');
        $this->_out('>>');
    }
	
	function _putextgstates()
    {
        for ($i = 1; $i <= count($this->extgstates); $i++)
        {
            $this->_newobj();
            $this->extgstates[$i]['n'] = $this->n;
            $this->_out('<</Type /ExtGState');
            foreach ($this->extgstates[$i]['parms'] as $k=>$v)
                $this->_out('/'.$k.' '.$v);
            $this->_out('>>');
            $this->_out('endobj');
        }
    }
}

/*
class PDF extends FPDF
	{
	// Page header
	function Header()
	{
		global $server, $inData;
		
		
		// Arial bold 15
		$this->SetFont('Arial','B',32);
		// Move to the right
		//$this->Cell(95);
		$this->setXY(95,5);
		// Title
		if(isset($inData['preview'])) {
			$this->SetTextColor(255,0,0); 
			$this->Cell(30,10,'Preview',0,0,'C');
		}
		// Line break
		$this->Ln(20);
	}
	
	function AcceptPageBreak()
	{
		return false;
	}
/*
	// Page footer
	function Footer()
	{
		// Position at 1.5 cm from bottom
		$this->SetY(-15);
		// Arial italic 8
		$this->SetFont('Arial','I',8);
		// Page number
		$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
	}
	
	function AcceptPageBreak()
	{
		return false;
	}
	
} */

$subdir = get_subdir();
	
if(isset($inData['preview'])) {
	$subdir .= "/generated_documents_preview/";
} else {
	$subdir .= "/generated_documents/";
}

if (!is_dir($upload_dir.$subdir)) {
	mkdir($upload_dir.$subdir, 0700, true);
}
	
$filename = create_filename().".pdf";
	
	
$pdf = new FPDF2File();
$pdf->Open($upload_dir.$subdir.$filename);
$pdf->SetAutoPageBreak(false, 0);
$pdf->SetMargins(0,0,0);
//$pdf->AliasNbPages();

$csvHeaders = array();

if($server->outData['data'][1]['datasource_file_name'] !== "") {
	if(isset($server->outData['data'][1]['datasource_file_name'])) {
		$csv->parse("..".$server->outData['data'][1]['datasource_data_path'].$server->outData['data'][1]['datasource_file_name'], 0 , 1000); // At max 1000 lines.
		
		//Leon 24-03-14 - Add this back in, and add error handling into the page designer to print a handy error message to the user
		//if($csv->error_info) {
		//	die(array("error" => 1, "details" => print_r($csv->error_info, 1)) );
		//}
		
		foreach($csv->titles as $key => $header) {
			$csvHeaders[] = "<".$header.">";
		}
		
		if(count($csv->data) == 0) {
			$csv->data[] = array();
		}
	} else {
		$csv->data[] = array();
	}
}

$pageCount = 0;

// Instanciation of inherited class
foreach ($csv->data as $csvKey => $csvRow) {

	//Iterate pages.
	
	foreach($server->outData['data'] as $pgKey => $pgData) {
		$pageCount++;	
		$orientation = "L";
				
		if($pgData['width'] < $pgData['height']) 
			$orientation = "P";
				
		$pdf->AddPage($orientation, array($pgData['width'], $pgData['height']));		
		$pageSize = array("width" => $pgData['width'], "height" => $pgData['height']);
		
		// Set the background.	
		if(strlen($pgData['data_path'].$pgData['file_name']) > 0) {
			$pdf->Image('../'.$server->outData['data'][$pgKey]['data_path'].$server->outData['data'][$pgKey]['file_name'],0,0,$pgData['width'],0); //210,0 means stretch horizontally, fill veritcally.   //-150 //210,297            //0,0,-300
		}
		
		$pdf->SetFontSize(19);
				
		$pageVariables = $pgData['variables']; //json_decode($pgData['variables'], 1);
		
		//Check that out variables json was correct json.
		if(is_array($pageVariables)) {
	
			//Iterate variables in the page.
			foreach($pageVariables as $key => $data) {
				
				//Determine the font color.
				preg_match('/^rgb\((\d+), (\d+), (\d+)\)$/', $data['font_color'], $fontColor);
				$pdf->SetTextColor($fontColor[1], $fontColor[2] , $fontColor[3]); 
				
				switch($data['font_family']) {
					case "Arial" : 
									$fontSize = $data['font_size'] * 0.81;
									//$paddingOffset = $fontSize + ((($data['font_padding']+3)*2)+2);
									break;
					case "Times"   :
									$fontSize = $data['font_size']  * 0.77;
									//$paddingOffset = $fontSize + ((($data['font_padding']+3)*2)+2);
									break;
					default: 
									$fontSize = $data['font_size']  * 0.77;
									//$paddingOffset = $fontSize + ((($data['font_padding'])*2)+2);
									break; 
				}
				
				$fontStyle = (strstr($data['font_style'], "bold") ? "B" : "") . (strstr($data['font_style'], "italic") ? "I" : "") . (strstr($data['font_style'], "underline") ? "U" : "");

				$pdf->SetFont($data['font_family'], $fontStyle, $fontSize); 	
				
				if($data['font_align'] == "L")
					$data['font_padding'] = 15;
						
				//Center uses default padding.
				$xPosition = $data['x']+(($data['font_padding']/4)); //Seems the padding interferes with right aligned text.
				
				if($data['font_align'] == "R") {
					$data['font_padding'] = 15;
					$xPosition = $data['x']-(($data['font_padding']/4)); //Seems the padding interferes with right aligned text.
				}
				
				$data['font_padding'] = 10;
				//$pdf->SetXY(pixelsTomm($xPosition), pixelsTomm($data['y']+($data['font_padding'] + $fontSize /16)-$fontSize/2)); // This math here aligns the canvas representation to the pdf version.
				$pdf->SetXY(pixelsTomm($xPosition), pixelsTomm($data['y']+($data['font_padding'] + $fontSize / 16)-$fontSize/2.5)); // This math here aligns the canvas representation to the pdf version.
				//$pdf->Cell(30,30,$data['name'],0,0,'C');	
				
				//Replace variable holders "{variable}" with their data from the CSV.
				$text  = str_replace($csvHeaders, $csvRow, $data['text']);
				
				$pdf->MultiCell(pixelsTomm($data['width']),(($fontSize*1.2)*0.352777778),$text,0,$data['font_align']); //0.352777778 = 1 point in mm.
				
				//Vertical (Y Axis) seems to be 2.5mm off by default when using the write function so add +2.5 to it when using write.
				//$pdf->Write(0, $data['name']); 
			}
		
		}
			
	}

	//Leon - 18-01-13 - Disabled the 1 page limit.
	//If this is a preview, break after 1 page.
	//if(isset($inData['preview'])) {
	//	break;
	//}
		
}


	function create_filename($namespace = '') {     
		static $guid = '';
		$uid = uniqid("", true);
		$data = "";
		$data .= @$_SERVER['REQUEST_TIME'];
		$data .= @$_SERVER['HTTP_USER_AGENT'];
		$data .= @$_SERVER['LOCAL_ADDR'];
		$data .= @$_SERVER['LOCAL_PORT'];
		$data .= @$_SERVER['REMOTE_ADDR'];
		$data .= @$_SERVER['REMOTE_PORT'];
		$hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
		$guid = substr($hash,  0,  8);
		return gmdate("Ymd")."_".$guid;
	}

    function get_subdir() {
        global $session;
		return $session->get_user_var('file_directory');
    }
	
	$pdf->Output();
	
	if(!isset($inData['preview'])) {
		$filePath  = "/user_files/".$subdir.$filename;
		$documentID = $_POST['document_id'];
		$sqlTime    = gmdate('Y-m-d H:i:s');
		$user_id    = $server->session->get_user_var('id');
		$genDataSourceID = $server->outData['data'][1]['datasource_id'];
		$query = "INSERT INTO generated_documents (created_at, updated_at, user_id, document_id, file_path, pages, generation_datasource_id) 
				  VALUES(?, NOW(), ?, ?, ?, ?, ? )";
							  
		$stmt = $server->sql->link->prepare($query);
		$stmt->bind_param('siisii', $sqlTime, $user_id, $documentID, $filePath, $pageCount, $genDataSourceID);	
		$stmt->execute();
		
		
		//Update a user's usage stats for billing
		$query = "UPDATE user_statistics SET billing_cycle_pages =  billing_cycle_pages + ?, pages_made_total = pages_made_total + ?, billing_cycle_documents = billing_cycle_documents + 1
				  WHERE user_id = ?";
							  
		$stmt = $server->sql->link->prepare($query);
		$stmt->bind_param('iii',  $pageCount, $pageCount, $user_id);	
		
		$stmt->execute();
	}

	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish = $time;
	$total_time = round(($finish - $start), 4);
	
	die(json_encode(array('dog_years' => $total_time, 'url' => $subdir.$filename, 'filename' => $filename)));

?>
