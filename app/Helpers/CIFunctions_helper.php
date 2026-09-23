<?php
use App\Libraries\AuthenticationServices;
use App\Models\SettingsModel;
use App\Controllers\Api\AuthController;
if (!function_exists('get_user')) {
    function get_user()
    {
        if (AuthenticationServices::check()) {
            $auth = new AuthController();
            return $auth->profile();
        } else {
            return null;
        }

    }
}

if (!function_exists('get_settings')) {
    function get_settings()
    {
        $settings = new SettingsModel();
        $settings_data = $settings->asObject()->first();

        if (!$settings_data) {
            //Create default data settings
            $data = array(
                'schoolname' => 'Schoolaname',
                'email' => 'info@email.test',
                'phone' => null,
                'logo' => null,
                'favicon' => null
            );
            $settings->save($data);
            $new_settings_data = $settings->asObject()->first();
            return $new_settings_data;
        } else {
            return $settings_data;
        }
    }
}



if (!function_exists('splitFullName')) {

    function splitFullName($fullName)
    {
        $nameParts = explode(",", $fullName);
        $numParts = count($nameParts);

        if ($numParts < 2) {
            return [
                'first_name' => '',
                'middle_name' => '',
                'last_name' => $fullName
            ];
        }


        // Assuming middle name is the last part
        //$middleName = array_pop($nameParts);

        $name_middle = array_pop($nameParts);
        $nameMiddleParts = explode(" ", $name_middle);

        $middleName = array_pop($nameMiddleParts);

        // Assuming last name is the first part
        $lastName = array_shift($nameParts);

        // firstname name is the remaining part
        //$firstName = implode(" ", $nameParts);
        $firstName = implode(" ", $nameMiddleParts);

        return [
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName
        ];
    }
}


if (!function_exists('convertNumberToWords')) {

    function convertNumberToWords($num)
    {
        $ones = [
            0 => "zero",
            1 => "one",
            2 => "two",
            3 => "three",
            4 => "four",
            5 => "five",
            6 => "six",
            7 => "seven",
            8 => "eight",
            9 => "nine",
            10 => "ten",
            11 => "eleven",
            12 => "twelve",
            13 => "thirteen",
            14 => "fourteen",
            15 => "fifteen",
            16 => "sixteen",
            17 => "seventeen",
            18 => "eighteen",
            19 => "nineteen"
        ];

        $tens = [
            2 => "twenty",
            3 => "thirty",
            4 => "forty",
            5 => "fifty",
            6 => "sixty",
            7 => "seventy",
            8 => "eighty",
            9 => "ninety"
        ];

        $hundreds = "hundred";
        $thousands = "thousand";
        $millions = "million";
        $billions = "billion";

        if ($num < 20) {
            return $ones[$num];
        } elseif ($num < 100) {
            return $tens[intval($num / 10)] . (($num % 10 > 0) ? "-" . $ones[$num % 10] : "");
        } elseif ($num < 1000) {
            return $ones[intval($num / 100)] . " " . $hundreds . (($num % 100 > 0) ? " and " . convertNumberToWords($num % 100) : "");
        } elseif ($num < 1000000) {
            return convertNumberToWords(intval($num / 1000)) . " " . $thousands . (($num % 1000 > 0) ? " " . convertNumberToWords($num % 1000) : "");
        } elseif ($num < 1000000000) {
            return convertNumberToWords(intval($num / 1000000)) . " " . $millions . (($num % 1000000 > 0) ? " " . convertNumberToWords($num % 1000000) : "");
        } else {
            return convertNumberToWords(intval($num / 1000000000)) . " " . $billions . (($num % 1000000000 > 0) ? " " . convertNumberToWords($num % 1000000000) : "");
        }
    }

}

if (!function_exists('numberToOrdinalWords')) {

    function numberToOrdinalWords($num)
    {
        $ones = [
            0 => "zeroth",
            1 => "first",
            2 => "second",
            3 => "third",
            4 => "fourth",
            5 => "fifth",
            6 => "sixth",
            7 => "seventh",
            8 => "eighth",
            9 => "ninth",
            10 => "tenth",
            11 => "eleventh",
            12 => "twelfth",
            13 => "thirteenth",
            14 => "fourteenth",
            15 => "fifteenth",
            16 => "sixteenth",
            17 => "seventeenth",
            18 => "eighteenth",
            19 => "nineteenth"
        ];

        $tens = [
            2 => "twentieth",
            3 => "thirtieth",
            4 => "fortieth",
            5 => "fiftieth",
            6 => "sixtieth",
            7 => "seventieth",
            8 => "eightieth",
            9 => "ninetieth"
        ];

        $prefixes = [
            2 => "twenty",
            3 => "thirty",
            4 => "forty",
            5 => "fifty",
            6 => "sixty",
            7 => "seventy",
            8 => "eighty",
            9 => "ninety"
        ];

        if ($num < 20) {
            return $ones[$num];
        } elseif ($num < 100) {
            $unit = $num % 10;
            $tensUnit = intval($num / 10);
            if ($unit === 0) {
                return $tens[$tensUnit];
            } else {
                return $prefixes[$tensUnit] . '-' . $ones[$unit];
            }
        } elseif ($num < 1000) {
            $hundredsUnit = intval($num / 100);
            $remainder = $num % 100;
            if ($remainder === 0) {
                return $ones[$hundredsUnit] . " hundredth";
            } else {
                return $ones[$hundredsUnit] . " hundred and " . numberToOrdinalWords($remainder);
            }
        } else {
            // For simplicity, we'll handle numbers up to 999,999
            $thousandsUnit = intval($num / 1000);
            $remainder = $num % 1000;
            if ($remainder === 0) {
                return numberToOrdinalWords($thousandsUnit) . " thousandth";
            } else {
                return numberToOrdinalWords($thousandsUnit) . " thousand " . numberToOrdinalWords($remainder);
            }
        }
    }



    if (!function_exists('generateTOSCode')) {
        function generateTOSCode($id)
        {
            $tos = new Tos();
            $toscode = 'TOS' . $id . $tos->where('subjectid', $id)->countAllResults();
            return $toscode;
        }
    }

    if (!function_exists('getTotalSlides')) {
        function getTotalSlides($id)
        {
            $lessoncontent = new LessonContent();
            $topiccount = $lessoncontent->where('topicid', $id)->countAllResults();
            return $topiccount;
        }
    }

    if (!function_exists('generateClassCode')) {
        function generateClassCode($sectionid, $subjectid)
        {
            $section = new Section();
            $grade = new GradeLevel();
            $subject = new Subject();
            $subData = $subject->asObject()->where('id', $subjectid)->first();
            $sectionData = $section->asObject()->where('id', $sectionid)->first();
            $gradeData = $grade->asObject()->where('id', $sectionData->grade_level_id)->first();
            $code = $gradeData->id . $sectionData->id . $subData->id . "-" . CIAuth::id() . "-" . get_settings()->schoolyear;
            return $code;
        }
    }


    if (!function_exists('generatePerformanceCode')) {
        function generatePerformanceCode($classid, $classcode)
        {
            $pt = new PerformanceTask();
            $count = $pt->asObject()->selectCount('id')->where('classid', $classid)->first();
            if ($count->id != null && $count->id > 0) {
                $code = 'PT' . $classcode . '-' . ($count->id + 1);
            } else {
                $code = 'PT' . $classcode . '-1';
            }
            return $code;
        }
    }

    if (!function_exists('generateQuizCode')) {
        function generateQuizCode($classid, $qtr, $qt)
        {
            $param = array('classid' => $classid, 'quarter' => $qtr, 'quiztype' => $qt);
            $cl = new Classes();
            $pt = new Quizzes();
            $cl_code = $cl->asObject()->where('id', $classid)->first();
            $count = $pt->asObject()->selectCount('id')->where($param)->first();
            if ($count->id != null && $count->id > 0) {
                switch ($qt) {
                    case 1:
                        # code...
                        $code = 'QZ' . '-' . $qtr . '-' . $cl_code->classcode . '-' . ($count->id + 1);
                        break;
                    case 2:
                        $code = 'PT' . '-' . $qtr . '-' . $cl_code->classcode . '-' . ($count->id + 1);
                        break;
                    case 3:
                        $code = 'QE' . '-' . $qtr . '-' . $cl_code->classcode . '-' . ($count->id + 1);
                        break;
                    default:
                        # code...
                        $code = 'UNDEFINED';
                        break;
                }
            } else {
                switch ($qt) {
                    case 1:
                        # code...
                        $code = 'QZ' . '-' . $qtr . '-' . $cl_code->classcode . '-1';
                        break;
                    case 2:
                        $code = 'PT' . '-' . $qtr . '-' . $cl_code->classcode . '-1';
                        break;
                    case 3:
                        $code = 'QE' . '-' . $qtr . '-' . $cl_code->classcode . '-1';
                        break;
                    default:
                        # code...
                        $code = 'UNDEFINED';
                        break;
                }
            }
            return $code;
        }
    }
    if (!function_exists('getOptions')) {
        function getOptions($questionid)
        {
            $options = new Options();
            $op_data = $options->asObject()->where('questionid', $questionid)->find();
            return $op_data;
        }
    }

}