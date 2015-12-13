<?php
namespace ageeweb\php_onion;
/**
 * @package php_onion
 *
 * @copyright  Copyright (C) 2015 AGeeWeb.nl - All rights reserved.
 * @license    MIT
 */

/**
 * class for use with Onion Omega 
 * sets GPIO direction / on / off 
 * sets relay on / off
 * sets onboard dock LED
 * how to use see HowToUse.php
 */
 
/*
 * setting up php: (in command line mode)
 *
 * >opkg update
 * >opkg install php5 php5-cgi zoneinfo-europe nano
 * nano=simpel editor for on the command line, but you can use the editor from Onion Console or vi
 * zoneinfo-europe=timezone package
 *
 * php.ini set timezone:
 * [Date]
 *   date.timezone = "Europe/Amsterdam" (or something else, use timezone package for your area)
 *
 *
 * add line
 * list interpreter ".php=/usr/bin/php-cgi"
 * to: /etc/config/uhhtpd
 *
 * and type in the command:
 * /etc/init.d/uhttpd restart
 * to restart the http demon
 *
 * place this file into:
 * /www/ or a subdirectory of it
 * and use it in your php programs
 */

//see also example from Rinus van Weert:
// https://community.onion.io/topic/39/simple-php-web-gpio-example-switching-leds

class OmegaOnion
{
    //format:
    //     %u = unsigned decimal number
    //     %b = integer binary
    //     %s = string
    //     %d = (signed) decimal number
    const IO_LOGFILE = 'IO.LOG';
    const FASTGPIO = 'fast-gpio';
                                 //fast-gpio set-{in/out}put <gpio>
    const FASTGPIO_SETDIRECTION = 'fast-gpio set-%sput %u';
                                 //fast-gpio get-direction <gpio>
    const FASTGPIO_GETDIRECTION = 'fast-gpio get-direction %u';
                           //fast-gpio set <gpio> <value: 0 or 1>
    const FASTGPIO_SETPIN = 'fast-gpio set %u %b';
                            //fast-gpio read <gpio>
    const FASTGPIO_READPIN = 'fast-gpio read %u';
                           //fast-gpio pwm <gpio> <freq in Hz> <duty cycle percentage>
    const FASTGPIO_PWMPIN = 'fast-gpio pwm %u %u %u';
				//need RGB color code  (output more than 1 row gives problems?)
    const EXP_LED = 'expled %s>/dev/null';
                    //relay-exp -s <dipswitch=000> -i;
    const EXP_RELAY_INIT = 'relay-exp -s %s -i';
                    //relay-exp -s <dipswitch=000> <channel:0 or 1 or all> <value:0 or 1>
    const EXP_RELAY_SET = 'relay-exp -s %s %s %u';

    protected $logFileName;
    protected $log;

    public static function now() 
	{
	
        return date("Y-m-d h:m:s");
	
	}

	public static function nowInt() 
	{
    
		return strtotime(self::now());
		
    }


    function __construct( $fileName = self::IO_LOGFILE )
    {

        //write output to log
		if ($fileName === FALSE ) {
			$this->logFileName = FALSE;
		} else {
			$this->logFileName = $fileName;
			$this->log = fopen($this->logFileName,"a");
			fwrite( $this->log, self::now() . "\n" );
		}

    }

    function __destruct()
    {

        //close logfile
		if ( ( $this->logFileName != FALSE ) ) {
			fclose( $this->log );
		}

    }

    public function writeLog( $var )
	{
		if ( ( $this->logFileName != FALSE ) and isset($var) ) {
			if ( is_array($var) ) {
			  foreach ( $var as $line ) {
				fwrite( $this->log, $line . "\n");
			  }
			} else {
				fwrite( $this->log, $var . "\n");
			}
		}
	}
    
    function execCommand( $command )
	{
	  $this->writeLog( $command );
	  $result = exec( $command );
	  $this->writeLog( $result );
	  return $result;
	}
    
   //define functions

    /* setGpioDirection
     * use setGpioDirection( GPIO-pin, direction )
     * direction:string
     *  'in'
     *  'out'
     */

    // we will use the onion gpio function to control GPIO pins
    public function setGpioDirection( $GPIO, $direction )
    {
        if ( $direction == 'in' or $direction == 'out') {
            $command = sprintf( self::FASTGPIO_SETDIRECTION, $direction, $GPIO);
            $this->execCommand( $command );
        }

    }

    /* getGpioDirection
     * use getGpioDirection( GPIO-pin )
     * returns:string
     *  'in'
     *  'out'
     */

    // we will use the onion gpio function to control GPIO pins
    public function getGpioDirection( $GPIO )
    {

        $command = sprintf( self::FASTGPIO_READPIN, $GPIO);
        $readOutput = $this->execCommand( $command );
        $result = ( strpos( $readOutput, 'input') === FALSE ? 'out' : 'in' );
        return $result;

    }

    /* readGpio
     * use readGpio( GPIO-pin )
     * returns:int
     *  1: on
     *  0: off
     */

    // we will use the onion gpio function to control GPIO pins
    public function readGpio( $GPIO )
    {

        $command = sprintf( self::FASTGPIO_READPIN, $GPIO);
        $readOutput = $this->execCommand( $command );
		if ( isset( $readOutput )) {
			$result = ( ( substr( rtrim( $readOutput ), -1 ) === '1' ) === FALSE ? 0 : 1 );
		}
        return $result;

    }

    /* writeGpio
     * use writeGpio( GPIO-pin, newValue )
     * newValue:int
     *  1: on
     *  0: off
     */

    // we will use the onion gpio function to control GPIO pins
    public function writeGpio( $GPIO, $newValue )
    {
	
		$result = 0;
		
        if ( $newValue < 2) {
            $command = sprintf( self::FASTGPIO_SETPIN, $GPIO, ($newValue === 1 ? 1 : 0 ) );
            $readOutput = $this->execCommand( $command );
            return $result;
        }

    }
    
    /* pwmGpio
     * use pwmGpio( GPIO-pin, time, percentage  )
     * time:int = interval time
     * percentage:int = percentage on 
     */

    // we will use the onion gpio function to control GPIO pins
    public function pwmGpio( $GPIO, $time, $perc )
    {
	
		$result = 0;
		
        if ( $newValue < 2) {
            $command = sprintf( self::FASTGPIO_PWMPIN, $GPIO, $time, $perc );
            $readOutput = $this->execCommand( $command );
        }

    }

    /* wait
     * use wait( milliSec )
     */

	public function wait( $milliSec ) 
	{
		$this->writeLog( "wait for $milliSec" );
		if ( $milliSec > 999 ) {
			sleep( $milliSec / 1000 );
		} else {
			usleep( $milliSec * 1000 );
		}
	}

    /* setRGBled
     * use setRGBled( [value] )
     * value = hex RGB color 
	 * or string "off" to switch Led complete off (default)
     */

    // we will use the onion gpio function to control GPIO pins
	public function setRGBled( $value = "off" ) 
	{
        if ( $value == "off" ) {
			$this->writeLog( 'setoff LED on dock' );
			//write 1="on" to 15/16/17 sets off the rgb led
			$this->writeGpio( 15, 1);
			$this->writeGpio( 16, 1);
			$this->writeGpio( 17, 1);
        } else {
            $command = sprintf( self::EXP_LED, $value);
			$this->writeLog( 'set LED on dock' );
            $this->execCommand( $command );
		}
    }

	
    /* initRelay
     * use initRelay( [dipSwitch] )
     * dipSwitch = if you have more than 1 relay, you can insert the dipSwitch values
	 * default = 000
     */

    // we will use the onion relay_exp function to control relay
    function initRelay( $dipSwitch = "000" ){ // init Relay expansion

		$command = sprintf( self::EXP_RELAY_INIT, $dipSwitch);
		return $this->execCommand( $command );
		
    }
	
    /* writeRelay
     * use writeRelay( [channel], [newValue], [dipSwitch] )
	 * channel:int
	 *  0 & 1: channel
	 *  2: for both (default)
	 * newValue:int
     *  1: on
     *  0: off (default)
     * dipSwitch:string
     *  if you have more than 1 relay, you can insert the dipSwitch values (example:010 or 100)
	 * default = 000
     */

    // we will use the onion relay_exp function to control relay
    function writeRelay( $channel = 2, $newValue = 0, $dipSwitch = "000" ){
	
		$command = sprintf( self::EXP_RELAY_SET, $dipSwitch, ($channel === 2 ? "all" : $channel ), ($newValue === 1 ? 1 : 0 ) );
		return $this->execCommand( $command );
		
    }

}
