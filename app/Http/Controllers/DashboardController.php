<?php

namespace App\Http\Controllers;

use DateTime;
use Carbon\Carbon;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class DashboardController extends Controller
{
    public $pharmacy;
    public $locations = [
        '100' => 'Farmasi Rawat Jalan',
        // '101' => 'Farmasi Rawat Inap',
        // '133' => 'Farmasi Rawat Non-UDD',
        '166' => 'Farmasi IGD',
    ];
    public $customerTypes = [
        'all' => 'Semua',
        '1' => 'JKN / BPJS - Kemenkes', //X004^500 = BPJS - Kemenkes
        '2' => 'Personal', //X004^999 = Pribadi, X004^251 = Karyawan - PTGJ, X004^300 = Pemerintah,
        '3' => 'Asuransi', //X004^100 = Rekanan, X004^200 = Perusahaan
        // '4' => 'Karyawan dan Yayasan', //X004^250 = Karyawan - FASKES, X004^400 = Rumah Sakit, X004^201 = Yayasan,
    ];
    public $statusOrders = [
        'all' => 'Semua',
        '1' => 'Selesai',
        '2' => 'Belum Selesai',
    ];
    public $timeRespons = [
        'nonRacikan' => 1800000,
        'racikan' => 3600000,
    ];
    public $pages = [
        '7' => '7',
        '10' => '10',
        '20' => '20',
        '50' => '50',
        '100' => '100',
    ];
    public $jenisOrders = [
        'all' => 'Semua',
        '1' => 'Racikan',
        '2' => 'Non Racikan',
    ];
    public $sorts = [
        '1' => 'ASC(Created Date)',
        '2' => 'DESC(Created Date)',
    ];

    public $perPage = 7;
    public $page = 1;
    public $statusOrderId = '2';
    public $jenisOrderId = 'all';
    public $sortId = '1';
    public $locationId = '100';
    public $customerTypeId = 'X004^999';
    public $dateRangeStr;
    public $maxRetries = 5;

    //Keterangan

    // GCCustomerType
    // X004^500 BPJS - Kemenkes
    // X004^100 Rekanan
    // X004^999 Pribadi
    // X004^250 Karyawan - FASKES
    // X004^201 Yayasan
    // X004^200 Perusahaan

    // Service Unit Name
    //100 - Rawat Jalan
    //101 - Rawat Inap
    //169 - IGD


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->pharmacy = new Pharmacy();
        $this->dateRangeStr = date('d-m-Y');
    }

    public function location(Request $request)
    {
        if ($request->date !== null) {
            $this->dateRangeStr = $request->date;
        }

        if (strpos($this->dateRangeStr, 'to') !== false) {


            // Memecah string menjadi dua tanggal
            list($start_date_str, $end_date_str) = explode(' to ', $this->dateRangeStr);

            // // Substring yang akan dicari
            // $search = "12:00PM";
            // $replace = "11:59PM";

            // // Mengonversi string tanggal ke objek Carbon
            // $start_date = DateTime::createFromFormat('d-m-Y h:iA', trim($start_date_str));
            // // Memeriksa apakah substring ada di dalam string
            // if (strpos($end_date_str, $search) !== false) {
            //     $new_end_date_str = str_replace($search, $replace, $end_date_str);
            //     $end_date = DateTime::createFromFormat('d-m-Y h:iA', trim($new_end_date_str));
            // } else {
            //     $end_date = DateTime::createFromFormat('d-m-Y h:iA', trim($end_date_str));
            // }

            $start_date = DateTime::createFromFormat('d-m-Y H:i', trim($start_date_str));
            $end_date = DateTime::createFromFormat('d-m-Y H:i', trim($end_date_str));
        } else {
            // Mendapatkan tanggal hari ini
            $today = new DateTime('today');

            // Mengatur waktu start time (00:00:00.000)
            $start_date = clone $today;  // Mengkloning objek DateTime
            $start_date->setTime(0, 0, 0, 0);

            // Mengatur waktu end time (23:59:59.000)
            $end_date = clone $today;  // Mengkloning objek DateTime
            $end_date->setTime(23, 59, 0, 0);

            $start_date_str =  $start_date->format('d-m-Y H:i');
            $end_date_str = $end_date->format('d-m-Y H:i');
            $this->dateRangeStr = $start_date_str . ' to ' . $end_date_str;
        }

        $data = [];
        // $data = $this->pharmacy->getCountOrderPharmacyRajalV2(startDate: $start_date, endDate: $end_date);

        while (true) {
            try {
                DB::transaction(function () use (&$data, $start_date, $end_date) {
                    $data = $this->pharmacy->getCountOrderPharmacyRajalV2(startDate: $start_date, endDate: $end_date);
                });
                break; // keluar dari loop jika transaksi berhasil
            } catch (QueryException $e) {
                Log::error('Database transaction failed: ' . $e->getMessage());
                // Tunggu sebentar sebelum mencoba lagi
                usleep(1000000); // tidur selama 1 detik
            }
        }

        // $data = $this->pharmacy->getCountOrderPharmacyRajal($start_date, $end_date);
        return view('pharmacy.dashboards.location', [
            'tittle' => 'Dashboard by Location',
            'active' => 'login',
            'locations' => $data,
            'date' => $this->dateRangeStr,
        ]);
    }

    public function payer(Request $request)
    {
        if ($request->location !== null) {
            $this->locationId = $request->location;
        }

        if ($request->date !== null) {
            $this->dateRangeStr = $request->date;
        }

        if (strpos($this->dateRangeStr, 'to') !== false) {

            // Memecah string menjadi dua tanggal
            list($start_date_str, $end_date_str) = explode(' to ', $this->dateRangeStr);

            $start_date = DateTime::createFromFormat('d-m-Y H:i', trim($start_date_str));
            $end_date = DateTime::createFromFormat('d-m-Y H:i', trim($end_date_str));
        } else {
            // Mendapatkan tanggal hari ini
            $today = new DateTime('today');

            // Mengatur waktu start time (00:00:00.000)
            $start_date = clone $today;  // Mengkloning objek DateTime
            $start_date->setTime(0, 0, 0, 0);

            // Mengatur waktu end time (23:59:59.000)
            $end_date = clone $today;  // Mengkloning objek DateTime
            $end_date->setTime(23, 59, 0, 0);

            $start_date_str =  $start_date->format('d-m-Y H:i');
            $end_date_str = $end_date->format('d-m-Y H:i');
            $this->dateRangeStr = $start_date_str . ' to ' . $end_date_str;
        }

        $data = $dataNonRacikans = $dataRacikans = [];

        // try {
        //     DB::transaction(function () use (&$data, &$dataNonRacikans, &$dataRacikans, $start_date, $end_date) {
        //         $data = $this->pharmacy->getCountOrderPharmacyRajalByCustomerTypeV2(startDate: $start_date, endDate: $end_date, location: $this->locationId);
        //         $dataNonRacikans = $this->pharmacy->getBottomFiveNonRacikanRajal(startDate: $start_date, endDate: $end_date, location: $this->locationId);
        //         $dataRacikans = $this->pharmacy->getBottomFiveRacikanRajal(startDate: $start_date, endDate: $end_date, location: $this->locationId);
        //     }, $this->maxRetries);
        // } catch (QueryException $e) {
        //     Log::error('Database transaction failed: ' . $e->getMessage());
        //     return response()->json(['error' => 'Internal Server Error'], 500);
        // }

        while (true) {
            try {
                DB::transaction(function () use (&$data, &$dataNonRacikans, &$dataRacikans, $start_date, $end_date) {
                    $data = $this->pharmacy->getCountOrderPharmacyRajalByCustomerTypeV2(startDate: $start_date, endDate: $end_date, location: $this->locationId);
                    $dataNonRacikans = $this->pharmacy->getBottomFiveNonRacikanRajal(startDate: $start_date, endDate: $end_date, location: $this->locationId);
                    $dataRacikans = $this->pharmacy->getBottomFiveRacikanRajal(startDate: $start_date, endDate: $end_date, location: $this->locationId);
                });
                break; // keluar dari loop jika transaksi berhasil
            } catch (QueryException $e) {
                Log::error('Database transaction failed: ' . $e->getMessage());
                // Tunggu sebentar sebelum mencoba lagi
                usleep(1000000); // tidur selama 1 detik
            }
        }

        return view('pharmacy.dashboards.payer', [
            'tittle' => 'Dashboard by Payer',
            'active' => 'login',
            'classes' => $data,
            'locations' => $this->locations,
            'locationId' => $this->locationId,
            'date' => $this->dateRangeStr,
            'dataRacikans' => $dataRacikans,
            'dataNonRacikans' => $dataNonRacikans,
            'timeRespons' => $this->timeRespons,
        ]);
    }

    public function order(Request $request)
    {
        // $statusOrder = $request->input('status_order', 'all');

        if ($request->location !== null) {
            $this->locationId = $request->location;
        }

        if ($request->customer_type !== null) {
            $this->customerTypeId = $request->customer_type;
        }

        if ($request->date !== null) {
            $this->dateRangeStr = $request->date;
        }

        $search = '';
        if ($request->search !== null) {
            $search = $request->search;
        }

        if ($request->status_order !== null) {
            $this->statusOrderId = $request->status_order;
        }

        if ($request->per_page !== null) {
            $this->perPage = $request->per_page;
        }

        if ($request->page !== null) {
            $this->page = $request->page;
        }

        if ($request->sort_by !== null) {
            $this->sortId = $request->sort_by;
        }

        if ($request->jenis_order !== null) {
            $this->jenisOrderId = $request->jenis_order;
        }

        if (strpos($this->dateRangeStr, 'to') !== false) {
            // Memecah string menjadi dua tanggal
            list($start_date_str, $end_date_str) = explode(' to ', $this->dateRangeStr);

            // Mengonversi string tanggal ke objek Carbon
            $start_date = Carbon::createFromFormat('d-m-Y', trim($start_date_str));
            $end_date = Carbon::createFromFormat('d-m-Y', trim($end_date_str));

            // Mengonversi objek Carbon ke format yyyy-mm-dd
            $start_date = $start_date->format('Y-m-d');
            $end_date = $end_date->format('Y-m-d');
        } else {
            $date = Carbon::createFromFormat('d-m-Y', trim($this->dateRangeStr));

            $start_date = $date->format('Y-m-d');
            $end_date = $date->format('Y-m-d');
        }

        // $start_date = $request->start_date;
        // $end_date = $request->end_date;
        // if ($start_date == null || $end_date == null) {
        //     $start_date = date('Y-m-d');
        //     $end_date = date('Y-m-d');
        // }

        $data = [];

        // try {
        //     DB::transaction(function () use (&$data, $start_date, $end_date, $search) {
        //         $data = $this->pharmacy->getListOrderRajal(startDate: $start_date, endDate: $end_date, customerType: $this->customerTypeId, location: $this->locationId, statusOrder: $this->statusOrderId, search: $search, perPage: $this->perPage, sortBy: $this->sortId, jenisOrder: $this->jenisOrderId);
        //     }, $this->maxRetries); // Retry 5 times on deadlock
        // } catch (QueryException $e) {
        //     Log::error('Database transaction failed: ' . $e->getMessage());
        //     return response()->json(['error' => 'Internal Server Error'], 500);
        // }

        while (true) {
            try {
                DB::transaction(function () use (&$data, $start_date, $end_date, $search) {
                    $data = $this->pharmacy->getListOrderRajal(startDate: $start_date, endDate: $end_date, customerType: $this->customerTypeId, location: $this->locationId, statusOrder: $this->statusOrderId, search: $search, perPage: $this->perPage, sortBy: $this->sortId, jenisOrder: $this->jenisOrderId);
                });
                break; // keluar dari loop jika transaksi berhasil
            } catch (QueryException $e) {
                Log::error('Database transaction failed: ' . $e->getMessage());
                // Tunggu sebentar sebelum mencoba lagi
                usleep(1000000); // tidur selama 1 detik
            }
        }

        return view('pharmacy.dashboards.list_order', [
            'tittle' => 'Dashboard List Order',
            'active' => 'login',
            'pharmacies' => $data,
            'locations' => $this->locations,
            'locationId' => $this->locationId,
            'customerTypes' => $this->customerTypes,
            'customerTypeId' => $this->customerTypeId,
            'statusOrders' => $this->statusOrders,
            'statusOrderId' => $this->statusOrderId,
            'date' => $this->dateRangeStr,
            'timeRespons' => $this->timeRespons,
            'pages' => $this->pages,
            'page' => $this->page,
            'perPage' => $this->perPage,
            'sorts' => $this->sorts,
            'sortId' => $this->sortId,
            'jenisOrders' => $this->jenisOrders,
            'jenisOrderId' => $this->jenisOrderId,
        ]);
    }
}
