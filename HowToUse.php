<?PHP 

require_once 'OmegaOnion.php'; //when this and OmegaOnion.php in same directory, or use autoloader

$base = basename($_SERVER['PHP_SELF']);

$onion = new ageeweb\php_onion\OmegaOnion( FALSE ); //FALSE is no logging


function init($onion)
{

  //set off
  $onion->setRGBled( );
	
}


//init start
if( count($_REQUEST) == 0 ) { // first call, no request
	init($onion);
} else {
	if ( isset($_REQUEST[value]) and isset($_REQUEST[gpio])) {
	
			$gpio = $_REQUEST[gpio];
			$value = $_REQUEST[value];
			
			if (( $value != "2" ) and ( $value != "4" )) {
				$onion->setGpioDirection( $gpio, 'out' );
			}
			
			if ($value == "0" or ($value == "1")) {
			  $onion->writeGpio( $gpio, ($value == "1"?1:0) );
			} else {
			  if ($value == "2" ) { //get value
				$now = $onion->readGpio( $gpio );
			  }
			  if ($value == "3" ) { //blink
				$onion->pwmGpio( $gpio, 2, 50 );
			  }
			  if ($value == "4" ) { //get direction
				$dir = $onion->getGpioDirection( $gpio );
			  }
 			  if ($value == "5" ) { //set LED
				//set RGB led to some Yellow
				$onion->setRGBled( "5F4F40" );
				//wait
				$onion->wait( 200 );
				$onion->setRGBled( );
				$onion->wait( 400 );
				$onion->setRGBled( "5F4F40" );
				$onion->wait( 1400 );
				//set off
				$onion->setRGBled( );
			  }
 			  if ($value == "6" ) { //init relay and set off
				$onion->initRelay(  );
			  }
 			  if ($value == "7" ) { //set relay on
				$onion->writeRelay( $gpio, 1 );
			  }
 			  if ($value == "8" ) { //set relay off
				$onion->writeRelay( $gpio, 0 );
			  }
 			  if ($value == "9" ) { //set relay both on
				$onion->writeRelay( 2, 1 );
			  }

			}
	}
}


?>
<HTML> 
	<HEAD> 
		<META name="viewport" content="width=device-width, initial-scale=1.0">
		<TITLE>Switch Leds</TITLE> 
		<STYLE>
			body {
				background-color: #F2F2F2;
				width: 400px;
			}
		</STYLE>
	</HEAD> 
	<BODY> 
	
	    <?php if ( $gpio == "" ) { $gpio = 1; } ?>
		
        <a href="<?php echo "$base?gpio=$gpio&value=1" ?>"><button>ON</button></a>
        <a href="<?php echo "$base?gpio=$gpio&value=0" ?>"><button>OFF</button></a>
        <a href="<?php echo "$base?gpio=$gpio&value=3" ?>"><button>BLINK</button></a>
        <a href="<?php echo "$base?gpio=$gpio&value=2" ?>"><button>READ</button></a>
        <a href="<?php echo "$base?gpio=$gpio&value=4" ?>"><button>DIRECTION</button></a>
        <a href="<?php echo "$base?gpio=$gpio&value=5" ?>"><button>RGB LED</button></a>
		<br>
		<br>
        <a href="<?php echo "$base?gpio=$gpio&value=6" ?>"><button>INIT RELAY</button></a>
        <a href="<?php echo "$base?gpio=1&value=9" ?>"><button>SET BOTH ON RELAY</button></a>
		<br>
        <a href="<?php echo "$base?gpio=0&value=7" ?>"><button>SET ON RELAY 0</button></a>
        <a href="<?php echo "$base?gpio=0&value=8" ?>"><button>SET OFF RELAY 0</button></a>
		<br>
        <a href="<?php echo "$base?gpio=1&value=7" ?>"><button>SET ON RELAY 1</button></a>
        <a href="<?php echo "$base?gpio=1&value=8" ?>"><button>SET OFF RELAY 1</button></a>
		
		<?php if ( isset( $now ) ) {
		  echo "<p>value is now $now</p>";
		}?>
		<?php if ( isset( $dir ) ) {
		  echo "<p>direction is now $dir</p>";
		}?>
	</BODY>
</HTML>
