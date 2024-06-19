<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pharmacy extends Model
{
    protected $connection = 'sqlsrv';

    use HasFactory;

    /**
     * Get five oldest order data when type is Non-Racikan
     *
     * @param string $startDate, $endDate, $location
     * @return array
     */
    function getFiveOldestOrderNonRacikanRajal($startDate, $endDate, $location)
    {
        $queryNonRacikan =
            DB::table('PrescriptionOrderHd as poh')
            ->distinct()
            ->select(DB::raw("
            poh.DispensaryServiceUnitID,
            sud.ServiceUnitName as 'Dispensary',
            su.ServiceUnitName as 'Poli',
            CASE
                WHEN poh.SendOrderDateTime IS NOT NULL
                    THEN DATEDIFF(SECOND, poh.SendOrderDateTime, CURRENT_TIMESTAMP)
                WHEN pch.ProposedDate IS NOT NULL
                    THEN DATEDIFF(SECOND, pch.ProposedDate, CURRENT_TIMESTAMP)
                ELSE
                    NULL
            END AS 'DurationMinutes',
            r.RegistrationNo,
            p.MedicalNo,
            p.FullName as 'PatientName',
            pm.FullName as 'Nama Dokter',
            ua.FullName as 'Orderer',
            sc2.StandardCodeName as 'StatusOrder',
            poh.PrescriptionOrderID,
            poh.PrescriptionOrderNo,
            pch.TransactionID,
            pch.TransactionNo,
            poh.SendOrderDateTime,
            pch.ProposedDate as ProposedDateTime,
            ua2.FullName as 'User Propose',
            poh.ClosedDate as ClosedDateFarmasi,
            poh.ClosedTime as ClosedTimeFarmasi,
            bp.BusinessPartnerID,
            c.GCCustomerType,
            sc.StandardCodeName as 'Penjamin',
            bp.BusinessPartnerName,
            pch.TotalAmount,
            poh.CreatedDate,
            sc3.StandardCodeName as 'StatusTransaksi',
            poh.PrescriptionDate,
            pod.IsCompound,
            'NON RACIKAN' as 'JenisResep'
        "))
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
            ->whereNotIn('r.MRN', [10, 527556])
            ->whereNotIn('poh.GCTransactionStatus', ['X121^999'])
            ->whereNull('poh.ClosedDate')
            ->whereNotExists(function ($query) use ($startDate, $endDate, $location) {
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
                    ->whereNotIn('poh2.GCTransactionStatus', ['X121^999'])
                    ->whereNot('pch.TotalAmount', '=', '.00')
                    ->whereNotIn('r.MRN', [10, 527556])
                    ->where('pod.IsCompound', 1)
                    ->whereRaw('poh2.PrescriptionOrderID = poh.PrescriptionOrderID')
                    ->whereNull('poh.ClosedDate');
            });

        $results = $queryNonRacikan->orderBy('DurationMinutes', 'desc')->limit(5)->get();

        return $results;
    }

    /**
     * Get five oldest order data when type is Racikan
     *
     * @param string $startDate, $endDate, $location
     * @return array
     */
    function getFiveOldestOrderRacikanRajal($startDate, $endDate, $location)
    {
        $queryRacikan = DB::table('PrescriptionOrderHd as poh')
            ->distinct()
            ->select(DB::raw("poh.DispensaryServiceUnitID,
            sud.ServiceUnitName as 'Dispensary',
            su.ServiceUnitName as 'Poli',
            CASE
                WHEN poh.SendOrderDateTime IS NOT NULL
                    THEN DATEDIFF(SECOND, poh.SendOrderDateTime, CURRENT_TIMESTAMP)
                WHEN pch.ProposedDate IS NOT NULL
                    THEN DATEDIFF(SECOND, pch.ProposedDate, CURRENT_TIMESTAMP)
                ELSE
                    NULL
            END AS 'DurationMinutes',
            r.RegistrationNo,
            p.MedicalNo,
            p.FullName as 'PatientName',
            pm.FullName as 'Nama Dokter',
            ua.FullName as 'Orderer',
            sc2.StandardCodeName as 'StatusOrder',
            poh.PrescriptionOrderID,
            poh.PrescriptionOrderNo,
            pch.TransactionID,
            pch.TransactionNo,
            poh.SendOrderDateTime,
            pch.ProposedDate as ProposedDateTime,
            ua2.FullName as 'User Propose',
            poh.ClosedDate as ClosedDateFarmasi,
            poh.ClosedTime as ClosedTimeFarmasi,
            bp.BusinessPartnerID,
            c.GCCustomerType,
            sc.StandardCodeName as 'Penjamin',
            bp.BusinessPartnerName,
            pch.TotalAmount,
            poh.CreatedDate,
            sc3.StandardCodeName as 'StatusTransaksi',
            poh.PrescriptionDate,
            pod.IsCompound,
            'RACIKAN' as 'JenisResep'
        "))
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
            ->whereNotIn('r.MRN', [10, 527556])
            ->whereNotIn('poh.GCTransactionStatus', ['X121^999'])
            ->where('pod.IsCompound', 1)
            ->whereNull('poh.ClosedDate');

        $results = $queryRacikan->orderBy('DurationMinutes', 'desc')->limit(5)->get();
        return $results;
    }

    /**
     * Get list order when location in Dispensary Rawat Jalan
     *
     * @param string $startDate, $endDate, $customerType, $location, $statusOrder, $search, $sortBy, $jenisOrder
     * @param int $perPage
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
        $queryRacikan = DB::table('PrescriptionOrderHd as poh')
            ->select(DB::raw("
            DISTINCT poh.DispensaryServiceUnitID,
            sud.ServiceUnitName as 'Dispensary',
            su.ServiceUnitName as 'Poli',
            r.RegistrationNo,
            p.MedicalNo,
            p.FullName as 'PatientName',
            pm.FullName as 'Nama Dokter',
            ua.FullName as 'Orderer',
            sc2.StandardCodeName as 'StatusOrder',
            poh.PrescriptionOrderID,
            poh.PrescriptionOrderNo,
            pch.TransactionID,
            pch.TransactionNo,
            poh.SendOrderDateTime,
            pch.ProposedDate as ProposedDateTime,
            ua2.FullName as 'User Propose',
            poh.ClosedDate as ClosedDateFarmasi,
            poh.ClosedTime as ClosedTimeFarmasi,
            bp.BusinessPartnerID,
            c.GCCustomerType,
            sc.StandardCodeName as 'Penjamin',
            bp.BusinessPartnerName,
            pch.TotalAmount,
            poh.CreatedDate,
            sc3.StandardCodeName as 'StatusTransaksi',
            poh.PrescriptionDate,
            pod.IsCompound,
            'RACIKAN' as 'JenisResep'
        "))
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
            ->join('StandardCode as sc', function ($join) {
                $join->on('c.GCCustomerType', '=', 'sc.StandardCodeID')
                    ->whereNotIn('c.GCCustomerType', ['X004^250', 'X004^201', 'X004^400']);
            })
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
            ->whereNotIn('r.MRN', [10, 527556])
            ->whereNotIn('poh.GCTransactionStatus', ['X121^999']);

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
        if ($customerType === '1') {
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

        //Filter berdasarkan jenis order = racikan
        $queryRacikan->where('pod.IsCompound', 1);

        // Query untuk resep non-racikan
        $queryNonRacikan = DB::table('PrescriptionOrderHd as poh')
            ->select(DB::raw("
            DISTINCT poh.DispensaryServiceUnitID,
            sud.ServiceUnitName as 'Dispensary',
            su.ServiceUnitName as 'Poli',
            r.RegistrationNo,
            p.MedicalNo,
            p.FullName as 'PatientName',
            pm.FullName as 'Nama Dokter',
            ua.FullName as 'Orderer',
            sc2.StandardCodeName as 'StatusOrder',
            poh.PrescriptionOrderID,
            poh.PrescriptionOrderNo,
            pch.TransactionID,
            pch.TransactionNo,
            poh.SendOrderDateTime,
            pch.ProposedDate as ProposedDateTime,
            ua2.FullName as 'User Propose',
            poh.ClosedDate as ClosedDateFarmasi,
            poh.ClosedTime as ClosedTimeFarmasi,
            bp.BusinessPartnerID,
            c.GCCustomerType,
            sc.StandardCodeName as 'Penjamin',
            bp.BusinessPartnerName,
            pch.TotalAmount,
            poh.CreatedDate,
            sc3.StandardCodeName as 'StatusTransaksi',
            poh.PrescriptionDate,
            pod.IsCompound,
            'NON RACIKAN' as 'JenisResep'
        "))
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
            ->join('StandardCode as sc', function ($join) {
                $join->on('c.GCCustomerType', '=', 'sc.StandardCodeID')
                    ->whereNotIn('c.GCCustomerType', ['X004^250', 'X004^201', 'X004^400']);
            })
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
            ->whereNot('pch.TotalAmount', '=', '.00') //Tidak di ambil
            ->whereNotIn('r.MRN', [10, 527556]) // MRN 10 dan 527556 adalah MRN dari Klink Teduh
            ->whereNotIn('poh.GCTransactionStatus', ['X121^999']) //Status Transaksi di void
            ->whereNotExists(function ($query) use ($startDate, $endDate, $location, $customerType, $statusOrder) {
                //Cari order yang tidak mengandung racikan
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
                    ->whereNotIn('poh2.GCTransactionStatus', ['X121^999'])
                    ->whereNot('pch.TotalAmount', '=', '.00')
                    ->whereNotIn('r.MRN', [10, 527556])
                    ->where('pod.IsCompound', 1)
                    ->whereRaw('poh2.PrescriptionOrderID = poh.PrescriptionOrderID');

                if ($customerType === '1') {
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

        if ($customerType === '1') {
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


    /**
     * Get summary report of order data group by Dispensary
     *
     * @param string $startDate, $endDate
     * @return array
     */
    function getSummaryOrderPharmacyGroupByLocation($startDate, $endDate)
    {
        // Subquery untuk Racikan
        $racikan = DB::table('PrescriptionOrderHd as poh')
            ->select(DB::raw("
            DISTINCT poh.DispensaryServiceUnitID,
            CASE
                WHEN (sud.ServiceUnitName = 'FARMASI RAWAT INAP NON UDD')
                    THEN 'FARMASI RANAP NON UDD'
                ELSE
                    sud.ServiceUnitName
                END
            AS 'Dispensary',
            CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME) AS 'ClosedDateTime',
            CASE
                WHEN (poh.ClosedDate IS NOT NULL AND poh.SendOrderDateTime IS NOT NULL)
                    THEN DATEDIFF(SECOND, poh.SendOrderDateTime, CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME))
                WHEN (poh.ClosedDate IS NOT NULL AND pch.ProposedDate IS NOT NULL)
                    THEN DATEDIFF(SECOND, pch.ProposedDate, CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME))
                ELSE
                    NULL
            END AS 'DurationMinutes',
            su.ServiceUnitName as 'Poli',
            r.RegistrationNo,
            p.MedicalNo,
            p.FullName as 'PatientName',
            pm.FullName as 'Nama Dokter',
            ua.FullName as 'Orderer',
            sc2.StandardCodeName as 'StatusOrder',
            poh.PrescriptionOrderID,
            poh.PrescriptionOrderNo,
            pch.TransactionID,
            pch.TransactionNo,
            poh.SendOrderDateTime,
            pch.ProposedDate as ProposedDateTime,
            ua2.FullName as 'User Propose',
            poh.ClosedDate as ClosedDateFarmasi,
            poh.ClosedTime as ClosedTimeFarmasi,
            bp.BusinessPartnerID,
            c.GCCustomerType,
            sc.StandardCodeName as 'Penjamin',
            bp.BusinessPartnerName,
            pch.TotalAmount,
            poh.CreatedDate,
            sc3.StandardCodeName as 'StatusTransaksi',
            'RACIKAN' as 'JenisResep'
        "))
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
            ->join('StandardCode as sc', function ($join) {
                $join->on('c.GCCustomerType', '=', 'sc.StandardCodeID')
                    ->whereNotIn('c.GCCustomerType', ['X004^250', 'X004^201', 'X004^400']);
            })
            ->join('StandardCode as sc2', 'poh.GCTransactionStatus', '=', 'sc2.StandardCodeID')
            ->leftJoin('StandardCode as sc3', 'pch.GCTransactionStatus', '=', 'sc3.StandardCodeID')
            ->join('HealthcareServiceUnit as hsu', 'cv.HealthcareServiceUnitID', '=', 'hsu.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as su', 'hsu.ServiceUnitID', '=', 'su.ServiceUnitID')
            ->join('HealthcareServiceUnit as hsud', 'poh.DispensaryServiceUnitID', '=', 'hsud.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as sud', 'hsud.ServiceUnitID', '=', 'sud.ServiceUnitID')
            ->join('ParamedicMaster as pm', 'poh.ParamedicID', '=', 'pm.ParamedicID')
            ->join('UserAttribute as ua', 'poh.CreatedBy', '=', 'ua.UserID')
            ->leftJoin('UserAttribute as ua2', 'pch.ProposedBy', '=', 'ua2.UserID')
            ->whereBetween('poh.CreatedDate', [$startDate, $endDate])
            ->whereNotIn('poh.GCTransactionStatus', ['X121^999'])
            ->whereNot('pch.TotalAmount', '=', '.00')
            ->whereNotIn('r.MRN', [10, 527556])
            ->whereNotIn('poh.DispensaryServiceUnitID', [101, 133])
            ->where('pod.IsCompound', 1);

        // Subquery untuk Non-Racikan
        $nonRacikan = DB::table('PrescriptionOrderHd as poh')
            ->select(DB::raw("
            DISTINCT poh.DispensaryServiceUnitID,
            CASE
                WHEN (sud.ServiceUnitName = 'FARMASI RAWAT INAP NON UDD')
                    THEN 'FARMASI RANAP NON UDD'
                ELSE
                    sud.ServiceUnitName
            END AS 'Dispensary',
            CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME) AS 'ClosedDateTime',
            CASE
                WHEN (poh.ClosedDate IS NOT NULL AND poh.SendOrderDateTime IS NOT NULL)
                    THEN DATEDIFF(SECOND, poh.SendOrderDateTime, CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME))
                WHEN (poh.ClosedDate IS NOT NULL AND pch.ProposedDate IS NOT NULL)
                    THEN DATEDIFF(SECOND, pch.ProposedDate, CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME))
                ELSE
                    NULL
            END AS 'DurationMinutes',
            su.ServiceUnitName as 'Poli',
            r.RegistrationNo,
            p.MedicalNo,
            p.FullName as 'PatientName',
            pm.FullName as 'Nama Dokter',
            ua.FullName as 'User Buat',
            sc2.StandardCodeName as 'StatusOrder',
            poh.PrescriptionOrderID,
            poh.PrescriptionOrderNo,
            pch.TransactionID,
            pch.TransactionNo,
            poh.SendOrderDateTime,
            pch.ProposedDate as ProposedDateTime,
            ua2.FullName as 'User Propose',
            poh.ClosedDate as ClosedDateFarmasi,
            poh.ClosedTime as ClosedTimeFarmasi,
            bp.BusinessPartnerID,
            c.GCCustomerType,
            sc.StandardCodeName as 'Penjamin',
            bp.BusinessPartnerName,
            pch.TotalAmount,
            poh.CreatedDate,
            sc3.StandardCodeName as 'StatusTransaksi',
            'NON RACIKAN' as 'JenisResep'
        "))
            ->leftJoin('PatientChargesHd as pch', function ($join) {
                $join->on('poh.PrescriptionOrderID', '=', 'pch.PrescriptionOrderID')
                    ->whereNotIn('pch.GCTransactionStatus', ['X121^999']);
            })
            ->join('ConsultVisit as cv', 'poh.VisitID', '=', 'cv.VisitID')
            ->join('Registration as r', 'cv.RegistrationID', '=', 'r.RegistrationID')
            ->join('Patient as p', 'r.MRN', '=', 'p.MRN')
            ->join('BusinessPartners as bp', 'r.BusinessPartnerID', '=', 'bp.BusinessPartnerID')
            ->join('Customer as c', 'bp.BusinessPartnerID', '=', 'c.BusinessPartnerID')
            ->join('StandardCode as sc', function ($join) {
                $join->on('c.GCCustomerType', '=', 'sc.StandardCodeID')
                    ->whereNotIn('c.GCCustomerType', ['X004^250', 'X004^201', 'X004^400']);
            })
            ->join('StandardCode as sc2', 'poh.GCTransactionStatus', '=', 'sc2.StandardCodeID')
            ->leftJoin('StandardCode as sc3', 'pch.GCTransactionStatus', '=', 'sc3.StandardCodeID')
            ->join('HealthcareServiceUnit as hsu', 'cv.HealthcareServiceUnitID', '=', 'hsu.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as su', 'hsu.ServiceUnitID', '=', 'su.ServiceUnitID')
            ->join('HealthcareServiceUnit as hsud', 'poh.DispensaryServiceUnitID', '=', 'hsud.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as sud', 'hsud.ServiceUnitID', '=', 'sud.ServiceUnitID')
            ->join('ParamedicMaster as pm', 'poh.ParamedicID', '=', 'pm.ParamedicID')
            ->join('UserAttribute as ua', 'poh.CreatedBy', '=', 'ua.UserID')
            ->leftJoin('UserAttribute as ua2', 'pch.ProposedBy', '=', 'ua2.UserID')
            ->whereBetween('poh.CreatedDate', [$startDate, $endDate])
            ->whereNotIn('poh.GCTransactionStatus', ['X121^999'])
            ->whereNot('pch.TotalAmount', '=', '.00')
            ->whereNotIn('poh.DispensaryServiceUnitID', [101, 133])
            ->whereNotIn('r.MRN', [10, 527556])
            ->whereNotExists(function ($query) use ($startDate, $endDate) {
                $query->select(DB::raw(1))
                    ->from('PrescriptionOrderHd as poh_inner')
                    ->join('PrescriptionOrderDt as pod_inner', 'poh_inner.PrescriptionOrderID', '=', 'pod_inner.PrescriptionOrderID')
                    ->leftJoin('PatientChargesHd as pch_inner', function ($join) {
                        $join->on('poh_inner.PrescriptionOrderID', '=', 'pch_inner.PrescriptionOrderID')
                            ->whereNotIn('pch_inner.GCTransactionStatus', ['X121^999']);
                    })
                    ->join('ConsultVisit as cv_inner', 'poh_inner.VisitID', '=', 'cv_inner.VisitID')
                    ->join('Registration as r_inner', 'cv_inner.RegistrationID', '=', 'r_inner.RegistrationID')
                    ->join('BusinessPartners as bp_inner', 'r_inner.BusinessPartnerID', '=', 'bp_inner.BusinessPartnerID')
                    ->join('Customer as c_inner', 'bp_inner.BusinessPartnerID', '=', 'c_inner.BusinessPartnerID')
                    ->whereBetween('poh.CreatedDate', [$startDate, $endDate])
                    ->whereNotIn('poh_inner.GCTransactionStatus', ['X121^999'])
                    ->whereNot('pch.TotalAmount', '=', '.00')
                    ->whereNotIn('r.MRN', [10, 527556])
                    ->where('pod_inner.IsCompound', 1)
                    ->whereNotIn('poh.DispensaryServiceUnitID', [101, 133])
                    ->whereColumn('poh.PrescriptionOrderID', 'poh_inner.PrescriptionOrderID');
            });

        // Gabungkan kedua subquery dengan UNION ALL
        $combinedOrders = $racikan->unionAll($nonRacikan);

        // Lakukan agregasi pada hasil gabungan dari subquery
        $results = DB::table(DB::raw("({$combinedOrders->toSql()}) as combined"))
            ->mergeBindings($combinedOrders)
            ->selectRaw("
                combined.Dispensary AS LocationName,
                combined.DispensaryServiceUnitID,
                COUNT(*) AS TotalOrder,
                SUM(
                    CASE
                        WHEN combined.DurationMinutes IS NOT NULL
                            THEN 1
                        ELSE 0
                    END
                ) AS TotalOrderSelesai,
                SUM(
                    CASE
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationMinutes <= 3600 AND combined.JenisResep = 'RACIKAN')
                            THEN 1
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationMinutes <= 1800 AND combined.JenisResep = 'NON RACIKAN')
                            THEN 1
                        ELSE 0
                    END
                ) AS TotalOrderOnTime,
                SUM(
                    CASE
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationMinutes > 3600 AND combined.JenisResep = 'RACIKAN')
                            THEN 1
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationMinutes > 1800 AND combined.JenisResep = 'NON RACIKAN')
                            THEN 1
                        ELSE 0
                    END
                ) AS TotalOrderLateTime,
                AVG(
                    CASE
                        WHEN (combined.JenisResep = 'RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                            THEN combined.DurationMinutes
                        END
                ) AS AverageDurationRacikan,
                AVG(
                    CASE
                        WHEN (combined.JenisResep = 'NON RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                    THEN
                        combined.DurationMinutes
                    END
                ) AS AverageDurationNonRacikan,
                SUM(
                    CASE
                        WHEN (combined.JenisResep = 'RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                            THEN combined.DurationMinutes
                    END
                ) AS SumDurationRacikan,
                SUM(
                    CASE
                        WHEN (combined.JenisResep = 'NON RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                    THEN
                        combined.DurationMinutes
                    END
                ) AS SumDurationNonRacikan,
                COUNT(
                    CASE
                        WHEN (combined.JenisResep = 'RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                            THEN combined.DurationMinutes
                        END
                ) AS CountDurationRacikan,
                COUNT(
                    CASE
                        WHEN (combined.JenisResep = 'NON RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                            THEN
                                combined.DurationMinutes
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
            ->groupBy('combined.DispensaryServiceUnitID')
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
        // Subquery untuk Racikan
        $racikan = DB::table('PrescriptionOrderHd as poh')
            ->distinct()
            ->select(DB::raw("
                poh.DispensaryServiceUnitID,
                sud.ServiceUnitName as 'Dispensary',
                CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME) AS 'ClosedDateTime',
                CASE
                    WHEN (poh.ClosedDate IS NOT NULL AND poh.SendOrderDateTime IS NOT NULL)
                        THEN DATEDIFF(SECOND, poh.SendOrderDateTime, CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME))
                    WHEN (poh.ClosedDate IS NOT NULL AND pch.ProposedDate IS NOT NULL)
                        THEN DATEDIFF(SECOND, pch.ProposedDate, CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME))
                    ELSE
                        NULL
                END AS 'DurationMinutes',
                CASE
                    WHEN c.GCCustomerType = 'X004^500'
                        THEN 'BPJS - Kemenkes'
                    WHEN c.GCCustomerType IN ('X004^999', 'X004^251', 'X004^300')
                        THEN 'Personal'
                    WHEN c.GCCustomerType IN ('X004^100', 'X004^200')
                        THEN 'Asuransi'
                END AS PenjaminGroup,
                su.ServiceUnitName as 'Poli',
                r.RegistrationNo,
                p.MedicalNo,
                p.FullName as 'PatientName',
                pm.FullName as 'Nama Dokter',
                ua.FullName as 'Orderer',
                sc2.StandardCodeName as 'StatusOrder',
                poh.PrescriptionOrderID,
                poh.PrescriptionOrderNo,
                pch.TransactionID,
                pch.TransactionNo,
                poh.SendOrderDateTime,
                pch.ProposedDate as ProposedDateTime,
                ua2.FullName as 'User Propose',
                poh.ClosedDate as ClosedDateFarmasi,
                poh.ClosedTime as ClosedTimeFarmasi,
                bp.BusinessPartnerID,
                c.GCCustomerType,
                sc.StandardCodeName as 'Penjamin',
                bp.BusinessPartnerName,
                pch.TotalAmount,
                poh.CreatedDate,
                sc3.StandardCodeName as 'StatusTransaksi',
                'RACIKAN' as 'JenisResep'"))
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
            ->join('StandardCode as sc', function ($join) {
                $join->on('c.GCCustomerType', '=', 'sc.StandardCodeID')
                    ->whereNotIn('c.GCCustomerType', ['X004^250', 'X004^201', 'X004^400']);
            })
            ->join('StandardCode as sc2', 'poh.GCTransactionStatus', '=', 'sc2.StandardCodeID')
            ->leftJoin('StandardCode as sc3', 'pch.GCTransactionStatus', '=', 'sc3.StandardCodeID')
            ->join('HealthcareServiceUnit as hsu', 'cv.HealthcareServiceUnitID', '=', 'hsu.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as su', 'hsu.ServiceUnitID', '=', 'su.ServiceUnitID')
            ->join('HealthcareServiceUnit as hsud', 'poh.DispensaryServiceUnitID', '=', 'hsud.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as sud', 'hsud.ServiceUnitID', '=', 'sud.ServiceUnitID')
            ->join('ParamedicMaster as pm', 'poh.ParamedicID', '=', 'pm.ParamedicID')
            ->join('UserAttribute as ua', 'poh.CreatedBy', '=', 'ua.UserID')
            ->leftJoin('UserAttribute as ua2', 'pch.ProposedBy', '=', 'ua2.UserID')
            ->whereBetween('poh.CreatedDate', [$startDate, $endDate])
            ->where('poh.DispensaryServiceUnitID', $location)
            ->whereNot('pch.TotalAmount', '=', '.00')
            ->whereNotIn('r.MRN', [10, 527556])
            ->whereNotIn('poh.GCTransactionStatus', ['X121^999'])
            ->where('pod.IsCompound', 1);

        // Subquery untuk Non-Racikan
        $nonRacikan = DB::table('PrescriptionOrderHd as poh')
            ->distinct()
            ->select(DB::raw("
                poh.DispensaryServiceUnitID,
                sud.ServiceUnitName as 'Dispensary',
                CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME) AS 'ClosedDateTime',
                CASE
                    WHEN (poh.ClosedDate IS NOT NULL AND poh.SendOrderDateTime IS NOT NULL)
                        THEN DATEDIFF(SECOND, poh.SendOrderDateTime, CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME))
                    WHEN (poh.ClosedDate IS NOT NULL AND pch.ProposedDate IS NOT NULL)
                        THEN DATEDIFF(SECOND, pch.ProposedDate, CAST(poh.ClosedDate AS DATETIME) + CAST(poh.ClosedTime AS TIME))
                    ELSE
                        NULL
                END AS 'DurationMinutes',
                CASE
                    WHEN c.GCCustomerType = 'X004^500'
                        THEN 'BPJS - Kemenkes'
                    WHEN c.GCCustomerType IN ('X004^999', 'X004^251', 'X004^300')
                        THEN 'Personal'
                    WHEN c.GCCustomerType IN ('X004^100', 'X004^200')
                        THEN 'Asuransi'
                END AS PenjaminGroup,
                su.ServiceUnitName as 'Poli',
                r.RegistrationNo,
                p.MedicalNo,
                p.FullName as 'PatientName',
                pm.FullName as 'Nama Dokter',
                ua.FullName as 'User Buat',
                sc2.StandardCodeName as 'StatusOrder',
                poh.PrescriptionOrderID,
                poh.PrescriptionOrderNo,
                pch.TransactionID,
                pch.TransactionNo,
                poh.SendOrderDateTime,
                pch.ProposedDate as ProposedDateTime,
                ua2.FullName as 'User Propose',
                poh.ClosedDate as ClosedDateFarmasi,
                poh.ClosedTime as ClosedTimeFarmasi,
                bp.BusinessPartnerID,
                c.GCCustomerType,
                sc.StandardCodeName as 'Penjamin',
                bp.BusinessPartnerName,
                pch.TotalAmount,
                poh.CreatedDate,
                sc3.StandardCodeName as 'StatusTransaksi',
                'NON RACIKAN' as 'JenisResep'
            "))
            ->leftJoin('PatientChargesHd as pch', function ($join) {
                $join->on('poh.PrescriptionOrderID', '=', 'pch.PrescriptionOrderID')
                    ->whereNotIn('pch.GCTransactionStatus', ['X121^999']);
            })
            ->join('ConsultVisit as cv', 'poh.VisitID', '=', 'cv.VisitID')
            ->join('Registration as r', 'cv.RegistrationID', '=', 'r.RegistrationID')
            ->join('Patient as p', 'r.MRN', '=', 'p.MRN')
            ->join('BusinessPartners as bp', 'r.BusinessPartnerID', '=', 'bp.BusinessPartnerID')
            ->join('Customer as c', 'bp.BusinessPartnerID', '=', 'c.BusinessPartnerID')
            ->join('StandardCode as sc', function ($join) {
                $join->on('c.GCCustomerType', '=', 'sc.StandardCodeID')
                    ->whereNotIn('c.GCCustomerType', ['X004^250', 'X004^201', 'X004^400']);
            })
            ->join('StandardCode as sc2', 'poh.GCTransactionStatus', '=', 'sc2.StandardCodeID')
            ->leftJoin('StandardCode as sc3', 'pch.GCTransactionStatus', '=', 'sc3.StandardCodeID')
            ->join('HealthcareServiceUnit as hsu', 'cv.HealthcareServiceUnitID', '=', 'hsu.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as su', 'hsu.ServiceUnitID', '=', 'su.ServiceUnitID')
            ->join('HealthcareServiceUnit as hsud', 'poh.DispensaryServiceUnitID', '=', 'hsud.HealthcareServiceUnitID')
            ->join('ServiceUnitMaster as sud', 'hsud.ServiceUnitID', '=', 'sud.ServiceUnitID')
            ->join('ParamedicMaster as pm', 'poh.ParamedicID', '=', 'pm.ParamedicID')
            ->join('UserAttribute as ua', 'poh.CreatedBy', '=', 'ua.UserID')
            ->leftJoin('UserAttribute as ua2', 'pch.ProposedBy', '=', 'ua2.UserID')
            ->whereBetween('poh.CreatedDate', [$startDate, $endDate])
            ->where('poh.DispensaryServiceUnitID', $location)
            ->whereNotIn('poh.GCTransactionStatus', ['X121^999'])
            ->whereNot('pch.TotalAmount', '=', '.00')
            ->whereNotIn('r.MRN', [10, 527556])
            ->whereNotExists(function ($query) use ($startDate, $endDate, $location) {
                $query->select(DB::raw(1))
                    ->from('PrescriptionOrderHd as poh_inner')
                    ->join('PrescriptionOrderDt as pod_inner', 'poh_inner.PrescriptionOrderID', '=', 'pod_inner.PrescriptionOrderID')
                    ->leftJoin('PatientChargesHd as pch_inner', function ($join) {
                        $join->on('poh_inner.PrescriptionOrderID', '=', 'pch_inner.PrescriptionOrderID')
                            ->whereNotIn('pch_inner.GCTransactionStatus', ['X121^999']);
                    })
                    ->join('ConsultVisit as cv_inner', 'poh_inner.VisitID', '=', 'cv_inner.VisitID')
                    ->join('Registration as r_inner', 'cv_inner.RegistrationID', '=', 'r_inner.RegistrationID')
                    ->join('BusinessPartners as bp_inner', 'r_inner.BusinessPartnerID', '=', 'bp_inner.BusinessPartnerID')
                    ->join('Customer as c_inner', 'bp_inner.BusinessPartnerID', '=', 'c_inner.BusinessPartnerID')
                    ->whereBetween('poh.CreatedDate', [$startDate, $endDate])
                    ->where('poh_inner.DispensaryServiceUnitID', $location)
                    ->whereNotIn('poh_inner.GCTransactionStatus', ['X121^999'])
                    ->whereNot('pch.TotalAmount', '=', '.00')
                    ->whereNotIn('r.MRN', [10, 527556])
                    ->where('pod_inner.IsCompound', 1)
                    ->whereColumn('poh.PrescriptionOrderID', 'poh_inner.PrescriptionOrderID');
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
                        WHEN combined.DurationMinutes IS NOT NULL
                            THEN 1
                        ELSE 0
                    END
                ) AS TotalOrderSelesai,
                SUM(
                    CASE
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationMinutes <= 3600 AND combined.JenisResep = 'RACIKAN')
                            THEN 1
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationMinutes <= 1800 AND combined.JenisResep = 'NON RACIKAN')
                            THEN 1
                        ELSE 0
                    END
                ) AS TotalOrderOnTime,
                SUM(
                    CASE
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationMinutes > 3600 AND combined.JenisResep = 'RACIKAN')
                            THEN 1
                        WHEN (combined.ClosedDateFarmasi IS NOT NULL AND combined.DurationMinutes > 1800 AND combined.JenisResep = 'NON RACIKAN')
                            THEN 1
                        ELSE 0
                    END
                ) AS TotalOrderLateTime,
                AVG(
                    CASE
                        WHEN (combined.JenisResep = 'RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                            THEN combined.DurationMinutes
                        END
                ) AS AverageDurationRacikan,
                AVG(
                    CASE
                        WHEN (combined.JenisResep = 'NON RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                    THEN
                        combined.DurationMinutes
                    END
                ) AS AverageDurationNonRacikan,
                SUM(
                    CASE
                        WHEN (combined.JenisResep = 'RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                            THEN combined.DurationMinutes
                    END
                ) AS SumDurationRacikan,
                SUM(
                    CASE
                        WHEN (combined.JenisResep = 'NON RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                    THEN
                        combined.DurationMinutes
                    END
                ) AS SumDurationNonRacikan,
                COUNT(
                    CASE
                        WHEN (combined.JenisResep = 'RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                            THEN combined.DurationMinutes
                        END
                ) AS CountDurationRacikan,
                COUNT(
                    CASE
                        WHEN (combined.JenisResep = 'NON RACIKAN' AND combined.DurationMinutes IS NOT NULL)
                            THEN
                                combined.DurationMinutes
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
}
