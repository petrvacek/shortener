<?php
//namespace VACEK;


class Str2Int{
    const BASE_ALNUM = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    const BASE_NUM = "0123456789";
    const BASE_BIN = "01";
    const BASE_HEX = "0123456789ABCDEF";
    const BASE_OCT = "01234567";
    const BASE_URLSAFE = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.~";
    
    
    /**
     * Shuffle base string order by vector
     * @param string $base
     * @param string|integer $vector
     * @return string
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
    
    public static function int2stringBC($integer,$charbase){
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

    public static function string2intBC($string,$charbase){
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
    
    public static function int2stringSTD($integer,$charbase){
        $base = $charbase;
	$length = strlen($base);
	while($integer > $length - 1)
	{
		$out = $base[fmod($integer, $length)] . $out;
		$integer = floor( $integer / $length );
	}
	return $base[$integer] . @$out;
    }
    
    public static function string2intSTD($string,$charbase)
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

}




## simple test


function p($p){
    echo "<pre>".print_r($p,true)."</pre>";
}


$base = Str2Int::ShuffleBase(Str2Int::BASE_URLSAFE, "some string / nuber / constant");


for($i=1;$i<200;$i+=10){
    $num = bcpow("10", (string) $i);
    
    $coded = Str2Int::int2stringBC($num, $base);
    p(array("original"=>$num,"encoded"=>$coded, "decoded"=>Str2Int::string2intBC($coded, $base),"charbase"=>$base));
    echo "<hr>";
}