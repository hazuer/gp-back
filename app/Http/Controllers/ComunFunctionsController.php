<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ComunFunctionsController extends Controller
{

    public function generatePassword()
    {
        ///get string
        $uppers = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $lowers = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = "0123456789";
        $specialChars = '!#$%&/()=?@';
        //get string length
        $numberCount = mb_strlen($numbers);
        $upperCount = mb_strlen($uppers);
        $specialCharsCount = mb_strlen($specialChars);
        //get one random string char
        $upper  =  mb_substr($uppers, Rand(0, $upperCount - 1), 1);
        $number =  mb_substr($numbers, Rand(0, $numberCount - 1), 1);
        $special = mb_substr($specialChars, Rand(0, $specialCharsCount - 1), 1);
        $count  =  mb_strlen($lowers);
        //get one random string four
        for ($i = 0, $result = ''; $i < 5; $i++) {
            $result .= mb_substr($lowers, Rand(0, $count - 1), 1);
        }
        //random positions
        $password = str_shuffle($upper . $result . $number . $special);
        //retunr password
        return $password;
    }
}
