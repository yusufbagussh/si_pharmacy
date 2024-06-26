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
    private $pharmacy;
    private $locations = [
        '100' => 'Farmasi Rawat Jalan',
        // '101' => 'Farmasi Rawat Inap',
        // '133' => 'Farmasi Rawat Non-UDD',
        '166' => 'Farmasi IGD',
    ];
    private $customerTypes = [
        'all' => 'Semua',
        '1' => 'JKN / BPJS - Kemenkes', //X004^500 = BPJS - Kemenkes
        '2' => 'Personal', //X004^999 = Pribadi, X004^251 = Karyawan - PTGJ, X004^300 = Pemerintah,
        '3' => 'Asuransi', //X004^100 = Rekanan, X004^200 = Perusahaan
        // '4' => 'Karyawan dan Yayasan', //X004^250 = Karyawan - FASKES, X004^400 = Rumah Sakit, X004^201 = Yayasan,
    ];
    private $statusOrders = [
        'all' => 'Semua',
        '1' => 'Selesai',
        '2' => 'Belum Selesai',
    ];
    private $timeRespons = [
        'nonRacikan' => 1800000,
        'racikan' => 3600000,
    ];
    private $pages = [
        '7' => '7',
        '10' => '10',
        '20' => '20',
        '50' => '50',
        '100' => '100',
    ];
    private $jenisOrders = [
        'all' => 'Semua',
        '1' => 'Racikan',
        '2' => 'Non Racikan',
    ];
    private $sorts = [
        '1' => 'ASC(Created Date)',
        '2' => 'DESC(Created Date)',
    ];

    private $perPage = 7;
    private $page = 1;
    private $statusOrderId = '2';
    private $jenisOrderId = 'all';
    private $sortId = '1';
    private $locationId = '100';
    private $customerTypeId = 'X004^999';
    private $dateRangeStr;
    private $search = '';

    private const DATE_SEPARATOR = ' to ';
    private const MAX_RETRIES = 5;
    private const RETRY_DELAY_US = 1000000;

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

    /**
     * get start date and end date from date range string.
     *
     * @return array
     */
    private function getStartDateAndEndDate()
    {
        if (strpos($this->dateRangeStr, self::DATE_SEPARATOR) !== false) {
            // Memecah string menjadi dua tanggal
            list($start_date_str, $end_date_str) = explode(' to ', $this->dateRangeStr);

            //Menjadikan string tanggal menjadi objek DateTime
            $start_date = DateTime::createFromFormat('d-m-Y H:i', trim($start_date_str));
            $end_date = DateTime::createFromFormat('d-m-Y H:i', trim($end_date_str));
        } else {
            // Mendapatkan tanggal hari ini
            $today = new DateTime('today');

            // Mengatur waktu start time (00:00:00.000)
            $start_date = clone $today;  // Mengkloning objek DateTime
            $start_date->setTime(0, 0, 0, 0);

            // Mengatur waktu end time (23:59:00.000)
            $end_date = clone $today;  // Mengkloning objek DateTime
            $end_date->setTime(23, 59, 0, 0);

            // Mengonversi objek DateTime ke string sebagai parameter yang di pass ke view
            $start_date_str =  $start_date->format('d-m-Y H:i');
            $end_date_str = $end_date->format('d-m-Y H:i');
            $this->dateRangeStr = $start_date_str . ' to ' . $end_date_str;
        }
        return array($start_date, $end_date);
    }

    /**
     * Execute a callback within a database transaction with retries.
     *
     * @param callable $callback
     */
    private function executeWithRetries(callable $callback)
    {
        $retries = 0;
        while ($retries < self::MAX_RETRIES) {
            try {
                DB::transaction($callback);
                return;
            } catch (QueryException $e) {
                Log::error('Database transaction failed: ' . $e->getMessage());
                usleep(self::RETRY_DELAY_US);
                $retries++;
            }
        }
        throw new \Exception('Max retries reached for database transaction');
    }

    /**
     * Get the view for dashboard with order data group by location.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function location(Request $request): View
    {
        // Cek apakah setiap request mengandung nilai atau tidak
        if ($request->location !== null) {
            $this->locationId = $request->location;
        }

        if ($request->date !== null) {
            $this->dateRangeStr = $request->date;
        }

        //cek apakah di dalam string terdapat kata 'to' atau tidak
        list($start_date, $end_date) = $this->getStartDateAndEndDate();

        $data = [];

        //Melakukan transaksi database, try hingga transaksi berhasil dan jeda setiap 1 detik sebelum mencoba lagi
        // while (true) {
        //     try {
        //         DB::transaction(function () use (&$data, $start_date, $end_date) {
        //             $data = $this->pharmacy->getSummaryOrderPharmacyGroupByLocation(startDate: $start_date, endDate: $end_date, location: $this->locationId);
        //         });
        //         break; // keluar dari loop jika transaksi berhasil
        //     } catch (QueryException $e) {
        //         Log::error('Database transaction failed: ' . $e->getMessage());
        //         // Tunggu sebentar sebelum mencoba lagi
        //         usleep(1000000); // tidur selama 1 detik
        //     }
        // }
        $this->executeWithRetries(function () use (&$data, $start_date, $end_date) {
            $data = $this->pharmacy->getSummaryOrderPharmacyGroupByLocation($start_date, $end_date, $this->locationId);
        });

        return view('pharmacy.dashboards.location', [
            'title' => 'Dashboard by Location',
            'active' => 'login',
            'pharmacyLocations' => $data,
            'locations' => $this->locations,
            'locationId' => $this->locationId,
            'date' => $this->dateRangeStr,
        ]);
    }

    /**
     * Get the view for dashboard with order data group by payer.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function payer(Request $request): View
    {
        // Cek setiap requet apakah mengandung nilai atau tidak
        if ($request->location !== null) {
            $this->locationId = $request->location;
        }

        if ($request->date !== null) {
            $this->dateRangeStr = $request->date;
        }

        //pecah request dateRangeStr menjadi dua, start date dan end date, jika mengandung kata 'to'
        list($start_date, $end_date) = $this->getStartDateAndEndDate();

        $dataSummary = $dataOrder = [];

        //Melakukan transaksi database, try hingga transaksi berhasil dan jeda setiap 1 detik sebelum mencoba lagi
        // while (true) {
        //     try {
        //         DB::transaction(function () use (&$dataSummary, &$dataOrder, &$dataNonRacikans, &$dataRacikans, $start_date, $end_date) {
        //             $dataSummary = $this->pharmacy->getSummaryOrderPharmacyRajalByPayer(startDate: $start_date, endDate: $end_date, location: $this->locationId);
        //             // $dataNonRacikans = $this->pharmacy->getFiveOldestOrderNonRacikanRajal(startDate: $start_date, endDate: $end_date, location: $this->locationId);
        //             // $dataRacikans = $this->pharmacy->getFiveOldestOrderRacikanRajal(startDate: $start_date, endDate: $end_date, location: $this->locationId);
        //             $dataOrder = $this->pharmacy->getFiveOldestOrderRajal(startDate: $start_date, endDate: $end_date, location: $this->locationId);
        //         });
        //         break; // keluar dari loop jika transaksi berhasil
        //     } catch (QueryException $e) {
        //         Log::error('Database transaction failed: ' . $e->getMessage());
        //         // Tunggu sebentar sebelum mencoba lagi
        //         usleep(1000000); // jeda selama 1 detik untuk mengeksekusi kembali
        //     }
        // }

        $this->executeWithRetries(function () use (&$dataSummary, &$dataOrder, $start_date, $end_date) {
            $dataSummary = $this->pharmacy->getSummaryOrderPharmacyRajalByPayer($start_date, $end_date, $this->locationId);
            $dataOrder = $this->pharmacy->getFiveOldestOrderRajal($start_date, $end_date, $this->locationId);
        });

        return view('pharmacy.dashboards.payer', [
            'title' => 'Dashboard by Payer',
            'active' => 'login',
            'classes' => $dataSummary,
            'locations' => $this->locations,
            'locationId' => $this->locationId,
            'date' => $this->dateRangeStr,
            'dataRacikans' => $dataOrder['dataRacikans'],
            'dataNonRacikans' => $dataOrder['dataNonRacikans'],
            'timeRespons' => $this->timeRespons,
        ]);
    }

    /**
     * Get the view for dashboard with list order data.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function order(Request $request): View
    {
        // Cek apakah setiap request mengandung nilai atau tidak
        if ($request->location !== null) {
            $this->locationId = $request->location;
        }

        if ($request->customer_type !== null) {
            $this->customerTypeId = $request->customer_type;
        }

        if ($request->date !== null) {
            $this->dateRangeStr = $request->date;
        }

        if ($request->search !== null) {
            $this->search = $request->search;
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

        //cek apakah di dalam string terdapat kata 'to' atau tidak
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

        $data = [];

        //Melakukan transaksi database, try hingga transaksi berhasil dan jeda setiap 1 detik sebelum mencoba lagi
        // while (true) {
        //     try {
        //         DB::transaction(function () use (&$data, $start_date, $end_date) {
        //             $data = $this->pharmacy->getListOrderRajal(startDate: $start_date, endDate: $end_date, customerType: $this->customerTypeId, location: $this->locationId, statusOrder: $this->statusOrderId, search: $this->search, perPage: $this->perPage, sortBy: $this->sortId, jenisOrder: $this->jenisOrderId);
        //         });
        //         break; // keluar dari loop jika transaksi berhasil
        //     } catch (QueryException $e) {
        //         Log::error('Database transaction failed: ' . $e->getMessage());
        //         // Tunggu sebentar sebelum mencoba lagi
        //         usleep(1000000); // tidur selama 1 detik
        //     }
        // }

        //Melakukan eksekusi transaksi database, try hingga transaksi berhasil dengan maksimal 5x percobaan
        $this->executeWithRetries(function () use (&$data, $start_date, $end_date) {
            $data = $this->pharmacy->getListOrderRajal(
                startDate: $start_date,
                endDate: $end_date,
                customerType: $this->customerTypeId,
                location: $this->locationId,
                statusOrder: $this->statusOrderId,
                search: $this->search,
                perPage: $this->perPage,
                sortBy: $this->sortId,
                jenisOrder: $this->jenisOrderId,
            );
        });

        return view('pharmacy.dashboards.list_order', [
            'title' => 'Dashboard List Order',
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
