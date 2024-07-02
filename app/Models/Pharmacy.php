<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pharmacy extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv_second';

    /**
     * Get Base Query for Pharmacy
     *
     * @param string $startDate, $endDate, $location
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getBaseQuery($startDate, $endDate, $location)
    {
        $basedQuery = DB::table('PrescriptionOrderHd as poh')
            ->distinct()
            ->select(
                'poh.DispensaryServiceUnitID',
                'su.ServiceUnitName AS Poli',
                'r.RegistrationNo',
                'p.MedicalNo',
                'p.FullName AS PatientName',
                'pm.FullName AS Nama Dokter',
                'ua.FullName AS Orderer',
                'sc2.StandardCodeName AS StatusOrder',
                'poh.PrescriptionOrderID',
                'poh.PrescriptionOrderNo',
                'pch.TransactionID',
                'pch.TransactionNo',
                'poh.SendOrderDateTime',
                'pch.ProposedDate AS ProposedDateTime',
                'ua2.FullName AS User Propose',
                'poh.ClosedDate AS ClosedDateFarmasi',
                'poh.ClosedTime AS ClosedTimeFarmasi',
                'bp.BusinessPartnerID',
                'c.GCCustomerType',
                'sc.StandardCodeName AS Penjamin',
                'bp.BusinessPartnerName',
                'pch.TotalAmount',
                'poh.CreatedDate',
                'sc3.StandardCodeName AS StatusTransaksi',
                'poh.PrescriptionDate',
                'pod.IsCompound',
            )
            ->join('PrescriptionOrderDt as pod', 'poh.PrescriptionOrderID', '=', 'pod.PrescriptionOrderID')
            ->leftJoin('PatientChargesHd as pch', function ($join) {
                $join->on('poh.PrescriptionOrderID', '=', 'pch.PrescriptionOrderID')
                    ->whereNotIn('pch.GCTransactionStatus', ['X121^999']);
            })
            ->join('ConsultVisit as cv', 'poh.VisitID', '=', 'cv.VisitID')
            ->join('Registration as r', 'cv.RegistrationID', '=', 'r.RegistrationID')
            ->join('Patient as p', 'r.MRN', '=', 'p.MRN')
            ->join('BusinessPartners as bp', 'r.BusinessPartnerID', '=', 'bp.BusinessPartnerID')
            ->join('Customer as c', 'bp.BusinessPartnerID', '=', 'c.BusinessPartnerID')
            ->join('StandardCode as sc', 'c.GCCustomerType', '=', 'sc.StandardCodeID')
            ->join('StandardCode as sc2', 'poh.GCTransactionStatus', '=', 'sc2.StandardCodeID')
            ->leftJoin('StandardCode as sc3', 'pch.GCTransactionStatus', '=', 'sc3.StandardCodeID')
            ->join('HealthcareServiceUnit as hsu', 'cv.HealthcareServiceUnitID', '=', 'hsu.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as su', 'hsu.ServiceUnitID', '=', 'su.ServiceUnitID')
            ->join('HealthcareServiceUnit as hsud', 'poh.DispensaryServiceUnitID', '=', 'hsud.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as sud', 'hsud.ServiceUnitID', '=', 'sud.ServiceUnitID')
            ->join('ParamedicMaster as pm', 'poh.ParamedicID', '=', 'pm.ParamedicID')
            ->join('UserAttribute as ua', 'poh.CreatedBy', '=', 'ua.UserID')
            ->leftJoin('UserAttribute as ua2', 'pch.ProposedBy', '=', 'ua2.UserID')
            ->whereBetween('poh.PrescriptionDate', [$startDate, $endDate])
            ->where('poh.DispensaryServiceUnitID', $location)
            ->whereNot('pch.TotalAmount', '=', '.00')
            // ->whereIn('c.GCCustomerType', ['X004^500', 'X004^999', 'X004^251', 'X004^300', 'X004^100', 'X004^200'])
            ->whereNotIn('r.MRN', [10, 527556])
            ->whereNotIn('poh.GCTransactionStatus', ['X121^999']);
        return $basedQuery;
    }

    /**
     * Get five oldest order data when type is Racikan
     *
     * @param string $query, $startDate, $endDate, $location
     * @return \Illuminate\Database\Query\Builder
     */
    protected function addWhereNotExistsQuery($query, $startDate, $endDate, $location)
    {
        return $query->whereNotExists(function ($query) use ($startDate, $endDate, $location) {
            $query->select(DB::raw(1))
                ->from('PrescriptionOrderHd as poh2')
                ->join('PrescriptionOrderDt as pod', 'poh2.PrescriptionOrderID', '=', 'pod.PrescriptionOrderID')
                ->leftJoin('PatientChargesHd as pch', function ($join) {
                    $join->on('poh2.PrescriptionOrderID', '=', 'pch.PrescriptionOrderID')
                        ->whereNotIn('pch.GCTransactionStatus', ['X121^999']);
                })
                ->join('ConsultVisit as cv', 'poh2.VisitID', '=', 'cv.VisitID')
                ->join('Registration as r', 'cv.RegistrationID', '=', 'r.RegistrationID')
                ->join('BusinessPartners as bp', 'r.BusinessPartnerID', '=', 'bp.BusinessPartnerID')
                ->join('Customer as c', 'bp.BusinessPartnerID', '=', 'c.BusinessPartnerID')
                ->whereBetween('poh.PrescriptionDate', [$startDate, $endDate])
                ->where('poh2.DispensaryServiceUnitID', $location)
                // ->whereIn('c.GCCustomerType', ['X004^500', 'X004^999', 'X004^251', 'X004^300', 'X004^100', 'X004^200'])
                ->whereNotIn('poh2.GCTransactionStatus', ['X121^999'])
                ->whereNot('pch.TotalAmount', '=', '.00')
                ->whereNotIn('r.MRN', [10, 527556])
                ->where('pod.IsCompound', 1)
                ->whereRaw('poh2.PrescriptionOrderID = poh.PrescriptionOrderID');
        });

        // return $addWhereNotExistsQuery;
    }

    // protected function addWhereNotExistsQueryV2($query, $startDate, $endDate, $location, $customerType, $statusOrder, $search)
    // {
    //     return $query->whereNotExists(function ($query) use ($startDate, $endDate, $location, $customerType, $statusOrder, $search) {
    //         $query->select(DB::raw(1))
    //             ->from('PrescriptionOrderHd as poh2')
    //             ->join('PrescriptionOrderDt as pod', 'poh2.PrescriptionOrderID', '=', 'pod.PrescriptionOrderID')
    //             ->leftJoin('PatientChargesHd as pch', function ($join) {
    //                 $join->on('poh2.PrescriptionOrderID', '=', 'pch.PrescriptionOrderID')
    //                     ->whereNotIn('pch.GCTransactionStatus', ['X121^999']);
    //             })
    //             ->join('ConsultVisit as cv', 'poh2.VisitID', '=', 'cv.VisitID')
    //             ->join('Registration as r', 'cv.RegistrationID', '=', 'r.RegistrationID')
    //             ->join('BusinessPartners as bp', 'r.BusinessPartnerID', '=', 'bp.BusinessPartnerID')
    //             ->join('Customer as c', 'bp.BusinessPartnerID', '=', 'c.BusinessPartnerID')
    //             ->whereBetween('poh.PrescriptionDate', [$startDate, $endDate])
    //             ->where('poh2.DispensaryServiceUnitID', $location)
    //             ->whereNotIn('poh2.GCTransactionStatus', ['X121^999'])
    //             ->whereNot('pch.TotalAmount', '=', '.00')
    //             ->whereNotIn('r.MRN', [10, 527556])
    //             ->where('pod.IsCompound', 1)
    //             ->whereRaw('poh2.PrescriptionOrderID = poh.PrescriptionOrderID');

    //         if ($customerType === '1') {
    //             $query->where('c.GCCustomerType', '=', 'X004^500');
    //         } else if ($customerType === '2') {
    //             $query->whereIn('c.GCCustomerType', ['X004^999', 'X004^251', 'X004^300']);
    //         } else if ($customerType === '3') {
    //             $query->whereIn('c.GCCustomerType', ['X004^100', 'X004^200']);
    //         } else if ($customerType === '4') {
    //             $query->whereIn('c.GCCustomerType', ['X004^250', 'X004^400', 'X004^201']);
    //         }

    //         if ($statusOrder !== 'all') {
    //             if ($statusOrder === '2') {
    //                 $query
    //                     ->whereNull('poh.ClosedDate');
    //             } else {
    //                 $query->whereNotNull('poh.ClosedDate');
    //             }
    //         }
    //         if (!empty($search)) {
    //             $query->where(function ($query) use ($search) {
    //                 $query
    //                     ->where('p.FullName', 'LIKE', "%{$search}%")
    //                     ->orWhere('p.MedicalNo', 'LIKE', "%{$search}%")
    //                     ->orWhere('sc.StandardCodeName', 'LIKE', "%{$search}%");
    //             });
    //         }
    //     });

    //     // return $addWhereNotExistsQuery;
    // }

    /**
     * Get five oldest order data when type is Non-Racikan
     *
     * @param string $startDate, $endDate, $location
     * @return array
     */
    function getFiveOldestOrderRajal($startDate, $endDate, $location)
    {
        $baseQueryOldestOrder = $this->getBaseQuery($startDate, $endDate, $location)
            ->addSelect(
                'sud.ServiceUnitName as Dispensary',
                DB::raw("CASE
                            WHEN poh.SendOrderDateTime IS NOT NULL
                                THEN DATEDIFF(SECOND, poh.SendOrderDateTime, CURRENT_TIMESTAMP)
                            WHEN pch.ProposedDate IS NOT NULL
                                THEN DATEDIFF(SECOND, pch.ProposedDate, CURRENT_TIMESTAMP)
                            ELSE
                                NULL
                            END
                        AS 'DurationSeconds'")
            );

        $dataRacikans = (clone $baseQueryOldestOrder)
            ->addSelect(DB::raw("'RACIKAN' AS JenisResep"))
            ->whereNull('poh.ClosedDate')
            ->where('pod.IsCompound', 1)
            ->orderBy('DurationSeconds', 'desc')
            ->limit(5)
            ->get();

        $dataNonRacikans = (clone $baseQueryOldestOrder)
            ->addSelect(DB::raw("'NON RACIKAN' AS JenisResep"))
            ->whereNull('poh.ClosedDate')
            ->where(function ($query) use ($startDate, $endDate, $location) {
                $this->addWhereNotExistsQuery($query, $startDate, $endDate, $location)
                    ->whereNull('poh.ClosedDate');
            })
            ->orderBy('DurationSeconds', 'desc')
            ->limit(5)
            ->get();

        $results = [
            'dataRacikans' => $dataRacikans,
            'dataNonRacikans' => $dataNonRacikans
        ];

        return $results;
    }

    /**
     * Get summary report of order data group by Dispensary
     *
     * @param string $startDate, $endDate
     * @return array
     */
    function getSummaryOrderPharmacyGroupByLocation($startDate, $endDate, $location)
    {
        $baseQueryOrderByLocation = $this->getBaseQuery($startDate, $endDate, $location)
            ->addSelect(
                DB::raw("CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME) AS ClosedDateTime"),
                DB::raw("CASE
                    WHEN (poh.ClosedDate IS NOT NULL AND poh.SendOrderDateTime IS NOT NULL)
                        THEN DATEDIFF(SECOND, poh.SendOrderDateTime, CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME))
                    WHEN (poh.ClosedDate IS NOT NULL AND pch.ProposedDate IS NOT NULL)
                        THEN DATEDIFF(SECOND, pch.ProposedDate, CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME))
                    ELSE
                        NULL
                    END
                AS DurationSeconds"),
                DB::raw("CASE
                    WHEN (sud.ServiceUnitName = 'FARMASI RAWAT INAP NON UDD')
                        THEN 'FARMASI RANAP NON UDD'
                    ELSE
                        sud.ServiceUnitName
                    END
                AS Dispensary"),
            )->whereIn('c.GCCustomerType', ['X004^500', 'X004^999', 'X004^251', 'X004^300', 'X004^100', 'X004^200']);
        // Subquery untuk Racikan
        $racikan = (clone $baseQueryOrderByLocation)
            ->addSelect((DB::raw("'RACIKAN' AS JenisResep")))
            // ->whereNotIn('poh.DispensaryServiceUnitID', [101, 133])
            ->where('pod.IsCompound', 1);

        // Subquery untuk Non-Racikan
        $nonRacikan = (clone $baseQueryOrderByLocation)
            ->addSelect((DB::raw("'NON RACIKAN' AS JenisResep")))
            // ->whereNotIn('poh.DispensaryServiceUnitID', [101, 133])
            ->where(function ($query) use ($startDate, $endDate, $location) {
                $this->addWhereNotExistsQuery($query, $startDate, $endDate, $location);
                $query->whereIn('c.GCCustomerType', ['X004^500', 'X004^999', 'X004^251', 'X004^300', 'X004^100', 'X004^200']);
            });

        // Gabungkan kedua subquery dengan UNION ALL
        $combinedOrders = $racikan->unionAll($nonRacikan);

        // Lakukan agregasi pada hasil gabungan dari subquery
        $results = DB::table(DB::raw("({$combinedOrders->toSql()}) as combined"))
            ->mergeBindings($combinedOrders)
            ->selectRaw("
                combined.Dispensary AS LocationName,
                COUNT(*) AS TotalOrder,
                SUM(
                    CASE
                        WHEN combined.DurationSeconds IS NOT NULL
                            THEN 1
                        ELSE 0
                    END
                ) AS TotalOrderSelesai,
                SUM(
                    CASE
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationSeconds <= 3600 AND combined.JenisResep = 'RACIKAN')
                            THEN 1
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationSeconds <= 1800 AND combined.JenisResep = 'NON RACIKAN')
                            THEN 1
                        ELSE 0
                    END
                ) AS TotalOrderOnTime,
                SUM(
                    CASE
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationSeconds > 3600 AND combined.JenisResep = 'RACIKAN')
                            THEN 1
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationSeconds > 1800 AND combined.JenisResep = 'NON RACIKAN')
                            THEN 1
                        ELSE 0
                    END
                ) AS TotalOrderLateTime,
                AVG(
                    CASE
                        WHEN (combined.JenisResep = 'RACIKAN' AND combined.DurationSeconds IS NOT NULL)
                            THEN combined.DurationSeconds
                        END
                ) AS AverageDurationRacikan,
                AVG(
                    CASE
                        WHEN (combined.JenisResep = 'NON RACIKAN' AND combined.DurationSeconds IS NOT NULL)
                    THEN
                        combined.DurationSeconds
                    END
                ) AS AverageDurationNonRacikan,
                SUM(
                    CASE
                        WHEN (combined.JenisResep = 'RACIKAN' AND combined.DurationSeconds IS NOT NULL)
                            THEN combined.DurationSeconds
                    END
                ) AS SumDurationRacikan,
                SUM(
                    CASE
                        WHEN (combined.JenisResep = 'NON RACIKAN' AND combined.DurationSeconds IS NOT NULL)
                    THEN
                        combined.DurationSeconds
                    END
                ) AS SumDurationNonRacikan,
                COUNT(
                    CASE
                        WHEN (combined.JenisResep = 'RACIKAN' AND combined.DurationSeconds IS NOT NULL)
                            THEN combined.DurationSeconds
                        END
                ) AS CountDurationRacikan,
                COUNT(
                    CASE
                        WHEN (combined.JenisResep = 'NON RACIKAN' AND combined.DurationSeconds IS NOT NULL)
                            THEN
                                combined.DurationSeconds
                    END
                ) AS CountDurationNonRacikan,
                COUNT(*) AS TotalOrder,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NULL THEN 1 ELSE 0 END) AS TotalOrderUnClosed,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NULL AND combined.JenisResep = 'NON RACIKAN' THEN 1 ELSE 0 END) AS TotalOrderNonRacikanUnClosed,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NULL AND combined.JenisResep = 'RACIKAN' THEN 1 ELSE 0 END) AS TotalOrderRacikanUnClosed,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NOT NULL THEN 1 ELSE 0 END) AS TotalOrderClosed,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NOT NULL AND combined.JenisResep = 'NON RACIKAN' THEN 1 ELSE 0 END) AS TotalOrderNonRacikanClosed,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NOT NULL AND combined.JenisResep = 'RACIKAN' THEN 1 ELSE 0 END) AS TotalOrderRacikanClosed
            ")
            ->groupBy('combined.Dispensary')
            ->orderBy('combined.Dispensary')
            ->get();
        return $results;
    }


    /**
     *  Get summary report of order data group by Payer Customer
     *
     * @param string $startDate, $endDate, $location
     * @return array
     */
    function getSummaryOrderPharmacyRajalByPayer($startDate, $endDate, $location)
    {
        $baseQueryOrderByPayer = $this->getBaseQuery($startDate, $endDate, $location)
            ->addSelect(
                'sud.ServiceUnitName as Dispensary',
                DB::raw("CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME) AS ClosedDateTime"),
                DB::raw("CASE
                            WHEN (poh.ClosedDate IS NOT NULL AND poh.SendOrderDateTime IS NOT NULL)
                                THEN DATEDIFF(SECOND, poh.SendOrderDateTime, CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME))
                            WHEN (poh.ClosedDate IS NOT NULL AND pch.ProposedDate IS NOT NULL)
                                THEN DATEDIFF(SECOND, pch.ProposedDate, CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME))
                            ELSE
                                NULL
                        END
                    AS DurationSeconds"),
                DB::raw("CASE
                            WHEN c.GCCustomerType = 'X004^500'
                                THEN 'BPJS - Kemenkes'
                            WHEN c.GCCustomerType IN ('X004^999', 'X004^251', 'X004^300')
                                THEN 'Personal'
                            WHEN c.GCCustomerType IN ('X004^100', 'X004^200')
                                THEN 'Asuransi'
                        END
                    AS PenjaminGroup")
            )->whereIn('c.GCCustomerType', ['X004^500', 'X004^999', 'X004^251', 'X004^300', 'X004^100', 'X004^200']);

        // Subquery untuk Racikan
        $racikan = (clone $baseQueryOrderByPayer)
            ->addSelect((DB::raw("'RACIKAN' AS JenisResep")))
            // ->whereNotIn('poh.DispensaryServiceUnitID', [101, 133])
            ->where('pod.IsCompound', 1);

        // Subquery untuk Non-Racikan
        $nonRacikan = (clone $baseQueryOrderByPayer)
            ->addSelect((DB::raw("'NON RACIKAN' AS JenisResep")))
            ->where(function ($query) use ($startDate, $endDate, $location) {
                $this->addWhereNotExistsQuery($query, $startDate, $endDate, $location);
                $query->whereIn('c.GCCustomerType', ['X004^500', 'X004^999', 'X004^251', 'X004^300', 'X004^100', 'X004^200']);
            });

        // Gabungkan kedua subquery dengan UNION ALL
        $combinedOrders = $racikan->unionAll($nonRacikan);

        // Lakukan agregasi pada hasil gabungan dari subquery
        $results = DB::table(DB::raw("({$combinedOrders->toSql()}) as combined"))
            ->mergeBindings($combinedOrders)
            ->selectRaw("
                combined.PenjaminGroup AS CustomerType,
                COUNT(*) AS TotalOrder,
                SUM(
                    CASE
                        WHEN combined.DurationSeconds IS NOT NULL
                            THEN 1
                        ELSE 0
                    END
                ) AS TotalOrderSelesai,
                SUM(
                    CASE
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationSeconds <= 3600 AND combined.JenisResep = 'RACIKAN')
                            THEN 1
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationSeconds <= 1800 AND combined.JenisResep = 'NON RACIKAN')
                            THEN 1
                        ELSE 0
                    END
                ) AS TotalOrderOnTime,
                SUM(
                    CASE
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationSeconds > 3600 AND combined.JenisResep = 'RACIKAN')
                            THEN 1
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationSeconds > 1800 AND combined.JenisResep = 'NON RACIKAN')
                            THEN 1
                        ELSE 0
                    END
                ) AS TotalOrderLateTime,
                AVG(
                    CASE
                        WHEN (combined.JenisResep = 'RACIKAN' AND combined.DurationSeconds IS NOT NULL)
                            THEN combined.DurationSeconds
                        END
                ) AS AverageDurationRacikan,
                AVG(
                    CASE
                        WHEN (combined.JenisResep = 'NON RACIKAN' AND combined.DurationSeconds IS NOT NULL)
                    THEN
                        combined.DurationSeconds
                    END
                ) AS AverageDurationNonRacikan,
                SUM(
                    CASE
                        WHEN (combined.JenisResep = 'RACIKAN' AND combined.DurationSeconds IS NOT NULL)
                            THEN combined.DurationSeconds
                    END
                ) AS SumDurationRacikan,
                SUM(
                    CASE
                        WHEN (combined.JenisResep = 'NON RACIKAN' AND combined.DurationSeconds IS NOT NULL)
                    THEN
                        combined.DurationSeconds
                    END
                ) AS SumDurationNonRacikan,
                COUNT(
                    CASE
                        WHEN (combined.JenisResep = 'RACIKAN' AND combined.DurationSeconds IS NOT NULL)
                            THEN combined.DurationSeconds
                        END
                ) AS CountDurationRacikan,
                COUNT(
                    CASE
                        WHEN (combined.JenisResep = 'NON RACIKAN' AND combined.DurationSeconds IS NOT NULL)
                            THEN
                                combined.DurationSeconds
                    END
                ) AS CountDurationNonRacikan,
                COUNT(*) AS TotalOrder,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NULL THEN 1 ELSE 0 END) AS TotalOrderUnClosed,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NULL AND combined.JenisResep = 'NON RACIKAN' THEN 1 ELSE 0 END) AS TotalOrderNonRacikanUnClosed,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NULL AND combined.JenisResep = 'RACIKAN' THEN 1 ELSE 0 END) AS TotalOrderRacikanUnClosed,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NOT NULL THEN 1 ELSE 0 END) AS TotalOrderClosed,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NOT NULL AND combined.JenisResep = 'NON RACIKAN' THEN 1 ELSE 0 END) AS TotalOrderNonRacikanClosed,
                SUM(CASE WHEN combined.ClosedDateFarmasi IS NOT NULL AND combined.JenisResep = 'RACIKAN' THEN 1 ELSE 0 END) AS TotalOrderRacikanClosed            ")
            ->groupBy('combined.PenjaminGroup')
            ->orderBy('combined.PenjaminGroup')
            ->get();
        return $results;
    }

    /**
     * Get list order when location in Dispensary Rawat Jalan
     *
     * @param string $startDate, $endDate, $customerType, $location, $statusOrder, $search, $sortBy, $jenisOrder
     * @return array
     */
    function getListOrderRajal(
        $startDate,
        $endDate,
        $customerType,
        $location,
        $statusOrder,
        $perPage,
        $search,
        $sortBy,
        $jenisOrder,
    ) {

        $baseQueryListOrder = $this
            ->getBaseQuery($startDate, $endDate, $location)
            ->addSelect(
                'sud.ServiceUnitName as Dispensary',
            );

        $queryRacikan = (clone $baseQueryListOrder)
            ->addSelect(DB::raw("'RACIKAN' AS JenisResep"))
            ->where('pod.IsCompound', 1);

        //Cek apakah ada paramter statusOrder, 1 = Open, 2 = Closed, all = semua
        if ($statusOrder !== 'all') {
            if ($statusOrder === '2') {
                $queryRacikan
                    ->whereNull('poh.ClosedDate');
            } else {
                $queryRacikan
                    ->WhereNotNull('poh.ClosedDate');
            }
        }

        //Cek apakah ada parameter customerType, 1 = JKN/BPJS, 2 = Personal, 3 = Asuransi
        if ($customerType === 'all') {
            $queryRacikan->whereIn('c.GCCustomerType', ['X004^500', 'X004^999', 'X004^251', 'X004^300', 'X004^100', 'X004^200']);
        } else if ($customerType === '1') {
            $queryRacikan->where('c.GCCustomerType', '=', 'X004^500');
        } else if ($customerType === '2') {
            $queryRacikan->whereIn('c.GCCustomerType', ['X004^999', 'X004^251', 'X004^300']);
        } else if ($customerType === '3') {
            $queryRacikan->whereIn('c.GCCustomerType', ['X004^100', 'X004^200']);
        } else if ($customerType === '4') {
            $queryRacikan->whereIn('c.GCCustomerType', ['X004^250', 'X004^400', 'X004^201']);
        }

        //Cek apakah ada parameter search
        if (!empty($search)) {
            $queryRacikan->where(function ($query) use ($search) {
                $query
                    ->where('p.FullName', 'LIKE', "%{$search}%")
                    ->orWhere('p.MedicalNo', 'LIKE', "%{$search}%")
                    ->orWhere('sc.StandardCodeName', 'LIKE', "%{$search}%");
            });
        }

        // Query untuk resep non-racikan
        $queryNonRacikan = (clone $baseQueryListOrder) //Status Transaksi di void
            ->addSelect(DB::raw("'NON RACIKAN' AS JenisResep"))
            ->where(function ($query) use ($startDate, $endDate, $location, $customerType, $statusOrder, $search) {
                $this->addWhereNotExistsQuery($query, $startDate, $endDate, $location);

                if ($customerType === 'all') {
                    $query->whereIn('c.GCCustomerType', ['X004^500', 'X004^999', 'X004^251', 'X004^300', 'X004^100', 'X004^200']);
                } else if ($customerType === '1') {
                    $query->where('c.GCCustomerType', '=', 'X004^500');
                } else if ($customerType === '2') {
                    $query->whereIn('c.GCCustomerType', ['X004^999', 'X004^251', 'X004^300']);
                } else if ($customerType === '3') {
                    $query->whereIn('c.GCCustomerType', ['X004^100', 'X004^200']);
                } else if ($customerType === '4') {
                    $query->whereIn('c.GCCustomerType', ['X004^250', 'X004^400', 'X004^201']);
                }

                if ($statusOrder !== 'all') {
                    if ($statusOrder === '2') {
                        $query
                            ->whereNull('poh.ClosedDate');
                    } else {
                        $query->whereNotNull('poh.ClosedDate');
                    }
                }
                if (!empty($search)) {
                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('p.FullName', 'LIKE', "%{$search}%")
                            ->orWhere('p.MedicalNo', 'LIKE', "%{$search}%")
                            ->orWhere('sc.StandardCodeName', 'LIKE', "%{$search}%");
                    });
                }
            });

        if ($statusOrder !== 'all') {
            if ($statusOrder === '2') {
                $queryNonRacikan
                    ->whereNull('poh.ClosedDate');
            } else {
                $queryNonRacikan
                    ->WhereNotNull('poh.ClosedDate');
            }
        }
        if ($customerType === 'all') {
            $queryNonRacikan->whereIn('c.GCCustomerType', ['X004^500', 'X004^999', 'X004^251', 'X004^300', 'X004^100', 'X004^200']);
        } else if ($customerType === '1') {
            $queryNonRacikan->where('c.GCCustomerType', '=', 'X004^500');
        } else if ($customerType === '2') {
            $queryNonRacikan->whereIn('c.GCCustomerType', ['X004^999', 'X004^251', 'X004^300']);
        } else if ($customerType === '3') {
            $queryNonRacikan->whereIn('c.GCCustomerType', ['X004^100', 'X004^200']);
        } else if ($customerType === '4') {
            $queryNonRacikan->whereIn('c.GCCustomerType', ['X004^250', 'X004^400', 'X004^201']);
        }
        if (!empty($search)) {
            $queryNonRacikan->where(function ($query) use ($search) {
                $query
                    ->where('p.FullName', 'LIKE', "%{$search}%")
                    ->orWhere('p.MedicalNo', 'LIKE', "%{$search}%")
                    ->orWhere('sc.StandardCodeName', 'LIKE', "%{$search}%");
            });
        }

        // Gabungkan hasil kedua query berdasarkan jenisOrder
        if ($jenisOrder == '1') {
            $combinedQuery = $queryRacikan;
        } elseif ($jenisOrder == '2') {
            $combinedQuery = $queryNonRacikan;
        } else {
            $combinedQuery = $queryRacikan->unionAll($queryNonRacikan);
        }

        $startDate = Carbon::createFromFormat('Y-m-d', trim($startDate));
        $endDate = Carbon::createFromFormat('Y-m-d', trim($endDate));

        $startDate = $startDate->format('d-m-Y');
        $endDate = $endDate->format('d-m-Y');

        $query = DB::table(DB::raw("({$combinedQuery->toSql()}) as combined"))
            ->mergeBindings($combinedQuery); // Pindahkan binding dari query sebelumnya
        if ($sortBy === '1') {
            $query->orderBy('CreatedDate', 'asc');
        } else {
            $query->orderBy('CreatedDate', 'desc');
        }
        $results = $query->paginate($perPage)
            ->appends([
                'status_order' => $statusOrder,
                'location' => $location,
                'customer_type' => $customerType,
                'date' => $startDate . ' to ' . $endDate,
                'per_page' => $perPage,
                'search' => $search
            ]);

        return $results;
    }
}
