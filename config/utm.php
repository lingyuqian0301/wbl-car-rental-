<?php

/**
 * UTM Reference Data
 * 
 * Contains faculties, programs, colleges, and Malaysian states
 * for dropdown menus in the application.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Faculties and Programs
    |--------------------------------------------------------------------------
    |
    | Faculty codes mapped to their programs
    |
    */

    'faculties' => [
        'AI' => [
            'name' => 'Artificial Intelligence',
            'programs' => ['SECZ'],
        ],
        'FC' => [
            'name' => 'Computing',
            'programs' => ['SECJ', 'SECP', 'SECR', 'SECV', 'SECB'],
        ],
        'FE' => [
            'name' => 'Engineering',
            'programs' => ['SKAA', 'SKMM', 'SKMB', 'SKMI', 'SKMA', 'SKMV', 'SKEE', 'SKEL', 'SKEM', 'SKKK', 'SKKP', 'SKKB', 'SKKG', 'SKBB'],
        ],
        'FS' => [
            'name' => 'Science',
            'programs' => ['SSCZ', 'SSCF', 'SSCA', 'SSCC', 'SSCE', 'SSCM', 'SSCG', 'SSCB'],
        ],
        'FBES' => [
            'name' => 'Built Environment & Surveying',
            'programs' => ['SBEA', 'SBEQ', 'SBEL', 'SBEP', 'SBEC', 'SBEG', 'SBEH', 'SBET'],
        ],
        'FSSH' => [
            'name' => 'Social Sciences & Humanities',
            'programs' => ['SHAR', 'SHAY', 'SPPB', 'SPPS', 'SPPR', 'SPPM', 'SPPQ'],
        ],
        'FM' => [
            'name' => 'Management',
            'programs' => ['SHAD', 'SHAF', 'SHAC'],
        ],
        'MJIIT' => [
            'name' => 'Malaysia-Japan International Institute of Technology',
            'programs' => ['SMJE', 'SMJM', 'SMJC'],
        ],
        'AHIBS' => [
            'name' => 'Azman Hashim International Business School',
            'programs' => ['SBSG'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Colleges
    |--------------------------------------------------------------------------
    |
    | College codes and their full names
    |
    */

    'colleges' => [
        'KRP' => 'Kolej Rahman Putra',
        'KTF' => 'Kolej Tun Fatimah',
        'KTR' => 'Kolej Tun Razak',
        'KTHO' => 'Kolej Tun Hussein Onn',
        'KTDI' => 'Kolej Tun Dr. Ismail',
        'KTC' => 'Kolej Tuanku Canselor',
        'KP' => 'Kolej Perdana',
        'K9' => 'Kolej 9',
        'K10' => 'Kolej 10',
        'KDSE' => 'Kolej Dato\' Seri Endon',
        'KDOJ' => 'Kolej Dato\' Onn Jaafar',
        'KSJ' => 'Kolej Sri Jelai',
    ],

    /*
    |--------------------------------------------------------------------------
    | Malaysian States
    |--------------------------------------------------------------------------
    |
    | All states and federal territories in Malaysia
    |
    */

    'states' => [
        'Johor',
        'Kedah',
        'Kelantan',
        'Melaka',
        'Negeri Sembilan',
        'Pahang',
        'Penang',
        'Perak',
        'Perlis',
        'Sabah',
        'Sarawak',
        'Selangor',
        'Terengganu',
        'Kuala Lumpur',
        'Putrajaya',
        'Labuan',
    ],

];

