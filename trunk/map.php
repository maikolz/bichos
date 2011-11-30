<?php
	if (!extension_loaded("MapScript"))
	{	 
		dl('php_mapscript.'.PHP_SHLIB_SUFFIX);
	}
	$mapObject = ms_newMapObj("/ms4w/apache/htdocs/map.map");
	$defSize=3;
	$checkPan="CHECKED";

	if ( isset($_GET["mapa_x"]) && isset($_GET["mapa_y"]) ) 
	{
		$arrayExtent = explode(" ",$_GET["extent"]); 
		$mapObject->setextent($arrayExtent[0],$arrayExtent[1],$arrayExtent[2],$arrayExtent[3]);
		$pointObject = ms_newpointObj();
		$pointObject->setXY($_GET["mapa_x"],$_GET["mapa_y"]);
		$extentRectObject = ms_newrectObj();
		$extentRectObject->setextent($arrayExtent[0],$arrayExtent[1],$arrayExtent[2],$arrayExtent[3]);
		$zoomFactor = $_GET["zoom"]*$_GET["zsize"];
		$defSize = $_GET['zsize'];

		if ($zoomFactor == 0) 
		{
			$zoomFactor = 1;
            $checkPan = "CHECKED";
			$checkZout = "";
			$checkZin = "";
		} 
		else 
		if ($zoomFactor < 0) 
		{
            $checkPan = "";
			$checkZout = "CHECKED";
			$checkZin = "";
			$zoomFactor = $zoomFactor - 1;
		} 
		else 
		{
            $checkPan = "";
            $checkZout = "";
			$checkZin = "CHECKED";
			$zoomFactor = $zoomFactor + 1;
		}
		$mapObject->zoompoint($zoomFactor,$pointObject,$mapObject->width,$mapObject->height,$extentRectObject);
	}
	else if ($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		$layers = $_POST['layers']; 
		$layers2 = $_POST['layersSalida']; 
		$stddev = $_POST['StandardDev'];
				
		$handle = fopen('requesttemplate.txt','r');
		$content = fread($handle,filesize('requesttemplate.txt'));
		if(!isset($layers))
		{
			echo("<p>No se selecciono ninguna capa!</p>\n");
		}
		else
		{
			$nLayers = count($layers);
			echo($nLayers);
			for($i=0; $i < $nLayers; $i++)
			{
				$content = str_replace("#M1<".$layers[$i].">","",$content);
			}
		}
		
		if(!isset($layers2))
		{
			echo("<p>No se selecciono ninguna capa de salida!</p>\n");
		}
		else
		{
			$nLayers2 = count($layers2);
			echo($layers2[0]);
			for($i=0; $i < $nLayers2; $i++)
			{
			echo($layers2[$i]);
				$content = str_replace("#OF<".$layers2[$i].">","",$content);
				$content = str_replace("#OM<".$layers2[$i].">","",$content);
			}
		}
		if(!isset($stddev))
		{
			//echo("<p>Usando StandardDev por defecto 0.674 </p>\n");
			$content = str_replace("<StandardDev>","0.674",$content);		
		}
		else
		{
			//echo ("/"+$stddev+"/");
			if($stddev==""){
				//echo("<p>Usando StandardDev por defecto 0.674 </p>\n");
				$content = str_replace("<StandardDev>","0.674",$content);
				
			}
			else
			{
				//echo("<p>Usando StandardDev proporcionado </p>\n");
				$content = str_replace("<StandardDev>",$stddev,$content);
			}
		}
		fclose($handle);
		$requestFileName = "request.txt";
		$requestFileHandle = fopen($requestFileName, 'w') or die("can't open file");
		fwrite($requestFileHandle, $content);
		fclose($requestFileHandle);
			
		set_time_limit(0);
		exec('om_console request.txt',$out ,$return_var);
		$layer = $mapObject->getLayerByName('bioclima');
		$layer->status = MS_ON;
	}
	$mapImage = $mapObject->draw();
	$urlImage = $mapImage->saveWebImage();
	$printExtentHTML = $mapObject->extent->minx." ".$mapObject->extent->miny." " .$mapObject->extent->maxx." ".$mapObject->extent->maxy;

?>
<HTML>
<style type="text/css" src="css/style.css">
		body {
		margin: 0;
		padding: 20;
		font-family: "Georgia", serif;
		
		}
		H2 {color:black;
		font-family: serif;
		
		font-style: normal;
		font-size:12}
		
		
		
	</style>
<HEAD>
<title>Map</title>
<style type="text/css">
<!--
.style3 {font-size: 12px; }
-->
</style>
</HEAD>


<script type="text/javascript">

function LimpiarVariables()
{
	var select2 = document.getElementById ("ambientales2");
	while (select2.options.length) {
                select2.options.remove (0);
            }
}
function UpdateSelected()
{
	
         

            var select1 = document.getElementById ("ambientales");
            var select2 = document.getElementById ("ambientales2");
 
            // removes all options from select 2
            while (select2.options.length) {
                select2.options.remove (0);
            }
           for (var i=0; i < select1.options.length; i++){
			   
			   if(select1.options[i].selected){
				   var option = new Option (select1.options[i].text, select1.options[i].value);
				   select2.options.add (option);
			   }
		   }
           
	}
	

</script>

<BODY onload "LimpiarVariables()">
<CENTER>
<FORM METHOD=GET ACTION=<?php echo $HTTP_SERVER_VARS['PHP_SELF']?>>
  <table width="800" >
    <tr>
      <td width="22%" scope="col">
		<table border="1" align="center" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <td><span class="style3"> Pan </span></td>
          <td><input type=RADIO name="zoom" value=0 <?php echo $checkPan; ?>>          </td>
        </tr>
        <tr>
          <td><span class="style3"> Zoom In </span></td>
          <td><input type=RADIO name="zoom" value=1 <?php echo $checkZin; ?>>          </td>
        </tr>
        <tr>
          <td><span class="style3"> Zoom Out </span></td>
          <td><input type=RADIO name="zoom" value=-1 <?php echo $checkZout; ?>>          </td>
        </tr>
        <tr>
          <td><span class="style3"> Zoom Size </span></td>
          <td><input type=TEXT name="zsize" value="<?php echo $defSize; ?>"  size=2>          </td>
        </tr>
        <tr>
          <td><span class="style3">Full Extent</span></td>
          <td><input type=SUBMIT name="full" value="Go"   size=2>          </td>
        </table>
      <td width="78%" scope="col" >
        <div align="center">
        <input type=IMAGE name="mapa" src="<?php echo $urlImage; ?>" border=1>
      </div></td>
	  <td>
	 
	  </td>
    </tr>
  </table>
  <INPUT TYPE=HIDDEN NAME="extent" VALUE="<?php echo $printExtentHTML; ?>">
</FORM>
<FORM METHOD=POST ACTION=<?php echo $HTTP_SERVER_VARS['PHP_SELF']?> >
 <table   >
    <tr>
      <td>
	<H2>Variables Ambientales</H2>
	</BR>
	<select name="layers[]" id="ambientales" size="5" multiple="multiple" onchange="UpdateSelected()">
		<option value="1">Precipitacion</option>
		<option value="2">Dias lluviosos</option>
		<option value="3">Radiacion</option>
		<option value="4">Temperatura Max</option>
		<option value="5">Temperatura Min</option>
	</select>
	<H2>Variables de Salida</H2>
		<select name="layersSalida[]" id="ambientales2" size="5" >
		<option value="1">Precipitacion</option>
		<option value="2">Dias lluviosos</option>
		<option value="3">Radiacion</option>
		<option value="4">Temperatura Max</option>
		<option value="5">Temperatura Min</option
	</select>
	</td>
	<td>
		<H2>Especies</span> </H2>
		<select name="layers" id="especies" size="2" multiple="multiple">
			<option value="1">Tinamus Major</option>
		</select>
	</td>
	<td>
		<H2>BioClim</H2>
		</BR>
		<H2>StandardDeviationCutoff:</H2>  <input id="stdDev" type="text" name="StandardDev" value="0.674" onclick="if(document.getElementById('stdDev').value=='0.674')document.getElementById('stdDev').value='';" 
		onblur="if(document.getElementById('stdDev').value=='')document.getElementById('stdDev').value='0.674'; "/>
		</td>
	</tr>
      </td>
      </table>
<input name="execute" type="submit" value="GO"/>
</FORM>
</CENTER>
</BODY>
</HTML>