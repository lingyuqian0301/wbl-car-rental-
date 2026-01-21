<?php

namespace App\Helpers;

use Carbon\Carbon;

class MalaysianICHelper
{
    /**
     * Parse Malaysian IC number and extract Date of Birth
     * 
     * IC Format: YYMMDD-PB-###G
     * Example: 990315-01-1234 → 15 March 1999
     * 
     * @param string $icNumber
     * @return Carbon|null
     */
    public static function extractDOB(string $icNumber): ?Carbon
    {
        // Remove any spaces or dashes
        $ic = preg_replace('/[\s\-]/', '', $icNumber);
        
        // Validate format: must be 12 digits
        if (!preg_match('/^\d{12}$/', $ic)) {
            return null;
        }
        
        // Extract YYMMDD (first 6 digits)
        $yy = substr($ic, 0, 2);
        $mm = substr($ic, 2, 2);
        $dd = substr($ic, 4, 2);
        
        // Determine century: if YY > current year's YY, assume 1900s, else 2000s
        $currentYear = (int) date('y'); // Get last 2 digits of current year
        $year = ((int) $yy > $currentYear) ? (1900 + (int) $yy) : (2000 + (int) $yy);
        
        // Validate date
        if (!checkdate((int) $mm, (int) $dd, $year)) {
            return null;
        }
        
        try {
            return Carbon::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $mm, $dd));
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Calculate age from DOB
     * 
     * @param Carbon $dob
     * @return int
     */
    public static function calculateAge(Carbon $dob): int
    {
        return $dob->age;
    }
    
    /**
     * Extract DOB and calculate age from IC number
     * 
     * @param string $icNumber
     * @return array ['dob' => Carbon|null, 'age' => int|null]
     */
    public static function extractDOBAndAge(string $icNumber): array
    {
        $dob = self::extractDOB($icNumber);
        
        if (!$dob) {
            return ['dob' => null, 'age' => null];
        }
        
        return [
            'dob' => $dob,
            'age' => self::calculateAge($dob)
        ];
    }
    
    /**
     * Validate Malaysian IC number format
     * 
     * @param string $icNumber
     * @return bool
     */
    public static function validate(string $icNumber): bool
    {
        // Remove spaces and dashes
        $ic = preg_replace('/[\s\-]/', '', $icNumber);
        
        // Must be 12 digits
        if (!preg_match('/^\d{12}$/', $ic)) {
            return false;
        }
        
        // Validate DOB portion
        $dob = self::extractDOB($icNumber);
        return $dob !== null;
    }
    
    /**
     * Format IC number with dashes
     * 
     * @param string $icNumber
     * @return string
     */
    public static function format(string $icNumber): string
    {
        $ic = preg_replace('/[\s\-]/', '', $icNumber);
        
        if (strlen($ic) === 12) {
            return substr($ic, 0, 6) . '-' . substr($ic, 6, 2) . '-' . substr($ic, 8, 4);
        }
        
        return $icNumber;
    }
}
