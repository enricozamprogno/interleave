<?php
/* ********************************************************************
* Interleave
* Copyright (c) 2001-2012 info@interleave.nl
* Licensed under the GNU GPL. For full terms see http://www.gnu.org/
*
* This file does several things :)
*
* Check http://www.interleave.nl/ for more information
*
* BUILD / RELEASE :: 5.5.1.20121028
*
**********************************************************************
*/
?>
<gauge>
	<!-- large gauge -->
	<circle x='145' y='130' radius='110' fill_color='555555' fill_alpha='100' line_thickness='6' line_color='333333' line_alpha='90'>
	<circle x='145' y='130' radius='100' start='240' end='480' fill_color='99bbff' fill_alpha='90' line_thickness='2' line_alpha='20'>
	<circle x='145' y='130' radius='80' start='240' end='480' fill_color='99bbff' fill_alpha='80'>
	<circle x='145' y='130' radius='20' fill_color='333333' fill_alpha='100' line_alpha='0'>
	<circle x='145' y='130' radius='20' start='130' end='230' fill_color='333333' fill_alpha='100' line_alpha='0'>
	
	<?php
	//these are PHP functions that generate the XML to to draw radial ticks and numbers
	//any script language can be used to generate the XML code like this

//	RadialTicks( 145, 130, 80, 15, 250, 387, 6, 8, "000000" );
	RadialTicks( 145, 130, 80, 10, 250, 490, 26, 4, "000000" );
//	RadialTicks( 145, 130, 80, 10, 55, 110, 6, 4, "000000" );
	
	$from = 0;
	$to = $_REQUEST['totcount'];

	RadialNumbers( 145, 130, 80, $from, $to, 245, 465, 9, 8, "444444" );

	// Minimum (0) = "-110";
	// Maximum (0) = "115";
	// Scale = "225";

	$value = $_REQUEST['valcount']; 

	$pc1 = $to / 100;  // 1 procent van WAARDES

	$t = $value / $pc1; // Percentage van waarde
	
	$span = ((220/100) * $t);

	$span -= 110;

	?>
	<rotate x='145' y='130' start='0' span='<?php echo $span; ?>' step='20' shake_frequency='100' shake_span='2' shadow_alpha='15'>
		<rect x='143' y='40' width='3' height='100' fill_color='ffffff' fill_alpha='90' line_alpha='0'>
	</rotate>
	<circle x='145' y='130' radius='1' fill_color='ffffff' fill_alpha='100' line_thickness='5' line_alpha='50'>
	<text x='95' y='180' width='100' size='14' color='ffffff' alpha='70' align='center'><?php echo $_REQUEST['Gtitle'];?></text>
	
	<?php
	//====================================
	//PHP function that generates the XML code to draw radial ticks
	function RadialTicks ( $x_center, $y_center, $radius,  $length, $start_angle, $end_angle, $ticks_count, $thickness, $color ){
		
		for ( $i=$start_angle; $i<=$end_angle; $i+=($end_angle-$start_angle)/($ticks_count-1) ){
			echo "	<line x1='".($x_center+sin(deg2rad($i))*$radius)."' y1='".($y_center-cos(deg2rad($i))*$radius)."' x2='".($x_center+sin(deg2rad($i))*($radius+$length))."' y2='".($y_center-cos(deg2rad($i))*($radius+$length))."' thickness='".$thickness."' color='".$color."'>";
		
		}
	}
	//====================================
	//PHP function that generates the XML code to draw radial numbers
	function RadialNumbers ( $x_center, $y_center, $radius,  $start_number, $end_number, $start_angle, $end_angle, $ticks_count, $font_size, $color ){
		
		$number=$start_number;
		
		for( $i=$start_angle; $i<=$end_angle; $i+=($end_angle-$start_angle)/($ticks_count-1) ){
			echo "	<text x='".($x_center+sin(deg2rad($i))*$radius)."' y='".($y_center-cos(deg2rad($i))*$radius)."' width='200' size='".$font_size."' color='".$color."' align='left' rotation='".$i."'>".$number."</text>";
			$number += ($end_number-$start_number)/($ticks_count-1);
		
		}
	}
	//====================================
	?>
</gauge>
