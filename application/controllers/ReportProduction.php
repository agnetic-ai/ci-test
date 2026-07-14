<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ReportProduction extends CI_Controller {

    public function __construct(){
		parent::__construct();
	}
	public function index()
	{
		exit;
	}

    //* Report Produksi Aum & Produksi Premi
    public function report_daily()
    {
        $date=$this->input->post('date');

        $data = array(
            [
                'branch_name' => 'CABANG BANDUNG',
                'cpp_idr_production' => 6400000,
                'cpp_usd_production' => 0,
                'total_production' => 4900000,
                'cpp_idr_jatem' => 75000,
                'cpp_usd_jatem' => 0,
                'total_jatem' => 75000,
                'refferal'  => [
                    [
                        'name' => 'Afiana',
                        'idr'  => 4310000,
                        'usd'  => 0,
                        'total'  => 4310000
                    ],
                    [
                        'name' => 'Darwin',
                        'idr'  => 5210000,
                        'usd'  => 20,
                        'total'  => 5210020
                    ]
                ]
            ],
            [
                'branch_name' => 'CABANG SOLO',
                'cpp_idr_production' => 6720000,
                'cpp_usd_production' => 31,
                'total_production' => 4900031,
                'cpp_idr_jatem' => 65000,
                'cpp_usd_jatem' => 0,
                'total_jatem' => 65000,
                'refferal'  => [
                    [
                        'name' => 'Afiana',
                        'idr'  => 4310000,
                        'usd'  => 0,
                        'total'  => 4310000
                    ],
                    [
                        'name' => 'Darwin',
                        'idr'  => 5210000,
                        'usd'  => 20,
                        'total'  => 5210020
                    ]
                ]
            ],
            [
                'branch_name' => 'CABANG SURABAYA',
                'cpp_idr_production' => 2900000,
                'cpp_usd_production' => 0,
                'total_production' => 4900000,
                'cpp_idr_jatem' => 90000,
                'cpp_usd_jatem' => 0,
                'total_jatem' => 90000,
                'refferal'  => [
                    [
                        'name' => 'Afiana',
                        'idr'  => 4310000,
                        'usd'  => 0,
                        'total'  => 4310000
                    ],
                    [
                        'name' => 'Darwin',
                        'idr'  => 5210000,
                        'usd'  => 20,
                        'total'  => 5210020
                    ]
                ]
            ],
            [
                'branch_name' => 'CAPEM ASEMKA',
                'cpp_idr_production' => 7200000,
                'cpp_usd_production' => 0,
                'total_production' => 4900000,
                'cpp_idr_jatem' => 35000,
                'cpp_usd_jatem' => 123,
                'total_jatem' => 35123,
                'refferal'  => [
                    [
                        'name' => 'Afiana',
                        'idr'  => 4310000,
                        'usd'  => 0,
                        'total'  => 4310000
                    ],
                    [
                        'name' => 'Darwin',
                        'idr'  => 5210000,
                        'usd'  => 20,
                        'total'  => 5210020
                    ]
                ]
            ],
            [
                'branch_name' => 'CAPEM GLODOK',
                'cpp_idr_production' => 4900000,
                'cpp_usd_production' => 0,
                'total_production' => 4900000,
                'cpp_idr_jatem' => 85000,
                'cpp_usd_jatem' => 0,
                'total_jatem' => 85000,
                'refferal'  => [
                    [
                        'name' => 'Afiana',
                        'idr'  => 4310000,
                        'usd'  => 0,
                        'total'  => 4310000
                    ],
                    [
                        'name' => 'Darwin',
                        'idr'  => 5210000,
                        'usd'  => 20,
                        'total'  => 5210020
                    ]
                ]
            ],
            [
                'branch_name' => 'CAPEM GREENVILE',
                'cpp_idr_production' => 4900000,
                'cpp_usd_production' => 0,
                'total_production' => 4900000,
                'cpp_idr_jatem' => 85000,
                'cpp_usd_jatem' => 0,
                'total_jatem' => 85000,
                'refferal'  => [
                    [
                        'name' => 'Afiana',
                        'idr'  => 4310000,
                        'usd'  => 0,
                        'total'  => 4310000
                    ],
                    [
                        'name' => 'Darwin',
                        'idr'  => 5210000,
                        'usd'  => 20,
                        'total'  => 5210020
                    ]
                ]
            ],
            [
                'branch_name' => 'CAPEM HARCO MANGGA DUA',
                'cpp_idr_production' => 4900000,
                'cpp_usd_production' => 0,
                'total_production' => 4900000,
                'cpp_idr_jatem' => 85000,
                'cpp_usd_jatem' => 0,
                'total_jatem' => 85000,
                'refferal'  => [
                    [
                        'name' => 'Afiana',
                        'idr'  => 4310000,
                        'usd'  => 0,
                        'total'  => 4310000
                    ],
                    [
                        'name' => 'Darwin',
                        'idr'  => 5210000,
                        'usd'  => 20,
                        'total'  => 5210020
                    ]
                ]
            ],
            [
                'branch_name' => 'CAPEM WURUK',
                'cpp_idr_production' => 4900000,
                'cpp_usd_production' => 0,
                'total_production' => 4900000,
                'cpp_idr_jatem' => 85000,
                'cpp_usd_jatem' => 0,
                'total_jatem' => 85000,
                'refferal'  => [
                    [
                        'name' => 'Afiana',
                        'idr'  => 4310000,
                        'usd'  => 0,
                        'total'  => 4310000
                    ],
                    [
                        'name' => 'Darwin',
                        'idr'  => 5210000,
                        'usd'  => 20,
                        'total'  => 5210020
                    ]
                ]
            ],
            [
                'branch_name' => 'CAPEM JATINEGARA',
                'cpp_idr_production' => 7300000,
                'cpp_usd_production' => 67,
                'total_production' => 4000670,
                'cpp_idr_jatem' => 85000,
                'cpp_usd_jatem' => 0,
                'total_jatem' => 85000,
                'refferal'  => [
                    [
                        'name' => 'Afiana',
                        'idr'  => 4310000,
                        'usd'  => 0,
                        'total'  => 4310000
                    ],
                    [
                        'name' => 'Darwin',
                        'idr'  => 5210000,
                        'usd'  => 20,
                        'total'  => 5210020
                    ]
                ]
            ],
            [
                'branch_name' => 'CAPEM KELAPA GADING',
                'cpp_idr_production' => 1400000,
                'cpp_usd_production' => 0,
                'total_production' => 4900000,
                'cpp_idr_jatem' => 85000,
                'cpp_usd_jatem' => 0,
                'total_jatem' => 85000,
                'refferal'  => [
                    [
                        'name' => 'Afiana',
                        'idr'  => 4310000,
                        'usd'  => 0,
                        'total'  => 4310000
                    ],
                    [
                        'name' => 'Darwin',
                        'idr'  => 5210000,
                        'usd'  => 20,
                        'total'  => 5210020
                    ]
                ]
            ],
        );

            echo json_encode(array(
            'error'     => 0,
            'message'   =>'success',
            'data' 		=> $data
        ));
        exit;
    }
    //* Report Produksi Premi

    //* Report Produksi Premi
    public function report_commit_premium(){
        $date=$this->input->post('date');

        $data = array(
            [
                'cluster' => 'BBC 1',
                'target' => 47,
                'daily' => 46.89,
                'accumulation' => 666.28,
            ],
            [
                'cluster' => 'BBC 2',
                'target' => 53,
                'daily' => 45.24,
                'accumulation' => 434.48,
            ],
            [
                'cluster' => 'AREA BUSSINESS FUNDING MANAGER',
                'target' => 47,
                'daily' => 45.39,
                'accumulation' => 112.36,
            ],
            [
                'cluster' => 'AREA CORPORATE FUNDING MANAGER',
                'target' => 47,
                'daily' => 1.84,
                'accumulation' => 55.05,
            ]
        );

            echo json_encode(array(
            'error'     => 0,
            'message'   =>'success',
            'data' 		=> $data
        ));
        exit;
    }

    public function report_production_premium(){
        $date=$this->input->post('date');

        $data = array(
            [
                'cluster' => 'BBC 1',
                'branch_name' => 'CAPEM PLUIT KENCANA',
                'nb' => 9179000000,
                'renewal' => 0,
                'total' => 9179000000,
            ],
            [
                'cluster' => 'BBC 1',
                'branch_name' => 'KTR KAS PIK',
                'nb' => 3362000000,
                'renewal' => 0,
                'total' => 3362000000,
            ],
            [
                'cluster' => 'BBC 2',
                'branch_name' => 'CAPEM MANGGA DUA',
                'nb' => 1890000000,
                'renewal' => 0,
                'total' => 1890000000,
            ],
            [
                'cluster' => 'AREA BUSSINESS FUNDING MANAGER',
                'branch_name' => 'AREA BUSSINESS FUNDING MANAGER',
                'nb' => 45394180000,
                'renewal' => 0,
                'total' => 45394180000,
            ],
            [
                'cluster' => 'AREA CORPORATE FUNDING MANAGER',
                'branch_name' => 'AREA CORPORATE FUNDING MANAGER',
                'nb' => 1844000000,
                'renewal' =>0,
                'total' => 1844000000,
            ]
        );

            echo json_encode(array(
            'error'     => 0,
            'message'   =>'success',
            'data' 		=> $data
        ));
        exit;
    }
    //* Report Produksi Premi

}