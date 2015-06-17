<?php

class ChargeBee_Util
{

	static function toCamelCaseFromUnderscore($str) {
		$func = create_function('$c', 'return strtoupper($c[1]);');
		return preg_replace_callback('/_([a-z])/', $func, $str);
	}

	static function toUnderscoreFromCamelCase($str) {
		$func = create_function('$c', 'return "_" . strtolower($c[1]);');
		return preg_replace_callback('/([A-Z])/', $func, $str);
	}

	static function serialize($value, $prefix=NULL, $idx=NULL)
	{
		if($value && !is_array($value)) 
		{
			throw new Exception("only arrays is allowed as value");
		}
		$serialized = array();
		foreach ($value as $k => $v)
		{
			if (is_array($v) && !is_int($k))
			{
				$serialized = array_merge($serialized, self::serialize($v, self::toUnderscoreFromCamelCase($k)));
			} 
			else if (is_array($v) && is_int($k))
			{
				$serialized = array_merge($serialized, self::serialize($v, $prefix, $k));
			}
			else 
			{
				$usK = self::toUnderscoreFromCamelCase($k);
				$key = (!is_null($prefix)?$prefix:'').(!is_null($prefix)?'['.$usK.']':$usK).(!is_null($idx)?'['.$idx.']':'');
				$serialized[$key] = $v;
			}
		}
		return $serialized;
	}


}

?>