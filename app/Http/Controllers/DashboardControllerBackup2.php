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
        // '166' => 'Farmasi IGD',
    ];
    private $customerTypes = [
        'all' => 'Semua',
        '1' => 'JKN / BPJS - Kemenkes',
        '2' => 'Personal',
        '3' => 'Asuransi',
        // '4' => 'Karyawan dan Yayasan',
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

    private $perPage = '7';
    private $page = 1;
    private $statusOrderId = '2';
    private $jenisOrderId = 'all';
    private $sortId = '1';
    private $locationId = '100';
    private $customerTypeId = 'X004^999';
    private $dateRangeStr;
    private $search = '';

    private const DATE_FORMAT = 'd-m-Y H:i';
    private const DATE_SEPARATOR = ' to ';
    private const MAX_RETRIES = 5;
    private const RETRY_DELAY_US = 1000000;

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
            list($start_date_str, $end_date_str) = explode(self::DATE_SEPARATOR, $this->dateRangeStr);
            $start_date = DateTime::createFromFormat(self::DATE_FORMAT, trim($start_date_str));
            $end_date = DateTime::createFromFormat(self::DATE_FORMAT, trim($end_date_str));
        } else {
            $today = new DateTime('today');
            $start_date = clone $today;
            $start_date->setTime(0, 0, 0, 0);
            $end_date = clone $today;
            $end_date->setTime(23, 59, 0, 0);
            $this->dateRangeStr =
                $start_date->format(self::DATE_FORMAT) . self::DATE_SEPARATOR . $end_date->format(self::DATE_FORMAT);
        }
        return [$start_date, $end_date];
    }

    /**
     * Validate request parameters and set them to the controller properties.
     *
     * @param Request $request
     * @param array $params
     */
    private function validateAndSetRequestParams(Request $request, array $params)
    {
        foreach ($params as $key => $default) {
            $this->$key = $request->input($key, $default);
        }
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
        if ($request->date !== null) {
            $this->dateRangeStr = $request->date;
        }

        list($start_date, $end_date) = $this->getStartDateAndEndDate();
        $data = [];

        $this->executeWithRetries(function () use (&$data, $start_date, $end_date) {
            $data = $this->pharmacy->getSummaryOrderPharmacyGroupByLocation($start_date, $end_date, $this->locationId);
        });

        return view('pharmacy.dashboards.location', [
            'title' => 'Dashboard by Location',
            'active' => 'login',
            'locations' => $data,
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
        $this->validateAndSetRequestParams($request, [
            'locationId' => $this->locationId,
            'dateRangeStr' => $this->dateRangeStr,
        ]);

        list($start_date, $end_date) = $this->getStartDateAndEndDate();
        $dataSummary = $dataOrder = [];

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
        $this->validateAndSetRequestParams($request, [
            'locationId' => $this->locationId,
            'customerTypeId' => $this->customerTypeId,
            'dateRangeStr' => $this->dateRangeStr,
            'search' => $this->search,
            'statusOrderId' => $this->statusOrderId,
            'perPage' => $this->perPage,
            'page' => $this->page,
            'sortId' => $this->sortId,
            'jenisOrderId' => $this->jenisOrderId,
        ]);

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
        while (true) {
            try {
                DB::transaction(function () use (&$data, $start_date, $end_date) {
                    $data = $this->pharmacy->getListOrderRajal(startDate: $start_date, endDate: $end_date, customerType: $this->customerTypeId, location: $this->locationId, statusOrder: $this->statusOrderId, search: $this->search, perPage: $this->perPage, sortBy: $this->sortId, jenisOrder: $this->jenisOrderId);
                });
                break; // keluar dari loop jika transaksi berhasil
            } catch (QueryException $e) {
                Log::error('Database transaction failed: ' . $e->getMessage());
                // Tunggu sebentar sebelum mencoba lagi
                usleep(1000000); // tidur selama 1 detik
            }
        }

        // $this->executeWithRetries(function () use (&$data, $start_date, $end_date) {
        //     $data = $this->pharmacy->getListOrderRajal(
        //         startDate: $start_date,
        //         endDate: $end_date,
        //         customerType: $this->customerTypeId,
        //         location: $this->locationId,
        //         statusOrder: $this->statusOrderId,
        //         search: $this->search,
        //         perPage: $this->perPage,
        //         sortBy: $this->sortId,
        //         jenisOrder: $this->jenisOrderId,
        //     );
        // });

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
