<?php
	if (!extension_loaded("MapScript"))
	{	 
		dl('php_mapscript.'.PHP_SHLIB_SUFFIX);
	}
	$mapObject = ms_newMapObj("/ms4w/apache/htdocs/map.map");
	$defSize=3;
	$checkPan="CHECKED";
	$bioclimexecuted = 0;

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
		$bioclimexecuted = $_GET['bioclim'];
	
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
		if ($bioclimexecuted)
		{
			$layer = $mapObject->getLayerByName('bioclima');
			$layer->status = MS_ON;
		}
		$mapObject->zoompoint($zoomFactor,$pointObject,$mapObject->width,$mapObject->height,$extentRectObject);
	}
	if ($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		$layers = $_POST['layers']; 
		$layers2 = $_POST['layersSalida']; 
		$stddev = $_POST['StandardDev'];
		$anno = $_POST['anno'];
				//PARAMETROS DEL ALGORITMO 
		$BGPoints= $_POST['BGPoints']; 
		$Absence= $_POST['Absence']; 
		$Presence= $_POST['Presence']; 
		$Iterations= $_POST['Iterations']; 
		$Tolerance= $_POST['Tolerance']; 
		$OutputFormat= $_POST['OutputFormat']; 
		$QuadraticFeatures= $_POST['QuadraticFeatures']; 
		$ProductFeatures= $_POST['ProductFeatures']; 
		$HingeFeatures= $_POST['HingeFeatures']; 
		$ThresholdFeatures= $_POST['ThresholdFeatures']; 
		
		//
				
		$handle = fopen('requesttemplate.txt','r');
		$content = fread($handle,filesize('requesttemplate.txt'));
		if(!isset($anno))
		{
			echo("<p>No se selecciono el a&ntilde;o!</p>\n");
			$anno="2000";
		}
		
		
		if(!isset($layers2))
		{
			echo("<p>No se selecciono ninguna capa de salida!</p>\n");
		}
		
		if(!isset($layers))
		{
			echo("<p>No se selecciono ninguna capa!</p>\n");
		}
		else
		{
			$nLayers = count($layers);
			//echo("<p>Capas:");
			//print_r($layers);
			for($i=0; $i < $nLayers; $i++)
			{
				$content = str_replace("#M1".$anno."<".$layers[$i].">","",$content);
			}
		}
		
		if(!isset($layers2))
		{
			echo("<p>No se selecciono ninguna capa de salida!</p>\n");
		}
		else
		{
			$nLayers2 = count($layers2);
			//echo("<p>Plantilla:");
			//print_r($layers2);
			for($i=0; $i < $nLayers2; $i++)
			{
				//echo("<p>Plantilla:".$layers2[$i]."</p>\n");
				$content = str_replace("#OF".$anno."<".$layers2[$i].">","",$content);
				$content = str_replace("#OM".$anno."<".$layers2[$i].">","",$content);
			}
		}
		//PARAMETROS DEL ALGORITMO 
			$content = str_replace("<NumberOfBackgroundPoints>",$BGPoints,$content);
			$content = str_replace("<UseAbsencesAsBackground>",$Absence,$content);
			$content = str_replace("<IncludePresencePointsInBackground>",$Presence,$content);
			$content = str_replace("<NumberOfIterations>",$Iterations,$content);
			$content = str_replace("<TerminateTolerance>",$Tolerance,$content);
			$content = str_replace("<OutputFormat>",$OutputFormat,$content);
			$content = str_replace("<QuadraticFeatures>",$QuadraticFeatures,$content);
			$content = str_replace("<ProductFeatures>",$ProductFeatures,$content);
			$content = str_replace("<HingeFeatures>",$HingeFeatures,$content);
			$content = str_replace("<ThresholdFeatures>",$ThresholdFeatures,$content);
		
		
		
		fclose($handle);
		$requestFileName = "request.txt";
		$requestFileHandle = fopen($requestFileName, 'w') or die("Error al abrir archivo");
		fwrite($requestFileHandle, $content);
		fclose($requestFileHandle);
			
		set_time_limit(0);
		exec("om_console.exe request.txt");
		$layer = $mapObject->getLayerByName('bioclima');
		$layer->status = MS_ON;
		$bioclimexecuted = 1;
	}
	$mapImage = $mapObject->draw();
	$urlImage = $mapImage->saveWebImage();
	$printExtentHTML = $mapObject->extent->minx." ".$mapObject->extent->miny." " .$mapObject->extent->maxx." ".$mapObject->extent->maxy;

?>
<HTML>
<style type="text/css" src="css/style.css">
		body {
		margin: 0;
		padding: 20px;
		font-family: "Arial", serif;
		
		background-image:url('images/fondo.jpg');
		color: #FFFFFF;
		
		}
		H2 {color:white;
		font-family: Arial;
		
		font-style: normal;
		font-size:14}
		
		H1 {color:white;
		font-family: Arial;
		
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
			select2.options.length = 0;

            //while (select2.options.length) {
               // select2.options.remove (0);
            //}
           for (var i=0; i < select1.options.length; i++){
			   
			   if(select1.options[i].selected){
				   var option = new Option (select1.options[i].text, select1.options[i].value);
				   select2.options.add (option);
			   }
		   }
           
	}
	

</script>

<BODY onload "LimpiarVariables()" >
<CENTER>

  <table width="1200" border="1"  bordercolor="white" >
  
    <tr>
	  <FORM METHOD=GET ACTION=<?php echo $HTTP_SERVER_VARS['PHP_SELF']?>>
      <td width="10%" scope="col">
		<table border="1" align="center" cellpadding="0" cellspacing="0" bordercolor="#FFFFFF">
        <tr>
          <td><span class="style3"> Pan </span></td>
          <td>
			<img src="/images/center.png" onclick="document.getElementById('recenter').checked = true" style="padding-left:4px;float:left"/>
			<input id="recenter" type=RADIO name="zoom" value=0 <?php echo $checkPan; ?> style="display:none;">
		  </td>
        </tr>
        <tr>
          <td><span class="style3"> Zoom In </span></td>
          <td>
			<img src="/images/zoomin.png" onclick="document.getElementById('zoomIn').checked = true" style="float:left"/>
			<input id="zoomIn" type=RADIO name="zoom" value=1 <?php echo $checkZin; ?> style="display:none;">          
		  </td>
        </tr>
        <tr>
          <td><span class="style3"> Zoom Out </span></td>
          <td>
			<img src="/images/zoomout.png" onclick="document.getElementById('zoomOut').checked = true" style="float:left"/>
			<input id="zoomOut" type=RADIO name="zoom" value=-1 <?php echo $checkZout; ?> style="display:none;">
		  </td>
        </tr>
        <tr>
          <td><span class="style3"> Zoom Size </span></td>
          <td>
			<input type=TEXT name="zsize" value="<?php echo $defSize; ?>" style="width:23px;margin-left:5px;">          
		  </td>
        </tr>
        </table>
	  </td>
      <td width="50%" scope="col"   >
        <div align="center">
		<H2>Inform&aacute;tica Aplicada a la Ecolog&iacute;a<H2>
		</BR>
		
		<H2>MAPA COSTA RICA</H2>
        <input type=IMAGE name="mapa" src="<?php echo $urlImage; ?>" border=1 >
      </div>
	  <div>
		<p>Maikol Zumbado B17651<br>
		Yeison Castillo 980820<br>
		Lilliana P&eacute;rez 997390
		</p>
	  </div>
	  </td>
	    <INPUT TYPE=HIDDEN NAME="extent" VALUE="<?php echo $printExtentHTML; ?>">
        <INPUT TYPE=HIDDEN NAME="bioclim" VALUE="<?php echo $bioclimexecuted; ?>">
      </FORM>
	  
	  <td style="vertical-align:top">
	   
	   <FORM METHOD=POST ACTION=<?php echo $HTTP_SERVER_VARS['PHP_SELF']?> >
	  
		<H2>Variables Ambientales</H2>
		
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
			<option value="5">Temperatura Min</option>
		</select>
	   
		<H2>Especies </H2>
		<select name="especies" id="especies" size="2" multiple="multiple">
			<option value="1">Tinamus Major</option>
		</select>
		<H2>A&ntilde;o </H2>
		<select name="anno" id="anno" size="5" >
			<option value="2000">2000</option>
			<option value="2030">2030</option>
			<option value="2050">2050</option>
			<option value="2080">2080</option>
		</select>
		
	</td>
	<td >
	
	        <H2>Algoritmo: MAXENT </H2>
		
		
		
		<table >
		 <tr>
		 
			   <td style="vertical-align:top">
				<H1>Number Of Background Points: </H1>
			   </td>
			   <td style="vertical-align:top">
				<H1><input id="BGPoints" type="text" name="BGPoints" value="1000" size=5 onclick="if(document.getElementById('BGPoints').value=='1000')document.getElementById('BGPoints').value='';" 
		onblur="if(document.getElementById('BGPoints').value=='')document.getElementById('BGPoints').value='1000'; "/></H1>
			   </td>
		  </tr>
		  <tr>		  
			   <td style="vertical-align:top">
				<H1>Use Absences As Background:  </H1>
			   </td>
			   <td style="vertical-align:top">
				<H1><input id="Absence" type="text" name="Absence" value="0" size=5 onclick="if(document.getElementById('Absence').value=='0')document.getElementById('Absence').value='';" 
		onblur="if(document.getElementById('Absence').value=='')document.getElementById('Absence').value='0'; "/></H1>
			   </td>
		   
	           </tr>

		   <tr>		  
			   <td style="vertical-align:top">
				<H1>Include Presence Points In Background:  </H1>
			   </td>
			   <td style="vertical-align:top">
				<H1><input id="Presence" type="text" name="Presence" value="1" size=5 onclick="if(document.getElementById('Presence').value=='1')document.getElementById('Presence').value='';" 
		onblur="if(document.getElementById('Presence').value=='')document.getElementById('Presence').value='1'; "/></H1>
			   </td>
		   
	           </tr>	
		   <tr>		  
			   <td style="vertical-align:top">
				<H1>Number Of Iterations:     </H1>
			   </td>
			   <td style="vertical-align:top">
				<H1><input id="Iterations" type="text" name="Iterations" value="500" size=5 onclick="if(document.getElementById('Iterations').value=='500')document.getElementById('Iterations').value='';" 
		onblur="if(document.getElementById('Iterations').value=='')document.getElementById('Iterations').value='500'; "/></H1>
			   </td>
		   
	           </tr>

		   <tr>		  
			   <td style="vertical-align:top">
				<H1>Terminate Tolerance:     </H1>
			   </td>
			   <td style="vertical-align:top">
				<H1><input id="Tolerance" type="text" name="Tolerance" value="0.00001" size=5 onclick="if(document.getElementById('Tolerance').value=='0.00001')document.getElementById('Tolerance').value='';" 
		onblur="if(document.getElementById('Tolerance').value=='')document.getElementById('Tolerance').value='0.00001'; "/> </H1>
			   </td>
		   
	           </tr>

		   <tr>		  
			   <td style="vertical-align:top">
				<H1>OutputFormat (1 = Raw, 2 = Logistic):     </H1>
			   </td>
			   <td style="vertical-align:top">
				<H1><input id="OutputFormat" type="text" name="OutputFormat" value="2" size=5 onclick="if(document.getElementById('OutputFormat').value=='2')document.getElementById('OutputFormat').value='';" 
		onblur="if(document.getElementById('OutputFormat').value=='')document.getElementById('OutputFormat').value='2'; if((document.getElementById('OutputFormat').value>2)||(document.getElementById('OutputFormat').value==0))document.getElementById('OutputFormat').value='2'; "/></H1>
			   </td>
		   
	           </tr>			   
		   <tr>		  
			   <td style="vertical-align:top">
				<H1>QuadraticFeatures (1 / 0):     </H1>
			   </td>
			   <td style="vertical-align:top">
				<H1><input id="QuadraticFeatures" type="text" name="QuadraticFeatures" value="1" size=5 onclick="if(document.getElementById('QuadraticFeatures').value=='1')document.getElementById('QuadraticFeatures').value='';" 
		onblur="if(document.getElementById('QuadraticFeatures').value=='')document.getElementById('QuadraticFeatures').value='1'; if(document.getElementById('QuadraticFeatures').value>1)document.getElementById('QuadraticFeatures').value='1'; "/></H1>
			   </td>
		   
	           </tr>
		   <tr>		  
			   <td style="vertical-align:top">
				<H1>ProductFeatures (1 / 0): </H1>
			   </td>
			   <td style="vertical-align:top">
				<H1><input id="ProductFeatures" type="text" name="ProductFeatures" value="1" size=5 onclick="if(document.getElementById('ProductFeatures').value=='1')document.getElementById('ProductFeatures').value='';" 
		onblur="if(document.getElementById('ProductFeatures').value=='')document.getElementById('ProductFeatures').value='1'; if(document.getElementById('ProductFeatures').value>1)document.getElementById('ProductFeatures').value='1'; "/></H1>
			   </td>
		   
	           </tr>
		   <tr>		  
			   <td style="vertical-align:top">
				<H1>HingeFeatures (1 / 0):     </H1>
			   </td>
			   <td style="vertical-align:top">
				<H1><input id="HingeFeatures" type="text" name="HingeFeatures" value="1" size=5 onclick="if(document.getElementById('HingeFeatures').value=='1')document.getElementById('HingeFeatures').value='';" 
		onblur="if(document.getElementById('HingeFeatures').value=='')document.getElementById('HingeFeatures').value='1'; if(document.getElementById('HingeFeatures').value>1)document.getElementById('HingeFeatures').value='1'; "/></H1>
			   </td>
		   
	           </tr>		   
		   <tr>		  
			   <td style="vertical-align:top">
				<H1>ThresholdFeatures (1 / 0):      </H1>
	
			   </td>
			   <td style="vertical-align:top">
				<H1><input id="ThresholdFeatures" type="text" name="ThresholdFeatures" value="1" size=5 onclick="if(document.getElementById('ThresholdFeatures').value=='1')document.getElementById('ThresholdFeatures').value='';" 
		onblur="if(document.getElementById('ThresholdFeatures').value=='')document.getElementById('ThresholdFeatures').value='1'; if(document.getElementById('ThresholdFeatures').value>1)document.getElementById('ThresholdFeatures').value='1'; "/> </H1>
			   </td>
		   
	            </tr>	
			<tr>		  
			   <td style="vertical-align:top">
				<input name="execute" type="submit" value="Modelar"/>
		         </td>
		</tr>
        </table>
</FORM>
	    </td>
    </tr>
  </table>


</CENTER>
</BODY>
</HTML>