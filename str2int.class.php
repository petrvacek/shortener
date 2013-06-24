<?php
//namespace VACEK;

/**
 * 
 * @author      Petr Vacek <vacek.eu@gmail.com>
 * @since       2013-06-21
 * 
 * Třída pro převod čísla do jiného prostoru a zpět
 * lze definovat bázi do které a z které probíhá převod
 * vhodné jako zkracovač velkých čísel
 * 
 * @package Vacek
 */

class Str2Int{
    
    const DOUBLE_LIMIT = 9007199254740992;
    const DOUBLE_LIMIT_REVERSE = 13537086546263552;
    
    const BASE_ALNUM = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    const BASE_NUM = "0123456789";
    const BASE_BIN = "01";
    const BASE_HEX = "0123456789ABCDEF";
    const BASE_OCT = "01234567";
    const BASE_URLSAFE = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.~";
    
    
    /**
     * Zamíchat s pořadím převodní báze
     * 
     * Here is  example:
     * <code>
     * <?
     * $base = Str2Int::ShuffleBase(Str2Int::BASE_ALNUM);
     * ?>
     * or
     * <?
     * $base = Str2Int::ShuffleBase(Str2Int::BASE_ALNUM,"some string");
     * ?>
     * or
     * <?
     * $base = Str2Int::ShuffleBase(Str2Int::BASE_ALNUM,12345625); // some int
     * ?>
     * </code>
     *
     * @param string $base Znaky báze
     * @param string|integer $vector nastavuje míchačku znaků
     * @return string zamíchaná báze 
     */
    public static function ShuffleBase($base,$vector = null){
        $vector = (string) $vector;
        if(is_null($vector)){
            $vector = __CLASS__;
        }
        
        $crc = crc32($vector);
        if($crc < 0 ) { // if hash is negative
            $crc*=-1;
        }
        if($crc == 0 ) $crc = 1;
        
        $abase = str_split($base);
        $nbase = array();
        $max = count($abase);
        
        // shuffle
        $last = $crc;
        $a=( $max / $crc);
        foreach($abase as $i=>$v){
            $last=fmod(pow( pow($i+1,2) +$last,2),$a )*$crc; 
            $nbase[$v]=$last;
        }
        asort($nbase);
        
        return implode("", array_keys($nbase));   
    }
    
    /**
     * převod čísla do jiné báze (do všech alfanumerických znaků)
     * používá knihovnu BCMath, je vhodná pro převod větších čísel
     * reverzní funkce k reverzní funkce k {@link Str2Int::string2intBC } 
     * 
     * @see BCMath 
     * @param string|int $integer
     * @param string $charbase báze (prostor) kam se převede zadané číslo
     * @return string
     */
    private static function int2stringBC($integer,$charbase = self::BASE_ALNUM){
        $int = (string) $integer;
        $base = $charbase;
	$length = strlen($base);        
        while(bccomp($int , (string) ($length - 1))>0)
	{
		$out = $base[bcmod($int, (string)$length)] . $out;
                $int =  bcdiv($int, (string) $length,0) ;
	}
        return $base[$int] . @$out;
    }

    /**
     * převod stringu na číslo
     * vhodná pro velké čísla / řetězce
     * reverzní funkce k {@link Str2Int::int2stringBC } 
     * 
     * @see BCMath 
     * @param string $string
     * @param string $charbase báze (prostor) ze kterého se převede zadaný string
     * @return string number in string
     */
    private static function string2intBC($string,$charbase = self::BASE_ALNUM){
        $base = $charbase;
        $length = strlen($base);
        $size = strlen($string) - 1;
        $string = str_split($string);
        $out = strpos($base, array_pop($string));
        foreach($string as $i => $char)
        {
                $out=  bcadd($out, bcmul(strpos($base, $char) , bcpow((string) $length, strval($size-$i),0),0),0);
        }
        return $out;
    }    
    
    private static function int2stringSTD($integer,$charbase = self::BASE_ALNUM){
        $base = $charbase;
	$length = strlen($base);
	while($integer > $length - 1)
	{
		$out = $base[fmod($integer, $length)] . $out;
		$integer = floor( $integer / $length );
	}
	return $base[$integer] . @$out;
    }
    
    private static function string2intSTD($string,$charbase = self::BASE_ALNUM)
    {
        $base = $charbase;
        $length = strlen($base);
        $size = strlen($string) - 1;
        $string = str_split($string);
        $out = strpos($base, array_pop($string));
        foreach($string as $i => $char)
        {
                $out += strpos($base, $char) * pow($length, $size - $i);
        }
        return $out;
    }    
    
    /**
     * převod čísla do jiného prostoru s automatickým rozdělením podle velikosti
     * @param type $integer
     * @param type $charbase
     * @return type
     */
    public static function int2string($integer,$charbase = self::BASE_ALNUM){
        if($integer > self::DOUBLE_LIMIT){
            return self::int2stringBC($integer, $charbase);
        }else{
            return self::int2stringSTD($integer, $charbase);
        }
    }
    /**
     * převod z prostoru definovaným vectoru do desítkové soustavy s automatickým rozdělením podle velikosti
     * @param type $string
     * @param type $charbase
     * @return type
     */
    public static function string2int($string,$charbase = self::BASE_ALNUM){
        $base = $charbase;
        $length = strlen($base);
        $size = strlen($string) - 1;
        $_string = str_split($string);
        $out = strpos($base, array_pop($_string));

        if($length>0){
            //$out = bcmul($length ,bcpow((string) $length, strval($size),0) ,0);
            $out = bcpow((string) $length, strval($size+1),0);
        }
        if($out > self::DOUBLE_LIMIT_REVERSE){
            return self::string2intBC($string, $charbase);
        }else{
            return self::string2intSTD($string, $charbase);
        }
    }
}

